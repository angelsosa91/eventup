<?php

namespace App\Http\Controllers;

use App\Models\ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ParentController extends Controller
{
    public function index()
    {
        return view('academic.parents.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = ParentModel::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $items = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $items->map(function ($item) {
            /** @var ParentModel $item */
            return [
                'id' => $item->id,
                'first_name' => $item->first_name,
                'last_name' => $item->last_name,
                'document_number' => $item->document_number,
                'email' => $item->email,
                'phone' => $item->phone,
                'students_count' => $item->students()->count(),
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = ParentModel::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Padre/Tutor creado exitosamente',
            'data' => $item,
        ]);
    }

    public function show(ParentModel $parent)
    {
        return response()->json($parent);
    }

    public function update(Request $request, ParentModel $parent)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'document_number' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'occupation' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $parent->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Padre/Tutor actualizado exitosamente',
            'data' => $parent,
        ]);
    }

    public function destroy(ParentModel $parent)
    {
        $parent->students()->detach();
        $parent->delete();

        return response()->json([
            'success' => true,
            'message' => 'Padre/Tutor eliminado exitosamente',
        ]);
    }
}
