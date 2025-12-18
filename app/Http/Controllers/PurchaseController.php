<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\AccountPayable;
use App\Models\CashRegister;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Services\AccountingIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchases.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Purchase::with(['supplier', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('purchase_number', 'like', "%{$search}%")
                        ->orWhere('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $purchases = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $purchases->map(function ($purchase) {
            return [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'invoice_number' => $purchase->invoice_number,
                'purchase_date' => $purchase->purchase_date->format('Y-m-d'),
                'supplier_name' => $purchase->supplier->name ?? 'Sin proveedor',
                'subtotal_exento' => number_format($purchase->subtotal_exento, 0, ',', '.'),
                'subtotal_5' => number_format($purchase->subtotal_5, 0, ',', '.'),
                'iva_5' => number_format($purchase->iva_5, 0, ',', '.'),
                'subtotal_10' => number_format($purchase->subtotal_10, 0, ',', '.'),
                'iva_10' => number_format($purchase->iva_10, 0, ',', '.'),
                'total' => number_format($purchase->total, 0, ',', '.'),
                'status' => $purchase->status,
                'payment_method' => $purchase->payment_method,
                'user_name' => $purchase->user->name ?? '',
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function create()
    {
        $purchaseNumber = Purchase::generatePurchaseNumber(auth()->user()->tenant_id);
        return view('purchases.create', compact('purchaseNumber'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'payment_type' => 'required|in:cash,credit',
            'credit_days' => 'required_if:payment_type,credit|nullable|integer|min:1',
            'invoice_number' => 'nullable|string|max:50',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Calcular fecha de vencimiento si es a crédito
            $creditDueDate = null;
            if ($request->payment_type === 'credit') {
                $creditDueDate = date('Y-m-d', strtotime($request->purchase_date . ' + ' . $request->credit_days . ' days'));
            }

            $purchase = Purchase::create([
                'tenant_id' => auth()->user()->tenant_id,
                'purchase_number' => Purchase::generatePurchaseNumber(auth()->user()->tenant_id),
                'purchase_date' => $request->purchase_date,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'invoice_number' => $request->invoice_number,
                'payment_type' => $request->payment_type,
                'credit_days' => $request->credit_days,
                'credit_due_date' => $creditDueDate,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => 'draft',
            ]);

            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                $item = new PurchaseItem([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $product->tax_rate,
                ]);

                $item->calculateValues();
                $purchase->items()->save($item);
            }

            $purchase->calculateTotals()->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra creada exitosamente',
                'data' => $purchase,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la compra: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return response()->json($purchase);
    }

    public function detail(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product', 'accountPayable', 'journalEntry']);
        return view('purchases.detail', compact('purchase'));
    }

    public function confirm(Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return response()->json([
                'errors' => ['status' => ['Solo se pueden confirmar compras en borrador']],
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Si es compra al contado en efectivo, verificar que haya caja abierta
            if ($purchase->payment_type === 'cash' && $purchase->payment_method === 'cash') {
                $cashRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

                if (!$cashRegister) {
                    throw new \Exception('Debes tener una caja abierta para confirmar compras en efectivo');
                }

                // Validar que haya saldo suficiente en caja
                $cashRegister->calculateExpectedBalance();
                if ($cashRegister->expected_balance < $purchase->total) {
                    throw new \Exception('Saldo insuficiente en caja. Disponible: ' . number_format($cashRegister->expected_balance, 0, ',', '.') . ' Gs.');
                }
            }

            // Incrementar stock de productos (compra = entrada de stock)
            foreach ($purchase->items as $item) {
                $product = $item->product;
                $product->stock += $item->quantity;
                $product->save();
            }

            $purchase->status = 'confirmed';
            $purchase->save();

            // Si es compra a crédito, crear cuenta por pagar
            if ($purchase->payment_type === 'credit' && $purchase->supplier_id) {
                $supplier = Supplier::find($purchase->supplier_id);

                AccountPayable::create([
                    'tenant_id' => $purchase->tenant_id,
                    'document_number' => AccountPayable::generateDocumentNumber($purchase->tenant_id),
                    'document_date' => $purchase->purchase_date,
                    'due_date' => $purchase->credit_due_date,
                    'supplier_id' => $purchase->supplier_id,
                    'supplier_name' => $supplier->name,
                    'purchase_id' => $purchase->id,
                    'purchase_number' => $purchase->purchase_number,
                    'description' => 'Compra a crédito - ' . $purchase->purchase_number,
                    'amount' => $purchase->total,
                    'paid_amount' => 0,
                    'balance' => $purchase->total,
                    'status' => 'pending',
                ]);
            }

            // Si es compra al contado en efectivo, registrar en caja
            if ($purchase->payment_type === 'cash' && $purchase->payment_method === 'cash') {
                $cashRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

                // Registrar movimiento en caja
                $cashRegister->movements()->create([
                    'type' => 'expense',
                    'concept' => 'payment',
                    'amount' => $purchase->total,
                    'description' => 'Compra ' . $purchase->purchase_number . ' - ' . $purchase->supplier->name,
                    'reference' => $purchase->purchase_number,
                    'purchase_id' => $purchase->id,
                ]);

                // Actualizar totales de caja
                $cashRegister->payments += $purchase->total;
                $cashRegister->calculateExpectedBalance();
                $cashRegister->save();
            }

            // Si es compra al contado por transferencia, registrar en cuenta bancaria predeterminada
            if ($purchase->payment_type === 'cash' && $purchase->payment_method === 'transfer') {
                $defaultAccount = BankAccount::getDefaultAccount(Auth::user()->tenant_id);

                if (!$defaultAccount) {
                    throw new \Exception('Debe configurar una cuenta bancaria predeterminada para registrar transferencias');
                }

                // Validar que haya saldo suficiente en la cuenta bancaria
                $defaultAccount->updateBalance();
                if ($defaultAccount->current_balance < $purchase->total) {
                    throw new \Exception('Saldo insuficiente en cuenta bancaria. Disponible: ' . number_format($defaultAccount->current_balance, 0, ',', '.') . ' Gs.');
                }

                // Calcular el balance después de la transacción
                $balanceAfter = $defaultAccount->current_balance - $purchase->total;

                // Crear transacción bancaria
                BankTransaction::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'bank_account_id' => $defaultAccount->id,
                    'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                    'transaction_date' => $purchase->purchase_date,
                    'type' => 'withdrawal',
                    'amount' => $purchase->total,
                    'concept' => 'Compra por transferencia',
                    'description' => 'Compra ' . $purchase->purchase_number . ' - ' . $purchase->supplier->name,
                    'reference' => $purchase->purchase_number,
                    'balance_after' => $balanceAfter,
                    'user_id' => Auth::id(),
                    'status' => 'completed',
                    'reconciled' => false,
                ]);

                // El saldo se actualiza automáticamente por el evento created del modelo
            }

            // Crear asiento contable automático
            $accountingService = new AccountingIntegrationService();
            $accountingService->createPurchaseJournalEntry($purchase);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra confirmada. Stock actualizado.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
            ], 500);
        }
    }

    public function cancel(Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return response()->json([
                'errors' => ['status' => ['La compra ya está anulada']],
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Si estaba confirmada, revertir el stock
            if ($purchase->status === 'confirmed') {
                foreach ($purchase->items as $item) {
                    $product = $item->product;
                    $product->stock -= $item->quantity;
                    $product->save();
                }

                // Si fue compra en efectivo, reversar el movimiento de caja
                if ($purchase->payment_type === 'cash' && $purchase->payment_method === 'cash') {
                    // Buscar la caja del usuario para la fecha de la compra
                    $cashRegister = CashRegister::getUserRegisterForDate(
                        Auth::user()->tenant_id,
                        Auth::id(),
                        $purchase->purchase_date->format('Y-m-d')
                    );

                    if ($cashRegister && $cashRegister->status === 'open') {
                        // Registrar movimiento de reversa en caja
                        $cashRegister->movements()->create([
                            'type' => 'income',
                            'concept' => 'other',
                            'amount' => $purchase->total,
                            'description' => 'Anulación de compra ' . $purchase->purchase_number,
                            'reference' => $purchase->purchase_number,
                            'purchase_id' => $purchase->id,
                        ]);

                        // Actualizar totales de caja (devolver el pago)
                        $cashRegister->payments -= $purchase->total;
                        $cashRegister->calculateExpectedBalance();
                        $cashRegister->save();
                    }
                }

                // Si fue compra por transferencia, cancelar la transacción bancaria
                if ($purchase->payment_type === 'cash' && $purchase->payment_method === 'transfer') {
                    // Buscar la transacción bancaria relacionada
                    $bankTransaction = BankTransaction::where('tenant_id', Auth::user()->tenant_id)
                        ->where('reference', $purchase->purchase_number)
                        ->where('type', 'withdrawal')
                        ->where('status', 'completed')
                        ->first();

                    if ($bankTransaction) {
                        $bankTransaction->status = 'cancelled';
                        $bankTransaction->save();

                        // Actualizar saldo de cuenta bancaria
                        $bankTransaction->bankAccount->updateBalance();
                    }
                }

                // Reversar asiento contable si existe
                if ($purchase->journal_entry_id) {
                    $accountingService = new AccountingIntegrationService();
                    $accountingService->reversePurchaseJournalEntry($purchase);
                }
            }

            $purchase->status = 'cancelled';
            $purchase->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compra anulada exitosamente.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
            ], 500);
        }
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status !== 'draft') {
            return response()->json([
                'errors' => ['status' => ['Solo se pueden eliminar compras en borrador']],
            ], 422);
        }

        try {
            $purchase->delete();
            return response()->json([
                'success' => true,
                'message' => 'Compra eliminada exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la compra',
            ], 500);
        }
    }
}
