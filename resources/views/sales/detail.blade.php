@extends('layouts.app')

@section('title', 'Detalle de Venta')
@section('page-title', 'Detalle de Venta')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Venta {{ $sale->sale_number }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('sales.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            @if($sale->status === 'draft')
            <button type="button" class="btn btn-success" onclick="confirmSale()">
                <i class="bi bi-check-lg"></i> Confirmar
            </button>
            @endif
            @if($sale->status !== 'cancelled')
            <button type="button" class="btn btn-danger" onclick="cancelSale()">
                <i class="bi bi-x-lg"></i> Anular
            </button>
            @endif
            @if($sale->status === 'confirmed')
            <a href="{{ route('credit-notes.create', ['sale_id' => $sale->id]) }}" class="btn btn-warning">
                <i class="bi bi-file-earmark-minus"></i> Nota de Crédito
            </a>
            @endif
            <a href="{{ route('sales.pdf', $sale) }}" target="_blank" class="btn btn-primary">
                <i class="bi bi-file-pdf"></i> Ver PDF
            </a>
            <a href="{{ route('sales.download-pdf', $sale) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Descargar PDF
            </a>
            <button type="button" class="btn btn-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Información de la venta -->
        <div class="row mb-4">
            <div class="col-md-3">
                <strong>Número:</strong><br>
                {{ $sale->sale_number }}
            </div>
            <div class="col-md-3">
                <strong>Fecha:</strong><br>
                {{ $sale->sale_date->format('d/m/Y') }}
            </div>
            <div class="col-md-3">
                <strong>Cliente:</strong><br>
                {{ $sale->customer ? $sale->customer->name : 'Sin cliente' }}
            </div>
            <div class="col-md-3">
                <strong>Estado:</strong><br>
                @switch($sale->status)
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
                <strong>Vendedor:</strong><br>
                {{ $sale->user->name }}
            </div>
            <div class="col-md-3">
                <strong>Tipo de Venta:</strong><br>
                @if($sale->payment_type === 'credit')
                    <span class="badge bg-warning">Crédito</span>
                @else
                    <span class="badge bg-success">Contado</span>
                @endif
            </div>
            <div class="col-md-3">
                <strong>Forma de Pago:</strong><br>
                {{ $sale->payment_method ?? 'No especificado' }}
            </div>
            <div class="col-md-3">
                @if($sale->payment_type === 'credit' && $sale->credit_due_date)
                    <strong>Vencimiento:</strong><br>
                    {{ $sale->credit_due_date->format('d/m/Y') }}
                    <small>({{ $sale->credit_days }} días)</small>
                @endif
            </div>
        </div>

        @if($sale->payment_type === 'credit' && $sale->accountReceivable)
        <div class="alert alert-info mb-4">
            <i class="bi bi-wallet2"></i>
            <strong>Cuenta por Cobrar:</strong>
            <a href="{{ route('account-receivables.show', $sale->accountReceivable->id) }}" class="alert-link">
                {{ $sale->accountReceivable->document_number }}
            </a>
            - Saldo: <strong>{{ number_format($sale->accountReceivable->balance, 0, ',', '.') }} Gs.</strong>
            - Estado:
            @if($sale->accountReceivable->status === 'pending')
                <span class="badge bg-warning">Pendiente</span>
            @elseif($sale->accountReceivable->status === 'partial')
                <span class="badge bg-info">Parcial</span>
            @elseif($sale->accountReceivable->status === 'paid')
                <span class="badge bg-success">Pagado</span>
            @endif
        </div>
        @endif

        @if($sale->journal_entry_id)
        <div class="alert alert-success mb-4">
            <i class="bi bi-journal-text"></i>
            <strong>Asiento Contable:</strong>
            <a href="{{ route('journal-entries.show', $sale->journal_entry_id) }}" class="alert-link">
                {{ $sale->journalEntry->entry_number }}
            </a>
            - Fecha: {{ \Carbon\Carbon::parse($sale->journalEntry->entry_date)->format('d/m/Y') }}
            - Estado: <span class="badge bg-success">{{ $sale->journalEntry->status === 'posted' ? 'Publicado' : 'Borrador' }}</span>
        </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-12">
                <strong>Notas:</strong><br>
                {{ $sale->notes ?? 'Sin notas' }}
            </div>
        </div>

        <!-- Items -->
        <h6 class="mb-3">Detalle de Items</h6>
        <table class="table table-bordered table-striped">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio</th>
                    <th class="text-center">IVA</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="text-end">{{ number_format($item->quantity, 2, ',', '.') }}</td>
                    <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $item->tax_rate }}%</td>
                    <td class="text-end">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="row">
            <div class="col-md-6 offset-md-6">
                <table class="table table-sm">
                    <tr>
                        <td>Total Exento:</td>
                        <td class="text-end">{{ number_format($sale->subtotal_exento, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Gravado 5%:</td>
                        <td class="text-end">{{ number_format($sale->subtotal_5, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>IVA 5%:</td>
                        <td class="text-end">{{ number_format($sale->iva_5, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Gravado 10%:</td>
                        <td class="text-end">{{ number_format($sale->subtotal_10, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>IVA 10%:</td>
                        <td class="text-end">{{ number_format($sale->iva_10, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>TOTAL:</strong></td>
                        <td class="text-end"><strong>{{ number_format($sale->total, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmSale() {
    $.messager.confirm('Confirmar', '¿Desea confirmar esta venta? Se descontará el stock.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('sales.confirm', $sale) }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.alert('Éxito', response.message, 'info', function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var message = '';
                    for (var key in errors) {
                        message += errors[key].join('<br>') + '<br>';
                    }
                    $.messager.alert('Error', message, 'error');
                }
            });
        }
    });
}

function cancelSale() {
    $.messager.confirm('Anular', '¿Desea anular esta venta?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('sales.cancel', $sale) }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.alert('Éxito', response.message, 'info', function() {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var message = '';
                    for (var key in errors) {
                        message += errors[key].join('<br>') + '<br>';
                    }
                    $.messager.alert('Error', message, 'error');
                }
            });
        }
    });
}
</script>
@endsection
