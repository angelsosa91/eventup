<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\CashRegister;
use App\Models\AccountReceivable;
use App\Models\AccountPayable;
use App\Models\AccountReceivablePayment;
use App\Models\AccountPayablePayment;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Reporte de ventas
     */
    public function sales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $status = $request->get('status', '');
        $customerId = $request->get('customer_id', '');

        $query = Sale::with(['customer', 'user'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($customerId, function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            })
            ->orderBy('sale_date', 'desc');

        $sales = $query->get();

        $totals = [
            'count' => $sales->count(),
            'subtotal_exento' => $sales->sum('subtotal_exento'),
            'subtotal_5' => $sales->sum('subtotal_5'),
            'iva_5' => $sales->sum('iva_5'),
            'subtotal_10' => $sales->sum('subtotal_10'),
            'iva_10' => $sales->sum('iva_10'),
            'total' => $sales->sum('total'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'sales' => $sales->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'sale_number' => $sale->sale_number,
                        'sale_date' => $sale->sale_date->format('d/m/Y'),
                        'customer_name' => $sale->customer->name ?? 'Sin cliente',
                        'subtotal_exento' => $sale->subtotal_exento,
                        'subtotal_5' => $sale->subtotal_5,
                        'iva_5' => $sale->iva_5,
                        'subtotal_10' => $sale->subtotal_10,
                        'iva_10' => $sale->iva_10,
                        'total' => $sale->total,
                        'status' => $sale->status,
                        'user_name' => $sale->user->name ?? '',
                    ];
                }),
                'totals' => $totals,
            ]);
        }

        return view('reports.sales', compact('startDate', 'endDate', 'status', 'customerId'));
    }

    /**
     * Reporte de compras
     */
    public function purchases(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $status = $request->get('status', '');
        $supplierId = $request->get('supplier_id', '');

        $query = Purchase::with(['supplier', 'user'])
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($supplierId, function ($q) use ($supplierId) {
                $q->where('supplier_id', $supplierId);
            })
            ->orderBy('purchase_date', 'desc');

        $purchases = $query->get();

        $totals = [
            'count' => $purchases->count(),
            'subtotal_exento' => $purchases->sum('subtotal_exento'),
            'subtotal_5' => $purchases->sum('subtotal_5'),
            'iva_5' => $purchases->sum('iva_5'),
            'subtotal_10' => $purchases->sum('subtotal_10'),
            'iva_10' => $purchases->sum('iva_10'),
            'total' => $purchases->sum('total'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'purchases' => $purchases->map(function ($purchase) {
                    return [
                        'id' => $purchase->id,
                        'purchase_number' => $purchase->purchase_number,
                        'purchase_date' => $purchase->purchase_date->format('d/m/Y'),
                        'supplier_name' => $purchase->supplier->name ?? 'Sin proveedor',
                        'invoice_number' => $purchase->invoice_number,
                        'subtotal_exento' => $purchase->subtotal_exento,
                        'subtotal_5' => $purchase->subtotal_5,
                        'iva_5' => $purchase->iva_5,
                        'subtotal_10' => $purchase->subtotal_10,
                        'iva_10' => $purchase->iva_10,
                        'total' => $purchase->total,
                        'status' => $purchase->status,
                        'user_name' => $purchase->user->name ?? '',
                    ];
                }),
                'totals' => $totals,
            ]);
        }

        return view('reports.purchases', compact('startDate', 'endDate', 'status', 'supplierId'));
    }

    /**
     * Reporte de gastos
     */
    public function expenses(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $status = $request->get('status', '');
        $categoryId = $request->get('category_id', '');

        $query = Expense::with(['category', 'supplier', 'user'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('expense_category_id', $categoryId);
            })
            ->orderBy('expense_date', 'desc');

        $expenses = $query->get();

        $totals = [
            'count' => $expenses->count(),
            'amount' => $expenses->sum('amount'),
            'tax_amount' => $expenses->sum('tax_amount'),
        ];

        // Agrupar por categoría
        $byCategory = $expenses->groupBy('expense_category_id')->map(function ($items, $key) {
            return [
                'category' => $items->first()->category->name ?? 'Sin categoría',
                'count' => $items->count(),
                'amount' => $items->sum('amount'),
            ];
        })->values();

        if ($request->ajax()) {
            return response()->json([
                'expenses' => $expenses->map(function ($expense) {
                    return [
                        'id' => $expense->id,
                        'expense_number' => $expense->expense_number,
                        'expense_date' => $expense->expense_date->format('d/m/Y'),
                        'category_name' => $expense->category->name ?? 'Sin categoría',
                        'description' => $expense->description,
                        'supplier_name' => $expense->supplier->name ?? '-',
                        'amount' => $expense->amount,
                        'tax_amount' => $expense->tax_amount,
                        'status' => $expense->status,
                        'user_name' => $expense->user->name ?? '',
                    ];
                }),
                'totals' => $totals,
                'byCategory' => $byCategory,
            ]);
        }

        return view('reports.expenses', compact('startDate', 'endDate', 'status', 'categoryId'));
    }

    /**
     * Reporte de inventario
     */
    public function inventory(Request $request)
    {
        $categoryId = $request->get('category_id', '');
        $stockFilter = $request->get('stock_filter', ''); // low, zero, all

        $query = Product::with('category')
            ->where('is_active', true)
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->when($stockFilter === 'low', function ($q) {
                $q->whereRaw('stock <= min_stock AND stock > 0');
            })
            ->when($stockFilter === 'zero', function ($q) {
                $q->where('stock', '<=', 0);
            })
            ->orderBy('name');

        $products = $query->get();

        $totals = [
            'count' => $products->count(),
            'total_stock_value' => $products->sum(function ($product) {
                return $product->stock * $product->purchase_price;
            }),
            'total_sale_value' => $products->sum(function ($product) {
                return $product->stock * $product->sale_price;
            }),
        ];

        if ($request->ajax()) {
            return response()->json([
                'products' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'code' => $product->code,
                        'name' => $product->name,
                        'category_name' => $product->category->name ?? 'Sin categoría',
                        'stock' => $product->stock,
                        'min_stock' => $product->min_stock,
                        'unit' => $product->unit,
                        'purchase_price' => $product->purchase_price,
                        'sale_price' => $product->sale_price,
                        'stock_value' => $product->stock * $product->purchase_price,
                        'is_low' => $product->stock <= $product->min_stock && $product->stock > 0,
                        'is_zero' => $product->stock <= 0,
                    ];
                }),
                'totals' => $totals,
            ]);
        }

        return view('reports.inventory', compact('categoryId', 'stockFilter'));
    }

    /**
     * Resumen general (para dashboard)
     */
    public function summary(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Ventas del período
        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->selectRaw('COUNT(*) as count, SUM(total) as total')
            ->first();

        // Compras del período
        $purchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->selectRaw('COUNT(*) as count, SUM(total) as total')
            ->first();

        // Gastos del período
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->where('status', 'paid')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        // Productos con stock bajo
        $lowStock = Product::where('is_active', true)
            ->whereRaw('stock <= min_stock')
            ->count();

        return response()->json([
            'sales' => [
                'count' => $sales->count ?? 0,
                'total' => $sales->total ?? 0,
            ],
            'purchases' => [
                'count' => $purchases->count ?? 0,
                'total' => $purchases->total ?? 0,
            ],
            'expenses' => [
                'count' => $expenses->count ?? 0,
                'total' => $expenses->total ?? 0,
            ],
            'low_stock' => $lowStock,
            'profit' => ($sales->total ?? 0) - ($purchases->total ?? 0) - ($expenses->total ?? 0),
        ]);
    }

    /**
     * Reporte de Flujo de Caja
     */
    public function cashFlow(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Ingresos
        $salesIncome = AccountReceivablePayment::whereHas('accountReceivable', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as amount'),
                DB::raw("'Cobro de Venta' as description"),
                DB::raw("'income' as type")
            )
            ->groupBy('date')
            ->get();

        // Egresos - Pagos a proveedores
        $purchasePayments = AccountPayablePayment::whereHas('accountPayable', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as amount'),
                DB::raw("'Pago a Proveedor' as description"),
                DB::raw("'expense' as type")
            )
            ->groupBy('date')
            ->get();

        // Egresos - Gastos
        $expenses = Expense::where('tenant_id', $tenantId)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(expense_date) as date'),
                DB::raw('SUM(amount) as amount'),
                DB::raw("'Gasto Operativo' as description"),
                DB::raw("'expense' as type")
            )
            ->groupBy('date')
            ->get();

        // Combinar todos los movimientos
        $movements = $salesIncome->concat($purchasePayments)->concat($expenses)
            ->sortBy('date')
            ->values();

        // Calcular saldo acumulado
        $balance = 0;
        $movementsWithBalance = $movements->map(function($item) use (&$balance) {
            if ($item->type === 'income') {
                $balance += $item->amount;
            } else {
                $balance -= $item->amount;
            }
            $item->balance = $balance;
            return $item;
        });

        // Totales
        $totalIncome = $salesIncome->sum('amount');
        $totalExpense = $purchasePayments->sum('amount') + $expenses->sum('amount');
        $netCashFlow = $totalIncome - $totalExpense;

        return view('reports.cash-flow', compact(
            'movementsWithBalance',
            'totalIncome',
            'totalExpense',
            'netCashFlow',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Reporte de Antigüedad de Saldos (Aging Report)
     */
    public function agingReport(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $type = $request->get('type', 'receivable'); // receivable o payable
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        if ($type === 'receivable') {
            // Cuentas por Cobrar
            $accounts = AccountReceivable::with(['sale.customer'])
                ->where('tenant_id', $tenantId)
                ->where('balance', '>', 0)
                ->get()
                ->map(function($account) use ($asOfDate) {
                    $dueDate = Carbon::parse($account->due_date);
                    $asOf = Carbon::parse($asOfDate);
                    $daysOverdue = $asOf->diffInDays($dueDate, false);

                    $account->days_overdue = $daysOverdue > 0 ? 0 : abs($daysOverdue);
                    $account->partner_name = $account->sale->customer->name;
                    $account->document_number = $account->sale->sale_number;
                    $account->date = $account->sale->sale_date;

                    // Clasificar por antigüedad
                    if ($daysOverdue > 0) {
                        $account->aging_bucket = 'current';
                    } elseif ($daysOverdue >= -30) {
                        $account->aging_bucket = '1-30';
                    } elseif ($daysOverdue >= -60) {
                        $account->aging_bucket = '31-60';
                    } elseif ($daysOverdue >= -90) {
                        $account->aging_bucket = '61-90';
                    } else {
                        $account->aging_bucket = '90+';
                    }

                    return $account;
                });
        } else {
            // Cuentas por Pagar
            $accounts = AccountPayable::with(['purchase.supplier'])
                ->where('tenant_id', $tenantId)
                ->where('balance', '>', 0)
                ->get()
                ->map(function($account) use ($asOfDate) {
                    $dueDate = Carbon::parse($account->due_date);
                    $asOf = Carbon::parse($asOfDate);
                    $daysOverdue = $asOf->diffInDays($dueDate, false);

                    $account->days_overdue = $daysOverdue > 0 ? 0 : abs($daysOverdue);
                    $account->partner_name = $account->purchase->supplier->name;
                    $account->document_number = $account->purchase->purchase_number;
                    $account->date = $account->purchase->purchase_date;

                    // Clasificar por antigüedad
                    if ($daysOverdue > 0) {
                        $account->aging_bucket = 'current';
                    } elseif ($daysOverdue >= -30) {
                        $account->aging_bucket = '1-30';
                    } elseif ($daysOverdue >= -60) {
                        $account->aging_bucket = '31-60';
                    } elseif ($daysOverdue >= -90) {
                        $account->aging_bucket = '61-90';
                    } else {
                        $account->aging_bucket = '90+';
                    }

                    return $account;
                });
        }

        // Totales por bucket
        $summary = [
            'current' => $accounts->where('aging_bucket', 'current')->sum('balance'),
            '1-30' => $accounts->where('aging_bucket', '1-30')->sum('balance'),
            '31-60' => $accounts->where('aging_bucket', '31-60')->sum('balance'),
            '61-90' => $accounts->where('aging_bucket', '61-90')->sum('balance'),
            '90+' => $accounts->where('aging_bucket', '90+')->sum('balance'),
        ];
        $summary['total'] = array_sum($summary);

        return view('reports.aging-report', compact('accounts', 'summary', 'type', 'asOfDate'));
    }

    /**
     * Reporte de Productos Más Vendidos
     */
    public function topProducts(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $limit = $request->get('limit', 20);

        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.status', 'confirmed')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->select(
                'products.id',
                'products.name',
                'products.code',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.subtotal) / SUM(sale_items.quantity) as avg_price'),
                DB::raw('COUNT(DISTINCT sales.id) as total_orders')
            )
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        return view('reports.top-products', compact(
            'topProducts',
            'startDate',
            'endDate',
            'limit'
        ));
    }

    /**
     * Reporte de Rentabilidad por Producto
     */
    public function profitability(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $products = Product::where('tenant_id', $tenantId)
            ->with(['saleItems' => function($q) use ($startDate, $endDate) {
                $q->whereHas('sale', function($sq) use ($startDate, $endDate) {
                    $sq->where('status', 'confirmed')
                      ->whereBetween('sale_date', [$startDate, $endDate]);
                });
            }])
            ->get()
            ->map(function($product) {
                $totalSold = $product->saleItems->sum('quantity');
                $revenue = $product->saleItems->sum('subtotal');
                $cost = $totalSold * $product->cost;
                $profit = $revenue - $cost;
                $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'quantity_sold' => $totalSold,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'margin' => $margin,
                ];
            })
            ->where('quantity_sold', '>', 0)
            ->sortByDesc('profit')
            ->values();

        $totalRevenue = $products->sum('revenue');
        $totalCost = $products->sum('cost');
        $totalProfit = $products->sum('profit');
        $avgMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        return view('reports.profitability', compact(
            'products',
            'totalRevenue',
            'totalCost',
            'totalProfit',
            'avgMargin',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Reporte de Movimientos de Inventario
     */
    public function inventoryMovements(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $productId = $request->get('product_id', '');

        // Movimientos de salida (ventas)
        $salesOut = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.tenant_id', $tenantId)
            ->where('sales.status', 'confirmed')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->when($productId, function($q) use ($productId) {
                $q->where('sale_items.product_id', $productId);
            })
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->sale->sale_date,
                    'product_name' => $item->product->name,
                    'product_code' => $item->product->code,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_value' => $item->subtotal,
                    'type' => 'OUT',
                    'document_number' => $item->sale->sale_number,
                ];
            });

        // Movimientos de entrada (compras)
        $purchasesIn = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->where('purchases.tenant_id', $tenantId)
            ->where('purchases.status', 'confirmed')
            ->whereBetween('purchases.purchase_date', [$startDate, $endDate])
            ->when($productId, function($q) use ($productId) {
                $q->where('purchase_items.product_id', $productId);
            })
            ->select(
                'purchases.purchase_date as date',
                'products.name as product_name',
                'products.code as product_code',
                'purchase_items.quantity',
                'purchase_items.unit_price',
                DB::raw('purchase_items.quantity * purchase_items.unit_price as total_value'),
                DB::raw("'IN' as type"),
                'purchases.purchase_number as document_number'
            )
            ->get();

        // Combinar y ordenar
        $movements = collect($salesOut)->concat($purchasesIn)
            ->sortBy('date')
            ->values();

        // Resumen
        $totalIn = collect($purchasesIn)->sum('quantity');
        $totalOut = collect($salesOut)->sum('quantity');

        // Obtener todos los productos para el filtro
        $allProducts = Product::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return view('reports.inventory-movements', compact(
            'movements',
            'totalIn',
            'totalOut',
            'allProducts',
            'startDate',
            'endDate',
            'productId'
        ));
    }
}
