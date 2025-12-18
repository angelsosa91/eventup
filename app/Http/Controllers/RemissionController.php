<?php

namespace App\Http\Controllers;

use App\Models\Remission;
use App\Models\RemissionItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class RemissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('remissions.index');
    }

    /**
     * Get data for DataGrid
     */
    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search');

        $query = Remission::with(['customer', 'createdBy', 'sale'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('remission_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $remissions = $query->skip(($page - 1) * $rows)->take($rows)->get();

        return response()->json([
            'total' => $total,
            'rows' => $remissions->map(function ($remission) {
                return [
                    'id' => $remission->id,
                    'remission_number' => $remission->remission_number,
                    'date' => $remission->date->format('d/m/Y'),
                    'customer_name' => $remission->customer->name,
                    'reason_text' => $remission->reason_text,
                    'delivery_address' => $remission->delivery_address ?? '-',
                    'sale_number' => $remission->sale ? $remission->sale->sale_number : '-',
                    'status' => $remission->status,
                    'status_text' => $remission->status_text,
                    'created_by' => $remission->createdBy->name,
                    'can_convert' => $remission->canBeConvertedToInvoice(),
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('remissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'delivery_address' => 'nullable|string|max:255',
            'reason' => 'required|in:transfer,consignment,demo,delivery',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Crear la remisión
            $remission = Remission::create([
                'tenant_id' => Auth::user()->tenant_id,
                'remission_number' => Remission::generateRemissionNumber(),
                'customer_id' => $request->customer_id,
                'date' => $request->date,
                'delivery_address' => $request->delivery_address,
                'reason' => $request->reason,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Crear los items de la remisión
            foreach ($request->items as $item) {
                RemissionItem::create([
                    'remission_id' => $remission->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'reserved_quantity' => 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remisión creada exitosamente',
                'remission' => $remission,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la remisión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $remission = Remission::with(['customer', 'items.product', 'createdBy', 'sale'])
            ->findOrFail($id);

        return view('remissions.show', compact('remission'));
    }

    /**
     * Confirm remission (reserve stock)
     */
    public function confirm($id)
    {
        try {
            DB::beginTransaction();

            $remission = Remission::with('items.product')->findOrFail($id);

            if (!$remission->canBeConfirmed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden confirmar remisiones en estado borrador'
                ], 422);
            }

            // Verificar stock disponible
            foreach ($remission->items as $item) {
                $product = $item->product;
                if ($product->stock < $item->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stock insuficiente para el producto: {$product->name}. Disponible: {$product->stock}, Requerido: {$item->quantity}"
                    ], 422);
                }
            }

            // Reservar stock (no reducir, solo marcar como reservado)
            foreach ($remission->items as $item) {
                $item->reserved_quantity = $item->quantity;
                $item->save();
            }

            // Actualizar estado
            $remission->status = 'confirmed';
            $remission->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remisión confirmada exitosamente. Stock reservado.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar la remisión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark as delivered
     */
    public function deliver($id)
    {
        try {
            $remission = Remission::findOrFail($id);

            if (!$remission->canBeDelivered()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden marcar como entregadas las remisiones confirmadas'
                ], 422);
            }

            $remission->status = 'delivered';
            $remission->save();

            return response()->json([
                'success' => true,
                'message' => 'Remisión marcada como entregada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como entregada: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert to sale/invoice
     */
    public function convertToSale($id, Request $request)
    {
        try {
            DB::beginTransaction();

            $remission = Remission::with(['items.product', 'customer'])->findOrFail($id);

            if (!$remission->canBeConvertedToInvoice()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta remisión no puede ser convertida a factura'
                ], 422);
            }

            // Validar datos adicionales de la venta
            $validator = Validator::make($request->all(), [
                'payment_type' => 'required|in:cash,credit',
                'payment_method' => 'required_if:payment_type,cash|string',
                'credit_days' => 'required_if:payment_type,credit|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Calcular totales
            $subtotalExento = 0;
            $subtotal5 = 0;
            $iva5 = 0;
            $subtotal10 = 0;
            $iva10 = 0;

            foreach ($remission->items as $item) {
                $product = $item->product;
                $subtotal = $item->quantity * $product->price;

                switch ($product->tax_rate) {
                    case 0:
                        $subtotalExento += $subtotal;
                        break;
                    case 5:
                        $subtotal5 += $subtotal;
                        $iva5 += $subtotal * 0.05;
                        break;
                    case 10:
                        $subtotal10 += $subtotal;
                        $iva10 += $subtotal * 0.10;
                        break;
                }
            }

            $total = $subtotalExento + $subtotal5 + $subtotal10;

            // Calcular fecha de vencimiento si es a crédito
            $creditDueDate = null;
            if ($request->payment_type === 'credit') {
                $creditDueDate = now()->addDays($request->credit_days)->toDateString();
            }

            // Crear la venta
            $sale = Sale::create([
                'tenant_id' => Auth::user()->tenant_id,
                'sale_number' => Sale::generateSaleNumber(Auth::user()->tenant_id),
                'customer_id' => $remission->customer_id,
                'user_id' => Auth::id(),
                'sale_date' => now()->toDateString(),
                'subtotal_exento' => $subtotalExento,
                'subtotal_5' => $subtotal5,
                'iva_5' => $iva5,
                'subtotal_10' => $subtotal10,
                'iva_10' => $iva10,
                'total' => $total,
                'payment_type' => $request->payment_type,
                'payment_method' => $request->payment_method ?? null,
                'credit_days' => $request->credit_days ?? null,
                'credit_due_date' => $creditDueDate,
                'status' => 'draft',
                'notes' => "Generada desde remisión {$remission->remission_number}",
            ]);

            // Crear items de la venta
            foreach ($remission->items as $item) {
                $product = $item->product;
                $quantity = $item->quantity;
                $price = $product->price;
                $subtotal = $quantity * $price;
                $taxAmount = 0;

                if ($product->tax_rate == 5) {
                    $taxAmount = $subtotal * 0.05;
                } elseif ($product->tax_rate == 10) {
                    $taxAmount = $subtotal * 0.10;
                }

                $itemTotal = $subtotal;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'tax_rate' => $product->tax_rate,
                    'tax_amount' => $taxAmount,
                    'total' => $itemTotal,
                ]);
            }

            // Actualizar remisión
            $remission->status = 'invoiced';
            $remission->sale_id = $sale->id;
            $remission->save();

            // Liberar reservas (las cantidades reservadas ya no son necesarias)
            foreach ($remission->items as $item) {
                $item->reserved_quantity = 0;
                $item->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remisión convertida a factura exitosamente',
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al convertir a factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel remission
     */
    public function cancel($id)
    {
        try {
            DB::beginTransaction();

            $remission = Remission::with('items')->findOrFail($id);

            if (!$remission->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta remisión no puede ser anulada'
                ], 422);
            }

            // Liberar reservas si estaban confirmadas
            if ($remission->status === 'confirmed') {
                foreach ($remission->items as $item) {
                    $item->reserved_quantity = 0;
                    $item->save();
                }
            }

            $remission->status = 'cancelled';
            $remission->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Remisión anulada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al anular la remisión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF
     */
    public function generatePDF($id)
    {
        $remission = Remission::with(['customer', 'items.product', 'createdBy'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.remission', compact('remission'));

        return $pdf->stream('remision-' . $remission->remission_number . '.pdf');
    }

    /**
     * Download PDF
     */
    public function downloadPDF($id)
    {
        $remission = Remission::with(['customer', 'items.product', 'createdBy'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.remission', compact('remission'));

        return $pdf->download('remision-' . $remission->remission_number . '.pdf');
    }

    /**
     * Get list for combo
     */
    public function list(Request $request)
    {
        $search = $request->get('q');
        $status = $request->get('status');

        $remissions = Remission::query()
            ->when($search, function ($q) use ($search) {
                $q->where('remission_number', 'like', "%{$search}%");
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('remission_number', 'desc')
            ->limit(20)
            ->get(['id', 'remission_number as text', 'status']);

        return response()->json($remissions);
    }
}
