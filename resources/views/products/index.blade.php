@extends('layouts.app')

@section('title', 'Productos - Neo ERP')
@section('page-title', 'Gestion de Productos')

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Toolbar -->
        <div id="toolbar" style="padding:5px;">
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newProduct()">Nuevo Producto</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editProduct()">Editar</a>
            <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteProduct()">Eliminar</a>
            <span class="ms-3">
                <input id="searchbox" class="easyui-searchbox" style="width:250px"
                    data-options="searcher:doSearch,prompt:'Buscar producto...'">
            </span>
        </div>

        <!-- DataGrid -->
        <table id="dg-products" class="easyui-datagrid" style="width:100%;height:700px"
            data-options="
                url:'{{ route('products.data') }}',
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
                    <th data-options="field:'stock',width:100,align:'right'">Stock</th>
                    <th data-options="field:'is_active',width:70,align:'center',formatter:formatStatus">Estado</th>
                </tr>
            </thead>
        </table>
    </div>
<div id="dlg-product" class="easyui-dialog" style="width:800px;padding:20px" closed="true" buttons="#dlg-buttons">
    <form id="fm-product" method="post">
        <input type="hidden" name="id" id="product-id">

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Código <span class="text-danger">*</span></label>
                <input class="easyui-textbox" name="code" id="product-code" style="width:100%" data-options="required:true">
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input class="easyui-textbox" name="name" id="product-name" style="width:100%" data-options="required:true">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Categoría</label>
                <select class="easyui-combobox" name="category_id" id="product-category" style="width:100%"
                    data-options="
                        url:'{{ route('categories.list') }}',
                        method:'get',
                        valueField:'id',
                        textField:'name',
                        panelHeight:'auto'
                    ">
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Unidad <span class="text-danger">*</span></label>
                <input class="easyui-textbox" name="unit" id="product-unit" style="width:100%" value="UND" data-options="required:true">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Código de Barras</label>
                <input class="easyui-textbox" name="barcode" id="product-barcode" style="width:100%">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input class="easyui-textbox" name="description" id="product-description" style="width:100%" data-options="multiline:true,height:60">
        </div>

        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Precio Compra (Gs.) <span class="text-danger">*</span></label>
                <input class="easyui-numberbox" name="purchase_price" id="product-purchase-price" style="width:100%"
                    data-options="min:0,precision:0,groupSeparator:'.',required:true">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Precio Venta (Gs.) <span class="text-danger">*</span></label>
                <input class="easyui-numberbox" name="sale_price" id="product-sale-price" style="width:100%"
                    data-options="min:0,precision:0,groupSeparator:'.',required:true">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">IVA <span class="text-danger">*</span></label>
                <select class="easyui-combobox" name="tax_rate" id="product-tax-rate" style="width:100%"
                    data-options="panelHeight:'auto',editable:false,required:true">
                    <option value="10">10% - General</option>
                    <option value="5">5% - Reducido</option>
                    <option value="0">Exento</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Stock Inicial</label>
                <input class="easyui-numberbox" name="stock" id="product-stock" style="width:100%"
                    data-options="min:0,precision:2,groupSeparator:'.'">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Stock Mínimo</label>
                <input class="easyui-numberbox" name="min_stock" id="product-min-stock" style="width:100%"
                    data-options="min:0,precision:2,groupSeparator:'.'">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Stock Máximo</label>
                <input class="easyui-numberbox" name="max_stock" id="product-max-stock" style="width:100%"
                    data-options="min:0,precision:2,groupSeparator:'.'">
            </div>
        </div>

        <div class="mb-3">
            <input type="checkbox" name="track_stock" id="product-track-stock" value="1" checked>
            <label for="product-track-stock">Controlar Stock</label>
        </div>

        <div class="mb-3">
            <input type="checkbox" name="is_active" id="product-active" value="1" checked>
            <label for="product-active">Producto Activo</label>
        </div>
    </form>
