@extends('layouts.app')

@section('title', 'Balance General')
@section('page-title', 'Balance General')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-0">Balance General</h5>
                <small class="text-muted">Estado de Situación Financiera al {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</small>
            </div>
            <div class="col-md-6 text-end">
                <form method="GET" action="{{ route('accounting.balance-sheet') }}" class="d-inline-flex gap-2">
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" style="width: 200px;">
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
        <div class="row">
            <!-- ACTIVOS -->
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3">ACTIVOS</h6>
                <table class="table table-sm">
                    <tbody>
                        @foreach($assets as $asset)
                            @include('accounting.reports.partials.account-tree-item', ['account' => $asset])
                        @endforeach
                        <tr class="table-primary fw-bold">
                            <td colspan="2">TOTAL ACTIVOS</td>
                            <td class="text-end">{{ number_format($totalAssets, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PASIVOS Y PATRIMONIO -->
            <div class="col-md-6">
                <!-- PASIVOS -->
                <h6 class="fw-bold text-danger mb-3">PASIVOS</h6>
                <table class="table table-sm">
                    <tbody>
                        @foreach($liabilities as $liability)
                            @include('accounting.reports.partials.account-tree-item', ['account' => $liability])
                        @endforeach
                        <tr class="table-danger fw-bold">
                            <td colspan="2">TOTAL PASIVOS</td>
                            <td class="text-end">{{ number_format($totalLiabilities, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- PATRIMONIO -->
                <h6 class="fw-bold text-success mb-3 mt-4">PATRIMONIO</h6>
                <table class="table table-sm">
                    <tbody>
                        @foreach($equity as $equityAccount)
                            @include('accounting.reports.partials.account-tree-item', ['account' => $equityAccount])
                        @endforeach
                        <tr>
                            <td colspan="2" style="padding-left: {{ 20 }}px;">
                                <span class="fw-bold">Resultado del Ejercicio</span>
                            </td>
                            <td class="text-end">{{ number_format($netIncome, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="table-success fw-bold">
                            <td colspan="2">TOTAL PATRIMONIO</td>
                            <td class="text-end">{{ number_format($totalEquity, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- TOTAL PASIVO + PATRIMONIO -->
                <table class="table table-sm">
                    <tbody>
                        <tr class="table-info fw-bold">
                            <td colspan="2">TOTAL PASIVO + PATRIMONIO</td>
                            <td class="text-end">{{ number_format($totalLiabilities + $totalEquity, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Verificación de balance -->
        @if($totalAssets != ($totalLiabilities + $totalEquity))
        <div class="alert alert-warning mt-3">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Advertencia:</strong> El balance no está cuadrado.
            Diferencia: {{ number_format(abs($totalAssets - ($totalLiabilities + $totalEquity)), 0, ',', '.') }} Gs.
        </div>
        @else
        <div class="alert alert-success mt-3">
            <i class="bi bi-check-circle"></i>
            <strong>Balance Cuadrado:</strong> Activos = Pasivos + Patrimonio
        </div>
        @endif
    </div>
</div>

<style media="print">
    .btn, form, .alert { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
</style>
@endsection
