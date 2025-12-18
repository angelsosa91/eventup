<?php

namespace App\Http\Controllers;

use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FamilyController extends Controller
{
    public function index()
    {
        return view('academic.families.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Family::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('billing_name', 'like', "%{$search}%")
                        ->orWhere('billing_ruc', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $items = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $items->map(function ($item) {
            /** @var \App\Models\Family $item */
            return [
                'id' => $item->id,
                'name' => $item->name,
                'budget_type' => $item->budget_type,
                'billing_name' => $item->billing_name,
                'billing_ruc' => $item->billing_ruc,
                'students_count' => $item->students()->count(),
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function list()
    {
        $items = Family::orderBy('name')
            ->get(['id', 'name']);

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'budget_type' => 'required|in:family,individual',
            'billing_name' => 'nullable|string|max:255',
            'billing_ruc' => 'nullable|string|max:50',
            'billing_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = Family::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Familia creada exitosamente',
            'data' => $item,
        ]);
    }

    public function show(Family $family)
    {
        return response()->json($family);
    }

    public function update(Request $request, Family $family)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'budget_type' => 'required|in:family,individual',
            'billing_name' => 'nullable|string|max:255',
            'billing_ruc' => 'nullable|string|max:50',
            'billing_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $family->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Familia actualizada exitosamente',
            'data' => $family,
        ]);
    }

    public function destroy(Family $family)
    {
        if ($family->students()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la familia. Tiene alumnos asociados.',
            ], 400);
        }

        $family->delete();

        return response()->json([
            'success' => true,
            'message' => 'Familia eliminada exitosamente',
        ]);
    }
}
