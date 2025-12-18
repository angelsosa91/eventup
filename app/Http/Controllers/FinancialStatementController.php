<?php

namespace App\Http\Controllers;

use App\Models\AccountChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinancialStatementController extends Controller
{
    /**
     * Mostrar Balance General
     */
    public function balanceSheet(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $date = $request->get('date', now()->format('Y-m-d'));

        // Obtener cuentas con saldos
        $assets = $this->getAccountsByType($tenantId, 'asset', $date);
        $liabilities = $this->getAccountsByType($tenantId, 'liability', $date);
        $equity = $this->getAccountsByType($tenantId, 'equity', $date);

        // Calcular totales
        $totalAssets = $this->calculateTotal($assets);
        $totalLiabilities = $this->calculateTotal($liabilities);
        $totalEquity = $this->calculateTotal($equity);

        // Calcular resultado del ejercicio (ingresos - gastos)
        $income = $this->getAccountsByType($tenantId, 'income', $date);
        $expenses = $this->getAccountsByType($tenantId, 'expense', $date);
        $totalIncome = $this->calculateTotal($income);
        $totalExpenses = $this->calculateTotal($expenses);
        $netIncome = $totalIncome - $totalExpenses;

        // Agregar resultado del ejercicio al patrimonio
        $totalEquity += $netIncome;

        return view('accounting.reports.balance-sheet', compact(
            'assets',
            'liabilities',
            'equity',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'netIncome',
            'date'
        ));
    }

    /**
     * Mostrar Estado de Resultados
     */
    public function incomeStatement(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Obtener ingresos y gastos
        $income = $this->getAccountsByType($tenantId, 'income', $endDate, $startDate);
        $expenses = $this->getAccountsByType($tenantId, 'expense', $endDate, $startDate);

        // Calcular totales
        $totalIncome = $this->calculateTotal($income);
        $totalExpenses = $this->calculateTotal($expenses);
        $netIncome = $totalIncome - $totalExpenses;

        return view('accounting.reports.income-statement', compact(
            'income',
            'expenses',
            'totalIncome',
            'totalExpenses',
            'netIncome',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Obtener cuentas por tipo con estructura jerárquica
     */
    private function getAccountsByType($tenantId, $accountType, $endDate, $startDate = null)
    {
        $accounts = AccountChart::where('tenant_id', $tenantId)
            ->where('account_type', $accountType)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        // Construir árbol jerárquico
        return $this->buildAccountTree($accounts, $endDate, $startDate);
    }

    /**
     * Construir árbol jerárquico de cuentas
     */
    private function buildAccountTree($accounts, $endDate, $startDate = null)
    {
        $tree = [];
        $accountsById = $accounts->keyBy('id');

        foreach ($accounts as $account) {
            // Calcular el saldo de la cuenta
            if ($startDate) {
                // Para estado de resultados (período)
                $balance = $this->getAccountBalanceForPeriod($account->id, $startDate, $endDate);
            } else {
                // Para balance general (acumulado hasta la fecha)
                $balance = $account->current_balance;
            }

            $accountData = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'level' => $account->level,
                'is_detail' => $account->is_detail,
                'nature' => $account->nature,
                'balance' => $balance,
                'children' => []
            ];

            if ($account->parent_id === null) {
                $tree[] = $accountData;
            } else {
                // Agregar como hijo
                $this->addToParent($tree, $account->parent_id, $accountData);
            }
        }

        // Calcular balances de cuentas padre
        $this->calculateParentBalances($tree);

        return $tree;
    }

    /**
     * Agregar cuenta a su padre en el árbol
     */
    private function addToParent(&$tree, $parentId, $accountData)
    {
        foreach ($tree as &$node) {
            if ($node['id'] == $parentId) {
                $node['children'][] = $accountData;
                return true;
            }
            if (!empty($node['children'])) {
                if ($this->addToParent($node['children'], $parentId, $accountData)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Calcular balances de cuentas padre (suma de hijos)
     */
    private function calculateParentBalances(&$tree)
    {
        foreach ($tree as &$node) {
            if (!empty($node['children'])) {
                $this->calculateParentBalances($node['children']);

                // Sumar balances de hijos
                $node['balance'] = 0;
                foreach ($node['children'] as $child) {
                    $node['balance'] += $child['balance'];
                }
            }
        }
    }

    /**
     * Obtener saldo de una cuenta para un período específico
     */
    private function getAccountBalanceForPeriod($accountId, $startDate, $endDate)
    {
        $result = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entry_lines.account_id', $accountId)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $totalDebit = $result->total_debit ?? 0;
        $totalCredit = $result->total_credit ?? 0;

        return $totalDebit - $totalCredit;
    }

    /**
     * Calcular total de un árbol de cuentas
     */
    private function calculateTotal($accounts)
    {
        $total = 0;
        foreach ($accounts as $account) {
            $total += $account['balance'];
        }
        return $total;
    }
}
