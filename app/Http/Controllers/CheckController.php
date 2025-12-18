<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Check;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::where('status', 'active')->get();
        return view('banks.checks.index', compact('accounts'));
    }

    public function data(Request $request)
    {
        $query = Check::with(['bankAccount', 'user']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('check_number', 'like', '%' . $request->search . '%')
                  ->orWhere('payee', 'like', '%' . $request->search . '%')
                  ->orWhere('issuer', 'like', '%' . $request->search . '%')
                  ->orWhere('concept', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->bank_account_id) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        $sort = $request->sort ?? 'issue_date';
        $order = $request->order ?? 'desc';
        $query->orderBy($sort, $order);

        $total = $query->count();
        $rows = $query->skip($request->offset ?? 0)
            ->take($request->limit ?? 20)
            ->get()
            ->map(function($check) {
                return [
                    'id' => $check->id,
                    'check_number' => $check->check_number,
                    'issue_date' => $check->issue_date->format('d/m/Y'),
                    'due_date' => $check->due_date ? $check->due_date->format('d/m/Y') : null,
                    'amount' => $check->amount,
                    'type' => $check->type,
                    'bank_account_name' => $check->type === 'issued' && $check->bankAccount ? $check->bankAccount->account_name : $check->bank_name,
                    'payee_issuer' => $check->type === 'issued' ? $check->payee : $check->issuer,
                    'concept' => $check->concept,
                    'status' => $check->status,
                    'is_overdue' => $check->isOverdue(),
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
        return view('banks.checks.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $rules = [
            'check_number' => 'required|string|max:50',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:issued,received',
            'concept' => 'required|string|max:255',
        ];

        if ($request->type === 'issued') {
            $rules['bank_account_id'] = 'required|exists:bank_accounts,id';
            $rules['payee'] = 'required|string|max:100';
        } else {
            $rules['bank_name'] = 'required|string|max:100';
            $rules['issuer'] = 'required|string|max:100';
        }

        $request->validate($rules);

        $check = Check::create([
            'tenant_id' => Auth::user()->tenant_id,
            'check_number' => $request->check_number,
            'issue_date' => $request->issue_date,
            'due_date' => $request->due_date,
            'amount' => $request->amount,
            'type' => $request->type,
            'bank_account_id' => $request->bank_account_id,
            'payee' => $request->payee,
            'bank_name' => $request->bank_name,
            'issuer' => $request->issuer,
            'concept' => $request->concept,
            'notes' => $request->notes,
            'user_id' => Auth::id(),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cheque registrado exitosamente',
            'id' => $check->id
        ]);
    }

    public function depositCheck(Request $request, $id)
    {
        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'deposit_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $check = Check::findOrFail($id);

            if ($check->type !== 'received') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden depositar cheques recibidos'
                ], 400);
            }

            if ($check->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'El cheque ya fue procesado'
                ], 400);
            }

            $bankAccount = BankAccount::findOrFail($request->bank_account_id);

            // Crear transacción bancaria
            $transaction = BankTransaction::create([
                'tenant_id' => Auth::user()->tenant_id,
                'bank_account_id' => $bankAccount->id,
                'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                'transaction_date' => $request->deposit_date,
                'type' => 'deposit',
                'amount' => $check->amount,
                'reference' => 'Cheque #' . $check->check_number,
                'concept' => 'Depósito de cheque - ' . $check->concept,
                'description' => 'Cheque del banco ' . $check->bank_name . ' de ' . $check->issuer,
                'balance_after' => $bankAccount->current_balance + $check->amount,
                'user_id' => Auth::id(),
                'status' => 'completed',
            ]);

            // Actualizar cheque
            $check->status = 'deposited';
            $check->bank_account_id = $bankAccount->id;
            $check->bank_transaction_id = $transaction->id;
            $check->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cheque depositado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al depositar el cheque: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cashCheck(Request $request, $id)
    {
        $request->validate([
            'cashed_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $check = Check::findOrFail($id);

            if (!in_array($check->status, ['pending', 'deposited'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cheque no puede ser cobrado en su estado actual'
                ], 400);
            }

            // Si es un cheque emitido, crear transacción bancaria
            if ($check->type === 'issued') {
                $transaction = BankTransaction::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'bank_account_id' => $check->bank_account_id,
                    'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                    'transaction_date' => $request->cashed_date,
                    'type' => 'check',
                    'amount' => $check->amount,
                    'reference' => 'Cheque #' . $check->check_number,
                    'concept' => 'Cheque cobrado - ' . $check->concept,
                    'description' => 'Cheque a la orden de ' . $check->payee,
                    'balance_after' => $check->bankAccount->current_balance - $check->amount,
                    'user_id' => Auth::id(),
                    'status' => 'completed',
                ]);

                $check->bank_transaction_id = $transaction->id;
            }

            $check->status = 'cashed';
            $check->cashed_date = $request->cashed_date;
            $check->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cheque marcado como cobrado'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cobrar el cheque: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bounceCheck($id)
    {
        DB::beginTransaction();
        try {
            $check = Check::findOrFail($id);

            if ($check->status === 'cashed') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede rechazar un cheque ya cobrado'
                ], 400);
            }

            // Si el cheque fue depositado, reversar la transacción
            if ($check->bank_transaction_id) {
                $transaction = BankTransaction::find($check->bank_transaction_id);
                if ($transaction) {
                    $transaction->status = 'cancelled';
                    $transaction->save();
                    $transaction->bankAccount->updateBalance();
                }
            }

            $check->status = 'bounced';
            $check->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cheque marcado como rechazado'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar el cheque: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id)
    {
        DB::beginTransaction();
        try {
            $check = Check::findOrFail($id);

            if ($check->status === 'cashed') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede anular un cheque ya cobrado'
                ], 400);
            }

            // Si el cheque fue depositado, reversar la transacción
            if ($check->bank_transaction_id) {
                $transaction = BankTransaction::find($check->bank_transaction_id);
                if ($transaction) {
                    $transaction->status = 'cancelled';
                    $transaction->save();
                    $transaction->bankAccount->updateBalance();
                }
            }

            $check->status = 'cancelled';
            $check->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cheque anulado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al anular el cheque: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $check = Check::with(['bankAccount', 'bankTransaction', 'user'])->findOrFail($id);
        return view('banks.checks.detail', compact('check'));
    }
}
