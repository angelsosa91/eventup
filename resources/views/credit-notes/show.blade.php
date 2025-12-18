@extends('layouts.app')

@section('title', 'Detalle de Nota de Crédito')
@section('page-title', 'Detalle de Nota de Crédito')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Nota de Crédito {{ $creditNote->credit_note_number }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('credit-notes.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            @if($creditNote->status === 'draft')
            <button type="button" class="btn btn-success" onclick="confirmCreditNote()">
                <i class="bi bi-check-lg"></i> Confirmar
            </button>
            <button type="button" class="btn btn-danger" onclick="cancelCreditNote()">
                <i class="bi bi-x-lg"></i> Anular
            </button>
            @endif
            @if($creditNote->status === 'confirmed')
            <a href="{{ route('credit-notes.pdf', $creditNote) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-file-pdf"></i> Ver PDF
            </a>
            <a href="{{ route('credit-notes.download-pdf', $creditNote) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Descargar PDF
            </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <!-- Información de la nota de crédito -->
        <div class="row mb-4">
            <div class="col-md-3">
                <strong>Número NC:</strong><br>
                {{ $creditNote->credit_note_number }}
            </div>
            <div class="col-md-3">
                <strong>Fecha:</strong><br>
                {{ $creditNote->date->format('d/m/Y') }}
            </div>
            <div class="col-md-3">
                <strong>Venta Referencia:</strong><br>
                <a href="{{ route('sales.detail', $creditNote->sale_id) }}">{{ $creditNote->sale->sale_number }}</a>
            </div>
            <div class="col-md-3">
                <strong>Estado:</strong><br>
                @switch($creditNote->status)
                    @case('draft')
                        <span class="badge bg-secondary">Borrador</span>
                        @break
                    @case('confirmed')
                        <span class="badge bg-success">Confirmada</span>
                        @break
                    @case('cancelled')
                        <span class="badge bg-danger">Anulada</span>
                        @break
                @endswitch
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <strong>Cliente:</strong><br>
                {{ $creditNote->customer->name }}
            </div>
            <div class="col-md-3">
                <strong>Motivo:</strong><br>
                {{ $creditNote->reason_text }}
            </div>
            <div class="col-md-3">
                <strong>Tipo:</strong><br>
                <span class="badge bg-info">{{ $creditNote->type_text }}</span>
            </div>
            <div class="col-md-3">
                <strong>Creado por:</strong><br>
                {{ $creditNote->createdBy->name }}
            </div>
        </div>

        @if($creditNote->journalEntry)
        <div class="alert alert-success mb-4">
            <i class="bi bi-journal-text"></i>
            <strong>Asiento Contable:</strong>
            <a href="{{ route('journal-entries.show', $creditNote->journalEntry->id) }}" class="alert-link">
                {{ $creditNote->journalEntry->entry_number }}
            </a>
            - Fecha: {{ \Carbon\Carbon::parse($creditNote->journalEntry->entry_date)->format('d/m/Y') }}
            - Estado: <span class="badge bg-success">{{ $creditNote->journalEntry->status === 'posted' ? 'Publicado' : 'Borrador' }}</span>
        </div>
        @endif

        @if($creditNote->notes)
        <div class="alert alert-info mb-4">
            <strong>Notas:</strong><br>
            {{ $creditNote->notes }}
        </div>
        @endif

        <!-- Items de la nota de crédito -->
        <h6>Items</h6>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio Unitario</th>
                    <th class="text-center">IVA</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">IVA</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creditNote->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-end">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->iva_type }}%</td>
                    <td class="text-end">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->iva_amount, 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <table class="table table-sm">
                    <tr>
                        <td><strong>Total Exento (0%):</strong></td>
                        <td class="text-end">{{ number_format($creditNote->subtotal_0, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr>
                        <td><strong>Gravado 5%:</strong></td>
                        <td class="text-end">{{ number_format($creditNote->subtotal_5, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr>
                        <td><strong>IVA 5%:</strong></td>
                        <td class="text-end">{{ number_format($creditNote->iva_5, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr>
                        <td><strong>Gravado 10%:</strong></td>
                        <td class="text-end">{{ number_format($creditNote->subtotal_10, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr>
                        <td><strong>IVA 10%:</strong></td>
                        <td class="text-end">{{ number_format($creditNote->iva_10, 0, ',', '.') }} Gs.</td>
                    </tr>
                    <tr class="table-danger">
                        <td><strong>TOTAL A DEVOLVER:</strong></td>
                        <td class="text-end"><strong>{{ number_format($creditNote->total, 0, ',', '.') }} Gs.</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmCreditNote() {
    $.messager.confirm('Confirmar', '¿Desea confirmar esta nota de crédito? Se devolverá el stock y se creará el asiento contable de reversión.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('credit-notes') }}/{{ $creditNote->id }}/confirm',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.show({
                        title: 'Éxito',
                        msg: response.message,
                        timeout: 3000,
                        showType: 'slide'
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    var error = xhr.responseJSON?.message || 'Error al confirmar la nota de crédito';
                    $.messager.alert('Error', error, 'error');
                }
            });
        }
    });
}

function cancelCreditNote() {
    $.messager.confirm('Anular', '¿Desea anular esta nota de crédito?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('credit-notes') }}/{{ $creditNote->id }}/cancel',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.show({
                        title: 'Éxito',
                        msg: response.message,
                        timeout: 3000,
                        showType: 'slide'
                    });
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                },
                error: function(xhr) {
                    var error = xhr.responseJSON?.message || 'Error al anular la nota de crédito';
                    $.messager.alert('Error', error, 'error');
                }
            });
        }
    });
}
</script>
@endsection
