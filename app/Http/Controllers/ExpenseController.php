<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\CashRegister;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Services\AccountingIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('expenses.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Expense::with(['category', 'supplier', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('expense_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $expenses = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $expenses->map(function ($expense) {
            return [
                'id' => $expense->id,
                'expense_number' => $expense->expense_number,
                'expense_date' => $expense->expense_date->format('Y-m-d'),
                'category_name' => $expense->category->name ?? 'Sin categoría',
                'supplier_name' => $expense->supplier->name ?? '-',
                'document_number' => $expense->document_number,
                'description' => $expense->description,
                'amount' => number_format((float) $expense->amount, 0, ',', '.'),
                'tax_rate' => $expense->tax_rate,
                'tax_amount' => number_format((float) $expense->tax_amount, 0, ',', '.'),
                'status' => $expense->status,
                'payment_method' => $expense->payment_method,
                'user_name' => $expense->user->name ?? '',
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function create()
    {
        $expenseNumber = Expense::generateExpenseNumber(auth()->user()->tenant_id);
        return view('expenses.create', compact('expenseNumber'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expense_date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'document_number' => 'nullable|string|max:50',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'tax_rate' => 'required|in:0,5,10',
            'payment_method' => 'required|string',
            'status' => 'required|in:pending,paid',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $expense = new Expense($request->all());
            $expense->tenant_id = auth()->user()->tenant_id;
            $expense->expense_number = Expense::generateExpenseNumber(auth()->user()->tenant_id);
            $expense->user_id = auth()->id();
            $expense->calculateTax();
            $expense->save();

            // Si se crea como pagado, registrar movimientos
            if ($expense->status === 'paid') {
                $this->processPaymentMovements($expense);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gasto registrado exitosamente' . ($expense->status === 'paid' ? ' y movimientos procesados' : ''),
                'data' => $expense,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function show(Expense $expense)
    {
        $expense->load(['category', 'supplier', 'user']);
        return response()->json($expense);
    }

    public function edit(Expense $expense)
    {
        $expense->load(['category', 'supplier']);
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validator = Validator::make($request->all(), [
            'expense_date' => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'document_number' => 'nullable|string|max:50',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'tax_rate' => 'required|in:0,5,10',
            'payment_method' => 'required|string',
            'status' => 'required|in:pending,paid,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense->fill($request->all());
        $expense->calculateTax();
        $expense->save();

        return response()->json([
            'success' => true,
            'message' => 'Gasto actualizado exitosamente',
            'data' => $expense,
        ]);
    }

    public function pay(Expense $expense)
    {
        if ($expense->status === 'paid') {
            return response()->json([
                'errors' => ['status' => ['El gasto ya está pagado']],
            ], 422);
        }

        if ($expense->status === 'cancelled') {
            return response()->json([
                'errors' => ['status' => ['No se puede pagar un gasto anulado']],
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Cargar relaciones necesarias
            $expense->load('category', 'supplier');

            // Registrar movimientos en caja/banco
            $this->processPaymentMovements($expense);

            // Marcar como pagado
            $expense->status = 'paid';
            $expense->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gasto marcado como pagado y movimientos generados',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function cancel(Expense $expense)
    {
        if ($expense->status === 'cancelled') {
            return response()->json([
                'errors' => ['status' => ['El gasto ya está anulado']],
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Si estaba pagado, reversar movimientos
            if ($expense->status === 'paid') {
                // 1. Reversar en Caja (si fue Efectivo)
                if ($expense->payment_method === 'Efectivo') {
                    $cashRegister = CashRegister::getUserRegisterForDate(
                        Auth::user()->tenant_id,
                        Auth::id(),
                        $expense->expense_date->format('Y-m-d')
                    );

                    if ($cashRegister && $cashRegister->status === 'open') {
                        $cashRegister->movements()->create([
                            'type' => 'income',
                            'concept' => 'other',
                            'amount' => $expense->amount,
                            'description' => 'Anulación de gasto ' . $expense->expense_number,
                            'reference' => $expense->expense_number,
                            'expense_id' => $expense->id,
                        ]);

                        $cashRegister->payments -= $expense->amount;
                        $cashRegister->calculateExpectedBalance();
                        $cashRegister->save();
                    }
                }

                // 2. Reversar en Banco (si fue Transferencia)
                if ($expense->payment_method === 'Transferencia') {
                    $bankTransaction = BankTransaction::where('tenant_id', Auth::user()->tenant_id)
                        ->where('reference', $expense->expense_number)
                        ->where('type', 'withdrawal')
                        ->where('status', 'completed')
                        ->first();

                    if ($bankTransaction) {
                        $bankTransaction->status = 'cancelled';
                        $bankTransaction->save();
                        $bankTransaction->bankAccount->updateBalance();
                    }
                }

                // 3. Reversar asiento contable
                if ($expense->journal_entry_id) {
                    $accountingService = new AccountingIntegrationService();
                    $accountingService->reverseExpenseJournalEntry($expense);
                }
            }

            // Marcar como anulado
            $expense->status = 'cancelled';
            $expense->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gasto anulado exitosamente' . ($expense->journal_entry_id ? ' y movimientos reversados' : ''),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
            ], 422);
        }
    }

    public function destroy(Expense $expense)
    {
        if ($expense->status === 'paid') {
            return response()->json([
                'errors' => ['status' => ['No se pueden eliminar gastos pagados']],
            ], 422);
        }

        try {
            $expense->delete();
            return response()->json([
                'success' => true,
                'message' => 'Gasto eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el gasto',
            ], 500);
        }
    }

    /**
     * Procesar movimientos de pago (Caja, Banco y Contabilidad)
     */
    private function processPaymentMovements(Expense $expense)
    {
        // 1. Validar y registrar en Caja (si es Efectivo)
        if ($expense->payment_method === 'Efectivo') {
            $cashRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

            if (!$cashRegister) {
                throw new \Exception('Debes tener una caja abierta para registrar gastos en efectivo');
            }

            // Validar saldo suficiente
            $cashRegister->calculateExpectedBalance();
            if ($cashRegister->expected_balance < (float) $expense->amount) {
                throw new \Exception('Saldo insuficiente en caja. Disponible: ' . number_format((float) $cashRegister->expected_balance, 0, ',', '.') . ' Gs.');
            }

            // Registrar movimiento en caja
            $cashRegister->movements()->create([
                'type' => 'expense',
                'concept' => 'other',
                'amount' => $expense->amount,
                'description' => 'Gasto ' . $expense->expense_number . ' - ' . $expense->description,
                'reference' => $expense->expense_number,
                'expense_id' => $expense->id,
            ]);

            // Actualizar totales de caja
            $cashRegister->payments += $expense->amount;
            $cashRegister->calculateExpectedBalance();
            $cashRegister->save();
        }

        // 2. Registrar en Banco (si es Transferencia)
        if ($expense->payment_method === 'Transferencia') {
            $defaultAccount = BankAccount::getDefaultAccount(Auth::user()->tenant_id);

            if (!$defaultAccount) {
                throw new \Exception('Debe configurar una cuenta bancaria predeterminada para registrar transferencias');
            }

            // Validar saldo suficiente
            $defaultAccount->updateBalance();
            if ($defaultAccount->current_balance < (float) $expense->amount) {
                throw new \Exception('Saldo insuficiente en la cuenta bancaria. Disponible: ' . number_format((float) $defaultAccount->current_balance, 0, ',', '.') . ' Gs.');
            }

            // Calcular el balance después de la transacción
            $balanceAfter = $defaultAccount->current_balance - $expense->amount;

            // Crear transacción bancaria
            BankTransaction::create([
                'tenant_id' => Auth::user()->tenant_id,
                'bank_account_id' => $defaultAccount->id,
                'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                'transaction_date' => $expense->expense_date,
                'type' => 'withdrawal',
                'amount' => $expense->amount,
                'concept' => 'Pago de gasto',
                'description' => 'Gasto ' . $expense->expense_number . ' - ' . $expense->description,
                'reference' => $expense->expense_number,
                'balance_after' => $balanceAfter,
                'user_id' => Auth::id(),
                'status' => 'completed',
                'reconciled' => false,
            ]);
        }

        // 3. Crear asiento contable
        $accountingService = new AccountingIntegrationService();
        $accountingService->createExpenseJournalEntry($expense);
    }
}