</div>
<div id="dlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton c6" iconCls="icon-ok" onclick="saveProduct()" style="width:90px">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#dlg-product').dialog('close')" style="width:90px">Cancelar</a>
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
    $('#dg-products').datagrid('load', {
        search: value
    });
}

function newProduct() {
    editingId = null;
    $('#dlg-product').dialog('open').dialog('setTitle', 'Nuevo Producto');
    $('#fm-product').form('clear');
    $('#product-active').prop('checked', true);
    $('#product-track-stock').prop('checked', true);
    $('#product-unit').textbox('setValue', 'UND');
    $('#product-purchase-price').numberbox('setValue', 0);
    $('#product-sale-price').numberbox('setValue', 0);
    $('#product-tax-rate').combobox('setValue', '10');
    $('#product-stock').numberbox('setValue', 0);
    $('#product-min-stock').numberbox('setValue', 0);
}

function editProduct() {
    var row = $('#dg-products').datagrid('getSelected');
    if (row) {
        editingId = row.id;
        $('#dlg-product').dialog('open').dialog('setTitle', 'Editar Producto');

        $.get('{{ url("/products") }}/' + row.id, function(data) {
            $('#product-id').val(data.id);
            $('#product-code').textbox('setValue', data.code);
            $('#product-name').textbox('setValue', data.name);
            $('#product-category').combobox('setValue', data.category_id || '');
            $('#product-unit').textbox('setValue', data.unit);
            $('#product-barcode').textbox('setValue', data.barcode || '');
            $('#product-description').textbox('setValue', data.description || '');
            $('#product-purchase-price').numberbox('setValue', data.purchase_price);
            $('#product-sale-price').numberbox('setValue', data.sale_price);
            $('#product-tax-rate').combobox('setValue', data.tax_rate);
            $('#product-stock').numberbox('setValue', data.stock);
            $('#product-min-stock').numberbox('setValue', data.min_stock);
            $('#product-max-stock').numberbox('setValue', data.max_stock || '');
            $('#product-track-stock').prop('checked', data.track_stock);
            $('#product-active').prop('checked', data.is_active);
        });
    } else {
        $.messager.alert('Aviso', 'Seleccione un producto para editar', 'warning');
    }
}

function saveProduct() {
    if (!$('#fm-product').form('validate')) {
        return;
    }

    var formData = {
        code: $('#product-code').textbox('getValue'),
        name: $('#product-name').textbox('getValue'),
        category_id: $('#product-category').combobox('getValue'),
        unit: $('#product-unit').textbox('getValue'),
        barcode: $('#product-barcode').textbox('getValue'),
        description: $('#product-description').textbox('getValue'),
        purchase_price: $('#product-purchase-price').numberbox('getValue'),
        sale_price: $('#product-sale-price').numberbox('getValue'),
        tax_rate: $('#product-tax-rate').combobox('getValue'),
        stock: $('#product-stock').numberbox('getValue'),
        min_stock: $('#product-min-stock').numberbox('getValue'),
        max_stock: $('#product-max-stock').numberbox('getValue'),
        track_stock: $('#product-track-stock').is(':checked') ? 1 : 0,
        is_active: $('#product-active').is(':checked') ? 1 : 0
    };

    var url = editingId ? '{{ url("/products") }}/' + editingId : '{{ route("products.store") }}';
    var method = editingId ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        method: method,
        data: formData,
        success: function(result) {
            if (result.success) {
                $('#dlg-product').dialog('close');
                $('#dg-products').datagrid('reload');
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

function deleteProduct() {
    var row = $('#dg-products').datagrid('getSelected');
    if (row) {
        $.messager.confirm('Confirmar', 'Esta seguro de eliminar el producto "' + row.name + '"?', function(r) {
            if (r) {
                $.ajax({
                    url: '{{ url("/products") }}/' + row.id,
                    method: 'DELETE',
                    success: function(result) {
                        if (result.success) {
                            $('#dg-products').datagrid('reload');
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
        $.messager.alert('Aviso', 'Seleccione un producto para eliminar', 'warning');
    }
}
</script>
@endpush
