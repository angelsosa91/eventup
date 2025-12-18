@extends('layouts.app')

@section('title', 'Análisis de Rentabilidad')
@section('page-title', 'Análisis de Rentabilidad')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Análisis de Rentabilidad por Producto</h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" action="{{ route('reports.profitability') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block w-100">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
            </div>
        </form>

        <!-- Resumen General -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Ingresos Totales</h6>
                        <h3 class="mb-0">{{ number_format($totalRevenue, 0, ',', '.') }} Gs.</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="card-title">Costos Totales</h6>
                        <h3 class="mb-0">{{ number_format($totalCost, 0, ',', '.') }} Gs.</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Utilidad Total</h6>
                        <h3 class="mb-0">{{ number_format($totalProfit, 0, ',', '.') }} Gs.</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Margen Promedio</h6>
                        <h3 class="mb-0">{{ number_format($avgMargin, 2) }}%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Rentabilidad por Producto -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Código</th>
                        <th class="text-end">Cant. Vendida</th>
                        <th class="text-end">Ingresos</th>
                        <th class="text-end">Costo</th>
                        <th class="text-end">Utilidad</th>
                        <th class="text-end">Margen %</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td class="fw-bold">{{ $product['name'] }}</td>
                            <td>{{ $product['code'] }}</td>
                            <td class="text-end">{{ number_format($product['quantity_sold'], 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($product['revenue'], 0, ',', '.') }} Gs.</td>
                            <td class="text-end">{{ number_format($product['cost'], 0, ',', '.') }} Gs.</td>
                            <td class="text-end {{ $product['profit'] >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                {{ number_format($product['profit'], 0, ',', '.') }} Gs.
                            </td>
                            <td class="text-end">
                                <span class="badge {{ $product['margin'] >= 30 ? 'bg-success' : ($product['margin'] >= 15 ? 'bg-warning' : 'bg-danger') }}">
                                    {{ number_format($product['margin'], 2) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                @if($product['margin'] >= 30)
                                    <i class="bi bi-emoji-smile-fill text-success fs-5"></i>
                                @elseif($product['margin'] >= 15)
                                    <i class="bi bi-emoji-neutral-fill text-warning fs-5"></i>
                                @else
                                    <i class="bi bi-emoji-frown-fill text-danger fs-5"></i>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No hay ventas en este período</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
