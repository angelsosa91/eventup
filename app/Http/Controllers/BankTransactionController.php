<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankTransactionController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::where('status', 'active')->get();
        return view('banks.transactions.index', compact('accounts'));
    }

    public function data(Request $request)
    {
        $query = BankTransaction::with(['bankAccount', 'user']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_number', 'like', '%' . $request->search . '%')
                  ->orWhere('reference', 'like', '%' . $request->search . '%')
                  ->orWhere('concept', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->bank_account_id) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->date_from) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $sort = $request->sort ?? 'transaction_date';
        $order = $request->order ?? 'desc';
        $query->orderBy($sort, $order);

        $total = $query->count();
        $rows = $query->skip($request->offset ?? 0)
            ->take($request->limit ?? 20)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'transaction_date' => $transaction->transaction_date->format('d/m/Y'),
                    'bank_account_name' => $transaction->bankAccount->account_name,
                    'type' => $transaction->type,
                    'amount' => $transaction->amount,
                    'reference' => $transaction->reference,
                    'concept' => $transaction->concept,
                    'balance_after' => $transaction->balance_after,
                    'status' => $transaction->status,
                    'reconciled' => $transaction->reconciled,
                    'user_name' => $transaction->user->name,
                ];
            });

        return response()->json([
            'total' => $total,
            'rows' => $rows
        ]);
    }

    public function create()
    {
        $accounts = BankAccount::where('status', 'active')->get();
        $transactionNumber = BankTransaction::generateTransactionNumber(Auth::user()->tenant_id);
        return view('banks.transactions.create', compact('accounts', 'transactionNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:deposit,withdrawal,transfer_in,transfer_out,charge,interest',
            'amount' => 'required|numeric|min:0.01',
            'concept' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $account = BankAccount::findOrFail($request->bank_account_id);

            // Calcular nuevo saldo
            if (in_array($request->type, ['deposit', 'transfer_in', 'interest'])) {
                $newBalance = $account->current_balance + $request->amount;
            } else {
                $newBalance = $account->current_balance - $request->amount;
            }

            // Validar saldo suficiente para retiros
            if (in_array($request->type, ['withdrawal', 'transfer_out', 'charge']) && $newBalance < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente en la cuenta bancaria'
                ], 400);
            }

            $transaction = BankTransaction::create([
                'tenant_id' => Auth::user()->tenant_id,
                'bank_account_id' => $account->id,
                'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                'transaction_date' => $request->transaction_date,
                'type' => $request->type,
                'amount' => $request->amount,
                'reference' => $request->reference,
                'concept' => $request->concept,
                'description' => $request->description,
                'balance_after' => $newBalance,
                'user_id' => Auth::id(),
                'status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transacción bancaria registrada exitosamente',
                'id' => $transaction->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la transacción: ' . $e->getMessage()
            ], 500);
        }
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:bank_accounts,id',
            'to_account_id' => 'required|exists:bank_accounts,id|different:from_account_id',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'concept' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $fromAccount = BankAccount::findOrFail($request->from_account_id);
            $toAccount = BankAccount::findOrFail($request->to_account_id);

            // Validar saldo suficiente
            if ($fromAccount->current_balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente en la cuenta de origen'
                ], 400);
            }

            // Crear transacción de salida
            $transactionOut = BankTransaction::create([
                'tenant_id' => Auth::user()->tenant_id,
                'bank_account_id' => $fromAccount->id,
                'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                'transaction_date' => $request->transaction_date,
                'type' => 'transfer_out',
                'amount' => $request->amount,
                'reference' => $request->reference,
                'concept' => 'Transferencia a ' . $toAccount->account_name . ' - ' . $request->concept,
                'description' => $request->description,
                'destination_account_id' => $toAccount->id,
                'balance_after' => $fromAccount->current_balance - $request->amount,
                'user_id' => Auth::id(),
                'status' => 'completed',
            ]);

            // Crear transacción de entrada
            $transactionIn = BankTransaction::create([
                'tenant_id' => Auth::user()->tenant_id,
                'bank_account_id' => $toAccount->id,
                'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                'transaction_date' => $request->transaction_date,
                'type' => 'transfer_in',
                'amount' => $request->amount,
                'reference' => $request->reference,
                'concept' => 'Transferencia desde ' . $fromAccount->account_name . ' - ' . $request->concept,
                'description' => $request->description,
                'destination_account_id' => $fromAccount->id,
                'balance_after' => $toAccount->current_balance + $request->amount,
                'user_id' => Auth::id(),
                'status' => 'completed',
                'related_transaction_id' => $transactionOut->id,
            ]);

            // Relacionar transacciones
            $transactionOut->related_transaction_id = $transactionIn->id;
            $transactionOut->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transferencia realizada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la transferencia: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cashDeposit(Request $request)
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'concept' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $cashRegister = CashRegister::findOrFail($request->cash_register_id);
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);

            // Validar que la caja pertenezca al usuario
            if ($cashRegister->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para usar esta caja'
                ], 403);
            }

            // Validar que la caja esté abierta
            if ($cashRegister->status !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => 'La caja debe estar abierta para realizar depósitos'
                ], 400);
            }

            // Validar saldo en caja
            $cashRegister->calculateExpectedBalance();
            if ($cashRegister->expected_balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente en caja'
                ], 400);
            }

            // Registrar egreso en caja
            $cashRegister->movements()->create([
                'type' => 'expense',
                'concept' => 'payment',
                'amount' => $request->amount,
                'description' => 'Depósito a banco: ' . $bankAccount->account_name . ' - ' . $request->concept,
                'reference' => $request->reference,
            ]);

            // Actualizar totales de caja
            $cashRegister->payments += $request->amount;
            $cashRegister->save();
            $cashRegister->calculateExpectedBalance();
            $cashRegister->save();

            // Crear transacción bancaria
            $transaction = BankTransaction::create([
                'tenant_id' => Auth::user()->tenant_id,
                'bank_account_id' => $bankAccount->id,
                'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                'transaction_date' => $request->transaction_date,
                'type' => 'deposit',
                'amount' => $request->amount,
                'reference' => $request->reference,
                'concept' => 'Depósito desde caja - ' . $request->concept,
                'description' => $request->description,
                'cash_register_id' => $cashRegister->id,
                'balance_after' => $bankAccount->current_balance + $request->amount,
                'user_id' => Auth::id(),
                'status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Depósito a banco registrado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el depósito: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cashWithdrawal(Request $request)
    {
        $request->validate([
            'cash_register_id' => 'required|exists:cash_registers,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'concept' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $cashRegister = CashRegister::findOrFail($request->cash_register_id);
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);

            // Validar que la caja pertenezca al usuario
            if ($cashRegister->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para usar esta caja'
                ], 403);
            }

            // Validar que la caja esté abierta
            if ($cashRegister->status !== 'open') {
                return response()->json([
                    'success' => false,
                    'message' => 'La caja debe estar abierta para recibir retiros'
                ], 400);
            }

            // Validar saldo en banco
            if ($bankAccount->current_balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente en la cuenta bancaria'
                ], 400);
            }

            // Registrar ingreso en caja
            $cashRegister->movements()->create([
                'type' => 'income',
                'concept' => 'other',
                'amount' => $request->amount,
                'description' => 'Retiro de banco: ' . $bankAccount->account_name . ' - ' . $request->concept,
                'reference' => $request->reference,
            ]);

            // Actualizar totales de caja (otros ingresos)
            $cashRegister->collections += $request->amount;
            $cashRegister->save();
            $cashRegister->calculateExpectedBalance();
            $cashRegister->save();

            // Crear transacción bancaria
            $transaction = BankTransaction::create([
                'tenant_id' => Auth::user()->tenant_id,
                'bank_account_id' => $bankAccount->id,
                'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                'transaction_date' => $request->transaction_date,
                'type' => 'withdrawal',
                'amount' => $request->amount,
                'reference' => $request->reference,
                'concept' => 'Retiro a caja - ' . $request->concept,
                'description' => $request->description,
                'cash_register_id' => $cashRegister->id,
                'balance_after' => $bankAccount->current_balance - $request->amount,
                'user_id' => Auth::id(),
                'status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Retiro de banco registrado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el retiro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $transaction = BankTransaction::with(['bankAccount', 'destinationAccount', 'user', 'cashRegister'])->findOrFail($id);
        return view('banks.transactions.detail', compact('transaction'));
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $transaction = BankTransaction::findOrFail($id);

            if ($transaction->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'La transacción ya está cancelada'
                ], 400);
            }

            if ($transaction->reconciled) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cancelar una transacción conciliada'
                ], 400);
            }

            $transaction->status = 'cancelled';
            $transaction->save();

            // Actualizar saldo de la cuenta
            $transaction->bankAccount->updateBalance();

            // Si tiene transacción relacionada (transferencia), cancelarla también
            if ($transaction->related_transaction_id) {
                $relatedTransaction = BankTransaction::find($transaction->related_transaction_id);
                if ($relatedTransaction) {
                    $relatedTransaction->status = 'cancelled';
                    $relatedTransaction->save();
                    $relatedTransaction->bankAccount->updateBalance();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transacción cancelada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la transacción: ' . $e->getMessage()
            ], 500);
        }
    }
}
