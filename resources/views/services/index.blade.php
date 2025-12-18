@extends('layouts.app')

@section('title', 'Servicios - Neo ERP')
@section('page-title', 'Gestión de Servicios')

@section('content')
    <div class="card">
        <div class="card-body">
            <!-- Toolbar -->
            <div id="toolbar" style="padding:5px;">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true"
                    onclick="newService()">Nuevo Servicio</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true"
                    onclick="editService()">Editar</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true"
                    onclick="deleteService()">Eliminar</a>
                <span class="ms-3">
                    <input id="searchbox" class="easyui-searchbox" style="width:250px"
                        data-options="searcher:doSearch,prompt:'Buscar servicio...'">
                </span>
            </div>

            <!-- DataGrid -->
            <table id="dg-services" class="easyui-datagrid" style="width:100%;height:700px" data-options="
                    url:'{{ route('services.data') }}',
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
                        <th data-options="field:'id',width:50,sortable:true">ID</th>
                        <th data-options="field:'code',width:100,sortable:true">Código</th>
                        <th data-options="field:'name',width:250,sortable:true">Nombre</th>
                        <th data-options="field:'category',width:120">Categoría</th>
                        <th data-options="field:'unit',width:70,align:'center'">Unidad</th>
                        <th data-options="field:'purchase_price',width:110,align:'right'">P. Compra</th>
                        <th data-options="field:'sale_price',width:110,align:'right'">P. Venta</th>
                        <th data-options="field:'is_active',width:70,align:'center',formatter:formatStatus">Estado</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <div id="dlg-service" class="easyui-dialog" style="width:700px;padding:20px" closed="true" buttons="#dlg-buttons"
        modal="true">
        <form id="fm-service" method="post">
            <input type="hidden" name="id" id="service-id">

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Código <span class="text-danger">*</span></label>
                    <input class="easyui-textbox" name="code" id="service-code" style="width:100%"
                        data-options="required:true">
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input class="easyui-textbox" name="name" id="service-name" style="width:100%"
                        data-options="required:true">
                </div>
            </div>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Categoría</label>
                    <select class="easyui-combobox" name="category_id" id="service-category" style="width:100%"
                        data-options="
                            url:'{{ route('categories.list') }}',
                            method:'get',
                            valueField:'id',
                            textField:'name',
                            panelHeight:'auto'
                        ">
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Unidad <span class="text-danger">*</span></label>
                    <input class="easyui-textbox" name="unit" id="service-unit" style="width:100%" value="SER"
                        data-options="required:true">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <input class="easyui-textbox" name="description" id="service-description" style="width:100%"
                    data-options="multiline:true,height:60">
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Precio Compra (Gs.) <span class="text-danger">*</span></label>
                    <input class="easyui-numberbox" name="purchase_price" id="service-purchase-price" style="width:100%"
                        data-options="min:0,precision:0,groupSeparator:'.',required:true">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Precio Venta (Gs.) <span class="text-danger">*</span></label>
                    <input class="easyui-numberbox" name="sale_price" id="service-sale-price" style="width:100%"
                        data-options="min:0,precision:0,groupSeparator:'.',required:true">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">IVA <span class="text-danger">*</span></label>
                    <select class="easyui-combobox" name="tax_rate" id="service-tax-rate" style="width:100%"
                        data-options="panelHeight:'auto',editable:false,required:true">
                        <option value="10">10% - General</option>
                        <option value="5">5% - Reducido</option>
                        <option value="0">Exento</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <input type="checkbox" name="is_active" id="service-active" value="1" checked>
                <label for="service-active">Servicio Activo</label>
            </div>
        </form>
    </div>
    <div id="dlg-buttons">
        <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveService()"
            style="width:90px">Guardar</a>
        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel"
            onclick="$('#dlg-service').dialog('close')" style="width:90px">Cancelar</a>
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
            $('#dg-services').datagrid('load', {
                search: value
            });
        }

        function newService() {
            editingId = null;
            $('#dlg-service').dialog('open').dialog('setTitle', 'Nuevo Servicio');
            $('#fm-service').form('clear');
            $('#service-active').prop('checked', true);
            $('#service-unit').textbox('setValue', 'SER');
            $('#service-purchase-price').numberbox('setValue', 0);
            $('#service-sale-price').numberbox('setValue', 0);
            $('#service-tax-rate').combobox('setValue', '10');
        }

        function editService() {
            var row = $('#dg-services').datagrid('getSelected');
            if (row) {
                editingId = row.id;
                $('#dlg-service').dialog('open').dialog('setTitle', 'Editar Servicio');

                $.get('{{ url("/services") }}/' + row.id, function (data) {
                    $('#service-id').val(data.id);
                    $('#service-code').textbox('setValue', data.code);
                    $('#service-name').textbox('setValue', data.name);
                    $('#service-category').combobox('setValue', data.category_id || '');
                    $('#service-unit').textbox('setValue', data.unit);
                    $('#service-description').textbox('setValue', data.description || '');
                    $('#service-purchase-price').numberbox('setValue', data.purchase_price);
                    $('#service-sale-price').numberbox('setValue', data.sale_price);
                    $('#service-tax-rate').combobox('setValue', data.tax_rate);
                    $('#service-active').prop('checked', data.is_active == 1);
                });
            } else {
                $.messager.alert('Aviso', 'Seleccione un servicio para editar', 'warning');
            }
        }

        function saveService() {
            if (!$('#fm-service').form('validate')) {
                return;
            }

            var formData = {
                code: $('#service-code').textbox('getValue'),
                name: $('#service-name').textbox('getValue'),
                category_id: $('#service-category').combobox('getValue'),
                unit: $('#service-unit').textbox('getValue'),
                description: $('#service-description').textbox('getValue'),
                purchase_price: $('#service-purchase-price').numberbox('getValue'),
                sale_price: $('#service-sale-price').numberbox('getValue'),
                tax_rate: $('#service-tax-rate').combobox('getValue'),
                is_active: $('#service-active').is(':checked') ? 1 : 0
            };

            var url = editingId ? '{{ url("/services") }}/' + editingId : '{{ route("services.store") }}';
            var method = editingId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function (result) {
                    if (result.success) {
                        $('#dlg-service').dialog('close');
                        $('#dg-services').datagrid('reload');
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
                error: function (xhr) {
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

        function deleteService() {
            var row = $('#dg-services').datagrid('getSelected');
            if (row) {
                $.messager.confirm('Confirmar', '¿Está seguro de eliminar el servicio "' + row.name + '"?', function (r) {
                    if (r) {
                        $.ajax({
                            url: '{{ url("/services") }}/' + row.id,
                            method: 'DELETE',
                            success: function (result) {
                                if (result.success) {
                                    $('#dg-services').datagrid('reload');
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
                            error: function (xhr) {
                                $.messager.alert('Error', xhr.responseJSON?.message || 'Error al eliminar', 'error');
                            }
                        });
                    }
                });
            } else {
                $.messager.alert('Aviso', 'Seleccione un servicio para eliminar', 'warning');
            }
        }
    </script>
@endpush