@extends('layouts.app')

@section('title', 'Cuentas Bancarias')
@section('page-title', 'Cuentas Bancarias')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newAccount()">Nueva Cuenta</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-tip" onclick="viewDetail()">Ver Detalle</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" onclick="editAccount()">Editar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="toggleStatus()">Activar/Desactivar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-lock" onclick="setAsDefault()">Establecer Predeterminada</a>
    <span style="margin-left: 20px;">
        <select id="status_filter" class="easyui-combobox" style="width: 150px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByStatus(value); }
        ">
            <option value="">Todos los estados</option>
            <option value="active">Activas</option>
            <option value="inactive">Inactivas</option>
            <option value="closed">Cerradas</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('bank-accounts.data') }}',
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
            <th data-options="field:'account_number',width:120,sortable:true">Número de Cuenta</th>
            <th data-options="field:'account_name',width:150">Nombre de Cuenta</th>
            <th data-options="field:'bank_name',width:120">Banco</th>
            <th data-options="field:'account_type',width:80,align:'center',formatter:formatAccountType">Tipo</th>
            <th data-options="field:'initial_balance',width:100,align:'right',formatter:formatMoney">Saldo Inicial</th>
            <th data-options="field:'current_balance',width:100,align:'right',formatter:formatMoney">Saldo Actual</th>
            <th data-options="field:'currency',width:60,align:'center'">Moneda</th>
            <th data-options="field:'is_default',width:80,align:'center',formatter:formatDefault">Predeterminada</th>
            <th data-options="field:'status',width:80,align:'center',formatter:formatStatus">Estado</th>
        </tr>
    </thead>
</table>

<!-- Account Dialog -->
<div id="accountDlg" class="easyui-dialog" style="width:600px;padding:20px;" closed="true" buttons="#accountDlg-buttons">
    <h5 id="accountTitle" class="mb-3"></h5>
    <form id="accountForm">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Número de Cuenta <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="account_number" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nombre de Cuenta <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="account_name" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Banco <span class="text-danger">*</span></label>
            <select id="bank_id" class="easyui-combobox" style="width: 100%;" data-options="
                url: '{{ route('banks.active') }}',
                method: 'get',
                valueField: 'id',
                textField: 'name',
                panelHeight: 'auto',
                required: true,
                editable: false,
                onSelect: function(record) { onBankSelect(record); }
            "></select>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tipo de Cuenta <span class="text-danger">*</span></label>
                <select class="form-select" id="account_type" required>
                    <option value="checking">Cuenta Corriente</option>
                    <option value="savings">Caja de Ahorro</option>
                    <option value="credit">Crédito</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Moneda <span class="text-danger">*</span></label>
                <select class="form-select" id="currency" required>
                    <option value="PYG">Guaraníes (PYG)</option>
                    <option value="USD">Dólares (USD)</option>
                    <option value="EUR">Euros (EUR)</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Saldo Inicial <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="initial_balance" step="0.01" min="0" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Titular de la Cuenta</label>
                <input type="text" class="form-control" id="account_holder">
            </div>
            <div class="col-md-6">
                <label class="form-label">Código SWIFT</label>
                <input type="text" class="form-control" id="swift_code">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea class="form-control" id="notes" rows="2"></textarea>
        </div>
    </form>
</div>
<div id="accountDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitAccount()">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#accountDlg').dialog('close')">Cancelar</a>
</div>

<script>
var currentAccountId = null;
var isEditMode = false;

function formatMoney(value) {
    if (!value) return '0';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function formatAccountType(value) {
    switch(value) {
        case 'checking': return '<span class="badge bg-primary">Cuenta Corriente</span>';
        case 'savings': return '<span class="badge bg-success">Caja de Ahorro</span>';
        case 'credit': return '<span class="badge bg-warning">Crédito</span>';
        default: return value;
    }
}

function formatStatus(value) {
    switch(value) {
        case 'active': return '<span class="badge bg-success">Activa</span>';
        case 'inactive': return '<span class="badge bg-secondary">Inactiva</span>';
        case 'closed': return '<span class="badge bg-danger">Cerrada</span>';
        default: return value;
    }
}

function formatDefault(value) {
    return value ? '<span class="badge bg-warning"><i class="fas fa-star"></i> Sí</span>' : '';
}

function onBankSelect(record) {
    // El código SWIFT y otros datos se pueden completar automáticamente si es necesario
    if (record.swift_code) {
        $('#swift_code').val(record.swift_code);
    }
}

function newAccount() {
    isEditMode = false;
    currentAccountId = null;
    $('#accountTitle').text('Nueva Cuenta Bancaria');
    $('#accountForm')[0].reset();
    $('#bank_id').combobox('clear');
    $('#account_number').prop('disabled', false);
    $('#initial_balance').prop('disabled', false);
    $('#accountDlg').dialog('open');
}

function editAccount() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }

    isEditMode = true;
    currentAccountId = row.id;
    $('#accountTitle').text('Editar Cuenta Bancaria');

    $('#account_number').val(row.account_number).prop('disabled', true);
    $('#account_name').val(row.account_name);

    // Cargar el banco seleccionado
    if (row.bank_id) {
        $('#bank_id').combobox('setValue', row.bank_id);
    } else {
        $('#bank_id').combobox('clear');
    }

    $('#account_type').val(row.account_type);
    $('#currency').val(row.currency);
    $('#initial_balance').val(row.initial_balance).prop('disabled', true);
    $('#account_holder').val(row.account_holder);
    $('#swift_code').val(row.swift_code);
    $('#notes').val(row.notes);

    $('#accountDlg').dialog('open');
}

function submitAccount() {
    if (!$('#accountForm')[0].checkValidity()) {
        $('#accountForm')[0].reportValidity();
        return;
    }

    var bankId = $('#bank_id').combobox('getValue');
    if (!bankId) {
        $.messager.alert('Validación', 'Debe seleccionar un banco', 'warning');
        return;
    }

    var data = {
        account_number: $('#account_number').val(),
        account_name: $('#account_name').val(),
        bank_id: bankId,
        account_type: $('#account_type').val(),
        currency: $('#currency').val(),
        initial_balance: $('#initial_balance').val(),
        account_holder: $('#account_holder').val(),
        swift_code: $('#swift_code').val(),
        notes: $('#notes').val()
    };

    var url = isEditMode ? '{{ url('bank-accounts') }}/' + currentAccountId : '{{ route('bank-accounts.store') }}';
    var method = isEditMode ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#accountDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al guardar';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function viewDetail() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }
    window.location.href = '{{ url('bank-accounts') }}/' + row.id;
}

function toggleStatus() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }

    var action = row.status === 'active' ? 'desactivar' : 'activar';
    $.messager.confirm('Confirmar', '¿Desea ' + action + ' esta cuenta?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('bank-accounts') }}/' + row.id + '/toggle-status',
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

function filterByStatus(value) {
    $('#dg').datagrid('load', { status: value });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}

function setAsDefault() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }

    if (row.status !== 'active') {
        $.messager.alert('Advertencia', 'Solo puede establecer como predeterminada una cuenta activa', 'warning');
        return;
    }

    if (row.is_default) {
        $.messager.alert('Información', 'Esta cuenta ya es la predeterminada', 'info');
        return;
    }

    $.messager.confirm('Confirmar', '¿Desea establecer esta cuenta como predeterminada para transferencias bancarias?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('bank-accounts') }}/' + row.id + '/set-default',
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
</script>
@endsection
