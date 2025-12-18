@extends('layouts.app')

@section('title', 'Categorías de Gastos - Neo ERP')
@section('page-title', 'Gestión de Categorías de Gastos')

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Toolbar -->
        <div id="toolbar" style="padding:5px;">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newCategory()">Nueva Categoría</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editCategory()">Editar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteCategory()">Eliminar</a>
            <span class="ms-3">
                <input id="searchbox" class="easyui-searchbox" style="width:250px"
                    data-options="searcher:doSearch,prompt:'Buscar categoría...'">
            </span>
        </div>

        <!-- DataGrid -->
        <table id="dg-categories" class="easyui-datagrid" style="width:100%;height:500px"
            data-options="
                url:'{{ route('expense-categories.data') }}',
                method:'get',
                toolbar:'#toolbar',
                pagination:true,
                pageSize:20,
                pageList:[10,20,50,100],
                rownumbers:true,
                singleSelect:true,
                fitColumns:true,
                sortName:'id',
                sortOrder:'desc',
                remoteSort:true
            ">
            <thead>
                <tr>
                    <th data-options="field:'id',width:60,sortable:true">ID</th>
                    <th data-options="field:'name',width:150,sortable:true">Nombre</th>
                    <th data-options="field:'description',width:200">Descripción</th>
                    <th data-options="field:'account_name',width:200">Cuenta Contable</th>
                    <th data-options="field:'expenses_count',width:80,align:'center'">Gastos</th>
                    <th data-options="field:'is_active',width:80,align:'center',formatter:formatStatus">Estado</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Dialog para crear/editar categoría -->
<div id="dlg-category" class="easyui-dialog" style="width:500px;padding:20px" closed="true" buttons="#dlg-buttons">
    <form id="fm-category" method="post">
        <input type="hidden" name="id" id="category-id">

        <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input class="easyui-textbox" name="name" id="category-name" style="width:100%" data-options="required:true">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input class="easyui-textbox" name="description" id="category-description" style="width:100%" data-options="multiline:true,height:80">
        </div>

        <div class="mb-3">
            <label class="form-label">Cuenta Contable del Plan de Cuentas <span class="text-danger">*</span></label>
            <select class="easyui-combobox" name="account_id" id="category-account" style="width:100%"
                data-options="
                    url:'{{ route('account-chart.detail-accounts') }}?account_type=expense',
                    method:'get',
                    valueField:'id',
                    textField:'name',
                    required:true,
                    editable:true,
                    panelHeight:'300px',
                    filter: function(q, row){
                        var opts = $(this).combobox('options');
                        return row[opts.textField].toLowerCase().indexOf(q.toLowerCase()) >= 0;
                    }
                ">
            </select>
            <small class="text-muted">Esta cuenta se debitará cuando se registre un gasto de esta categoría</small>
        </div>

        <div class="mb-3">
            <input type="checkbox" name="is_active" id="category-active" value="1" checked>
            <label for="category-active">Categoría Activa</label>
        </div>
    </form>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveCategory()" style="width:90px">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#dlg-category').dialog('close')" style="width:90px">Cancelar</a>
</div>
@endsection

@push('scripts')
<script>
var editingId = null;

function formatStatus(value) {
    if (value) {
        return '<span class="badge bg-success">Activo</span>';
    } else {
        return '<span class="badge bg-danger">Inactivo</span>';
    }
}

function doSearch(value) {
    $('#dg-categories').datagrid('load', {
        search: value
    });
}

function newCategory() {
    editingId = null;
    $('#dlg-category').dialog('open').dialog('setTitle', 'Nueva Categoría de Gasto');
    $('#fm-category').form('clear');
    $('#category-active').prop('checked', true);
}

function editCategory() {
    var row = $('#dg-categories').datagrid('getSelected');
    if (row) {
        editingId = row.id;
        $('#dlg-category').dialog('open').dialog('setTitle', 'Editar Categoría de Gasto');

        $.get('{{ url("/expense-categories") }}/' + row.id, function(data) {
            $('#category-id').val(data.id);
            $('#category-name').textbox('setValue', data.name);
            $('#category-description').textbox('setValue', data.description || '');
            $('#category-account').combobox('setValue', data.account_id);
            $('#category-active').prop('checked', data.is_active);
        });
    } else {
        $.messager.alert('Aviso', 'Seleccione una categoría para editar', 'warning');
    }
}

function saveCategory() {
    if (!$('#fm-category').form('validate')) {
        return;
    }

    var formData = {
        name: $('#category-name').textbox('getValue'),
        description: $('#category-description').textbox('getValue'),
        account_id: $('#category-account').combobox('getValue'),
        is_active: $('#category-active').is(':checked') ? 1 : 0
    };

    var url = editingId ? '{{ url("/expense-categories") }}/' + editingId : '{{ route("expense-categories.store") }}';
    var method = editingId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function(result) {
            if (result.success) {
                $('#dlg-category').dialog('close');
                $('#dg-categories').datagrid('reload');
                $.messager.show({
                    title: 'Éxito',
                    msg: result.message,
                    timeout: 3000,
                    showType: 'slide'
                });
            } else {
                $.messager.alert('Error', result.message, 'error');
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            if (errors) {
                var msg = Object.values(errors).flat().join('<br>');
                $.messager.alert('Error de validación', msg, 'error');
            } else {
                $.messager.alert('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
            }
        }
    });
}

function deleteCategory() {
    var row = $('#dg-categories').datagrid('getSelected');
    if (row) {
        $.messager.confirm('Confirmar', '¿Está seguro de eliminar la categoría "' + row.name + '"?', function(r) {
            if (r) {
                $.ajax({
                    url: '{{ url("/expense-categories") }}/' + row.id,
                    method: 'DELETE',
                    success: function(result) {
                        if (result.success) {
                            $('#dg-categories').datagrid('reload');
                            $.messager.show({
                                title: 'Éxito',
                                msg: result.message,
                                timeout: 3000,
                                showType: 'slide'
                            });
                        } else {
                            $.messager.alert('Error', result.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        $.messager.alert('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                    }
                });
            }
        });
    } else {
        $.messager.alert('Aviso', 'Seleccione una categoría para eliminar', 'warning');
    }
}
</script>
@endpush
