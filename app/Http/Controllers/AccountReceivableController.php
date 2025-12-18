<?php

namespace App\Http\Controllers;

use App\Models\AccountReceivable;
use App\Models\AccountReceivablePayment;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CompanySetting;
use App\Services\AccountingIntegrationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountReceivableController extends Controller
{
    public function index()
    {
        return view('account-receivables.index');
    }

    public function data(Request $request)
    {
        $query = AccountReceivable::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('document_number', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('sale_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->customer_id) {
            $query->where('customer_id', $request->customer_id);
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
        $documentNumber = AccountReceivable::generateDocumentNumber(Auth::user()->tenant_id);
        return view('account-receivables.create', compact('documentNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_date' => 'required|date',
            'due_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $customer = Customer::findOrFail($request->customer_id);

        $receivable = AccountReceivable::create([
            'tenant_id' => Auth::user()->tenant_id,
            'document_number' => AccountReceivable::generateDocumentNumber(Auth::user()->tenant_id),
            'document_date' => $request->document_date,
            'due_date' => $request->due_date,
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'description' => $request->description,
            'amount' => $request->amount,
            'paid_amount' => 0,
            'balance' => $request->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cuenta por cobrar creada exitosamente',
            'id' => $receivable->id
        ]);
    }

    public function show($id)
    {
        $receivable = AccountReceivable::with(['customer', 'sale', 'payments.user'])->findOrFail($id);
        return view('account-receivables.detail', compact('receivable'));
    }

    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,transfer,check,card,other',
        ]);

        $receivable = AccountReceivable::findOrFail($id);

        if ($receivable->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Esta cuenta ya está pagada'
            ], 400);
        }

        if ($receivable->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Esta cuenta está anulada'
            ], 400);
        }

        if ($request->amount > $receivable->balance) {
            return response()->json([
                'success' => false,
                'message' => 'El monto del pago excede el saldo pendiente'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Si el pago es en efectivo, verificar que haya caja abierta
            if ($request->payment_method === 'cash') {
                $cashRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

                if (!$cashRegister) {
                    throw new \Exception('Debes tener una caja abierta para registrar cobros en efectivo');
                }
            }

            $payment = AccountReceivablePayment::create([
                'account_receivable_id' => $receivable->id,
                'payment_number' => AccountReceivablePayment::generatePaymentNumber($receivable->id),
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'user_id' => Auth::id(),
            ]);

            $receivable->updateBalance();

            // Si el pago es en efectivo, registrar en caja
            if ($request->payment_method === 'cash') {
                $cashRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

                // Registrar movimiento en caja
                $cashRegister->movements()->create([
                    'type' => 'income',
                    'concept' => 'collection',
                    'amount' => $request->amount,
                    'description' => 'Cobro ' . $payment->payment_number . ' - ' . $receivable->customer_name . ' - ' . $receivable->document_number,
                    'reference' => $payment->payment_number,
                    'account_receivable_payment_id' => $payment->id,
                ]);

                // Actualizar totales de caja
                $cashRegister->collections += $request->amount;
                $cashRegister->calculateExpectedBalance();
                $cashRegister->save();
            }

            // Si el pago es por transferencia, registrar en cuenta bancaria predeterminada
            if ($request->payment_method === 'transfer') {
                $defaultAccount = BankAccount::getDefaultAccount(Auth::user()->tenant_id);

                if (!$defaultAccount) {
                    throw new \Exception('Debe configurar una cuenta bancaria predeterminada para registrar transferencias');
                }

                // Crear transacción bancaria
                BankTransaction::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'bank_account_id' => $defaultAccount->id,
                    'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                    'transaction_date' => $request->payment_date,
                    'type' => 'deposit',
                    'amount' => $request->amount,
                    'description' => 'Cobro ' . $payment->payment_number . ' - ' . $receivable->customer_name . ' - ' . $receivable->document_number,
                    'reference' => $payment->payment_number,
                    'status' => 'completed',
                    'reconciled' => false,
                ]);

                // Actualizar saldo de cuenta bancaria
                $defaultAccount->updateBalance();
            }

            // Crear asiento contable automático
            $accountingService = new AccountingIntegrationService();
            $payment->load('accountReceivable');
            $accountingService->createReceivablePaymentJournalEntry($payment, Auth::user()->tenant_id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id)
    {
        $receivable = AccountReceivable::findOrFail($id);

        if ($receivable->paid_amount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede anular una cuenta con pagos registrados'
            ], 400);
        }

        $receivable->status = 'cancelled';
        $receivable->save();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta por cobrar anulada exitosamente'
        ]);
    }

    public function destroy($id)
    {
        $receivable = AccountReceivable::findOrFail($id);

        if ($receivable->paid_amount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una cuenta con pagos registrados'
            ], 400);
        }

        $receivable->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta por cobrar eliminada exitosamente'
        ]);
    }

    public function byCustomer(Request $request)
    {
        $query = AccountReceivable::select('customer_id', 'customer_name')
            ->selectRaw('COUNT(*) as total_documents')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('SUM(paid_amount) as total_paid')
            ->selectRaw('SUM(balance) as total_balance')
            ->where('status', '!=', 'cancelled')
            ->groupBy('customer_id', 'customer_name')
            ->having('total_balance', '>', 0)
            ->orderBy('total_balance', 'desc');

        $customers = $query->get();

        return view('account-receivables.by-customer', compact('customers'));
    }

    /**
     * Generar PDF de recibo de pago
     */
    public function generatePaymentPDF($paymentId)
    {
        $tenantId = Auth::user()->tenant_id;

        $payment = AccountReceivablePayment::with(['accountReceivable.sale.customer'])
            ->findOrFail($paymentId);

        // Verificar pertenencia al tenant
        if ($payment->accountReceivable->sale->tenant_id != $tenantId) {
            abort(403);
        }

        $companySettings = CompanySetting::where('tenant_id', $tenantId)->first();

        $pdf = Pdf::loadView('pdf.payment-receipt', compact('payment', 'companySettings'));

        return $pdf->stream('recibo-' . $payment->payment_number . '.pdf');
    }

    /**
     * Descargar PDF de recibo de pago
     */
    public function downloadPaymentPDF($paymentId)
    {
        $tenantId = Auth::user()->tenant_id;

        $payment = AccountReceivablePayment::with(['accountReceivable.sale.customer'])
            ->findOrFail($paymentId);

        // Verificar pertenencia al tenant
        if ($payment->accountReceivable->sale->tenant_id != $tenantId) {
            abort(403);
        }

        $companySettings = CompanySetting::where('tenant_id', $tenantId)->first();

        $pdf = Pdf::loadView('pdf.payment-receipt', compact('payment', 'companySettings'));

        return $pdf->download('recibo-' . $payment->payment_number . '.pdf');
    }
}
