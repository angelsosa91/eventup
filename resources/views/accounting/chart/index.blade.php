@extends('layouts.app')

@section('title', 'Plan de Cuentas')
@section('page-title', 'Plan de Cuentas')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newAccount()">Nueva Cuenta</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newSubAccount()">Nueva Subcuenta</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" onclick="editAccount()">Editar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deleteAccount()">Eliminar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-reload" onclick="$('#tg').treegrid('reload')">Recargar</a>
    <span style="margin-left: 20px;">
        <select id="type_filter" class="easyui-combobox" style="width: 180px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByType(value); }
        ">
            <option value="">Todos los tipos</option>
            <option value="asset">Activo</option>
            <option value="liability">Pasivo</option>
            <option value="equity">Patrimonio</option>
            <option value="income">Ingreso</option>
            <option value="expense">Gasto</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
</div>

<table id="tg" class="easyui-treegrid" style="width:100%;height:650px;"
       data-options="
           url: '{{ route('account-chart.tree') }}',
           method: 'get',
           rownumbers: true,
           idField: 'id',
           treeField: 'name',
           toolbar: '#toolbar',
           animate: true,
           fitColumns: true
       ">
    <thead>
        <tr>
            <th data-options="field:'code',width:120">Código</th>
            <th data-options="field:'name',width:300">Nombre de Cuenta</th>
            <th data-options="field:'account_type',width:100,align:'center',formatter:formatAccountType">Tipo</th>
            <th data-options="field:'nature',width:80,align:'center',formatter:formatNature">Naturaleza</th>
            <th data-options="field:'is_detail',width:80,align:'center',formatter:formatIsDetail">Detalle</th>
            <th data-options="field:'opening_balance',width:120,align:'right',formatter:formatMoney">Saldo Inicial</th>
            <th data-options="field:'current_balance',width:120,align:'right',formatter:formatMoney">Saldo Actual</th>
            <th data-options="field:'is_active',width:80,align:'center',formatter:formatStatus">Estado</th>
        </tr>
    </thead>
</table>

<!-- Account Dialog -->
<div id="accountDlg" class="easyui-dialog" style="width:700px;padding:20px;" closed="true" buttons="#accountDlg-buttons">
    <h5 id="accountTitle" class="mb-3"></h5>
    <form id="accountForm">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Código <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control" id="account_code" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                        <i class="bi bi-magic"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="account_name" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                <select class="form-select" id="account_type" required onchange="updateNature()">
                    <option value="">Seleccione...</option>
                    <option value="asset">Activo</option>
                    <option value="liability">Pasivo</option>
                    <option value="equity">Patrimonio</option>
                    <option value="income">Ingreso</option>
                    <option value="expense">Gasto</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Naturaleza <span class="text-danger">*</span></label>
                <select class="form-select" id="account_nature" required>
                    <option value="debit">Deudora</option>
                    <option value="credit">Acreedora</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Saldo Inicial</label>
                <input type="number" class="form-control" id="opening_balance" step="0.01" value="0">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" id="account_description" rows="2"></textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_detail">
            <label class="form-check-label" for="is_detail">
                Es cuenta de detalle (recibe movimientos contables)
            </label>
        </div>

        <input type="hidden" id="parent_id">
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
    const types = {
        'asset': '<span class="badge bg-success">Activo</span>',
        'liability': '<span class="badge bg-danger">Pasivo</span>',
        'equity': '<span class="badge bg-primary">Patrimonio</span>',
        'income': '<span class="badge bg-info">Ingreso</span>',
        'expense': '<span class="badge bg-warning">Gasto</span>'
    };
    return types[value] || value;
}

function formatNature(value) {
    return value === 'debit' ?
        '<span class="badge bg-secondary">Deudora</span>' :
        '<span class="badge bg-dark">Acreedora</span>';
}

function formatIsDetail(value) {
    return value ? '<i class="bi bi-check-circle text-success"></i>' : '';
}

function formatStatus(value) {
    return value ?
        '<span class="badge bg-success">Activa</span>' :
        '<span class="badge bg-secondary">Inactiva</span>';
}

function newAccount() {
    isEditMode = false;
    currentAccountId = null;
    $('#accountTitle').text('Nueva Cuenta Principal');
    $('#accountForm')[0].reset();
    $('#parent_id').val('');
    $('#account_code').prop('disabled', false);
    $('#accountDlg').dialog('open');
}

function newSubAccount() {
    var row = $('#tg').treegrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta padre', 'info');
        return;
    }

    isEditMode = false;
    currentAccountId = null;
    $('#accountTitle').text('Nueva Subcuenta de: ' + row.code + ' - ' + row.name);
    $('#accountForm')[0].reset();
    $('#parent_id').val(row.id);
    $('#account_type').val(row.account_type);
    $('#account_nature').val(row.nature);
    $('#account_code').prop('disabled', false);

    // Generar código automáticamente
    generateCode();

    $('#accountDlg').dialog('open');
}

function editAccount() {
    var row = $('#tg').treegrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }

    isEditMode = true;
    currentAccountId = row.id;
    $('#accountTitle').text('Editar Cuenta');

    $('#account_code').val(row.code).prop('disabled', true);
    $('#account_name').val(row.name);
    $('#account_type').val(row.account_type);
    $('#account_nature').val(row.nature);
    $('#account_description').val(row.description);
    $('#opening_balance').val(row.opening_balance);
    $('#is_detail').prop('checked', row.is_detail);

    $('#accountDlg').dialog('open');
}

function submitAccount() {
    if (!$('#accountForm')[0].checkValidity()) {
        $('#accountForm')[0].reportValidity();
        return;
    }

    var data = {
        parent_id: $('#parent_id').val() || null,
        code: $('#account_code').val(),
        name: $('#account_name').val(),
        description: $('#account_description').val(),
        account_type: $('#account_type').val(),
        nature: $('#account_nature').val(),
        opening_balance: $('#opening_balance').val(),
        is_detail: $('#is_detail').is(':checked')
    };

    var url = isEditMode ?
        '{{ url('account-chart') }}/' + currentAccountId :
        '{{ route('account-chart.store') }}';
    var method = isEditMode ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#accountDlg').dialog('close');
            $('#tg').treegrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al guardar';
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            }
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function deleteAccount() {
    var row = $('#tg').treegrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una cuenta', 'info');
        return;
    }

    $.messager.confirm('Confirmar', '¿Está seguro de eliminar esta cuenta?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('account-chart') }}/' + row.id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#tg').treegrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al eliminar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function generateCode() {
    var parentId = $('#parent_id').val();

    $.ajax({
        url: '{{ route('account-chart.generate-code') }}',
        method: 'GET',
        data: { parent_id: parentId || null },
        success: function(response) {
            $('#account_code').val(response.code);
        },
        error: function() {
            $.messager.alert('Error', 'Error al generar código', 'error');
        }
    });
}

function updateNature() {
    var type = $('#account_type').val();
    var nature = 'debit';

    if (type === 'liability' || type === 'equity' || type === 'income') {
        nature = 'credit';
    }

    $('#account_nature').val(nature);
}

function filterByType(value) {
    $('#tg').treegrid('load', { account_type: value });
}

function doSearch(value) {
    $('#tg').treegrid('load', { search: value });
}
</script>
@endsection
