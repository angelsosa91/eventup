@extends('layouts.app')

@section('title', 'Productos Más Vendidos')
@section('page-title', 'Productos Más Vendidos')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Productos Más Vendidos</h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" action="{{ route('reports.top-products') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Límite</label>
                <select name="limit" class="form-select">
                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>Top 10</option>
                    <option value="20" {{ $limit == 20 ? 'selected' : '' }}>Top 20</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>Top 50</option>
                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>Top 100</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block w-100">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
            </div>
        </form>

        <!-- Tabla de Productos -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Código</th>
                        <th class="text-end">Cantidad Vendida</th>
                        <th class="text-end">Ingresos Totales</th>
                        <th class="text-end">Precio Promedio</th>
                        <th class="text-center">Órdenes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $index => $product)
                        <tr>
                            <td>
                                @if($index === 0)
                                    <i class="bi bi-trophy-fill text-warning fs-5"></i>
                                @elseif($index === 1)
                                    <i class="bi bi-trophy-fill text-secondary fs-5"></i>
                                @elseif($index === 2)
                                    <i class="bi bi-trophy-fill text-danger fs-5"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td class="fw-bold">{{ $product->name }}</td>
                            <td>{{ $product->code }}</td>
                            <td class="text-end">
                                <span class="badge bg-primary">{{ number_format($product->total_quantity, 0, ',', '.') }}</span>
                            </td>
                            <td class="text-end text-success fw-bold">
                                {{ number_format($product->total_revenue, 0, ',', '.') }} Gs.
                            </td>
                            <td class="text-end">
                                {{ number_format($product->avg_price, 0, ',', '.') }} Gs.
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $product->total_orders }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay ventas en este período</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
