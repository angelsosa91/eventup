<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankReconciliationLine;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    public function index()
    {
        return view('banks.reconciliations.index');
    }

    public function data(Request $request)
    {
        $query = BankReconciliation::with(['bankAccount', 'reconciledBy']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('reconciliation_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('bankAccount', function($q) use ($request) {
                      $q->where('account_name', 'like', '%' . $request->search . '%')
                        ->orWhere('account_number', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->bank_account_id) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->date_from) {
            $query->where('reconciliation_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('reconciliation_date', '<=', $request->date_to);
        }

        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'desc';
        $query->orderBy($sort, $order);

        $total = $query->count();
        $rows = $query->skip($request->offset ?? 0)
            ->take($request->limit ?? 20)
            ->get();

        return response()->json([
            'total' => $total,
            'rows' => $rows
        ]);
    }

    public function create()
    {
        $bankAccounts = BankAccount::active()->get();
        return view('banks.reconciliations.create', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'reconciliation_date' => 'required|date',
            'statement_start_date' => 'required|date',
            'statement_end_date' => 'required|date|after_or_equal:statement_start_date',
            'opening_balance' => 'required|numeric',
            'closing_balance' => 'required|numeric',
            'transaction_ids' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $tenantId = Auth::user()->tenant_id;

            // Generar número de conciliación
            $reconciliationNumber = BankReconciliation::generateReconciliationNumber($tenantId);

            // Obtener el saldo del sistema en la fecha de conciliación
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);

            // Calcular el saldo del sistema basado en transacciones hasta la fecha final
            $deposits = BankTransaction::where('bank_account_id', $request->bank_account_id)
                ->whereIn('type', ['deposit', 'transfer_in', 'interest'])
                ->where('status', 'completed')
                ->where('transaction_date', '<=', $request->statement_end_date)
                ->sum('amount');

            $withdrawals = BankTransaction::where('bank_account_id', $request->bank_account_id)
                ->whereIn('type', ['withdrawal', 'transfer_out', 'check', 'charge'])
                ->where('status', 'completed')
                ->where('transaction_date', '<=', $request->statement_end_date)
                ->sum('amount');

            $systemBalance = $bankAccount->initial_balance + $deposits - $withdrawals;

            // Crear la conciliación
            $reconciliation = BankReconciliation::create([
                'tenant_id' => $tenantId,
                'bank_account_id' => $request->bank_account_id,
                'reconciliation_number' => $reconciliationNumber,
                'reconciliation_date' => $request->reconciliation_date,
                'statement_start_date' => $request->statement_start_date,
                'statement_end_date' => $request->statement_end_date,
                'opening_balance' => $request->opening_balance,
                'closing_balance' => $request->closing_balance,
                'system_balance' => $systemBalance,
                'difference' => $request->closing_balance - $systemBalance,
                'status' => 'draft',
                'notes' => $request->notes,
            ]);

            // Agregar las líneas de transacciones seleccionadas
            if ($request->transaction_ids && count($request->transaction_ids) > 0) {
                foreach ($request->transaction_ids as $transactionId) {
                    BankReconciliationLine::create([
                        'bank_reconciliation_id' => $reconciliation->id,
                        'bank_transaction_id' => $transactionId,
                        'matched_in_statement' => true,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conciliación bancaria creada exitosamente',
                'id' => $reconciliation->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la conciliación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $reconciliation = BankReconciliation::with([
            'bankAccount',
            'reconciledBy',
            'lines.bankTransaction'
        ])->findOrFail($id);

        return view('banks.reconciliations.show', compact('reconciliation'));
    }

    public function edit($id)
    {
        $reconciliation = BankReconciliation::with(['lines'])->findOrFail($id);

        if ($reconciliation->status !== 'draft') {
            return redirect()->route('bank-reconciliations.show', $id)
                ->with('error', 'Solo se pueden editar conciliaciones en borrador');
        }

        $bankAccounts = BankAccount::active()->get();

        return view('banks.reconciliations.edit', compact('reconciliation', 'bankAccounts'));
    }

    public function update(Request $request, $id)
    {
        $reconciliation = BankReconciliation::findOrFail($id);

        if ($reconciliation->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden editar conciliaciones en borrador'
            ], 400);
        }

        $request->validate([
            'reconciliation_date' => 'required|date',
            'statement_start_date' => 'required|date',
            'statement_end_date' => 'required|date|after_or_equal:statement_start_date',
            'opening_balance' => 'required|numeric',
            'closing_balance' => 'required|numeric',
            'transaction_ids' => 'array',
        ]);

        DB::beginTransaction();
        try {
            // Recalcular el saldo del sistema
            $bankAccount = $reconciliation->bankAccount;

            $deposits = BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
                ->whereIn('type', ['deposit', 'transfer_in', 'interest'])
                ->where('status', 'completed')
                ->where('transaction_date', '<=', $request->statement_end_date)
                ->sum('amount');

            $withdrawals = BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
                ->whereIn('type', ['withdrawal', 'transfer_out', 'check', 'charge'])
                ->where('status', 'completed')
                ->where('transaction_date', '<=', $request->statement_end_date)
                ->sum('amount');

            $systemBalance = $bankAccount->initial_balance + $deposits - $withdrawals;

            // Actualizar la conciliación
            $reconciliation->update([
                'reconciliation_date' => $request->reconciliation_date,
                'statement_start_date' => $request->statement_start_date,
                'statement_end_date' => $request->statement_end_date,
                'opening_balance' => $request->opening_balance,
                'closing_balance' => $request->closing_balance,
                'system_balance' => $systemBalance,
                'difference' => $request->closing_balance - $systemBalance,
                'notes' => $request->notes,
            ]);

            // Eliminar líneas existentes y crear nuevas
            $reconciliation->lines()->delete();

            if ($request->transaction_ids && count($request->transaction_ids) > 0) {
                foreach ($request->transaction_ids as $transactionId) {
                    BankReconciliationLine::create([
                        'bank_reconciliation_id' => $reconciliation->id,
                        'bank_transaction_id' => $transactionId,
                        'matched_in_statement' => true,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conciliación bancaria actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la conciliación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $reconciliation = BankReconciliation::findOrFail($id);

        if ($reconciliation->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden eliminar conciliaciones en borrador'
            ], 400);
        }

        $reconciliation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conciliación bancaria eliminada exitosamente'
        ]);
    }

    public function post($id)
    {
        $reconciliation = BankReconciliation::findOrFail($id);

        try {
            $reconciliation->post();

            return response()->json([
                'success' => true,
                'message' => 'Conciliación bancaria publicada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function cancel($id)
    {
        $reconciliation = BankReconciliation::findOrFail($id);

        try {
            $reconciliation->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Conciliación bancaria cancelada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Obtener transacciones pendientes de conciliar para una cuenta bancaria
     */
    public function getUnreconciledTransactions(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $query = BankTransaction::where('bank_account_id', $request->bank_account_id)
            ->where('reconciled', false)
            ->where('status', 'completed');

        if ($request->start_date) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Generar reporte de conciliación
     */
    public function report($id)
    {
        $reconciliation = BankReconciliation::with([
            'bankAccount',
            'reconciledBy',
            'lines.bankTransaction'
        ])->findOrFail($id);

        return view('banks.reconciliations.report', compact('reconciliation'));
    }
}
