<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Event;
use App\Models\EventBudget;
use App\Models\Customer;
use App\Models\CashRegister;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $today = now()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');
        $startOfLastMonth = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $endOfLastMonth = now()->subMonth()->endOfMonth()->format('Y-m-d');

        // ==================== MÉTRICAS FINANCIERAS ====================

        // Aportes del mes
        $contributionsMonth = Contribution::where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->whereBetween('contribution_date', [$startOfMonth, $endOfMonth])
            ->where('amount', '>', 0) // Solo aportes positivos
            ->sum('amount');

        // Aportes del mes pasado (para comparación)
        $contributionsLastMonth = Contribution::where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->whereBetween('contribution_date', [$startOfLastMonth, $endOfLastMonth])
            ->where('amount', '>', 0)
            ->sum('amount');

        // Calcular porcentaje de cambio
        $contributionsChange = 0;
        if ($contributionsLastMonth > 0) {
            $contributionsChange = (($contributionsMonth - $contributionsLastMonth) / $contributionsLastMonth) * 100;
        }

        // Devoluciones del mes
        $refundsMonth = abs(Contribution::where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->whereBetween('contribution_date', [$startOfMonth, $endOfMonth])
            ->where('amount', '<', 0) // Solo devoluciones
            ->sum('amount'));

        // Balance neto (Aportes - Devoluciones)
        $netBalance = $contributionsMonth - $refundsMonth;

        // Saldo en Caja/Banco
        $cashBalance = CashRegister::where('tenant_id', $tenantId)
            ->where('status', 'open')
            ->sum('expected_balance');

        $bankBalance = BankAccount::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->sum('current_balance');

        $totalBalance = $cashBalance + $bankBalance;

        // ==================== MÉTRICAS DE EVENTOS ====================

        // Eventos activos
        $activeEvents = Event::where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->count();

        // Próximo evento
        $upcomingEvent = Event::where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->where('event_date', '>=', $today)
            ->orderBy('event_date', 'asc')
            ->first();

        // Presupuestos pendientes
        $pendingBudgets = EventBudget::where('tenant_id', $tenantId)
            ->where('status', 'draft')
            ->count();

        // Total alumnos inscritos en eventos
        $enrolledStudents = EventBudget::where('tenant_id', $tenantId)
            ->distinct('customer_id')
            ->count('customer_id');

        // ==================== MÉTRICAS DE ALUMNOS ====================

        // Total alumnos activos
        $totalStudents = Customer::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        // Nuevos alumnos del mes
        $newStudentsMonth = Customer::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])
            ->count();

        // Alumnos con saldo pendiente (han aportado)
        $studentsWithBalance = Contribution::where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->distinct('customer_id')
            ->count('customer_id');

        // ==================== GRÁFICOS ====================

        // Aportes vs Devoluciones (últimos 6 meses)
        $contributionsTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $startDate = $month->copy()->startOfMonth()->format('Y-m-d');
            $endDate = $month->copy()->endOfMonth()->format('Y-m-d');

            $monthContributions = Contribution::where('tenant_id', $tenantId)
                ->where('status', 'confirmed')
                ->whereBetween('contribution_date', [$startDate, $endDate])
                ->where('amount', '>', 0)
                ->sum('amount');

            $monthRefunds = abs(Contribution::where('tenant_id', $tenantId)
                ->where('status', 'confirmed')
                ->whereBetween('contribution_date', [$startDate, $endDate])
                ->where('amount', '<', 0)
                ->sum('amount'));

            $contributionsTrend[] = [
                'month' => $month->format('M Y'),
                'contributions' => (float) $monthContributions,
                'refunds' => (float) $monthRefunds,
            ];
        }

        // Aportes por método de pago (mes actual)
        $contributionsByMethod = Contribution::where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->whereBetween('contribution_date', [$startOfMonth, $endOfMonth])
            ->where('amount', '>', 0)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        // ==================== ACTIVIDADES RECIENTES ====================

        // Últimos 5 aportes
        $recentContributions = Contribution::with('customer')
            ->where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->orderBy('contribution_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // Presupuestos recientes
        $recentBudgets = EventBudget::with(['customer', 'event'])
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Eventos próximos (siguiente semana)
        $upcomingEvents = Event::where('tenant_id', $tenantId)
            ->where('status', 'confirmed')
            ->whereBetween('event_date', [$today, now()->addDays(7)->format('Y-m-d')])
            ->orderBy('event_date', 'asc')
            ->get();

        return view('dashboard', compact(
            'contributionsMonth',
            'contributionsChange',
            'refundsMonth',
            'netBalance',
            'totalBalance',
            'cashBalance',
            'bankBalance',
            'activeEvents',
            'upcomingEvent',
            'pendingBudgets',
            'enrolledStudents',
            'totalStudents',
            'newStudentsMonth',
            'studentsWithBalance',
            'contributionsTrend',
            'contributionsByMethod',
            'recentContributions',
            'recentBudgets',
            'upcomingEvents'
        ));
    }
}
