@extends('layouts.app')

@section('title', 'Movimientos de Inventario')
@section('page-title', 'Movimientos de Inventario')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Movimientos de Inventario</h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" action="{{ route('reports.inventory-movements') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Producto</label>
                <select name="product_id" class="form-select">
                    <option value="">Todos los productos</option>
                    @foreach($allProducts as $prod)
                        <option value="{{ $prod->id }}" {{ $productId == $prod->id ? 'selected' : '' }}>
                            {{ $prod->code }} - {{ $prod->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block w-100">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
            </div>
        </form>

        <!-- Resumen de Movimientos -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Total Entradas</h6>
                        <h3 class="mb-0">{{ number_format($totalIn, 0, ',', '.') }} unidades</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title">Total Salidas</h6>
                        <h3 class="mb-0">{{ number_format($totalOut, 0, ',', '.') }} unidades</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Movimientos -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Documento</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Precio Unitario</th>
                        <th class="text-end">Valor Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($movement['date'])->format('d/m/Y') }}</td>
                            <td>
                                <div class="fw-bold">{{ $movement['product_name'] }}</div>
                                <small class="text-muted">{{ $movement['product_code'] }}</small>
                            </td>
                            <td>
                                @if($movement['type'] === 'IN')
                                    <span class="badge bg-success">
                                        <i class="bi bi-arrow-down-circle"></i> Entrada
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-arrow-up-circle"></i> Salida
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $movement['document_number'] }}</small>
                            </td>
                            <td class="text-end {{ $movement['type'] === 'IN' ? 'text-success' : 'text-danger' }} fw-bold">
                                {{ $movement['type'] === 'IN' ? '+' : '-' }}{{ number_format($movement['quantity'], 0, ',', '.') }}
                            </td>
                            <td class="text-end">{{ number_format($movement['unit_price'], 0, ',', '.') }} Gs.</td>
                            <td class="text-end fw-bold">{{ number_format($movement['total_value'], 0, ',', '.') }} Gs.</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay movimientos en este período</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
