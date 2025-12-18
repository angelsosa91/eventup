<?php

namespace App\Http\Controllers;

use App\Models\CreditNote;
use App\Models\CreditNoteItem;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\AccountReceivable;
use App\Services\AccountingIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditNoteController extends Controller
{
    protected $accountingService;

    public function __construct(AccountingIntegrationService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('credit-notes.index');
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

        $query = CreditNote::with(['customer', 'sale', 'createdBy'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('credit_note_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('sale', function ($q) use ($search) {
                            $q->where('sale_number', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $creditNotes = $query->skip(($page - 1) * $rows)->take($rows)->get();

        return response()->json([
            'total' => $total,
            'rows' => $creditNotes->map(function ($creditNote) {
                return [
                    'id' => $creditNote->id,
                    'credit_note_number' => $creditNote->credit_note_number,
                    'date' => $creditNote->date->format('d/m/Y'),
                    'sale_number' => $creditNote->sale->sale_number,
                    'customer_name' => $creditNote->customer->name,
                    'reason_text' => $creditNote->reason_text,
                    'type_text' => $creditNote->type_text,
                    'total' => number_format($creditNote->total, 0, ',', '.'),
                    'status' => $creditNote->status,
                    'status_text' => $creditNote->status_text,
                    'created_by' => $creditNote->createdBy->name,
                ];
            }),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $saleId = $request->get('sale_id');
        $sale = null;

        if ($saleId) {
            $sale = Sale::with(['customer', 'items.product'])->findOrFail($saleId);
        }

        return view('credit-notes.create', compact('sale'));
    }

    /**
     * Get sale details for credit note
     */
    public function getSaleDetails($saleId)
    {
        $sale = Sale::with(['customer', 'items.product'])->findOrFail($saleId);

        // Verificar si la venta está confirmada
        if ($sale->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden crear notas de crédito para ventas confirmadas'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'sale' => [
                'id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'sale_date' => $sale->sale_date->format('d/m/Y'),
                'customer_id' => $sale->customer_id,
                'customer_name' => $sale->customer->name,
                'subtotal_exento' => $sale->subtotal_exento,
                'subtotal_5' => $sale->subtotal_5,
                'iva_5' => $sale->iva_5,
                'subtotal_10' => $sale->subtotal_10,
                'iva_10' => $sale->iva_10,
                'total' => $sale->total,
                'items' => $sale->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'iva_type' => $item->tax_rate,
                        'subtotal' => $item->subtotal,
                        'iva_amount' => $item->tax_amount,
                        'total' => $item->total,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sale_id' => 'required|exists:sales,id',
            'date' => 'required|date',
            'reason' => 'required|in:return,discount,error,cancellation',
            'type' => 'required|in:total,partial',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.iva_type' => 'required|in:0,5,10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($request->sale_id);

            // Verificar que la venta esté confirmada
            if ($sale->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden crear notas de crédito para ventas confirmadas'
                ], 422);
            }

            // Calcular totales
            $subtotal0 = 0;
            $subtotal5 = 0;
            $subtotal10 = 0;
            $iva5 = 0;
            $iva10 = 0;

            foreach ($request->items as $item) {
                $quantity = $item['quantity'];
                $price = $item['price'];
                $ivaType = $item['iva_type'];

                $subtotal = $quantity * $price;

                switch ($ivaType) {
                    case '0':
                        $subtotal0 += $subtotal;
                        break;
                    case '5':
                        $ivaAmount = $subtotal * 0.05;
                        $subtotal5 += $subtotal;
                        $iva5 += $ivaAmount;
                        break;
                    case '10':
                        $ivaAmount = $subtotal * 0.10;
                        $subtotal10 += $subtotal;
                        $iva10 += $ivaAmount;
                        break;
                }
            }

            $total = $subtotal0 + $subtotal5 + $subtotal10;

            // Crear la nota de crédito
            $creditNote = CreditNote::create([
                'tenant_id' => Auth::user()->tenant_id,
                'credit_note_number' => CreditNote::generateCreditNoteNumber(),
                'sale_id' => $request->sale_id,
                'customer_id' => $sale->customer_id,
                'date' => $request->date,
                'reason' => $request->reason,
                'type' => $request->type,
                'subtotal_0' => $subtotal0,
                'subtotal_5' => $subtotal5,
                'subtotal_10' => $subtotal10,
                'iva_5' => $iva5,
                'iva_10' => $iva10,
                'total' => $total,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Crear los items de la nota de crédito
            foreach ($request->items as $item) {
                $quantity = $item['quantity'];
                $price = $item['price'];
                $ivaType = $item['iva_type'];

                $subtotal = $quantity * $price;
                $ivaAmount = 0;

                if ($ivaType == '5') {
                    $ivaAmount = $subtotal * 0.05;
                } elseif ($ivaType == '10') {
                    $ivaAmount = $subtotal * 0.10;
                }

                $itemTotal = $subtotal;

                CreditNoteItem::create([
                    'credit_note_id' => $creditNote->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'price' => $price,
                    'iva_type' => $ivaType,
                    'subtotal' => $subtotal,
                    'iva_amount' => $ivaAmount,
                    'total' => $itemTotal,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nota de crédito creada exitosamente',
                'credit_note' => $creditNote,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la nota de crédito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $creditNote = CreditNote::with(['customer', 'sale', 'items.product', 'createdBy'])
            ->findOrFail($id);

        return view('credit-notes.show', compact('creditNote'));
    }

    /**
     * Confirm credit note
     */
    public function confirm($id)
    {
        try {
            DB::beginTransaction();

            $creditNote = CreditNote::with(['items.product', 'sale'])->findOrFail($id);

            if ($creditNote->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden confirmar notas de crédito en estado borrador'
                ], 422);
            }

            // Actualizar estado
            $creditNote->status = 'confirmed';
            $creditNote->save();

            // Devolver productos al stock
            foreach ($creditNote->items as $item) {
                $product = $item->product;
                $product->stock += $item->quantity;
                $product->save();
            }

            // Crear asiento contable de reversión
            $this->accountingService->createCreditNoteJournalEntry($creditNote);

            // Si la venta fue a crédito, reducir la cuenta por cobrar
            if ($creditNote->sale->payment_type === 'credit') {
                $accountReceivable = $creditNote->sale->accountReceivable;
                if ($accountReceivable) {
                    // Reducir el monto de la cuenta por cobrar
                    $accountReceivable->total_amount -= $creditNote->total;
                    $accountReceivable->balance -= $creditNote->total;

                    // Si el balance llega a 0, marcar como pagado
                    if ($accountReceivable->balance <= 0) {
                        $accountReceivable->status = 'paid';
                        $accountReceivable->balance = 0;
                    }

                    $accountReceivable->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nota de crédito confirmada exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al confirmar la nota de crédito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel credit note
     */
    public function cancel($id)
    {
        try {
            $creditNote = CreditNote::findOrFail($id);

            if ($creditNote->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'La nota de crédito ya está anulada'
                ], 422);
            }

            if ($creditNote->status === 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede anular una nota de crédito confirmada'
                ], 422);
            }

            $creditNote->status = 'cancelled';
            $creditNote->save();

            return response()->json([
                'success' => true,
                'message' => 'Nota de crédito anulada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al anular la nota de crédito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF
     */
    public function generatePDF($id)
    {
        $creditNote = CreditNote::with(['customer', 'sale', 'items.product', 'createdBy'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.credit-note', compact('creditNote'));

        return $pdf->stream('nota-credito-' . $creditNote->credit_note_number . '.pdf');
    }

    /**
     * Download PDF
     */
    public function downloadPDF($id)
    {
        $creditNote = CreditNote::with(['customer', 'sale', 'items.product', 'createdBy'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.credit-note', compact('creditNote'));

        return $pdf->download('nota-credito-' . $creditNote->credit_note_number . '.pdf');
    }

    /**
     * Get list for combo
     */
    public function list(Request $request)
    {
        $search = $request->get('q');

        $creditNotes = CreditNote::query()
            ->when($search, function ($q) use ($search) {
                $q->where('credit_note_number', 'like', "%{$search}%");
            })
            ->orderBy('credit_note_number', 'asc')
            ->limit(20)
            ->get(['id', 'credit_note_number as text']);

        return response()->json($creditNotes);
    }
}
