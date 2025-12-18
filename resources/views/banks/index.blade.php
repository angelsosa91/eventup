@extends('layouts.app')

@section('title', 'Bancos')
@section('page-title', 'Bancos')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newBank()">Nuevo Banco</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" onclick="editBank()">Editar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deleteBank()">Eliminar</a>
    <span style="margin-left: 20px;">
        <select id="status_filter" class="easyui-combobox" style="width: 150px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByStatus(value); }
        ">
            <option value="">Todos los estados</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('banks.list') }}',
           method: 'get',
           toolbar: '#toolbar',
           pagination: true,
           rownumbers: true,
           singleSelect: true,
           fitColumns: true,
           pageSize: 20,
           pageList: [10, 20, 50, 100],
           sortName: 'name',
           sortOrder: 'asc',
           remoteSort: true
       ">
    <thead>
        <tr>
            <th data-options="field:'name',width:200,sortable:true">Nombre Oficial</th>
            <th data-options="field:'short_name',width:120">Nombre Corto</th>
            <th data-options="field:'code',width:100,align:'center'">Código SET/BCP</th>
            <th data-options="field:'swift_code',width:120,align:'center'">Código SWIFT</th>
            <th data-options="field:'country',width:100,align:'center'">País</th>
            <th data-options="field:'is_active',width:80,align:'center',formatter:formatStatus">Estado</th>
        </tr>
    </thead>
</table>

<!-- Bank Dialog -->
<div id="bankDlg" class="easyui-dialog" style="width:600px;padding:20px;" closed="true" buttons="#bankDlg-buttons">
    <h5 id="bankTitle" class="mb-3"></h5>
    <form id="bankForm" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Nombre oficial <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="bank_name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Nombre corto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="bank_short_name" required>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Código SET/BCP</label>
                <input type="text" class="form-control" id="bank_code">
            </div>
            <div class="col-md-6">
                <label class="form-label">Código SWIFT/BIC</label>
                <input type="text" class="form-control" id="bank_swift_code">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">País <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="bank_country" value="Paraguay" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Logo</label>
            <input type="file" class="form-control" id="bank_logo" accept="image/*">
            <div id="current_logo" class="mt-2" style="display: none;">
                <img id="logo_preview" src="" alt="Logo actual" style="max-height: 100px;">
            </div>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="bank_is_active" checked>
            <label class="form-check-label" for="bank_is_active">Activo</label>
        </div>
    </form>
</div>
<div id="bankDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitBank()">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#bankDlg').dialog('close')">Cancelar</a>
</div>

<script>
var currentBankId = null;
var isEditMode = false;

function formatStatus(value) {
    return value ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">Inactivo</span>';
}

function newBank() {
    isEditMode = false;
    currentBankId = null;
    $('#bankTitle').text('Nuevo Banco');
    $('#bankForm')[0].reset();
    $('#bank_is_active').prop('checked', true);
    $('#current_logo').hide();
    $('#bankDlg').dialog('open');
}

function editBank() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un banco', 'info');
        return;
    }

    isEditMode = true;
    currentBankId = row.id;
    $('#bankTitle').text('Editar Banco');

    $('#bank_name').val(row.name);
    $('#bank_short_name').val(row.short_name);
    $('#bank_code').val(row.code);
    $('#bank_swift_code').val(row.swift_code);
    $('#bank_country').val(row.country);
    $('#bank_is_active').prop('checked', row.is_active);

    if (row.logo) {
        $('#logo_preview').attr('src', '{{ asset('storage') }}/' + row.logo);
        $('#current_logo').show();
    } else {
        $('#current_logo').hide();
    }

    $('#bankDlg').dialog('open');
}

function submitBank() {
    if (!$('#bankForm')[0].checkValidity()) {
        $('#bankForm')[0].reportValidity();
        return;
    }

    var formData = new FormData();
    formData.append('name', $('#bank_name').val());
    formData.append('short_name', $('#bank_short_name').val());
    formData.append('code', $('#bank_code').val());
    formData.append('swift_code', $('#bank_swift_code').val());
    formData.append('country', $('#bank_country').val());
    formData.append('is_active', $('#bank_is_active').is(':checked') ? 1 : 0);

    var logoFile = $('#bank_logo')[0].files[0];
    if (logoFile) {
        formData.append('logo', logoFile);
    }

    var url = isEditMode ? '{{ url('banks') }}/' + currentBankId : '{{ route('banks.store') }}';

    if (isEditMode) {
        formData.append('_method', 'PUT');
    }

    $.ajax({
        url: url,
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#bankDlg').dialog('close');
            $('#dg').datagrid('reload');
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

function deleteBank() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un banco', 'info');
        return;
    }

    $.messager.confirm('Confirmar', '¿Está seguro de eliminar este banco?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('banks') }}/' + row.id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al eliminar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function filterByStatus(value) {
    $('#dg').datagrid('load', { is_active: value });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}
</script>
@endsection
