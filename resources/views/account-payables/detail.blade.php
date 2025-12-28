@extends('layouts.app')

@section('title', 'Detalle de Cuenta por Pagar')
@section('page-title', 'Detalle de Cuenta por Pagar')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cuenta por Pagar - {{ $payable->document_number }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('account-payables.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted">Información General</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Documento:</strong></td>
                        <td>{{ $payable->document_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha:</strong></td>
                        <td>{{ $payable->document_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Vencimiento:</strong></td>
                        <td>{{ $payable->due_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Proveedor:</strong></td>
                        <td>{{ $payable->supplier_name }}</td>
                    </tr>
                    @if($payable->purchase_number)
                    <tr>
                        <td><strong>Compra:</strong></td>
                        <td>
                            <a href="{{ route('purchases.show', $payable->purchase_id) }}">{{ $payable->purchase_number }}</a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Descripción:</strong></td>
                        <td>{{ $payable->description }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Montos</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Monto Total:</strong></td>
                        <td class="text-end">{{ number_format($payable->amount, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr>
                        <td><strong>Monto Pagado:</strong></td>
                        <td class="text-end">{{ number_format($payable->paid_amount, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr class="table-active">
                        <td><strong>Saldo Pendiente:</strong></td>
                        <td class="text-end"><strong>{{ number_format($payable->balance, 0, ',', '.') }} Gs.</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @if($payable->status === 'pending')
                                <span class="badge bg-warning">Pendiente</span>
                            @elseif($payable->status === 'partial')
                                <span class="badge bg-info">Parcial</span>
                            @elseif($payable->status === 'paid')
                                <span class="badge bg-success">Pagado</span>
                            @elseif($payable->status === 'cancelled')
                                <span class="badge bg-danger">Anulado</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <h6 class="text-muted mb-3">Historial de Pagos</h6>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Recibo</th>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th class="text-end">Monto</th>
                    <th>Usuario</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payable->payments as $payment)
                <tr>
                    <td>{{ $payment->payment_number }}</td>
                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                    <td>
                        @if($payment->payment_method === 'cash') Efectivo
                        @elseif($payment->payment_method === 'transfer') Transferencia
                        @elseif($payment->payment_method === 'check') Cheque
                        @elseif($payment->payment_method === 'card') Tarjeta
                        @else Otro
                        @endif
                    </td>
                    <td>{{ $payment->reference }}</td>
                    <td class="text-end">{{ number_format($payment->amount, 0, ',', '.') }} Gs.</td>
                    <td>{{ $payment->user->name }}</td>
                    <td>{{ $payment->notes }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay pagos registrados</td>
                </tr>
                @endforelse
            </tbody>
            @if($payable->payments->count() > 0)
            <tfoot class="table-light">
                <tr>
                    <th colspan="4" class="text-end">Total Pagado:</th>
                    <th class="text-end">{{ number_format($payable->paid_amount, 0, ',', '.') }} Gs.</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
