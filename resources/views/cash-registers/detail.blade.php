@extends('layouts.app')

@section('title', 'Detalle de Arqueo')
@section('page-title', 'Detalle de Arqueo')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Arqueo de Caja - {{ $register->register_number }}</h5>
        <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('cash-registers.index') }}'">
            <i class="bi bi-arrow-left"></i> Volver
        </button>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted">Información General</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Número:</strong></td>
                        <td>{{ $register->register_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha:</strong></td>
                        <td>{{ $register->register_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Usuario:</strong></td>
                        <td>{{ $register->user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Apertura:</strong></td>
                        <td>{{ $register->opened_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @if($register->closed_at)
                    <tr>
                        <td><strong>Cierre:</strong></td>
                        <td>{{ $register->closed_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @if($register->status === 'open')
                                <span class="badge bg-success">Abierta</span>
                            @else
                                <span class="badge bg-secondary">Cerrada</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Resumen Financiero</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Saldo Inicial:</strong></td>
                        <td class="text-end">{{ number_format($register->opening_balance, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>Ventas en Efectivo:</strong></td>
                        <td class="text-end">{{ number_format($register->sales_cash, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr class="table-success">
                        <td><strong>Cobros Recibidos:</strong></td>
                        <td class="text-end">{{ number_format($register->collections, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr class="table-danger">
                        <td><strong>Pagos Realizados:</strong></td>
                        <td class="text-end">{{ number_format($register->payments, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr class="table-danger">
                        <td><strong>Gastos:</strong></td>
                        <td class="text-end">{{ number_format($register->expenses, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Saldo Esperado:</strong></td>
                        <td class="text-end"><strong>{{ number_format($register->expected_balance, 0, ',', '.') }} Gs.</strong></td>
                    </tr>
                    @if($register->status === 'closed')
                    <tr class="table-info">
                        <td><strong>Saldo Real:</strong></td>
                        <td class="text-end"><strong>{{ number_format($register->actual_balance, 0, ',', '.') }} Gs.</strong></td>
                    </tr>
                    <tr class="{{ $register->difference == 0 ? 'table-success' : ($register->difference > 0 ? 'table-warning' : 'table-danger') }}">
                        <td><strong>Diferencia:</strong></td>
                        <td class="text-end">
                            <strong>
                                @if($register->difference > 0)
                                    +{{ number_format($register->difference, 0, ',', '.') }} Gs. (Sobrante)
                                @elseif($register->difference < 0)
                                    {{ number_format($register->difference, 0, ',', '.') }} Gs. (Faltante)
                                @else
                                    Sin diferencia
                                @endif
                            </strong>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <h6 class="text-muted mb-3">Movimientos del Día</h6>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th>Descripción</th>
                    <th>Referencia</th>
                    <th class="text-end">Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($register->movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('H:i:s') }}</td>
                    <td>
                        @if($movement->type === 'income')
                            <span class="badge bg-success">Ingreso</span>
                        @else
                            <span class="badge bg-danger">Egreso</span>
                        @endif
                    </td>
                    <td>
                        @if($movement->concept === 'sale') Venta
                        @elseif($movement->concept === 'collection') Cobro
                        @elseif($movement->concept === 'payment') Pago
                        @elseif($movement->concept === 'expense') Gasto
                        @else Otro
                        @endif
                    </td>
                    <td>{{ $movement->description }}</td>
                    <td>{{ $movement->reference }}</td>
                    <td class="text-end">
                        @if($movement->type === 'income')
                            <span class="text-success">+{{ number_format($movement->amount, 0, ',', '.') }} Gs.</span>
                        @else
                            <span class="text-danger">-{{ number_format($movement->amount, 0, ',', '.') }} Gs.</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No hay movimientos registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($register->notes)
        <div class="mt-3">
            <strong>Notas:</strong>
            <p>{{ $register->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
