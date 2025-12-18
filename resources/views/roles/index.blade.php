@extends('layouts.app')

@section('title', 'Roles - Neo ERP')
@section('page-title', 'Gestion de Roles')

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Toolbar -->
        <div id="toolbar" style="padding:5px;">
            @can('roles.create')
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newRole()">Nuevo Rol</a>
            @endcan
            @can('roles.edit')
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editRole()">Editar</a>
            @endcan
            @can('roles.delete')
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteRole()">Eliminar</a>
            @endcan
        </div>

        <!-- DataGrid -->
        <table id="dg-roles" class="easyui-datagrid" style="width:100%;height:700px"
            data-options="
                url:'{{ route('roles.data') }}',
                method:'get',
                toolbar:'#toolbar',
                pagination:true,
                pageSize:20,
                rownumbers:true,
                singleSelect:true,
                fitColumns:true,
                sortName:'id',
                sortOrder:'asc',
                remoteSort:true
            ">
            <thead>
                <tr>
                    <th data-options="field:'id',width:60,sortable:true">ID</th>
                    <th data-options="field:'name',width:150,sortable:true">Nombre</th>
                    <th data-options="field:'slug',width:150">Slug</th>
                    <th data-options="field:'description',width:250">Descripcion</th>
                    <th data-options="field:'users_count',width:100,align:'center'">Usuarios</th>
                    <th data-options="field:'permissions_count',width:100,align:'center'">Permisos</th>
                    <th data-options="field:'is_system',width:100,align:'center',formatter:formatSystem">Sistema</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Dialog para crear/editar rol -->
<div id="dlg-role" class="easyui-dialog" style="width:600px;padding:20px" closed="true" buttons="#dlg-buttons">
    <form id="fm-role" method="post">
        <input type="hidden" name="id" id="role-id">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input class="easyui-textbox" name="name" id="role-name" style="width:100%" data-options="required:true">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Slug <span class="text-danger">*</span></label>
                <input class="easyui-textbox" name="slug" id="role-slug" style="width:100%" data-options="required:true">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripcion</label>
            <input class="easyui-textbox" name="description" id="role-description" style="width:100%" data-options="multiline:true,height:60">
        </div>
        <div class="mb-3">
            <label class="form-label">Permisos</label>
            <div id="permissions-container" style="max-height:300px;overflow-y:auto;border:1px solid #ddd;padding:10px;border-radius:4px;">
                <!-- Permisos se cargan dinamicamente -->
            </div>
        </div>
    </form>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveRole()" style="width:90px">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#dlg-role').dialog('close')" style="width:90px">Cancelar</a>
</div>
@endsection

@push('scripts')
<script>
var editingId = null;
var allPermissions = {};

function formatSystem(value) {
    if (value) {
        return '<span class="badge bg-info">Sistema</span>';
    }
    return '';
}

function loadPermissions() {
    $.get('{{ route("roles.permissions") }}', function(data) {
        allPermissions = data;
        var html = '';
        for (var module in data) {
            html += '<div class="mb-3">';
            html += '<strong class="text-primary text-capitalize">' + module + '</strong>';
            html += '<div class="ms-3">';
            data[module].forEach(function(perm) {
                html += '<div class="form-check">';
                html += '<input class="form-check-input permission-checkbox" type="checkbox" value="' + perm.id + '" id="perm-' + perm.id + '">';
                html += '<label class="form-check-label" for="perm-' + perm.id + '">' + perm.name + '</label>';
                html += '</div>';
            });
            html += '</div></div>';
        }
        $('#permissions-container').html(html);
    });
}

function newRole() {
    editingId = null;
    $('#dlg-role').dialog('open').dialog('setTitle', 'Nuevo Rol');
    $('#fm-role').form('clear');
    $('.permission-checkbox').prop('checked', false);
}

function editRole() {
    var row = $('#dg-roles').datagrid('getSelected');
    if (row) {
        if (row.is_system) {
            $.messager.alert('Aviso', 'No se puede editar un rol del sistema', 'warning');
            return;
        }
        editingId = row.id;
        $('#dlg-role').dialog('open').dialog('setTitle', 'Editar Rol');

        $.get('{{ url("/roles") }}/' + row.id, function(data) {
            $('#role-id').val(data.id);
            $('#role-name').textbox('setValue', data.name);
            $('#role-slug').textbox('setValue', data.slug);
            $('#role-description').textbox('setValue', data.description || '');

            $('.permission-checkbox').prop('checked', false);
            data.permissions.forEach(function(permId) {
                $('#perm-' + permId).prop('checked', true);
            });
        });
    } else {
        $.messager.alert('Aviso', 'Seleccione un rol para editar', 'warning');
    }
}

function saveRole() {
    if (!$('#fm-role').form('validate')) {
        return;
    }

    var permissions = [];
    $('.permission-checkbox:checked').each(function() {
        permissions.push($(this).val());
    });

    var formData = {
        name: $('#role-name').textbox('getValue'),
        slug: $('#role-slug').textbox('getValue'),
        description: $('#role-description').textbox('getValue'),
        permissions: permissions
    };

    var url = editingId ? '{{ url("/roles") }}/' + editingId : '{{ route("roles.store") }}';
    var method = editingId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function(result) {
            if (result.success) {
                $('#dlg-role').dialog('close');
                $('#dg-roles').datagrid('reload');
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

function deleteRole() {
    var row = $('#dg-roles').datagrid('getSelected');
    if (row) {
        if (row.is_system) {
            $.messager.alert('Aviso', 'No se puede eliminar un rol del sistema', 'warning');
            return;
        }
        $.messager.confirm('Confirmar', 'Esta seguro de eliminar el rol "' + row.name + '"?', function(r) {
            if (r) {
                $.ajax({
                    url: '{{ url("/roles") }}/' + row.id,
                    method: 'DELETE',
                    success: function(result) {
                        if (result.success) {
                            $('#dg-roles').datagrid('reload');
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
        $.messager.alert('Aviso', 'Seleccione un rol para eliminar', 'warning');
    }
}

$(function() {
    loadPermissions();
});
</script>
@endpush
