@extends('layouts.app')

@section('title', 'Detalle de Remisión')
@section('page-title', 'Detalle de Remisión')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Remisión {{ $remission->remission_number }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('remissions.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>

            @if($remission->canBeConfirmed())
            <button type="button" class="btn btn-primary" onclick="confirmRemission()">
                <i class="bi bi-check-circle"></i> Confirmar
            </button>
            @endif

            @if($remission->canBeDelivered())
            <button type="button" class="btn btn-info" onclick="deliverRemission()">
                <i class="bi bi-truck"></i> Marcar Entregada
            </button>
            @endif

            @if($remission->canBeConvertedToInvoice())
            <button type="button" class="btn btn-success" onclick="showConvertDialog()">
                <i class="bi bi-file-earmark-text"></i> Convertir a Factura
            </button>
            @endif

            @if($remission->canBeCancelled())
            <button type="button" class="btn btn-danger" onclick="cancelRemission()">
                <i class="bi bi-x-circle"></i> Anular
            </button>
            @endif

            <button type="button" class="btn btn-outline-primary" onclick="printRemission()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Información General -->
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="150">Número:</th>
                        <td>{{ $remission->remission_number }}</td>
                    </tr>
                    <tr>
                        <th>Fecha:</th>
                        <td>{{ $remission->date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <th>Cliente:</th>
                        <td>
                            <a href="{{ route('customers.show', $remission->customer_id) }}">
                                {{ $remission->customer->name }}
                            </a>
                            <br>
                            <small class="text-muted">{{ $remission->customer->tax_id }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Motivo:</th>
                        <td>{{ $remission->reason_text }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th width="150">Estado:</th>
                        <td>
                            <span class="badge {{ $remission->status_badge }}">
                                {{ $remission->status_text }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Dirección Entrega:</th>
                        <td>{{ $remission->delivery_address ?: '-' }}</td>
                    </tr>
                    @if($remission->sale_id)
                    <tr>
                        <th>Factura:</th>
                        <td>
                            <a href="{{ route('sales.detail', $remission->sale_id) }}" class="btn btn-sm btn-success">
                                <i class="bi bi-file-earmark-text"></i> Ver Factura
                            </a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th>Creado por:</th>
                        <td>
                            {{ $remission->createdBy->name }}
                            <br>
                            <small class="text-muted">{{ $remission->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Items de la Remisión -->
        <div class="row mb-4">
            <div class="col-12">
                <h6 class="mb-3">Productos</h6>
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th width="100" class="text-end">Cantidad</th>
                            @if($remission->status === 'confirmed')
                            <th width="120" class="text-end">Reservado</th>
                            @endif
                            <th width="100" class="text-end">Stock Actual</th>
                            <th width="200">Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($remission->items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('products.show', $item->product_id) }}">
                                    {{ $item->product->name }}
                                </a>
                                <br>
                                <small class="text-muted">{{ $item->product->code }}</small>
                            </td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                            @if($remission->status === 'confirmed')
                            <td class="text-end">
                                <span class="badge bg-warning">{{ number_format($item->reserved_quantity, 2) }}</span>
                            </td>
                            @endif
                            <td class="text-end">{{ number_format($item->product->stock, 2) }}</td>
                            <td>{{ $item->notes ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Notas -->
        @if($remission->notes)
        <div class="row">
            <div class="col-12">
                <h6 class="mb-2">Notas / Observaciones</h6>
                <div class="alert alert-info">
                    {{ $remission->notes }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal para Convertir a Factura -->
<div id="convertDialog" class="easyui-dialog" title="Convertir a Factura" style="width:500px;padding:20px"
     data-options="modal:true,closed:true,buttons:'#convertButtons'">
    <form id="convertForm">
        <div class="mb-3">
            <label class="form-label">Tipo de Pago <span class="text-danger">*</span></label>
            <select class="form-select" id="payment_type" name="payment_type" required>
                <option value="cash">Contado</option>
                <option value="credit">Crédito</option>
            </select>
        </div>
        <div class="mb-3" id="creditDaysField" style="display: none;">
            <label class="form-label">Días de Crédito <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="credit_days" name="credit_days" min="1" value="30">
        </div>
        <div class="mb-3">
            <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
            <select class="form-select" id="payment_method" name="payment_method" required>
                <option value="cash">Efectivo</option>
                <option value="transfer">Transferencia</option>
                <option value="check">Cheque</option>
                <option value="card">Tarjeta</option>
            </select>
        </div>
    </form>
    <div id="convertButtons">
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="executeConvert()">Convertir</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" onclick="$('#convertDialog').dialog('close')">Cancelar</a>
    </div>
</div>

<script>
$(function() {
    // Mostrar campo de días de crédito si es necesario
    $('#payment_type').change(function() {
        if ($(this).val() === 'credit') {
            $('#creditDaysField').show();
        } else {
            $('#creditDaysField').hide();
        }
    });
});

function confirmRemission() {
    $.messager.confirm('Confirmar', '¿Desea confirmar esta remisión? Se reservarán los productos en el inventario.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('remissions.confirm', $remission->id) }}',
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
                    var error = xhr.responseJSON?.message || 'Error al confirmar la remisión';
                    $.messager.alert('Error', error, 'error');
                }
            });
        }
    });
}

function deliverRemission() {
    $.messager.confirm('Confirmar', '¿Desea marcar esta remisión como entregada?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ route('remissions.deliver', $remission->id) }}',
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
                    var error = xhr.responseJSON?.message || 'Error al marcar como entregada';
                    $.messager.alert('Error', error, 'error');
                }
            });
        }
    });
}

function showConvertDialog() {
    $('#convertDialog').dialog('open');
    $('#payment_type').val('cash');
    $('#payment_method').val('cash');
    $('#creditDaysField').hide();
}

function executeConvert() {
    var formData = {
        payment_type: $('#payment_type').val(),
        payment_method: $('#payment_method').val(),
        credit_days: $('#payment_type').val() === 'credit' ? $('#credit_days').val() : null
    };

    $.ajax({
        url: '{{ route('remissions.convert-to-sale', $remission->id) }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            $('#convertDialog').dialog('close');
            $.messager.show({
                title: 'Éxito',
                msg: response.message,
                timeout: 3000,
                showType: 'slide'
            });
            setTimeout(function() {
                window.location.href = response.redirect;
            }, 1500);
        },
        error: function(xhr) {
            var error = xhr.responseJSON?.message || 'Error al convertir a factura';
            $.messager.alert('Error', error, 'error');
        }
    });
}

function cancelRemission() {
    $.messager.prompt('Anular Remisión', 'Ingrese el motivo de anulación:', function(reason) {
        if (reason) {
            $.ajax({
                url: '{{ route('remissions.cancel', $remission->id) }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                contentType: 'application/json',
                data: JSON.stringify({ reason: reason }),
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
                    var error = xhr.responseJSON?.message || 'Error al anular la remisión';
                    $.messager.alert('Error', error, 'error');
                }
            });
        }
    });
}

function printRemission() {
    window.open('{{ route('remissions.pdf', $remission->id) }}', '_blank');
}
</script>
@endsection
