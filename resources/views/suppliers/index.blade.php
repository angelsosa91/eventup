@extends('layouts.app')

@section('title', 'Proveedores - Neo ERP')
@section('page-title', 'Gestion de Proveedores')

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Toolbar -->
        <div id="toolbar" style="padding:5px;">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newSupplier()">Nuevo Proveedor</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editSupplier()">Editar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteSupplier()">Eliminar</a>
            <span class="ms-3">
                <input id="searchbox" class="easyui-searchbox" style="width:250px"
                    data-options="searcher:doSearch,prompt:'Buscar proveedor...'">
            </span>
        </div>

        <!-- DataGrid -->
        <table id="dg-suppliers" class="easyui-datagrid" style="width:100%;height:700px"
            data-options="
                url:'{{ route('suppliers.data') }}',
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
                    <th data-options="field:'contact_person',width:150">Contacto</th>
                    <th data-options="field:'payment_days',width:100,align:'center'">Dias Pago</th>
                    <th data-options="field:'is_active',width:80,align:'center',formatter:formatStatus">Estado</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<!-- Dialog para crear/editar proveedor -->
<div id="dlg-supplier" class="easyui-dialog" style="width:700px;padding:20px" closed="true" buttons="#dlg-buttons">
    <form id="fm-supplier" method="post">
        <input type="hidden" name="id" id="supplier-id">

        <div class="row">
            <div class="col-md-8 mb-3">
                <label class="form-label">Nombre / Razon Social <span class="text-danger">*</span></label>
                <input class="easyui-textbox" name="name" id="supplier-name" style="width:100%" data-options="required:true">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">RUC / CI</label>
                <input class="easyui-textbox" name="ruc" id="supplier-ruc" style="width:100%">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input class="easyui-textbox" name="email" id="supplier-email" style="width:100%" data-options="validType:'email'">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Telefono</label>
                <input class="easyui-textbox" name="phone" id="supplier-phone" style="width:100%">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Celular</label>
                <input class="easyui-textbox" name="mobile" id="supplier-mobile" style="width:100%">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Direccion</label>
            <input class="easyui-textbox" name="address" id="supplier-address" style="width:100%" data-options="multiline:true,height:60">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Ciudad</label>
                <input class="easyui-textbox" name="city" id="supplier-city" style="width:100%">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Pais</label>
                <input class="easyui-textbox" name="country" id="supplier-country" style="width:100%" value="Paraguay">
            </div>
        </div>


        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Persona de Contacto</label>
                <input class="easyui-textbox" name="contact_person" id="supplier-contact" style="width:100%">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Dias de Pago</label>
                <input class="easyui-numberbox" name="payment_days" id="supplier-payment-days" style="width:100%"
                    data-options="min:0,precision:0">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Banco</label>
                <input class="easyui-textbox" name="bank_name" id="supplier-bank-name" style="width:100%">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Cuenta Bancaria</label>
                <input class="easyui-textbox" name="bank_account" id="supplier-bank-account" style="width:100%">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notas</label>
            <input class="easyui-textbox" name="notes" id="supplier-notes" style="width:100%" data-options="multiline:true,height:60">
        </div>

        <div class="mb-3">
            <input type="checkbox" name="is_active" id="supplier-active" value="1" checked>
            <label for="supplier-active">Proveedor Activo</label>
        </div>
    </form>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveSupplier()" style="width:90px">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#dlg-supplier').dialog('close')" style="width:90px">Cancelar</a>
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
    $('#dg-suppliers').datagrid('load', {
        search: value
    });
}

function newSupplier() {
    editingId = null;
    $('#dlg-supplier').dialog('open').dialog('setTitle', 'Nuevo Proveedor');
    $('#fm-supplier').form('clear');
    $('#supplier-active').prop('checked', true);
    $('#supplier-country').textbox('setValue', 'Paraguay');
    $('#supplier-payment-days').numberbox('setValue', 0);
}

function editSupplier() {
    var row = $('#dg-suppliers').datagrid('getSelected');
    if (row) {
        editingId = row.id;
        $('#dlg-supplier').dialog('open').dialog('setTitle', 'Editar Proveedor');

        $.get('{{ url("/suppliers") }}/' + row.id, function(data) {
            $('#supplier-id').val(data.id);
            $('#supplier-name').textbox('setValue', data.name);
            $('#supplier-ruc').textbox('setValue', data.ruc || '');
            $('#supplier-email').textbox('setValue', data.email || '');
            $('#supplier-phone').textbox('setValue', data.phone || '');
            $('#supplier-mobile').textbox('setValue', data.mobile || '');
            $('#supplier-address').textbox('setValue', data.address || '');
            $('#supplier-city').textbox('setValue', data.city || '');
            $('#supplier-country').textbox('setValue', data.country || 'Paraguay');
            $('#supplier-contact').textbox('setValue', data.contact_person || '');
            $('#supplier-bank-name').textbox('setValue', data.bank_name || '');
            $('#supplier-bank-account').textbox('setValue', data.bank_account || '');
            $('#supplier-payment-days').numberbox('setValue', data.payment_days || 0);
            $('#supplier-notes').textbox('setValue', data.notes || '');
            $('#supplier-active').prop('checked', data.is_active);
        });
    } else {
        $.messager.alert('Aviso', 'Seleccione un proveedor para editar', 'warning');
    }
}

function saveSupplier() {
    if (!$('#fm-supplier').form('validate')) {
        return;
    }

    var formData = {
        name: $('#supplier-name').textbox('getValue'),
        ruc: $('#supplier-ruc').textbox('getValue'),
        email: $('#supplier-email').textbox('getValue'),
        phone: $('#supplier-phone').textbox('getValue'),
        mobile: $('#supplier-mobile').textbox('getValue'),
        address: $('#supplier-address').textbox('getValue'),
        city: $('#supplier-city').textbox('getValue'),
        country: $('#supplier-country').textbox('getValue'),
        contact_person: $('#supplier-contact').textbox('getValue'),
        payment_days: $('#supplier-payment-days').numberbox('getValue'),
        bank_name: $('#supplier-bank-name').textbox('getValue'),
        bank_account: $('#supplier-bank-account').textbox('getValue'),
        notes: $('#supplier-notes').textbox('getValue'),
        is_active: $('#supplier-active').is(':checked') ? 1 : 0
    };

    var url = editingId ? '{{ url("/suppliers") }}/' + editingId : '{{ route("suppliers.store") }}';
    var method = editingId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function(result) {
            if (result.success) {
                $('#dlg-supplier').dialog('close');
                $('#dg-suppliers').datagrid('reload');
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

function deleteSupplier() {
    var row = $('#dg-suppliers').datagrid('getSelected');
    if (row) {
        $.messager.confirm('Confirmar', 'Esta seguro de eliminar el proveedor "' + row.name + '"?', function(r) {
            if (r) {
                $.ajax({
                    url: '{{ url("/suppliers") }}/' + row.id,
                    method: 'DELETE',
                    success: function(result) {
                        if (result.success) {
                            $('#dg-suppliers').datagrid('reload');
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
        $.messager.alert('Aviso', 'Seleccione un proveedor para eliminar', 'warning');
    }
}
</script>
@endpush
