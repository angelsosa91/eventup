@extends('layouts.app')

@section('title', 'Cuentas por Pagar')
@section('page-title', 'Cuentas por Pagar')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newPayable()">Nueva Cuenta</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-tip" onclick="viewDetail()">Ver Detalle</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="addPayment()">Registrar Pago</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelPayable()">Anular</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deletePayable()">Eliminar</a>
    <span style="margin-left: 20px;">
        <select id="status_filter" class="easyui-combobox" style="width: 150px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByStatus(value); }
        ">
            <option value="">Todos los estados</option>
            <option value="pending">Pendiente</option>
            <option value="partial">Parcial</option>
            <option value="paid">Pagado</option>
            <option value="cancelled">Anulado</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
    <span style="float: right;">
        <a href="{{ route('account-payables.by-supplier') }}" class="easyui-linkbutton" iconCls="icon-search">Ver por Proveedor</a>
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('account-payables.data') }}',
           method: 'get',
           toolbar: '#toolbar',
           pagination: true,
           rownumbers: true,
           singleSelect: true,
           fitColumns: true,
           pageSize: 20,
           pageList: [10, 20, 50, 100],
           sortName: 'id',
           sortOrder: 'desc',
           remoteSort: true
       ">
    <thead>
        <tr>
            <th data-options="field:'document_number',width:100,sortable:true">Documento</th>
            <th data-options="field:'document_date',width:80,sortable:true">Fecha</th>
            <th data-options="field:'due_date',width:80,sortable:true">Vencimiento</th>
            <th data-options="field:'supplier_name',width:200">Proveedor</th>
            <th data-options="field:'purchase_number',width:100">Compra</th>
            <th data-options="field:'amount',width:100,align:'right',formatter:formatMoney">Monto</th>
            <th data-options="field:'paid_amount',width:100,align:'right',formatter:formatMoney">Pagado</th>
            <th data-options="field:'balance',width:100,align:'right',formatter:formatMoney">Saldo</th>
            <th data-options="field:'status',width:90,align:'center',formatter:formatStatus">Estado</th>
        </tr>
    </thead>
</table>

<!-- Payment Dialog -->
<div id="paymentDlg" class="easyui-dialog" style="width:500px;padding:20px;" closed="true" buttons="#paymentDlg-buttons">
    <h5 id="paymentTitle" class="mb-3"></h5>
    <form id="paymentForm">
        <div class="mb-3">
            <label class="form-label">Fecha de Pago <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="payment_date" value="{{ date('Y-m-d') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Monto <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="payment_amount" step="0.01" min="0.01" required>
            <small class="text-muted">Saldo pendiente: <span id="balance_info"></span></small>
        </div>
        <div class="mb-3">
            <label class="form-label">Método de Pago <span class="text-danger">*</span></label>
            <select class="form-select" id="payment_method" required>
                <option value="cash">Efectivo</option>
                <option value="transfer">Transferencia</option>
                <option value="check">Cheque</option>
                <option value="card">Tarjeta</option>
                <option value="other">Otro</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Referencia</label>
            <input type="text" class="form-control" id="payment_reference" maxlength="100">
        </div>
        <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea class="form-control" id="payment_notes" rows="2"></textarea>
        </div>
    </form>
</div>
<div id="paymentDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitPayment()">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#paymentDlg').dialog('close')">Cancelar</a>
</div>

<script>
var currentPayableId = null;

function formatMoney(value) {
    if (!value) return '0.00';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function formatStatus(value) {
    switch(value) {
        case 'pending': return '<span class="badge bg-warning">Pendiente</span>';
        case 'partial': return '<span class="badge bg-info">Parcial</span>';
        case 'paid': return '<span class="badge bg-success">Pagado</span>';
        case 'cancelled': return '<span class="badge bg-danger">Anulado</span>';
        default: return value;
    }
}

function newPayable() {
    window.location.href = '{{ route('account-payables.create') }}';
}

function viewDetail() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }
    window.location.href = '{{ url('account-payables') }}/' + row.id;
}

function addPayment() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }
    if (row.status === 'paid') {
        $.messager.alert('Información', 'Esta cuenta ya está pagada', 'warning');
        return;
    }
    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'Esta cuenta está anulada', 'warning');
        return;
    }

    currentPayableId = row.id;
    $('#paymentTitle').text('Registrar Pago - ' + row.document_number);
    $('#balance_info').text(formatMoney(row.balance));
    $('#payment_amount').attr('max', row.balance).val(row.balance);
    $('#paymentForm')[0].reset();
    $('#payment_date').val('{{ date('Y-m-d') }}');
    $('#paymentDlg').dialog('open');
}

function submitPayment() {
    if (!$('#paymentForm')[0].checkValidity()) {
        $('#paymentForm')[0].reportValidity();
        return;
    }

    var data = {
        payment_date: $('#payment_date').val(),
        amount: $('#payment_amount').val(),
        payment_method: $('#payment_method').val(),
        reference: $('#payment_reference').val(),
        notes: $('#payment_notes').val()
    };

    $.ajax({
        url: '{{ url('account-payables') }}/' + currentPayableId + '/add-payment',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#paymentDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al registrar el pago';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function cancelPayable() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }
    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'La cuenta ya está anulada', 'warning');
        return;
    }
    if (row.paid_amount > 0) {
        $.messager.alert('Información', 'No se puede anular una cuenta con pagos registrados', 'warning');
        return;
    }

    $.messager.confirm('Anular', '¿Desea anular esta cuenta por pagar?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('account-payables') }}/' + row.id + '/cancel',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function deletePayable() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }
    if (row.paid_amount > 0) {
        $.messager.alert('Información', 'No se puede eliminar una cuenta con pagos registrados', 'warning');
        return;
    }

    $.messager.confirm('Eliminar', '¿Desea eliminar esta cuenta por pagar?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('account-payables') }}/' + row.id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function filterByStatus(value) {
    $('#dg').datagrid('load', { status: value });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}
</script>
@endsection
