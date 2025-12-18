@extends('layouts.app')

@section('title', 'Detalle de Cuenta Bancaria')
@section('page-title', 'Detalle de Cuenta Bancaria')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cuenta Bancaria - {{ $account->account_number }}</h5>
        <div>
            <a href="{{ route('bank-accounts.reconciliation', $account->id) }}" class="btn btn-info btn-sm">
                <i class="bi bi-check2-square"></i> Conciliación
            </a>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('bank-accounts.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="text-muted">Información de la Cuenta</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Número de Cuenta:</strong></td>
                        <td>{{ $account->account_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nombre:</strong></td>
                        <td>{{ $account->account_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Banco:</strong></td>
                        <td>{{ $account->bank_name }}</td>
                    </tr>
                    @if($account->bank_code)
                    <tr>
                        <td><strong>Código Banco:</strong></td>
                        <td>{{ $account->bank_code }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Tipo:</strong></td>
                        <td>
                            @if($account->account_type === 'checking')
                                <span class="badge bg-primary">Cuenta Corriente</span>
                            @elseif($account->account_type === 'savings')
                                <span class="badge bg-success">Caja de Ahorro</span>
                            @else
                                <span class="badge bg-warning">Crédito</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Moneda:</strong></td>
                        <td>{{ $account->currency }}</td>
                    </tr>
                    @if($account->account_holder)
                    <tr>
                        <td><strong>Titular:</strong></td>
                        <td>{{ $account->account_holder }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @if($account->status === 'active')
                                <span class="badge bg-success">Activa</span>
                            @elseif($account->status === 'inactive')
                                <span class="badge bg-secondary">Inactiva</span>
                            @else
                                <span class="badge bg-danger">Cerrada</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Saldos</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Saldo Inicial:</strong></td>
                        <td class="text-end">{{ number_format($account->initial_balance, 0, ',', '.') }} {{ $account->currency }}</td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>Saldo Actual:</strong></td>
                        <td class="text-end"><strong>{{ number_format($account->current_balance, 0, ',', '.') }} {{ $account->currency }}</strong></td>
                    </tr>
                    <tr class="table-warning">
                        <td><strong>Saldo Disponible:</strong></td>
                        <td class="text-end"><strong>{{ number_format($account->available_balance, 0, ',', '.') }} {{ $account->currency }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="2"><small class="text-muted">* El saldo disponible considera cheques pendientes</small></td>
                    </tr>
                </table>
            </div>
        </div>

        <h6 class="text-muted mb-3">Últimos Movimientos</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Número</th>
                        <th>Tipo</th>
                        <th>Concepto</th>
                        <th>Referencia</th>
                        <th class="text-end">Monto</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($account->transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                        <td>{{ $transaction->transaction_number }}</td>
                        <td>
                            @if($transaction->type === 'deposit')
                                <span class="badge bg-success">Depósito</span>
                            @elseif($transaction->type === 'withdrawal')
                                <span class="badge bg-danger">Retiro</span>
                            @elseif($transaction->type === 'transfer_in')
                                <span class="badge bg-info">Transfer. Entrada</span>
                            @elseif($transaction->type === 'transfer_out')
                                <span class="badge bg-warning">Transfer. Salida</span>
                            @elseif($transaction->type === 'check')
                                <span class="badge bg-primary">Cheque</span>
                            @elseif($transaction->type === 'charge')
                                <span class="badge bg-secondary">Cargo</span>
                            @else
                                <span class="badge bg-light text-dark">Interés</span>
                            @endif
                        </td>
                        <td>{{ $transaction->concept }}</td>
                        <td>{{ $transaction->reference }}</td>
                        <td class="text-end">
                            @if(in_array($transaction->type, ['deposit', 'transfer_in', 'interest']))
                                <span class="text-success">+{{ number_format($transaction->amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-danger">-{{ number_format($transaction->amount, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($transaction->balance_after, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($transaction->reconciled)
                                <i class="bi bi-check-circle-fill text-success" title="Conciliado"></i>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No hay movimientos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($account->transactions->count() > 0)
        <div class="mt-3">
            <a href="{{ route('bank-transactions.index') }}?bank_account_id={{ $account->id }}" class="btn btn-primary btn-sm">
                Ver Todos los Movimientos
            </a>
        </div>
        @endif

        @if($account->notes)
        <div class="mt-4">
            <strong>Notas:</strong>
            <p>{{ $account->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
