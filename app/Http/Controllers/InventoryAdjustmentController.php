<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InventoryAdjustmentController extends Controller
{
    public function index()
    {
        return view('inventory-adjustments.index');
    }

    public function data(Request $request)
    {
        $page = $request->get('page', 1);
        $rows = $request->get('rows', 20);
        $sort = $request->get('sort', 'id');
        $order = $request->get('order', 'desc');
        $search = $request->get('search', '');

        $query = InventoryAdjustment::with('user')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('adjustment_number', 'like', "%{$search}%")
                        ->orWhere('reason', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $order);

        $total = $query->count();
        $adjustments = $query->skip(($page - 1) * $rows)->take($rows)->get();

        $data = $adjustments->map(function ($adj) {
            return [
                'id' => $adj->id,
                'adjustment_number' => $adj->adjustment_number,
                'adjustment_date' => $adj->adjustment_date->format('Y-m-d'),
                'type' => $adj->type,
                'reason' => $adj->reason,
                'items_count' => $adj->items()->count(),
                'status' => $adj->status,
                'user_name' => $adj->user->name ?? '',
            ];
        });

        return response()->json([
            'total' => $total,
            'rows' => $data,
        ]);
    }

    public function create()
    {
        $adjustmentNumber = InventoryAdjustment::generateNumber(auth()->user()->tenant_id);
        return view('inventory-adjustments.create', compact('adjustmentNumber'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adjustment_date' => 'required|date',
            'type' => 'required|in:in,out',
            'reason' => 'required|string|max:100',
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

            $adjustment = InventoryAdjustment::create([
                'tenant_id' => auth()->user()->tenant_id,
                'adjustment_number' => InventoryAdjustment::generateNumber(auth()->user()->tenant_id),
                'adjustment_date' => $request->adjustment_date,
                'user_id' => auth()->id(),
                'type' => $request->type,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => 'draft',
            ]);

            foreach ($request->items as $itemData) {
                $product = Product::find($itemData['product_id']);

                $adjustment->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $product->purchase_price,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ajuste de inventario creado exitosamente',
                'data' => $adjustment,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el ajuste: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(InventoryAdjustment $inventoryAdjustment)
    {
        $inventoryAdjustment->load(['user', 'items.product']);
        return response()->json($inventoryAdjustment);
    }

    public function confirm(InventoryAdjustment $inventoryAdjustment)
    {
        if ($inventoryAdjustment->status !== 'draft') {
            return response()->json([
                'errors' => ['status' => ['Solo se pueden confirmar ajustes en borrador']],
            ], 422);
        }

        try {
            DB::beginTransaction();

            foreach ($inventoryAdjustment->items as $item) {
                $product = $item->product;

                if ($inventoryAdjustment->type === 'in') {
                    $product->stock += $item->quantity;
                } else {
                    $product->stock -= $item->quantity;
                }

                $product->save();
            }

            $inventoryAdjustment->status = 'confirmed';
            $inventoryAdjustment->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ajuste confirmado. Stock actualizado.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
            ], 500);
        }
    }

    public function cancel(InventoryAdjustment $inventoryAdjustment)
    {
        if ($inventoryAdjustment->status === 'cancelled') {
            return response()->json([
                'errors' => ['status' => ['El ajuste ya está anulado']],
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Si estaba confirmado, revertir el stock
            if ($inventoryAdjustment->status === 'confirmed') {
                foreach ($inventoryAdjustment->items as $item) {
                    $product = $item->product;

                    if ($inventoryAdjustment->type === 'in') {
                        $product->stock -= $item->quantity;
                    } else {
                        $product->stock += $item->quantity;
                    }

                    $product->save();
                }
            }

            $inventoryAdjustment->status = 'cancelled';
            $inventoryAdjustment->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ajuste anulado exitosamente.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'errors' => ['general' => [$e->getMessage()]],
            ], 500);
        }
    }

    public function destroy(InventoryAdjustment $inventoryAdjustment)
    {
        if ($inventoryAdjustment->status !== 'draft') {
            return response()->json([
                'errors' => ['status' => ['Solo se pueden eliminar ajustes en borrador']],
            ], 422);
        }

        try {
            $inventoryAdjustment->delete();
            return response()->json([
                'success' => true,
                'message' => 'Ajuste eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el ajuste',
            ], 500);
        }
    }
}
