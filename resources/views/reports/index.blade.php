@extends('layouts.app')

@section('title', 'Reportes')
@section('page-title', 'Centro de Reportes')

@section('content')
<!-- Reportes Operacionales -->
<div class="mb-4">
    <h5 class="text-muted mb-3"><i class="bi bi-briefcase"></i> Reportes Operacionales</h5>
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cart-check text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Reporte de Ventas</h5>
                    <p class="card-text text-muted">Análisis de ventas por período, cliente y estado</p>
                    <a href="{{ route('reports.sales') }}" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-bag text-success" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Reporte de Compras</h5>
                    <p class="card-text text-muted">Análisis de compras por período y proveedor</p>
                    <a href="{{ route('reports.purchases') }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cash-stack text-warning" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Reporte de Gastos</h5>
                    <p class="card-text text-muted">Análisis de gastos por categoría y período</p>
                    <a href="{{ route('reports.expenses') }}" class="btn btn-warning">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam text-info" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Reporte de Inventario</h5>
                    <p class="card-text text-muted">Stock actual, productos bajos y valorización</p>
                    <a href="{{ route('reports.inventory') }}" class="btn btn-info">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reportes Financieros -->
<div class="mb-4">
    <h5 class="text-muted mb-3"><i class="bi bi-cash-coin"></i> Reportes Financieros</h5>
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card h-100 border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-left-right text-primary" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Flujo de Caja</h5>
                    <p class="card-text text-muted">Ingresos, egresos y saldo acumulado por período</p>
                    <a href="{{ route('reports.cash-flow') }}" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100 border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-warning" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Antigüedad de Saldos</h5>
                    <p class="card-text text-muted">Cuentas por cobrar/pagar clasificadas por antigüedad</p>
                    <a href="{{ route('reports.aging-report') }}" class="btn btn-warning">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reportes de Análisis -->
<div class="mb-4">
    <h5 class="text-muted mb-3"><i class="bi bi-graph-up"></i> Análisis y Rentabilidad</h5>
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card h-100 border-success">
                <div class="card-body text-center">
                    <i class="bi bi-trophy text-warning" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Productos Más Vendidos</h5>
                    <p class="card-text text-muted">Ranking de productos con mayor rotación y ventas</p>
                    <a href="{{ route('reports.top-products') }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100 border-success">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up-arrow text-success" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Rentabilidad</h5>
                    <p class="card-text text-muted">Análisis de costos, ingresos y márgenes por producto</p>
                    <a href="{{ route('reports.profitability') }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card h-100 border-info">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-down-up text-info" style="font-size: 3rem;"></i>
                    <h5 class="card-title mt-3">Movimientos de Inventario</h5>
                    <p class="card-text text-muted">Entradas y salidas detalladas de productos</p>
                    <a href="{{ route('reports.inventory-movements') }}" class="btn btn-info">
                        <i class="bi bi-file-earmark-text"></i> Ver Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
