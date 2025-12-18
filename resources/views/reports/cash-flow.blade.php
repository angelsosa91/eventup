@extends('layouts.app')

@section('title', 'Reporte de Flujo de Caja')
@section('page-title', 'Reporte de Flujo de Caja')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Flujo de Caja</h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" action="{{ route('reports.cash-flow') }}" class="row g-3 mb-4">
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

        <!-- Resumen -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Total Ingresos</h6>
                        <h3 class="mb-0">{{ number_format($totalIncome, 0, ',', '.') }} Gs.</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title">Total Egresos</h6>
                        <h3 class="mb-0">{{ number_format($totalExpense, 0, ',', '.') }} Gs.</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card {{ $netCashFlow >= 0 ? 'bg-primary' : 'bg-warning' }} text-white">
                    <div class="card-body">
                        <h6 class="card-title">Flujo Neto</h6>
                        <h3 class="mb-0">{{ number_format($netCashFlow, 0, ',', '.') }} Gs.</h3>
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
                        <th>Descripción</th>
                        <th>Tipo</th>
                        <th class="text-end">Ingresos</th>
                        <th class="text-end">Egresos</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movementsWithBalance as $movement)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($movement->date)->format('d/m/Y') }}</td>
                            <td>{{ $movement->description }}</td>
                            <td>
                                @if($movement->type === 'income')
                                    <span class="badge bg-success">Ingreso</span>
                                @else
                                    <span class="badge bg-danger">Egreso</span>
                                @endif
                            </td>
                            <td class="text-end text-success">
                                @if($movement->type === 'income')
                                    {{ number_format($movement->amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end text-danger">
                                @if($movement->type === 'expense')
                                    {{ number_format($movement->amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end fw-bold">
                                {{ number_format($movement->balance, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay movimientos en este período</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
