@extends('layouts.app')

@section('title', 'Dashboard - Neo ERP')
@section('page-title', 'Dashboard')

@section('content')
<!-- Métricas principales -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 opacity-75">Ventas Hoy</h6>
                        <h3 class="card-title mb-0">{{ number_format($salesToday->total ?? 0, 0, ',', '.') }}</h3>
                        <small class="opacity-75">{{ $salesToday->count ?? 0 }} facturas</small>
                    </div>
                    <i class="bi bi-cart-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 opacity-75">Ventas del Mes</h6>
                        <h3 class="card-title mb-0">{{ number_format($salesMonth->total ?? 0, 0, ',', '.') }}</h3>
                        <small class="opacity-75">{{ $salesMonth->count ?? 0 }} facturas</small>
                    </div>
                    <i class="bi bi-graph-up-arrow fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 opacity-75">Compras del Mes</h6>
                        <h3 class="card-title mb-0">{{ number_format($purchasesMonth->total ?? 0, 0, ',', '.') }}</h3>
                        <small class="opacity-75">{{ $purchasesMonth->count ?? 0 }} compras</small>
                    </div>
                    <i class="bi bi-bag fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card {{ $profit >= 0 ? 'bg-info' : 'bg-danger' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 opacity-75">Resultado del Mes</h6>
                        <h3 class="card-title mb-0">{{ number_format($profit, 0, ',', '.') }}</h3>
                        <small class="opacity-75">Ventas - Compras - Gastos</small>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Segunda fila de métricas -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Gastos del Mes</h6>
                        <h4 class="mb-0 text-danger">{{ number_format($expensesMonth->total ?? 0, 0, ',', '.') }}</h4>
                    </div>
                    <i class="bi bi-cash-stack fs-2 text-danger"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Clientes Activos</h6>
                        <h4 class="mb-0">{{ $totalCustomers }}</h4>
                    </div>
                    <i class="bi bi-people fs-2 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Productos</h6>
                        <h4 class="mb-0">{{ $totalProducts }}</h4>
                    </div>
                    <i class="bi bi-box-seam fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card {{ count($lowStockProducts) > 0 ? 'border-danger' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Stock Bajo</h6>
                        <h4 class="mb-0 {{ count($lowStockProducts) > 0 ? 'text-danger' : '' }}">{{ count($lowStockProducts) }}</h4>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-2 {{ count($lowStockProducts) > 0 ? 'text-danger' : 'text-muted' }}"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Últimas ventas -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Últimas Ventas</h5>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-primary">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>{{ $sale->sale_date->format('d/m/Y') }}</td>
                            <td>{{ $sale->sale_number }}</td>
                            <td>{{ $sale->customer->name ?? 'Sin cliente' }}</td>
                            <td class="text-end fw-bold">{{ number_format($sale->total, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No hay ventas registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Accesos rápidos y Stock bajo -->
    <div class="col-md-4 mb-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Accesos Rápidos</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('sales.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-2"></i>Nueva Venta
                    </a>
                    <a href="{{ route('purchases.create') }}" class="btn btn-outline-success">
                        <i class="bi bi-bag-plus me-2"></i>Nueva Compra
                    </a>
                    <a href="{{ route('expenses.create') }}" class="btn btn-outline-warning">
                        <i class="bi bi-cash me-2"></i>Nuevo Gasto
                    </a>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-info">
                        <i class="bi bi-file-bar-graph me-2"></i>Ver Reportes
                    </a>
                </div>
            </div>
        </div>

        @if(count($lowStockProducts) > 0)
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Productos con Stock Bajo</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @foreach($lowStockProducts as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $product->code }}</strong><br>
                            <small class="text-muted">{{ $product->name }}</small>
                        </div>
                        <span class="badge bg-danger rounded-pill">
                            {{ number_format($product->stock, 0) }} {{ $product->unit }}
                        </span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Gráfico de ventas -->
@if(count($salesByDay) > 0)
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ventas de los Últimos 7 Días</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $maxTotal = $salesByDay->max('total') ?: 1;
                    @endphp
                    @foreach($salesByDay as $day)
                    <div class="col text-center">
                        <div class="d-flex flex-column align-items-center">
                            <div style="height: 150px; width: 100%; display: flex; align-items: flex-end; justify-content: center;">
                                <div class="bg-primary" style="width: 60%; height: {{ ($day->total / $maxTotal) * 100 }}%; min-height: 5px; border-radius: 4px 4px 0 0;"></div>
                            </div>
                            <small class="fw-bold mt-2">{{ number_format($day->total, 0, ',', '.') }}</small>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($day->sale_date)->format('d/m') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
