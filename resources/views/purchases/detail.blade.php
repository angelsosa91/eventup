@extends('layouts.app')

@section('title', 'Detalle de Compra')
@section('page-title', 'Detalle de Compra')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Compra {{ $purchase->purchase_number }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('purchases.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            @if($purchase->status === 'draft')
            <button type="button" class="btn btn-success" onclick="confirmPurchase()">
                <i class="bi bi-check-circle"></i> Confirmar
            </button>
            @endif
            @if($purchase->status !== 'cancelled')
            <button type="button" class="btn btn-danger" onclick="cancelPurchase()">
                <i class="bi bi-x-circle"></i> Anular
            </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <!-- Información de la compra -->
        <div class="row mb-4">
            <div class="col-md-3">
                <strong>Fecha:</strong><br>
                {{ $purchase->purchase_date->format('d/m/Y') }}
            </div>
            <div class="col-md-3">
                <strong>Proveedor:</strong><br>
                {{ $purchase->supplier->name ?? 'Sin proveedor' }}
            </div>
            <div class="col-md-2">
                <strong>Tipo de Compra:</strong><br>
                @if($purchase->payment_type === 'credit')
                    <span class="badge bg-warning">Crédito</span>
                @else
                    <span class="badge bg-success">Contado</span>
                @endif
            </div>
            <div class="col-md-2">
                <strong>Estado:</strong><br>
                @switch($purchase->status)
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
            <div class="col-md-2">
                @if($purchase->payment_type === 'credit' && $purchase->credit_due_date)
                    <strong>Vencimiento:</strong><br>
                    {{ $purchase->credit_due_date->format('d/m/Y') }}
                    <small>({{ $purchase->credit_days }} días)</small>
                @endif
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <strong>Fact. Proveedor:</strong><br>
                {{ $purchase->invoice_number ?? '-' }}
            </div>
            <div class="col-md-4">
                <strong>Forma de Pago:</strong><br>
                {{ $purchase->payment_method }}
            </div>
        </div>

        @if($purchase->payment_type === 'credit' && $purchase->accountPayable)
        <div class="alert alert-info mb-4">
            <i class="bi bi-credit-card"></i>
            <strong>Cuenta por Pagar:</strong>
            <a href="{{ route('account-payables.show', $purchase->accountPayable->id) }}" class="alert-link">
                {{ $purchase->accountPayable->document_number }}
            </a>
            - Saldo: <strong>{{ number_format($purchase->accountPayable->balance, 0, ',', '.') }} Gs.</strong>
            - Estado:
            @if($purchase->accountPayable->status === 'pending')
                <span class="badge bg-warning">Pendiente</span>
            @elseif($purchase->accountPayable->status === 'partial')
                <span class="badge bg-info">Parcial</span>
            @elseif($purchase->accountPayable->status === 'paid')
                <span class="badge bg-success">Pagado</span>
            @endif
        </div>
        @endif

        @if($purchase->journal_entry_id)
        <div class="alert alert-success mb-4">
            <i class="bi bi-journal-text"></i>
            <strong>Asiento Contable:</strong>
            <a href="{{ route('journal-entries.show', $purchase->journal_entry_id) }}" class="alert-link">
                {{ $purchase->journalEntry->entry_number }}
            </a>
            - Fecha: {{ \Carbon\Carbon::parse($purchase->journalEntry->entry_date)->format('d/m/Y') }}
            - Estado: <span class="badge bg-success">{{ $purchase->journalEntry->status === 'posted' ? 'Publicado' : 'Borrador' }}</span>
        </div>
        @endif

        <!-- Items de la compra -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio</th>
                    <th class="text-center">IVA</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
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
        <div class="row mt-3">
            <div class="col-md-6">
                @if($purchase->notes)
                <div class="mb-3">
                    <strong>Notas:</strong><br>
                    {{ $purchase->notes }}
                </div>
                @endif
                <div>
                    <strong>Usuario:</strong> {{ $purchase->user->name }}<br>
                    <strong>Creado:</strong> {{ $purchase->created_at->format('d/m/Y H:i') }}
                </div>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td>Total Exento:</td>
                        <td class="text-end">{{ number_format($purchase->subtotal_exento, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Gravado 5%:</td>
                        <td class="text-end">{{ number_format($purchase->subtotal_5, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>IVA 5%:</td>
                        <td class="text-end">{{ number_format($purchase->iva_5, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Gravado 10%:</td>
                        <td class="text-end">{{ number_format($purchase->subtotal_10, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>IVA 10%:</td>
                        <td class="text-end">{{ number_format($purchase->iva_10, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="table-primary">
                        <td><strong>TOTAL:</strong></td>
                        <td class="text-end"><strong>{{ number_format($purchase->total, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmPurchase() {
    $.messager.confirm('Confirmar', '¿Desea confirmar esta compra? Se incrementará el stock.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('purchases') }}/{{ $purchase->id }}/confirm',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.alert('Éxito', response.message, 'info', function() {
                        window.location.reload();
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

function cancelPurchase() {
    $.messager.confirm('Anular', '¿Desea anular esta compra? Se revertirá el stock si estaba confirmada.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('purchases') }}/{{ $purchase->id }}/cancel',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.alert('Éxito', response.message, 'info', function() {
                        window.location.reload();
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
@endpush
@endsection
