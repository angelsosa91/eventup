@extends('layouts.app')

@section('title', 'Estado de Resultados')
@section('page-title', 'Estado de Resultados')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-0">Estado de Resultados</h5>
                <small class="text-muted">
                    Período del {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                    al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </small>
            </div>
            <div class="col-md-6 text-end">
                <form method="GET" action="{{ route('accounting.income-statement') }}" class="d-inline-flex gap-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="width: 150px;">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" style="width: 150px;">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-filter"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- INGRESOS -->
        <h6 class="fw-bold text-success mb-3">INGRESOS</h6>
        <table class="table table-sm table-hover">
            <tbody>
                @foreach($income as $incomeAccount)
                    @include('accounting.reports.partials.account-tree-item', ['account' => $incomeAccount])
                @endforeach
                <tr class="table-success fw-bold">
                    <td colspan="2">TOTAL INGRESOS</td>
                    <td class="text-end">{{ number_format($totalIncome, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- GASTOS -->
        <h6 class="fw-bold text-danger mb-3 mt-4">GASTOS</h6>
        <table class="table table-sm table-hover">
            <tbody>
                @foreach($expenses as $expense)
                    @include('accounting.reports.partials.account-tree-item', ['account' => $expense])
                @endforeach
                <tr class="table-danger fw-bold">
                    <td colspan="2">TOTAL GASTOS</td>
                    <td class="text-end">{{ number_format($totalExpenses, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- RESULTADO -->
        <table class="table table-sm mt-4">
            <tbody>
                <tr class="table-{{ $netIncome >= 0 ? 'success' : 'danger' }} fw-bold fs-5">
                    <td colspan="2">
                        {{ $netIncome >= 0 ? 'UTILIDAD DEL PERÍODO' : 'PÉRDIDA DEL PERÍODO' }}
                    </td>
                    <td class="text-end">{{ number_format(abs($netIncome), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @if($netIncome >= 0)
        <div class="alert alert-success mt-3">
            <i class="bi bi-graph-up-arrow"></i>
            <strong>Resultado Positivo:</strong> La empresa generó utilidades de {{ number_format($netIncome, 0, ',', '.') }} Gs. en este período.
        </div>
        @else
        <div class="alert alert-warning mt-3">
            <i class="bi bi-graph-down-arrow"></i>
            <strong>Resultado Negativo:</strong> La empresa tuvo pérdidas de {{ number_format(abs($netIncome), 0, ',', '.') }} Gs. en este período.
        </div>
        @endif
    </div>
</div>

<style media="print">
    .btn, form, .alert { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
</style>
@endsection
