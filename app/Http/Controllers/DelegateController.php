<?php

namespace App\Http\Controllers;

use App\Models\Delegate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DelegateController extends Controller
{
    public function index()
    {
        return view('academic.delegates.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Delegate::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $items = $query->skip(($page - 1) * $rows)->take($rows)->get();

        return response()->json([
            'total' => $total,
            'rows' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = Delegate::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Delegado creado exitosamente',
            'data' => $item,
        ]);
    }

    public function show(Delegate $delegate)
    {
        return response()->json($delegate);
    }

    public function update(Request $request, Delegate $delegate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $delegate->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Delegado actualizado exitosamente',
            'data' => $delegate,
        ]);
    }

    public function destroy(Delegate $delegate)
    {
        $delegate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Delegado eliminado exitosamente',
        ]);
    }
}
