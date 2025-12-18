<?php

namespace App\Http\Controllers;

use App\Models\AccountChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountChartController extends Controller
{
    public function index()
    {
        return view('accounting.chart.index');
    }

    /**
     * Obtener datos para TreeGrid
     */
    public function tree(Request $request)
    {
        $query = AccountChart::where('tenant_id', Auth::user()->tenant_id);

        // Filtros
        if ($request->has('account_type') && $request->account_type !== '') {
            $query->where('account_type', $request->account_type);
        }

        if ($request->has('is_detail') && $request->is_detail !== '') {
            $query->where('is_detail', $request->is_detail);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        // Obtener todas las cuentas
        $accounts = $query->orderBy('code')->get();

        // Convertir a estructura de árbol
        $tree = $this->buildTree($accounts);

        return response()->json($tree);
    }

    /**
     * Construir estructura de árbol
     */
    private function buildTree($accounts, $parentId = null)
    {
        $branch = [];

        foreach ($accounts as $account) {
            if ($account->parent_id == $parentId) {
                $children = $this->buildTree($accounts, $account->id);

                $node = [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'account_type' => $account->account_type,
                    'nature' => $account->nature,
                    'level' => $account->level,
                    'is_detail' => $account->is_detail,
                    'is_active' => $account->is_active,
                    'opening_balance' => $account->opening_balance,
                    'current_balance' => $account->current_balance,
                    'state' => count($children) > 0 ? 'closed' : 'open',
                ];

                if (count($children) > 0) {
                    $node['children'] = $children;
                }

                $branch[] = $node;
            }
        }

        return $branch;
    }

    /**
     * Obtener cuentas de detalle activas (para combobox)
     */
    public function detailAccounts(Request $request)
    {
        $query = AccountChart::where('tenant_id', Auth::user()->tenant_id)
            ->where('is_detail', true)
            ->where('is_active', true);

        if ($request->has('account_type') && $request->account_type) {
            $query->where('account_type', $request->account_type);
        }

        $accounts = $query->orderBy('code')->get()->map(function($account) {
            return [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->code . ' - ' . $account->name,
            ];
        });

        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:account_chart,id',
            'code' => 'required|string|max:20|unique:account_chart,code,NULL,id,tenant_id,' . Auth::user()->tenant_id,
            'name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,income,expense',
            'nature' => 'required|in:debit,credit',
            'is_detail' => 'required|string',
        ]);

        // Determinar el nivel
        $level = 1;
        if ($request->parent_id) {
            $parent = AccountChart::findOrFail($request->parent_id);
            $level = $parent->level + 1;
        }

        $account = AccountChart::create([
            'tenant_id' => Auth::user()->tenant_id,
            'parent_id' => $request->parent_id,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'account_type' => $request->account_type,
            'nature' => $request->nature,
            'level' => $level,
            'is_detail' => $request->is_detail === 'true' ?? false,
            'is_active' => true,
            'opening_balance' => $request->opening_balance ?? 0,
            'current_balance' => $request->opening_balance ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta creada exitosamente',
            'data' => $account,
        ]);
    }

    public function show($id)
    {
        $account = AccountChart::with('parent')->findOrFail($id);
        return response()->json($account);
    }

    public function update(Request $request, $id)
    {
        $account = AccountChart::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,income,expense',
            'nature' => 'required|in:debit,credit',
            'is_detail' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $account->update([
            'name' => $request->name,
            'description' => $request->description,
            'account_type' => $request->account_type,
            'nature' => $request->nature,
            'is_detail' => $request->is_detail ?? $account->is_detail,
            'is_active' => $request->is_active ?? $account->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta actualizada exitosamente',
        ]);
    }

    public function destroy($id)
    {
        $account = AccountChart::findOrFail($id);

        // Verificar si tiene cuentas hijas
        if ($account->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una cuenta que tiene subcuentas',
            ], 400);
        }

        // Verificar si tiene movimientos
        if ($account->journalEntryLines()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una cuenta que tiene movimientos contables',
            ], 400);
        }

        $account->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta eliminada exitosamente',
        ]);
    }

    /**
     * Generar el siguiente código de cuenta
     */
    public function generateCode(Request $request)
    {
        $parentId = $request->parent_id;
        $code = AccountChart::generateNextCode($parentId, Auth::user()->tenant_id);

        return response()->json(['code' => $code]);
    }
}
