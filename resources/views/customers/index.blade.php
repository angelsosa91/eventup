@extends('layouts.app')

@section('title', 'Clientes - Neo ERP')
@section('page-title', 'Gestion de Clientes')

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Toolbar -->
        <div id="toolbar" style="padding:5px;">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newCustomer()">Nuevo Cliente</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editCustomer()">Editar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteCustomer()">Eliminar</a>
            <span class="ms-3">
                <input id="searchbox" class="easyui-searchbox" style="width:250px"
                    data-options="searcher:doSearch,prompt:'Buscar cliente...'">
            </span>
        </div>

        <!-- DataGrid -->
        <table id="dg-customers" class="easyui-datagrid" style="width:100%;height:700px"
            data-options="
                url:'{{ route('customers.data') }}',
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
                    <th data-options="field:'name',width:200,sortable:true">Nombre</th>
                    <th data-options="field:'ruc',width:120,sortable:true">RUC</th>
                    <th data-options="field:'email',width:180">Email</th>
                    <th data-options="field:'phone',width:120">Telefono</th>
                    <th data-options="field:'city',width:120">Ciudad</th>
                    <th data-options="field:'credit_limit',width:120,align:'right'">Limite Credito</th>
                    <th data-options="field:'is_active',width:80,align:'center',formatter:formatStatus">Estado</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Dialog para crear/editar cliente -->
<div id="dlg-customer" class="easyui-dialog" style="width:700px;padding:20px" closed="true" buttons="#dlg-buttons">
    <form id="fm-customer" method="post">
        <input type="hidden" name="id" id="customer-id">

        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Nombre / Razon Social <span class="text-danger">*</span></label>
                <input class="easyui-textbox" name="name" id="customer-name" style="width:100%" data-options="required:true">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">RUC / CI</label>
                <input class="easyui-textbox" name="ruc" id="customer-ruc" style="width:100%">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input class="easyui-textbox" name="email" id="customer-email" style="width:100%" data-options="validType:'email'">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Telefono</label>
                <input class="easyui-textbox" name="phone" id="customer-phone" style="width:100%">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Celular</label>
                <input class="easyui-textbox" name="mobile" id="customer-mobile" style="width:100%">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Direccion</label>
            <input class="easyui-textbox" name="address" id="customer-address" style="width:100%" data-options="multiline:true,height:60">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Ciudad</label>
                <input class="easyui-textbox" name="city" id="customer-city" style="width:100%">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Pais</label>
                <input class="easyui-textbox" name="country" id="customer-country" style="width:100%" value="Paraguay">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Limite de Credito (Gs.)</label>
                <input class="easyui-numberbox" name="credit_limit" id="customer-credit-limit" style="width:100%"
                    data-options="min:0,precision:0,groupSeparator:'.'">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Dias de Credito</label>
                <input class="easyui-numberbox" name="credit_days" id="customer-credit-days" style="width:100%"
                    data-options="min:0,precision:0">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notas</label>
            <input class="easyui-textbox" name="notes" id="customer-notes" style="width:100%" data-options="multiline:true,height:60">
        </div>

        <div class="mb-3">
            <input type="checkbox" name="is_active" id="customer-active" value="1" checked>
            <label for="customer-active">Cliente Activo</label>
        </div>
    </form>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveCustomer()" style="width:90px">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#dlg-customer').dialog('close')" style="width:90px">Cancelar</a>
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
    $('#dg-customers').datagrid('load', {
        search: value
    });
}

function newCustomer() {
    editingId = null;
    $('#dlg-customer').dialog('open').dialog('setTitle', 'Nuevo Cliente');
    $('#fm-customer').form('clear');
    $('#customer-active').prop('checked', true);
    $('#customer-country').textbox('setValue', 'Paraguay');
    $('#customer-credit-limit').numberbox('setValue', 0);
    $('#customer-credit-days').numberbox('setValue', 0);
}

function editCustomer() {
    var row = $('#dg-customers').datagrid('getSelected');
    if (row) {
        editingId = row.id;
        $('#dlg-customer').dialog('open').dialog('setTitle', 'Editar Cliente');

        $.get('{{ url("/customers") }}/' + row.id, function(data) {
            $('#customer-id').val(data.id);
            $('#customer-name').textbox('setValue', data.name);
            $('#customer-ruc').textbox('setValue', data.ruc || '');
            $('#customer-email').textbox('setValue', data.email || '');
            $('#customer-phone').textbox('setValue', data.phone || '');
            $('#customer-mobile').textbox('setValue', data.mobile || '');
            $('#customer-address').textbox('setValue', data.address || '');
            $('#customer-city').textbox('setValue', data.city || '');
            $('#customer-country').textbox('setValue', data.country || 'Paraguay');
            $('#customer-credit-limit').numberbox('setValue', data.credit_limit || 0);
            $('#customer-credit-days').numberbox('setValue', data.credit_days || 0);
            $('#customer-notes').textbox('setValue', data.notes || '');
            $('#customer-active').prop('checked', data.is_active);
        });
    } else {
        $.messager.alert('Aviso', 'Seleccione un cliente para editar', 'warning');
    }
}

function saveCustomer() {
    if (!$('#fm-customer').form('validate')) {
        return;
    }

    var formData = {
        name: $('#customer-name').textbox('getValue'),
        ruc: $('#customer-ruc').textbox('getValue'),
        email: $('#customer-email').textbox('getValue'),
        phone: $('#customer-phone').textbox('getValue'),
        mobile: $('#customer-mobile').textbox('getValue'),
        address: $('#customer-address').textbox('getValue'),
        city: $('#customer-city').textbox('getValue'),
        country: $('#customer-country').textbox('getValue'),
        credit_limit: $('#customer-credit-limit').numberbox('getValue'),
        credit_days: $('#customer-credit-days').numberbox('getValue'),
        notes: $('#customer-notes').textbox('getValue'),
        is_active: $('#customer-active').is(':checked') ? 1 : 0
    };

    var url = editingId ? '{{ url("/customers") }}/' + editingId : '{{ route("customers.store") }}';
    var method = editingId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function(result) {
            if (result.success) {
                $('#dlg-customer').dialog('close');
                $('#dg-customers').datagrid('reload');
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

function deleteCustomer() {
    var row = $('#dg-customers').datagrid('getSelected');
    if (row) {
        $.messager.confirm('Confirmar', 'Esta seguro de eliminar el cliente "' + row.name + '"?', function(r) {
            if (r) {
                $.ajax({
                    url: '{{ url("/customers") }}/' + row.id,
                    method: 'DELETE',
                    success: function(result) {
                        if (result.success) {
                            $('#dg-customers').datagrid('reload');
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
        $.messager.alert('Aviso', 'Seleccione un cliente para eliminar', 'warning');
    }
}
</script>
@endpush
