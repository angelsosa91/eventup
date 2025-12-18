@extends('layouts.app')

@section('title', 'Detalle de Conciliación')
@section('page-title', 'Detalle de Conciliación')

@section('content')
<div class="mb-3">
    <a href="{{ route('bank-reconciliations.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
    @if($reconciliation->status === 'draft')
    <a href="{{ route('bank-reconciliations.edit', $reconciliation->id) }}" class="btn btn-warning">
        <i class="fas fa-edit"></i> Editar
    </a>
    <button class="btn btn-success" onclick="postReconciliation()">
        <i class="fas fa-check"></i> Publicar
    </button>
    <button class="btn btn-danger" onclick="deleteReconciliation()">
        <i class="fas fa-trash"></i> Eliminar
    </button>
    @endif
    @if($reconciliation->status === 'posted')
    <button class="btn btn-danger" onclick="cancelReconciliation()">
        <i class="fas fa-ban"></i> Cancelar Conciliación
    </button>
    @endif
    <a href="{{ route('bank-reconciliations.report', $reconciliation->id) }}" target="_blank" class="btn btn-info">
        <i class="fas fa-print"></i> Imprimir Reporte
    </a>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Conciliación {{ $reconciliation->reconciliation_number }}</h5>
            @if($reconciliation->status === 'draft')
            <span class="badge bg-secondary">Borrador</span>
            @elseif($reconciliation->status === 'posted')
            <span class="badge bg-success">Publicado</span>
            @else
            <span class="badge bg-danger">Cancelado</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6>Información General</h6>
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Número:</th>
                        <td>{{ $reconciliation->reconciliation_number }}</td>
                    </tr>
                    <tr>
                        <th>Cuenta Bancaria:</th>
                        <td>{{ $reconciliation->bankAccount->account_name }} ({{ $reconciliation->bankAccount->account_number }})</td>
                    </tr>
                    <tr>
                        <th>Banco:</th>
                        <td>{{ $reconciliation->bankAccount->bank_name }}</td>
                    </tr>
                    <tr>
                        <th>Fecha de Conciliación:</th>
                        <td>{{ \Carbon\Carbon::parse($reconciliation->reconciliation_date)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Período:</th>
                        <td>{{ \Carbon\Carbon::parse($reconciliation->statement_start_date)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($reconciliation->statement_end_date)->format('d/m/Y') }}</td>
                    </tr>
                    @if($reconciliation->reconciledBy)
                    <tr>
                        <th>Conciliado por:</th>
                        <td>{{ $reconciliation->reconciledBy->name }}</td>
                    </tr>
                    @endif
                    @if($reconciliation->posted_at)
                    <tr>
                        <th>Publicado el:</th>
                        <td>{{ \Carbon\Carbon::parse($reconciliation->posted_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6">
                <h6>Resumen de Saldos</h6>
                <table class="table table-sm">
                    <tr>
                        <th width="60%">Saldo Inicial (Estado de Cuenta):</th>
                        <td class="text-end">{{ number_format($reconciliation->opening_balance, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Saldo Final (Estado de Cuenta):</th>
                        <td class="text-end"><strong>{{ number_format($reconciliation->closing_balance, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <th>Saldo en Sistema:</th>
                        <td class="text-end"><strong>{{ number_format($reconciliation->system_balance, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr class="@if(abs($reconciliation->difference) < 1) table-success @elseif(abs($reconciliation->difference) < 10000) table-warning @else table-danger @endif">
                        <th>Diferencia:</th>
                        <td class="text-end">
                            <strong>
                                @if(abs($reconciliation->difference) < 1)
                                    0 ✓
                                @else
                                    {{ number_format($reconciliation->difference, 0, ',', '.') }}
                                @endif
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Transacciones Conciliadas:</th>
                        <td class="text-end">{{ $reconciliation->lines->count() }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if($reconciliation->notes)
        <div class="row mb-4">
            <div class="col-12">
                <h6>Notas</h6>
                <div class="alert alert-info">
                    {{ $reconciliation->notes }}
                </div>
            </div>
        </div>
        @endif

        <h6>Transacciones Incluidas en la Conciliación</h6>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th>Referencia</th>
                    <th class="text-end">Monto</th>
                    <th class="text-end">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDeposits = 0;
                    $totalWithdrawals = 0;
                @endphp
                @foreach($reconciliation->lines as $line)
                @php
                    $transaction = $line->bankTransaction;
                    if (in_array($transaction->type, ['deposit', 'transfer_in', 'interest'])) {
                        $totalDeposits += $transaction->amount;
                    } else {
                        $totalWithdrawals += $transaction->amount;
                    }
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</td>
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
                            <span class="badge bg-success">Interés</span>
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
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <th colspan="5" class="text-end">Total Depósitos:</th>
                    <th class="text-end text-success">+{{ number_format($totalDeposits, 0, ',', '.') }}</th>
                    <th></th>
                </tr>
                <tr class="table-light">
                    <th colspan="5" class="text-end">Total Retiros:</th>
                    <th class="text-end text-danger">-{{ number_format($totalWithdrawals, 0, ',', '.') }}</th>
                    <th></th>
                </tr>
                <tr class="table-light">
                    <th colspan="5" class="text-end">Diferencia Neta:</th>
                    <th class="text-end"><strong>{{ number_format($totalDeposits - $totalWithdrawals, 0, ',', '.') }}</strong></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
function postReconciliation() {
    @if(abs($reconciliation->difference) >= 1)
    var warningMsg = '<br><span class="text-danger">ADVERTENCIA: Existe una diferencia de {{ number_format($reconciliation->difference, 0, ',', '.') }} entre el estado de cuenta y el sistema.</span>';
    @else
    var warningMsg = '';
    @endif

    $.messager.confirm('Confirmar', '¿Desea publicar esta conciliación?' + warningMsg +
                       '<br><br>Una vez publicada, las transacciones quedarán marcadas como conciliadas.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('bank-reconciliations.post', $reconciliation->id) }}',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    location.reload();
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al publicar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function cancelReconciliation() {
    $.messager.confirm('Confirmar', '¿Desea cancelar esta conciliación?<br><br>' +
                       'Las transacciones volverán a quedar como NO conciliadas.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('bank-reconciliations.cancel', $reconciliation->id) }}',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    location.reload();
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al cancelar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function deleteReconciliation() {
    $.messager.confirm('Confirmar', '¿Desea eliminar esta conciliación?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('bank-reconciliations.destroy', $reconciliation->id) }}',
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    window.location.href = '{{ route('bank-reconciliations.index') }}';
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al eliminar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}
</script>
@endsection
