<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');

        // Ventas del mes
        $salesMonth = Sale::whereBetween('sale_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'confirmed')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        // Ventas de hoy
        $salesToday = Sale::where('sale_date', $today)
            ->where('status', 'confirmed')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        // Compras del mes
        $purchasesMonth = Purchase::whereBetween('purchase_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'confirmed')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        // Gastos del mes
        $expensesMonth = Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'paid')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->first();

        // Productos con stock bajo
        $lowStockProducts = Product::where('is_active', true)
            ->whereRaw('stock <= min_stock')
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get(['id', 'code', 'name', 'stock', 'min_stock', 'unit']);

        // Últimas ventas
        $recentSales = Sale::with('customer')
            ->where('status', 'confirmed')
            ->orderBy('sale_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Ventas por día (últimos 7 días)
        $salesByDay = Sale::where('status', 'confirmed')
            ->where('sale_date', '>=', now()->subDays(6)->format('Y-m-d'))
            ->selectRaw('sale_date, COUNT(*) as count, SUM(total) as total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        // Contadores generales
        $totalCustomers = Customer::where('is_active', true)->count();
        $totalSuppliers = Supplier::where('is_active', true)->count();
        $totalProducts = Product::where('is_active', true)->count();

        // Calcular ganancia bruta del mes
        $profit = ($salesMonth->total ?? 0) - ($purchasesMonth->total ?? 0) - ($expensesMonth->total ?? 0);

        return view('dashboard', compact(
            'salesMonth',
            'salesToday',
            'purchasesMonth',
            'expensesMonth',
            'lowStockProducts',
            'recentSales',
            'salesByDay',
            'totalCustomers',
            'totalSuppliers',
            'totalProducts',
            'profit'
        ));
    }
}
