@extends('layouts.app')

@section('title', 'Usuarios - Neo ERP')
@section('page-title', 'Gestion de Usuarios')

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Toolbar -->
        <div id="toolbar" style="padding:5px;">
            @can('users.create')
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">Nuevo Usuario</a>
            @endcan
            @can('users.edit')
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Editar</a>
            @endcan
            @can('users.delete')
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteUser()">Eliminar</a>
            @endcan
            <span class="ms-3">
                <input id="searchbox" class="easyui-searchbox" style="width:250px"
                    data-options="searcher:doSearch,prompt:'Buscar usuario...'">
            </span>
        </div>

        <!-- DataGrid -->
        <table id="dg-users" class="easyui-datagrid" style="width:100%;height:700px"
            data-options="
                url:'{{ route('users.data') }}',
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
                    <th data-options="field:'email',width:200,sortable:true">Email</th>
                    <th data-options="field:'tenant',width:150">Empresa</th>
                    <th data-options="field:'roles',width:150">Roles</th>
                    <th data-options="field:'is_active',width:80,align:'center',formatter:formatStatus">Estado</th>
                    <th data-options="field:'created_at',width:130">Creado</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Dialog para crear/editar usuario -->
<div id="dlg-user" class="easyui-dialog" style="width:500px;padding:20px" closed="true" buttons="#dlg-buttons">
    <form id="fm-user" method="post">
        <input type="hidden" name="id" id="user-id">
        <div class="mb-3">
            <label class="form-label">Nombre <span class="text-danger">*</span></label>
            <input class="easyui-textbox" name="name" id="user-name" style="width:100%" data-options="required:true">
        </div>
        <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input class="easyui-textbox" name="email" id="user-email" style="width:100%" data-options="required:true,validType:'email'">
        </div>
        <div class="mb-3">
            <label class="form-label">Contrasena <span id="password-required" class="text-danger">*</span></label>
            <input class="easyui-textbox" name="password" id="user-password" type="password" style="width:100%">
            <small class="text-muted" id="password-help" style="display:none">Dejar vacio para mantener la contrasena actual</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Roles</label>
            <select class="easyui-combobox" name="roles[]" id="user-roles" style="width:100%"
                data-options="
                    url:'{{ route('roles.list') }}',
                    method:'get',
                    valueField:'id',
                    textField:'name',
                    multiple:true,
                    panelHeight:'auto'
                ">
            </select>
        </div>
        <div class="mb-3">
            <input type="checkbox" name="is_active" id="user-active" value="1" checked>
            <label for="user-active">Usuario Activo</label>
        </div>
    </form>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveUser()" style="width:90px">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#dlg-user').dialog('close')" style="width:90px">Cancelar</a>
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
    $('#dg-users').datagrid('load', {
        search: value
    });
}

function newUser() {
    editingId = null;
    $('#dlg-user').dialog('open').dialog('setTitle', 'Nuevo Usuario');
    $('#fm-user').form('clear');
    $('#user-active').prop('checked', true);
    $('#user-password').textbox({required: true});
    $('#password-required').show();
    $('#password-help').hide();
}

function editUser() {
    var row = $('#dg-users').datagrid('getSelected');
    if (row) {
        editingId = row.id;
        $('#dlg-user').dialog('open').dialog('setTitle', 'Editar Usuario');
        $('#user-password').textbox({required: false});
        $('#password-required').hide();
        $('#password-help').show();

        $.get('{{ url("/users") }}/' + row.id, function(data) {
            $('#user-id').val(data.id);
            $('#user-name').textbox('setValue', data.name);
            $('#user-email').textbox('setValue', data.email);
            $('#user-roles').combobox('setValues', data.roles);
            $('#user-active').prop('checked', data.is_active);
        });
    } else {
        $.messager.alert('Aviso', 'Seleccione un usuario para editar', 'warning');
    }
}

function saveUser() {
    if (!$('#fm-user').form('validate')) {
        return;
    }

    var formData = {
        name: $('#user-name').textbox('getValue'),
        email: $('#user-email').textbox('getValue'),
        password: $('#user-password').textbox('getValue'),
        roles: $('#user-roles').combobox('getValues'),
        is_active: $('#user-active').is(':checked') ? 1 : 0
    };

    var url = editingId ? '{{ url("/users") }}/' + editingId : '{{ route("users.store") }}';
    var method = editingId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function(result) {
            if (result.success) {
                $('#dlg-user').dialog('close');
                $('#dg-users').datagrid('reload');
                $.messager.show({
                    title: 'Exito',
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
                $.messager.alert('Error de validacion', msg, 'error');
            } else {
                $.messager.alert('Error', xhr.responseJSON?.message || 'Error al guardar', 'error');
            }
        }
    });
}

function deleteUser() {
    var row = $('#dg-users').datagrid('getSelected');
    if (row) {
        $.messager.confirm('Confirmar', 'Esta seguro de eliminar el usuario "' + row.name + '"?', function(r) {
            if (r) {
                $.ajax({
                    url: '{{ url("/users") }}/' + row.id,
                    method: 'DELETE',
                    success: function(result) {
                        if (result.success) {
                            $('#dg-users').datagrid('reload');
                            $.messager.show({
                                title: 'Exito',
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
        $.messager.alert('Aviso', 'Seleccione un usuario para eliminar', 'warning');
    }
}
</script>
@endpush
