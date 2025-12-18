@extends('layouts.app')

@section('title', 'Cuentas por Cobrar por Cliente')
@section('page-title', 'Cuentas por Cobrar por Cliente')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cuentas por Cobrar por Cliente</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('account-receivables.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Cliente</th>
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
                @forelse($customers as $customer)
                <tr>
                    <td>
                        <a href="{{ route('account-receivables.index') }}?customer_id={{ $customer->customer_id }}">
                            {{ $customer->customer_name }}
                        </a>
                    </td>
                    <td class="text-center">{{ $customer->total_documents }}</td>
                    <td class="text-end">{{ number_format($customer->total_amount, 0, ',', '.') }} Gs.</td>
                    <td class="text-end">{{ number_format($customer->total_paid, 0, ',', '.') }} Gs.</td>
                    <td class="text-end"><strong>{{ number_format($customer->total_balance, 0, ',', '.') }} Gs.</strong></td>
                </tr>
                @php
                    $totalDocuments += $customer->total_documents;
                    $totalAmount += $customer->total_amount;
                    $totalPaid += $customer->total_paid;
                    $totalBalance += $customer->total_balance;
                @endphp
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No hay cuentas pendientes</td>
                </tr>
                @endforelse
            </tbody>
            @if($customers->count() > 0)
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
