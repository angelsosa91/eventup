@extends('layouts.app')

@section('title', 'Detalle de Asiento Contable')
@section('page-title', 'Detalle de Asiento Contable')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Asiento N° {{ $entry->entry_number }}</h5>
        <div>
            @if($entry->status === 'draft')
                <span class="badge bg-warning">Borrador</span>
            @elseif($entry->status === 'posted')
                <span class="badge bg-success">Contabilizado</span>
            @else
                <span class="badge bg-danger">Anulado</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <strong>Fecha:</strong><br>
                {{ $entry->entry_date->format('d/m/Y') }}
            </div>
            <div class="col-md-3">
                <strong>Período:</strong><br>
                {{ $entry->period }}
            </div>
            <div class="col-md-3">
                <strong>Tipo:</strong><br>
                @if($entry->entry_type === 'manual')
                    <span class="badge bg-primary">Manual</span>
                @else
                    <span class="badge bg-info">Automático</span>
                @endif
            </div>
            <div class="col-md-3">
                <strong>Usuario:</strong><br>
                {{ $entry->user->name }}
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <strong>Descripción:</strong><br>
                {{ $entry->description }}
            </div>
        </div>

        @if($entry->notes)
        <div class="row mb-4">
            <div class="col-md-12">
                <strong>Notas:</strong><br>
                {{ $entry->notes }}
            </div>
        </div>
        @endif

        <hr>

        <h5 class="mb-3">Líneas del Asiento</h5>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th style="width: 10%">Código</th>
                        <th style="width: 30%">Cuenta</th>
                        <th style="width: 30%">Descripción</th>
                        <th style="width: 15%" class="text-end">Débito</th>
                        <th style="width: 15%" class="text-end">Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->lines as $line)
                    <tr>
                        <td>{{ $line->account->code }}</td>
                        <td>{{ $line->account->name }}</td>
                        <td>{{ $line->description }}</td>
                        <td class="text-end">
                            @if($line->debit > 0)
                                {{ number_format($line->debit, 0, ',', '.') }}
                            @endif
                        </td>
                        <td class="text-end">
                            @if($line->credit > 0)
                                {{ number_format($line->credit, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end"><strong>TOTALES:</strong></td>
                        <td class="text-end"><strong>{{ number_format($entry->total_debit, 0, ',', '.') }}</strong></td>
                        <td class="text-end"><strong>{{ number_format($entry->total_credit, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-center">
                            @if($entry->is_balanced)
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> BALANCEADO</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-exclamation-triangle"></i> NO BALANCEADO - Diferencia: {{ number_format(abs($entry->total_debit - $entry->total_credit), 0, ',', '.') }}</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($entry->posted_at)
        <div class="alert alert-info mt-3">
            <i class="bi bi-info-circle"></i> <strong>Contabilizado el:</strong> {{ $entry->posted_at->format('d/m/Y H:i') }}
        </div>
        @endif

        @if($entry->cancelled_at)
        <div class="alert alert-danger mt-3">
            <i class="bi bi-x-circle"></i> <strong>Anulado el:</strong> {{ $entry->cancelled_at->format('d/m/Y H:i') }}
        </div>
        @endif

        <div class="mt-4">
            @if($entry->status === 'draft')
                <a href="{{ route('journal-entries.edit', $entry->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <button type="button" class="btn btn-success" onclick="postEntry()">
                    <i class="bi bi-check-circle"></i> Contabilizar
                </button>
                <button type="button" class="btn btn-danger" onclick="deleteEntry()">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            @elseif($entry->status === 'posted')
                <button type="button" class="btn btn-warning" onclick="cancelEntry()">
                    <i class="bi bi-x-circle"></i> Anular
                </button>
            @endif

            <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function postEntry() {
    @if(!$entry->is_balanced)
        $.messager.alert('Error', 'El asiento no está balanceado. No se puede contabilizar.', 'error');
        return;
    @endif

    $.messager.confirm('Confirmar', '¿Desea contabilizar este asiento? Esta acción afectará los saldos de las cuentas.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('journal-entries.post', $entry->id) }}',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al contabilizar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function cancelEntry() {
    $.messager.confirm('Confirmar', '¿Desea anular este asiento?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('journal-entries.cancel', $entry->id) }}',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al anular';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function deleteEntry() {
    $.messager.confirm('Confirmar', '¿Está seguro de eliminar este asiento?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('journal-entries.destroy', $entry->id) }}',
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    setTimeout(function() {
                        window.location.href = '{{ route('journal-entries.index') }}';
                    }, 1500);
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
@endpush
