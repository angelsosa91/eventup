@extends('layouts.app')

@section('title', 'Cuentas por Pagar por Proveedor')
@section('page-title', 'Cuentas por Pagar por Proveedor')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cuentas por Pagar por Proveedor</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('account-payables.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Proveedor</th>
                    <th class="text-center">Documentos</th>
                    <th class="text-end">Monto Total</th>
                    <th class="text-end">Pagado</th>
                    <th class="text-end">Saldo Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDocuments = 0;
                    $totalAmount = 0;
                    $totalPaid = 0;
                    $totalBalance = 0;
                @endphp
                @forelse($suppliers as $supplier)
                <tr>
                    <td>
                        <a href="{{ route('account-payables.index') }}?supplier_id={{ $supplier->supplier_id }}">
                            {{ $supplier->supplier_name }}
                        </a>
                    </td>
                    <td class="text-center">{{ $supplier->total_documents }}</td>
                    <td class="text-end">{{ number_format($supplier->total_amount, 0, ',', '.') }} Gs.</td>
                    <td class="text-end">{{ number_format($supplier->total_paid, 0, ',', '.') }} Gs.</td>
                    <td class="text-end"><strong>{{ number_format($supplier->total_balance, 0, ',', '.') }} Gs.</strong></td>
                </tr>
                @php
                    $totalDocuments += $supplier->total_documents;
                    $totalAmount += $supplier->total_amount;
                    $totalPaid += $supplier->total_paid;
                    $totalBalance += $supplier->total_balance;
                @endphp
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No hay cuentas pendientes</td>
                </tr>
                @endforelse
            </tbody>
            @if($suppliers->count() > 0)
            <tfoot class="table-light">
                <tr>
                    <th>TOTALES</th>
                    <th class="text-center">{{ $totalDocuments }}</th>
                    <th class="text-end">{{ number_format($totalAmount, 0, ',', '.') }} Gs.</th>
                    <th class="text-end">{{ number_format($totalPaid, 0, ',', '.') }} Gs.</th>
                    <th class="text-end">{{ number_format($totalBalance, 0, ',', '.') }} Gs.</th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
