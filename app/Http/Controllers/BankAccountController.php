<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BankAccountController extends Controller
{
    public function index()
    {
        return view('banks.accounts.index');
    }

    public function data(Request $request)
    {
        $query = BankAccount::with('bank');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('account_number', 'like', '%' . $request->search . '%')
                  ->orWhere('account_name', 'like', '%' . $request->search . '%')
                  ->orWhere('bank_name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('bank', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'desc';
        $query->orderBy($sort, $order);

        $total = $query->count();
        $rows = $query->skip($request->offset ?? 0)
            ->take($request->limit ?? 20)
            ->get()
            ->map(function($account) {
                // Asegurar que bank_name se llene desde la relación si existe bank_id
                if ($account->bank_id && $account->bank) {
                    $account->bank_name = $account->bank->name;
                }
                return $account;
            });

        return response()->json([
            'total' => $total,
            'rows' => $rows
        ]);
    }

    public function list(Request $request)
    {
        $query = BankAccount::where('status', 'active');

        if ($request->q) {
            $query->where(function($q) use ($request) {
                $q->where('account_number', 'like', '%' . $request->q . '%')
                  ->orWhere('account_name', 'like', '%' . $request->q . '%')
                  ->orWhere('bank_name', 'like', '%' . $request->q . '%');
            });
        }

        $accounts = $query->limit(50)->get();
        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|max:50|unique:bank_accounts',
            'account_name' => 'required|string|max:100',
            'bank_id' => 'required|exists:banks,id',
            'account_type' => 'required|in:checking,savings,credit',
            'initial_balance' => 'required|numeric|min:0',
        ]);

        // Obtener el banco para guardar su información
        $bank = \App\Models\Bank::find($request->bank_id);

        $account = BankAccount::create([
            'tenant_id' => Auth::user()->tenant_id,
            'bank_id' => $request->bank_id,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'bank_name' => $bank->name,
            'bank_code' => $bank->code,
            'account_type' => $request->account_type,
            'currency' => $request->currency ?? 'PYG',
            'initial_balance' => $request->initial_balance,
            'current_balance' => $request->initial_balance,
            'account_holder' => $request->account_holder,
            'swift_code' => $request->swift_code,
            'notes' => $request->notes,
            'status' => 'active',
            'is_default' => false,
        ]);

        // Si is_default es true, establecer esta cuenta como predeterminada
        if ($request->is_default) {
            $account->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cuenta bancaria creada exitosamente',
            'id' => $account->id
        ]);
    }

    public function show($id)
    {
        $account = BankAccount::with(['transactions' => function($q) {
            $q->orderBy('transaction_date', 'desc')
              ->orderBy('id', 'desc')
              ->limit(100);
        }])->findOrFail($id);

        return view('banks.accounts.detail', compact('account'));
    }

    public function update(Request $request, $id)
    {
        $account = BankAccount::findOrFail($id);

        $request->validate([
            'account_name' => 'required|string|max:100',
            'bank_id' => 'required|exists:banks,id',
            'account_type' => 'required|in:checking,savings,credit',
        ]);

        // Obtener el banco para guardar su información
        $bank = \App\Models\Bank::find($request->bank_id);

        $account->update([
            'bank_id' => $request->bank_id,
            'account_name' => $request->account_name,
            'bank_name' => $bank->name,
            'bank_code' => $bank->code,
            'account_type' => $request->account_type,
            'account_holder' => $request->account_holder,
            'swift_code' => $request->swift_code,
            'notes' => $request->notes,
        ]);

        // Si is_default es true, establecer esta cuenta como predeterminada
        if ($request->is_default && !$account->is_default) {
            $account->setAsDefault();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cuenta bancaria actualizada exitosamente'
        ]);
    }

    public function toggleStatus($id)
    {
        $account = BankAccount::findOrFail($id);

        $newStatus = $account->status === 'active' ? 'inactive' : 'active';
        $account->status = $newStatus;
        $account->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado exitosamente',
            'status' => $newStatus
        ]);
    }

    public function setDefault($id)
    {
        $account = BankAccount::findOrFail($id);

        if ($account->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede establecer como predeterminada una cuenta activa'
            ], 400);
        }

        $account->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta establecida como predeterminada exitosamente'
        ]);
    }

    public function reconciliation($id)
    {
        $account = BankAccount::findOrFail($id);
        $unreconciledTransactions = $account->getUnreconciledTransactions();

        return view('banks.accounts.reconciliation', compact('account', 'unreconciledTransactions'));
    }

    public function reconcile(Request $request, $id)
    {
        $request->validate([
            'bank_statement_balance' => 'required|numeric',
            'reconciliation_date' => 'required|date',
            'transaction_ids' => 'array',
        ]);

        DB::beginTransaction();
        try {
            $account = BankAccount::findOrFail($id);

            // Marcar transacciones como conciliadas
            if ($request->transaction_ids) {
                BankTransaction::whereIn('id', $request->transaction_ids)
                    ->update([
                        'reconciled' => true,
                        'reconciled_date' => $request->reconciliation_date
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Conciliación bancaria realizada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al realizar la conciliación: ' . $e->getMessage()
            ], 500);
        }
    }
}
