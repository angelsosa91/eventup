@extends('layouts.app')

@section('title', 'Dashboard - EventUP')
@section('page-title', 'Tablero de Control')

@section('content')
    <!-- Métricas Financieras Principales -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">Aportes del Mes</h6>
                            <h3 class="card-title mb-0">{{ number_format($contributionsMonth, 0, ',', '.') }} Gs.</h3>
                            <small class="text-white-50">
                                @if($contributionsChange > 0)
                                    <i class="bi bi-arrow-up"></i> {{ number_format($contributionsChange, 1) }}% vs mes anterior
                                @elseif($contributionsChange < 0)
                                    <i class="bi bi-arrow-down"></i> {{ number_format(abs($contributionsChange), 1) }}% vs mes anterior
                                @else
                                    <span class="text-white-50">= Mismo nivel</span>
                                @endif
                            </small>
                        </div>
                        <div class="bg-white rounded-circle p-3 bg-opacity-10">
                            <i class="bi bi-wallet2 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-danger text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">Devoluciones Mes</h6>
                            <h3 class="card-title mb-0">{{ number_format($refundsMonth, 0, ',', '.') }} Gs.</h3>
                            <small class="text-white-50">Reintegros realizados</small>
                        </div>
                        <div class="bg-white rounded-circle p-3 bg-opacity-10">
                            <i class="bi bi-arrow-counterclockwise fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card {{ $netBalance >= 0 ? 'bg-success' : 'bg-warning text-dark' }} text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">Balance Neto Mes</h6>
                            <h3 class="card-title mb-0">{{ number_format($netBalance, 0, ',', '.') }} Gs.</h3>
                            <small class="opacity-75">Aportes - Devoluciones</small>
                        </div>
                        <div class="bg-white rounded-circle p-3 bg-opacity-10">
                            <i class="bi bi-graph-up-arrow fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 opacity-75">Saldo Caja/Banco</h6>
                            <h3 class="card-title mb-0">{{ number_format($totalBalance, 0, ',', '.') }} Gs.</h3>
                            <small class="text-white-50">
                                <i class="bi bi-cash"></i> {{ number_format($cashBalance, 0, ',', '.') }} | 
                                <i class="bi bi-bank"></i> {{ number_format($bankBalance, 0, ',', '.') }}
                            </small>
                        </div>
                        <div class="bg-white rounded-circle p-3 bg-opacity-10">
                            <i class="bi bi-piggy-bank fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Gráficos y Tablas -->
        <div class="col-md-8">
            <!-- Gráfico de Tendencia -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Evolución Financiera (Últimos 6 Meses)</h5>
                </div>
                <div class="card-body">
                    <canvas id="financialTrendChart" height="100"></canvas>
                </div>
            </div>

            <!-- Últimos Aportes -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Últimos Aportes</h5>
                    <a href="{{ route('contributions.index') }}" class="btn btn-sm btn-outline-primary">Ver Todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Alumno</th>
                                    <th>Ref/Método</th>
                                    <th class="text-end">Monto</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentContributions as $contribution)
                                    <tr>
                                        <td>{{ $contribution->contribution_date->format('d/m/Y') }}</td>
                                        <td class="fw-bold">{{ $contribution->customer->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $contribution->payment_method }}</span>
                                            @if($contribution->reference)
                                                <small class="text-muted d-block">{{ Str::limit($contribution->reference, 10) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($contribution->amount < 0)
                                                <span class="text-danger fw-bold">{{ number_format($contribution->amount, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-success fw-bold">+{{ number_format($contribution->amount, 0, ',', '.') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($contribution->amount < 0)
                                                <span class="badge bg-danger-subtle text-danger">Devolución</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success">Aporte</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No hay movimientos recientes</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Alertas y Resúmenes -->
        <div class="col-md-4">
            
            <!-- Agenda / Próximos Eventos -->
            <div class="card mb-4 shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">📅 Próximos Eventos</h5>
                </div>
                <div class="card-body">
                    @forelse($upcomingEvents as $event)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom last-no-border">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-2 text-center me-3" style="min-width: 60px;">
                                <span class="d-block fw-bold h5 mb-0">{{ $event->event_date->format('d') }}</span>
                                <span class="small text-uppercase">{{ $event->event_date->format('M') }}</span>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold">{{ $event->name }}</h6>
                                <small class="text-muted"><i class="bi bi-clock"></i> {{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('H:i') : 'Todo el día' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-calendar-x fs-1 opacity-50"></i>
                            <p class="mt-2 text-small">No hay eventos para los próximos 7 días</p>
                            <a href="{{ route('events.index') }}" class="btn btn-sm btn-warning">Ver Calendario</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Resumen de Alumnos -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">🎓 Alumnado</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <h3 class="mb-0 text-primary">{{ $totalStudents }}</h3>
                                <small class="text-muted">Total Alumnos</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <h3 class="mb-0 text-success">+{{ $newStudentsMonth }}</h3>
                                <small class="text-muted">Nuevos este mes</small>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                             <div class="p-3 border rounded bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0 text-dark">{{ $studentsWithBalance }}</h5>
                                    <small class="text-muted">Alumnos aportantes</small>
                                </div>
                                <i class="bi bi-people-fill fs-3 text-secondary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

             <!-- Accesos Rápidos -->
             <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">⚡ Accesos Rápidos</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('contributions.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-plus-circle text-primary me-2"></i> Nuevo Aporte</div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div><i class="bi bi-person-plus text-success me-2"></i> Nuevo Alumno</div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                    <a href="{{ route('event-budgets.index') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                        <div>
                            <i class="bi bi-file-earmark-text text-warning me-2"></i> Presupuestos
                            @if($pendingBudgets > 0)
                                <span class="badge bg-danger rounded-pill ms-2">{{ $pendingBudgets }}</span>
                            @endif
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('financialTrendChart').getContext('2d');
        
        // Datos desde el controlador
        const trendData = @json($contributionsTrend);
        
        const labels = trendData.map(item => item.month);
        const contributions = trendData.map(item => item.contributions);
        const refunds = trendData.map(item => item.refunds);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Aportes',
                        data: contributions,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Devoluciones',
                        data: refunds,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('es-PY').format(value) + ' Gs';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
<style>
    .last-no-border:last-child { border-bottom: none !important; margin-bottom: 0 !important; padding-bottom: 0 !important; }
    .card { border-radius: 0.5rem; border: none; }
</style>
@endpush