<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\AccountReceivable;
use App\Models\CashRegister;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CompanySetting;
use App\Services\AccountingIntegrationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    /**
     * Mostrar listado de ventas
     */
    public function index()
    {
        return view('sales.index');
    }

    /**
     * Obtener datos para el DataGrid
     */
    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = Sale::with(['customer', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('sale_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            });

        $total = $query->count();

        $sales = $query->orderBy($sort, $order)
            ->skip(($page - 1) * $rows)
            ->take($rows)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'sale_date' => $sale->sale_date->format('d/m/Y'),
                    'customer_name' => $sale->customer ? $sale->customer->name : 'Sin cliente',
                    'user_name' => $sale->user->name,
                    'subtotal_exento' => number_format($sale->subtotal_exento, 0, ',', '.'),
                    'subtotal_5' => number_format($sale->subtotal_5, 0, ',', '.'),
                    'iva_5' => number_format($sale->iva_5, 0, ',', '.'),
                    'subtotal_10' => number_format($sale->subtotal_10, 0, ',', '.'),
                    'iva_10' => number_format($sale->iva_10, 0, ',', '.'),
                    'total' => number_format($sale->total, 0, ',', '.'),
                    'status' => $sale->status,
                    'status_label' => $sale->status_label,
                    'payment_method' => $sale->payment_method,
                ];
            });

        return response()->json([
            'total' => $total,
            'rows' => $sales
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $saleNumber = Sale::generateSaleNumber(auth()->user()->tenant_id);
        return view('sales.create', compact('saleNumber'));
    }

    /**
     * Guardar nueva venta
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'sale_date' => 'required|date',
            'payment_type' => 'required|in:cash,credit',
            'credit_days' => 'required_if:payment_type,credit|nullable|integer|min:1',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validar que ventas a crédito tengan cliente
        if ($request->payment_type === 'credit' && !$request->customer_id) {
            return response()->json([
                'errors' => ['customer_id' => ['Las ventas a crédito requieren un cliente']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Calcular fecha de vencimiento si es a crédito
            $creditDueDate = null;
            if ($request->payment_type === 'credit') {
                $creditDueDate = date('Y-m-d', strtotime($request->sale_date . ' + ' . $request->credit_days . ' days'));
            }

            // Crear la venta
            $sale = Sale::create([
                'tenant_id' => auth()->user()->tenant_id,
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'sale_number' => Sale::generateSaleNumber(auth()->user()->tenant_id),
                'sale_date' => $request->sale_date,
                'payment_type' => $request->payment_type,
                'credit_days' => $request->credit_days,
                'credit_due_date' => $creditDueDate,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'status' => 'draft',
            ]);

            // Crear los items
            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                $item = new SaleItem([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'tax_rate' => $product->tax_rate,
                ]);

                $item->calculateValues();
                $sale->items()->save($item);
            }

            // Calcular totales de la venta
            $sale->load('items');
            $sale->calculateTotals();
            $sale->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta creada correctamente',
                'data' => $sale
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => ['Error al crear la venta: ' . $e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Mostrar una venta
     */
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product']);

        return response()->json([
            'sale' => $sale,
            'items' => $sale->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'subtotal' => $item->subtotal,
                    'tax_amount' => $item->tax_amount,
                    'total' => $item->total,
                ];
            })
        ]);
    }

    /**
     * Confirmar venta (descontar stock)
     */
    public function confirm(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return response()->json([
                'errors' => ['general' => ['Solo se pueden confirmar ventas en borrador']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Si es venta al contado en efectivo, verificar que haya caja abierta
            if ($sale->payment_type === 'cash' && $sale->payment_method === 'cash') {
                $cashRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

                if (!$cashRegister) {
                    throw new \Exception('Debes tener una caja abierta para confirmar ventas en efectivo');
                }
            }

            // Verificar stock disponible
            foreach ($sale->items as $item) {
                if ($item->product && $item->product->track_stock) {
                    if ($item->product->stock < $item->quantity) {
                        throw new \Exception("Stock insuficiente para {$item->product_name}. Disponible: {$item->product->stock}");
                    }
                }
            }

            // Descontar stock
            foreach ($sale->items as $item) {
                if ($item->product && $item->product->track_stock) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }

            $sale->status = 'confirmed';
            $sale->save();

            // Si es venta a crédito, crear cuenta por cobrar
            if ($sale->payment_type === 'credit' && $sale->customer_id) {
                $customer = Customer::find($sale->customer_id);

                AccountReceivable::create([
                    'tenant_id' => $sale->tenant_id,
                    'document_number' => AccountReceivable::generateDocumentNumber($sale->tenant_id),
                    'document_date' => $sale->sale_date,
                    'due_date' => $sale->credit_due_date,
                    'customer_id' => $sale->customer_id,
                    'customer_name' => $customer->name,
                    'sale_id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'description' => 'Venta a crédito - ' . $sale->sale_number,
                    'amount' => $sale->total,
                    'paid_amount' => 0,
                    'balance' => $sale->total,
                    'status' => 'pending',
                ]);
            }

            // Si es venta al contado en efectivo, registrar en caja
            if ($sale->payment_type === 'cash' && $sale->payment_method === 'cash') {
                $cashRegister = CashRegister::getOpenRegister(Auth::user()->tenant_id, Auth::id());

                // Registrar movimiento en caja
                $cashRegister->movements()->create([
                    'type' => 'income',
                    'concept' => 'sale',
                    'amount' => $sale->total,
                    'description' => 'Venta ' . $sale->sale_number . ($sale->customer_id ? ' - ' . $sale->customer->name : ''),
                    'reference' => $sale->sale_number,
                    'sale_id' => $sale->id,
                ]);

                // Actualizar totales de caja
                $cashRegister->sales_cash += $sale->total;
                $cashRegister->calculateExpectedBalance();
                $cashRegister->save();
            }

            // Si es venta al contado por transferencia, registrar en cuenta bancaria predeterminada
            if ($sale->payment_type === 'cash' && $sale->payment_method === 'transfer') {
                $defaultAccount = BankAccount::getDefaultAccount(Auth::user()->tenant_id);

                if (!$defaultAccount) {
                    throw new \Exception('Debe configurar una cuenta bancaria predeterminada para registrar transferencias');
                }

                // Calcular el balance después de la transacción
                $balanceAfter = $defaultAccount->current_balance + $sale->total;

                // Crear transacción bancaria
                BankTransaction::create([
                    'tenant_id' => Auth::user()->tenant_id,
                    'bank_account_id' => $defaultAccount->id,
                    'transaction_number' => BankTransaction::generateTransactionNumber(Auth::user()->tenant_id),
                    'transaction_date' => $sale->sale_date,
                    'type' => 'deposit',
                    'amount' => $sale->total,
                    'concept' => 'Venta por transferencia',
                    'description' => 'Venta ' . $sale->sale_number . ($sale->customer_id ? ' - ' . $sale->customer->name : ''),
                    'reference' => $sale->sale_number,
                    'balance_after' => $balanceAfter,
                    'user_id' => Auth::id(),
                    'status' => 'completed',
                    'reconciled' => false,
                ]);

                // El saldo se actualiza automáticamente por el evento created del modelo
            }

            // Crear asiento contable automático
            $accountingService = new AccountingIntegrationService();
            $accountingService->createSaleJournalEntry($sale);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta confirmada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Anular venta (devolver stock)
     */
    public function cancel(Sale $sale)
    {
        if ($sale->status === 'cancelled') {
            return response()->json([
                'errors' => ['general' => ['La venta ya está anulada']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Si estaba confirmada, devolver stock
            if ($sale->status === 'confirmed') {
                foreach ($sale->items as $item) {
                    if ($item->product && $item->product->track_stock) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }

                // Si fue venta en efectivo, reversar el movimiento de caja
                if ($sale->payment_type === 'cash' && $sale->payment_method === 'cash') {
                    // Buscar la caja del usuario para la fecha de la venta
                    $cashRegister = CashRegister::getUserRegisterForDate(
                        Auth::user()->tenant_id,
                        Auth::id(),
                        $sale->sale_date->format('Y-m-d')
                    );

                    if ($cashRegister && $cashRegister->status === 'open') {
                        // Registrar movimiento de reversa en caja
                        $cashRegister->movements()->create([
                            'type' => 'expense',
                            'concept' => 'other',
                            'amount' => $sale->total,
                            'description' => 'Anulación de venta ' . $sale->sale_number,
                            'reference' => $sale->sale_number,
                            'sale_id' => $sale->id,
                        ]);

                        // Actualizar totales de caja (restar de ventas en efectivo)
                        $cashRegister->sales_cash -= $sale->total;
                        $cashRegister->calculateExpectedBalance();
                        $cashRegister->save();
                    }
                }

                // Si fue venta por transferencia, cancelar la transacción bancaria
                if ($sale->payment_type === 'cash' && $sale->payment_method === 'transfer') {
                    // Buscar la transacción bancaria relacionada
                    $bankTransaction = BankTransaction::where('tenant_id', Auth::user()->tenant_id)
                        ->where('reference', $sale->sale_number)
                        ->where('type', 'deposit')
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
                if ($sale->journal_entry_id) {
                    $accountingService = new AccountingIntegrationService();
                    $accountingService->reverseSaleJournalEntry($sale);
                }
            }

            $sale->status = 'cancelled';
            $sale->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta anulada correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => ['Error al anular la venta: ' . $e->getMessage()]]
            ], 422);
        }
    }

    /**
     * Eliminar venta (solo borradores)
     */
    public function destroy(Sale $sale)
    {
        if ($sale->status !== 'draft') {
            return response()->json([
                'errors' => ['general' => ['Solo se pueden eliminar ventas en borrador']]
            ], 422);
        }

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Venta eliminada correctamente'
        ]);
    }

    /**
     * Ver detalle de venta (vista)
     */
    public function detail(Sale $sale)
    {
        $sale->load(['customer', 'user', 'items.product', 'accountReceivable', 'journalEntry']);
        return view('sales.detail', compact('sale'));
    }

    /**
     * Generar PDF de la factura
     */
    public function generatePDF(Sale $sale)
    {
        $tenantId = Auth::user()->tenant_id;

        // Verificar pertenencia al tenant
        if ($sale->tenant_id != $tenantId) {
            abort(403);
        }

        $sale->load(['customer', 'user', 'items.product']);
        $companySettings = CompanySetting::where('tenant_id', $tenantId)->first();

        $pdf = Pdf::loadView('pdf.sale-invoice', compact('sale', 'companySettings'));

        return $pdf->stream('factura-' . $sale->sale_number . '.pdf');
    }

    /**
     * Descargar PDF de la factura
     */
    public function downloadPDF(Sale $sale)
    {
        $tenantId = Auth::user()->tenant_id;

        // Verificar pertenencia al tenant
        if ($sale->tenant_id != $tenantId) {
            abort(403);
        }

        $sale->load(['customer', 'user', 'items.product']);
        $companySettings = CompanySetting::where('tenant_id', $tenantId)->first();

        $pdf = Pdf::loadView('pdf.sale-invoice', compact('sale', 'companySettings'));

        return $pdf->download('factura-' . $sale->sale_number . '.pdf');
    }

    /**
     * Obtener listado de ventas para combo/select
     */
    public function list(Request $request)
    {
        $search = $request->get('q');
        $status = $request->get('status');

        $query = Sale::with(['customer'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('sale_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('id', 'desc')
            ->limit(20);

        $sales = $query->get()->map(function ($sale) {
            return [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'sale_date' => $sale->sale_date->format('d/m/Y'),
                'customer_name' => $sale->customer ? $sale->customer->name : 'Sin cliente',
                'total' => number_format($sale->total, 0, ',', '.'),
            ];
        });

        return response()->json($sales);
    }
}
