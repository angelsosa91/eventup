<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        return view('expense-categories.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = ExpenseCategory::with('account')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $categories = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'account_id' => $category->account_id,
                'account_name' => $category->account ? $category->account->code . ' - ' . $category->account->name : 'Sin cuenta',
                'expenses_count' => $category->expenses()->count(),
                'is_active' => $category->is_active,
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function list(Request $request)
    {
        $search = $request->get('q', '');

        $categories = ExpenseCategory::where('is_active', true)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:account_chart,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['tenant_id'] = auth()->user()->tenant_id;

        $category = ExpenseCategory::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Categoría de gasto creada exitosamente',
            'data' => $category,
        ]);
    }

    public function show(ExpenseCategory $expenseCategory)
    {
        return response()->json($expenseCategory);
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'account_id' => 'nullable|exists:account_chart,id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expenseCategory->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Categoría de gasto actualizada exitosamente',
            'data' => $expenseCategory,
        ]);
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        try {
            $expenseCategory->delete();
            return response()->json([
                'success' => true,
                'message' => 'Categoría de gasto eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar la categoría. Tiene gastos asociados.',
            ], 400);
        }
    }
}
