@extends('layouts.app')

@section('title', 'Reporte de Antigüedad de Saldos')
@section('page-title', 'Reporte de Antigüedad de Saldos')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Antigüedad de Saldos</h5>
    </div>
    <div class="card-body">
        <!-- Filtros -->
        <form method="GET" action="{{ route('reports.aging-report') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label">Tipo de Reporte</label>
                <select name="type" class="form-select">
                    <option value="receivable" {{ $type === 'receivable' ? 'selected' : '' }}>Cuentas por Cobrar</option>
                    <option value="payable" {{ $type === 'payable' ? 'selected' : '' }}>Cuentas por Pagar</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha de Corte</label>
                <input type="date" name="as_of_date" class="form-control" value="{{ $asOfDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block w-100">
                    <i class="bi bi-filter"></i> Filtrar
                </button>
            </div>
        </form>

        <!-- Resumen por Antigüedad -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Resumen por Antigüedad</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Vigente</th>
                                        <th>1-30 días</th>
                                        <th>31-60 días</th>
                                        <th>61-90 días</th>
                                        <th>+90 días</th>
                                        <th class="fw-bold">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-success">{{ number_format($summary['current'], 0, ',', '.') }} Gs.</td>
                                        <td class="text-info">{{ number_format($summary['1-30'], 0, ',', '.') }} Gs.</td>
                                        <td class="text-warning">{{ number_format($summary['31-60'], 0, ',', '.') }} Gs.</td>
                                        <td class="text-danger">{{ number_format($summary['61-90'], 0, ',', '.') }} Gs.</td>
                                        <td class="text-dark fw-bold">{{ number_format($summary['90+'], 0, ',', '.') }} Gs.</td>
                                        <td class="fw-bold fs-5">{{ number_format($summary['total'], 0, ',', '.') }} Gs.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla Detallada -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>{{ $type === 'receivable' ? 'Cliente' : 'Proveedor' }}</th>
                        <th>Documento</th>
                        <th>Fecha</th>
                        <th>Vencimiento</th>
                        <th class="text-center">Días Vencido</th>
                        <th class="text-center">Antigüedad</th>
                        <th class="text-end">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td>{{ $account->partner_name }}</td>
                            <td>{{ $account->document_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($account->date)->format('d/m/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($account->due_date)->format('d/m/Y') }}</td>
                            <td class="text-center">
                                @if($account->days_overdue > 0)
                                    <span class="badge bg-danger">{{ $account->days_overdue }}</span>
                                @else
                                    <span class="badge bg-success">{{ abs($account->days_overdue) }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($account->aging_bucket === 'current')
                                    <span class="badge bg-success">Vigente</span>
                                @elseif($account->aging_bucket === '1-30')
                                    <span class="badge bg-info">1-30 días</span>
                                @elseif($account->aging_bucket === '31-60')
                                    <span class="badge bg-warning">31-60 días</span>
                                @elseif($account->aging_bucket === '61-90')
                                    <span class="badge bg-danger">61-90 días</span>
                                @else
                                    <span class="badge bg-dark">+90 días</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">{{ number_format($account->balance, 0, ',', '.') }} Gs.</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay cuentas pendientes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
