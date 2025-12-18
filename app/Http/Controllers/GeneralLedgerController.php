<?php

namespace App\Http\Controllers;

use App\Models\AccountChart;
use App\Models\JournalEntryLine;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeneralLedgerController extends Controller
{
    /**
     * Libro Mayor - General Ledger
     */
    public function index()
    {
        return view('accounting.reports.general-ledger');
    }

    /**
     * Obtener datos del Libro Mayor
     */
    public function data(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:account_chart,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $account = AccountChart::findOrFail($request->account_id);

        // Obtener saldo inicial (movimientos antes de la fecha inicial)
        $openingBalance = JournalEntryLine::whereHas('journalEntry', function($q) use ($request) {
                $q->where('tenant_id', Auth::user()->tenant_id)
                  ->where('status', 'posted')
                  ->where('entry_date', '<', $request->date_from);
            })
            ->where('account_id', $request->account_id)
            ->selectRaw('SUM(debit) - SUM(credit) as balance')
            ->value('balance') ?? 0;

        // Ajustar saldo inicial según naturaleza de la cuenta
        if ($account->nature === 'credit') {
            $openingBalance = -$openingBalance;
        }

        // Obtener movimientos del período
        $query = JournalEntryLine::with(['journalEntry'])
            ->whereHas('journalEntry', function($q) use ($request) {
                $q->where('tenant_id', Auth::user()->tenant_id)
                  ->where('status', 'posted')
                  ->whereBetween('entry_date', [$request->date_from, $request->date_to]);
            })
            ->where('account_id', $request->account_id)
            ->orderBy('created_at');

        $movements = $query->get();

        // Calcular saldos acumulados
        $runningBalance = $openingBalance;
        $rows = [];

        foreach ($movements as $movement) {
            $entry = $movement->journalEntry;

            // Calcular el movimiento según la naturaleza de la cuenta
            if ($account->nature === 'debit') {
                $runningBalance += $movement->debit - $movement->credit;
            } else {
                $runningBalance += $movement->credit - $movement->debit;
            }

            $rows[] = [
                'id' => $movement->id,
                'entry_date' => $entry->entry_date->format('Y-m-d'),
                'entry_number' => $entry->entry_number,
                'description' => $movement->description ?: $entry->description,
                'debit' => $movement->debit,
                'credit' => $movement->credit,
                'balance' => $runningBalance,
            ];
        }

        return response()->json([
            'account' => [
                'code' => $account->code,
                'name' => $account->name,
                'nature' => $account->nature,
            ],
            'opening_balance' => $openingBalance,
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

    /**
     * Balance de Comprobación - Trial Balance
     */
    public function trialBalance()
    {
        return view('accounting.reports.trial-balance');
    }

    /**
     * Obtener datos del Balance de Comprobación
     */
    public function trialBalanceData(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        // Obtener todas las cuentas de detalle activas
        $accounts = AccountChart::where('tenant_id', Auth::user()->tenant_id)
            ->where('is_detail', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $totalBalanceDebit = 0;
        $totalBalanceCredit = 0;

        foreach ($accounts as $account) {
            // Obtener movimientos del período
            $movements = JournalEntryLine::whereHas('journalEntry', function($q) use ($request) {
                    $q->where('tenant_id', Auth::user()->tenant_id)
                      ->where('status', 'posted')
                      ->whereBetween('entry_date', [$request->date_from, $request->date_to]);
                })
                ->where('account_id', $account->id)
                ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
                ->first();

            $debit = $movements->total_debit ?? 0;
            $credit = $movements->total_credit ?? 0;

            // Solo incluir cuentas con movimientos
            if ($debit > 0 || $credit > 0) {
                $balance = $debit - $credit;

                // Determinar si el saldo es deudor o acreedor
                $balanceDebit = $balance > 0 ? $balance : 0;
                $balanceCredit = $balance < 0 ? abs($balance) : 0;

                $rows[] = [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'account_type' => $account->account_type,
                    'nature' => $account->nature,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance_debit' => $balanceDebit,
                    'balance_credit' => $balanceCredit,
                ];

                $totalDebit += $debit;
                $totalCredit += $credit;
                $totalBalanceDebit += $balanceDebit;
                $totalBalanceCredit += $balanceCredit;
            }
        }

        return response()->json([
            'rows' => $rows,
            'total' => count($rows),
            'totals' => [
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'balance_debit' => $totalBalanceDebit,
                'balance_credit' => $totalBalanceCredit,
                'is_balanced' => abs($totalDebit - $totalCredit) < 0.01,
            ],
        ]);
    }

    /**
     * Exportar Libro Mayor a Excel/PDF
     */
    public function exportGeneralLedger(Request $request)
    {
        // TODO: Implementar exportación a Excel/PDF
        return response()->json(['message' => 'Exportación pendiente de implementación']);
    }

    /**
     * Exportar Balance de Comprobación a Excel/PDF
     */
    public function exportTrialBalance(Request $request)
    {
        // TODO: Implementar exportación a Excel/PDF
        return response()->json(['message' => 'Exportación pendiente de implementación']);
    }
}
