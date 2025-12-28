@extends('layouts.app')

@section('title', 'Remisiones')
@section('page-title', 'Remisiones')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newRemission()">Nueva Remisión</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="viewRemission()">Ver Detalle</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="confirmRemission()">Confirmar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-truck" onclick="deliverRemission()">Marcar Entregada</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-large-smartart" onclick="convertToSale()">Convertir a Factura</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelRemission()">Anular</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print" onclick="printRemission()">Imprimir PDF</a>
    <span style="margin-left: 20px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 300px"
               data-options="prompt:'Buscar por número o cliente...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:700px;"
       data-options="
           url: '{{ route('remissions.data') }}',
           method: 'get',
           toolbar: '#toolbar',
           pagination: true,
           rownumbers: true,
           singleSelect: true,
           fitColumns: false,
           pageSize: 20,
           pageList: [10, 20, 50, 100],
           sortName: 'id',
           sortOrder: 'desc',
           remoteSort: true
       ">
    <thead>
        <tr>
            <th data-options="field:'remission_number',width:120,sortable:true">Número</th>
            <th data-options="field:'date',width:100,sortable:true">Fecha</th>
            <th data-options="field:'customer_name',width:200">Cliente</th>
            <th data-options="field:'reason_text',width:150">Motivo</th>
            <th data-options="field:'delivery_address',width:200">Dirección Entrega</th>
            <th data-options="field:'sale_number',width:120">Factura</th>
            <th data-options="field:'status',width:120,align:'center',formatter:formatStatus">Estado</th>
            <th data-options="field:'created_by',width:120">Creado por</th>
        </tr>
    </thead>
</table>

<!-- Modal para convertir a factura -->
<div id="dlg-convert" class="easyui-dialog" style="width:500px;padding:20px"
     data-options="closed:true,modal:true,buttons:'#dlg-convert-buttons'">
    <h4>Convertir Remisión a Factura</h4>
    <form id="convert-form" style="margin-top:20px">
        <div class="mb-3">
            <label class="form-label">Tipo de Venta <span class="text-danger">*</span></label>
            <select class="form-select" id="payment_type" name="payment_type" onchange="toggleCreditFields()">
                <option value="cash">Contado</option>
                <option value="credit">Crédito</option>
            </select>
        </div>
        <div class="mb-3" id="payment_method_field">
            <label class="form-label">Forma de Pago <span class="text-danger">*</span></label>
            <select class="form-select" id="payment_method" name="payment_method">
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta">Tarjeta</option>
                <option value="Transferencia">Transferencia</option>
                <option value="Cheque">Cheque</option>
            </select>
        </div>
        <div class="mb-3" id="credit_days_field" style="display:none;">
            <label class="form-label">Días de Crédito <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="credit_days" name="credit_days" min="1" value="30">
        </div>
    </form>
</div>
<div id="dlg-convert-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="doConvert()">Convertir</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#dlg-convert').dialog('close')">Cancelar</a>
</div>

<script>
function formatStatus(value, row) {
    switch(value) {
        case 'draft':
            return '<span class="badge bg-secondary">Borrador</span>';
        case 'confirmed':
            return '<span class="badge bg-primary">Confirmada</span>';
        case 'delivered':
            return '<span class="badge bg-info">Entregada</span>';
        case 'invoiced':
            return '<span class="badge bg-success">Facturada</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Anulada</span>';
        default:
            return value;
    }
}

function newRemission() {
    window.location.href = '{{ route('remissions.create') }}';
}

function viewRemission() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        window.location.href = '{{ url('remissions') }}/' + row.id;
    } else {
        $.messager.alert('Información', 'Seleccione una remisión', 'info');
    }
}

function confirmRemission() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una remisión', 'info');
        return;
    }
    if (row.status !== 'draft') {
        $.messager.alert('Información', 'Solo se pueden confirmar remisiones en borrador', 'warning');
        return;
    }
    $.messager.confirm('Confirmar', '¿Desea confirmar esta remisión? Se reservará el stock.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('remissions') }}/' + row.id + '/confirm',
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
                    $('#dg').datagrid('reload');
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
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una remisión', 'info');
        return;
    }
    if (row.status !== 'confirmed') {
        $.messager.alert('Información', 'Solo se pueden marcar como entregadas las remisiones confirmadas', 'warning');
        return;
    }
    $.messager.confirm('Confirmar', '¿Desea marcar esta remisión como entregada?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('remissions') }}/' + row.id + '/deliver',
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
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var error = xhr.responseJSON?.message || 'Error al marcar como entregada';
                    $.messager.alert('Error', error, 'error');
                }
            });
        }
    });
}

var selectedRemissionId = null;

function convertToSale() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una remisión', 'info');
        return;
    }
    if (!row.can_convert) {
        $.messager.alert('Información', 'Esta remisión no puede ser convertida a factura', 'warning');
        return;
    }

    selectedRemissionId = row.id;
    $('#dlg-convert').dialog('open');
    $('#convert-form')[0].reset();
    toggleCreditFields();
}

function toggleCreditFields() {
    var paymentType = $('#payment_type').val();
    if (paymentType === 'credit') {
        $('#payment_method_field').hide();
        $('#credit_days_field').show();
    } else {
        $('#payment_method_field').show();
        $('#credit_days_field').hide();
    }
}

function doConvert() {
    var formData = {
        payment_type: $('#payment_type').val(),
        payment_method: $('#payment_method').val(),
        credit_days: $('#credit_days').val()
    };

    $.ajax({
        url: '{{ url('remissions') }}/' + selectedRemissionId + '/convert-to-sale',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            $('#dlg-convert').dialog('close');
            $.messager.show({
                title: 'Éxito',
                msg: response.message + '<br>Factura: ' + response.sale_number,
                timeout: 5000,
                showType: 'slide'
            });
            $('#dg').datagrid('reload');

            // Preguntar si desea ver la factura
            $.messager.confirm('Ver Factura', '¿Desea ver la factura creada?', function(r) {
                if (r) {
                    window.location.href = '{{ url('sales') }}/' + response.sale_id + '/detail';
                }
            });
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            if (errors) {
                var errorMsg = Object.values(errors).flat().join('<br>');
                $.messager.alert('Error de Validación', errorMsg, 'error');
            } else {
                var error = xhr.responseJSON?.message || 'Error al convertir a factura';
                $.messager.alert('Error', error, 'error');
            }
        }
    });
}

function cancelRemission() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una remisión', 'info');
        return;
    }
    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'La remisión ya está anulada', 'warning');
        return;
    }
    if (row.status === 'invoiced') {
        $.messager.alert('Información', 'No se puede anular una remisión que ya fue facturada', 'warning');
        return;
    }
    $.messager.confirm('Anular', '¿Desea anular esta remisión?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('remissions') }}/' + row.id + '/cancel',
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
                    $('#dg').datagrid('reload');
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
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        window.open('{{ url('remissions') }}/' + row.id + '/pdf', '_blank');
    } else {
        $.messager.alert('Información', 'Seleccione una remisión', 'info');
    }
}

function doSearch(value, name) {
    $('#dg').datagrid('load', {
        search: value
    });
}
</script>
@endsection
