<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Category::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $categories = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'code' => $category->code,
                'description' => $category->description,
                'products_count' => $category->products()->count(),
                'is_active' => $category->is_active,
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function list()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = Category::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Categoría creada exitosamente',
            'data' => $category,
        ]);
    }

    public function show(Category $category)
    {
        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Categoría actualizada exitosamente',
            'data' => $category,
        ]);
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return response()->json([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría. Tiene productos asociados.',
            ], 400);
        }
    }
}
