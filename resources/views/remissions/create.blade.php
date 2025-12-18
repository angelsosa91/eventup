@extends('layouts.app')

@section('title', 'Nueva Remisión')
@section('page-title', 'Nueva Remisión')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Nueva Remisión</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('remissions.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            <button type="button" class="btn btn-primary" onclick="saveRemission()">
                <i class="bi bi-save"></i> Guardar
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="remissionForm">
            @csrf
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date"
                           value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cliente <span class="text-danger">*</span></label>
                    <input id="customer_id" name="customer_id" style="width: 100%;height:38px;" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Motivo <span class="text-danger">*</span></label>
                    <select class="form-select" id="reason" name="reason" required>
                        <option value="delivery">Entrega</option>
                        <option value="transfer">Traslado entre sucursales</option>
                        <option value="consignment">Consignación</option>
                        <option value="demo">Demostración</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Dirección de Entrega</label>
                    <input type="text" class="form-control" id="delivery_address" name="delivery_address">
                </div>
            </div>

            <!-- Agregar productos -->
            <div class="row mb-3">
                <div class="col-md-5">
                    <label class="form-label">Agregar Producto</label>
                    <input id="product_search" style="width: 100%;height:38px;">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control" id="product_quantity" value="1" min="0.01" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Stock Disponible</label>
                    <input type="text" class="form-control" id="product_stock" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-success w-100" onclick="addItem()">
                        <i class="bi bi-plus"></i> Agregar
                    </button>
                </div>
            </div>

            <!-- Items de la remisión -->
            <table id="itemsGrid" class="easyui-datagrid" style="width:100%;height:350px"
                   data-options="
                       singleSelect: true,
                       fitColumns: true
                   ">
                <thead>
                    <tr>
                        <th data-options="field:'product_name',width:300">Producto</th>
                        <th data-options="field:'quantity',width:100,align:'right'">Cantidad</th>
                        <th data-options="field:'stock',width:100,align:'right'">Stock</th>
                        <th data-options="field:'notes',width:200">Notas</th>
                        <th data-options="field:'action',width:80,align:'center',formatter:formatAction">Acción</th>
                    </tr>
                </thead>
            </table>

            <!-- Notas -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Notas / Observaciones</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
var items = [];
var selectedProduct = null;

$(function() {
    // ComboGrid de clientes
    $('#customer_id').combogrid({
        panelWidth: 500,
        idField: 'id',
        textField: 'name',
        url: '{{ route('customers.list') }}',
        mode: 'remote',
        delay: 500,
        columns: [[
            {field:'name',title:'Nombre',width:250},
            {field:'tax_id',title:'RUC/CI',width:120},
            {field:'phone',title:'Teléfono',width:100}
        ]]
    });

    // ComboGrid de productos
    $('#product_search').combogrid({
        panelWidth: 600,
        idField: 'id',
        textField: 'name',
        url: '{{ route('products.list') }}',
        mode: 'remote',
        delay: 500,
        columns: [[
            {field:'name',title:'Producto',width:300},
            {field:'code',title:'Código',width:100},
            {field:'stock',title:'Stock',width:80,align:'right'},
            {field:'price',title:'Precio',width:100,align:'right'}
        ]],
        onSelect: function(index, row) {
            selectedProduct = row;
            $('#product_stock').val(row.stock);
            $('#product_quantity').focus().select();
        }
    });

    // Enter en cantidad para agregar
    $('#product_quantity').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            addItem();
        }
    });
});

function addItem() {
    if (!selectedProduct) {
        $.messager.alert('Error', 'Debe seleccionar un producto', 'error');
        return;
    }

    var quantity = parseFloat($('#product_quantity').val());
    if (isNaN(quantity) || quantity <= 0) {
        $.messager.alert('Error', 'La cantidad debe ser mayor a 0', 'error');
        return;
    }

    if (quantity > selectedProduct.stock) {
        $.messager.alert('Advertencia', 'La cantidad excede el stock disponible (' + selectedProduct.stock + ')', 'warning');
        return;
    }

    // Verificar si el producto ya existe
    var exists = items.find(item => item.product_id === selectedProduct.id);
    if (exists) {
        $.messager.alert('Error', 'El producto ya fue agregado', 'error');
        return;
    }

    var item = {
        product_id: selectedProduct.id,
        product_name: selectedProduct.name,
        quantity: quantity,
        stock: selectedProduct.stock,
        notes: ''
    };

    items.push(item);
    refreshGrid();

    // Limpiar
    $('#product_search').combogrid('clear');
    $('#product_quantity').val(1);
    $('#product_stock').val('');
    selectedProduct = null;
}

function removeItem(index) {
    $.messager.confirm('Confirmar', '¿Desea eliminar este item?', function(r) {
        if (r) {
            items.splice(index, 1);
            refreshGrid();
        }
    });
}

function refreshGrid() {
    $('#itemsGrid').datagrid('loadData', items);
}

function formatAction(value, row, index) {
    return '<a href="javascript:void(0)" onclick="removeItem(' + index + ')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></a>';
}

function saveRemission() {
    if (items.length === 0) {
        $.messager.alert('Error', 'Debe agregar al menos un producto', 'error');
        return;
    }

    var customerId = $('#customer_id').combogrid('getValue');
    if (!customerId) {
        $.messager.alert('Error', 'Debe seleccionar un cliente', 'error');
        return;
    }

    var formData = {
        customer_id: customerId,
        date: $('#date').val(),
        delivery_address: $('#delivery_address').val(),
        reason: $('#reason').val(),
        notes: $('#notes').val(),
        items: items.map(function(item) {
            return {
                product_id: item.product_id,
                quantity: item.quantity,
                notes: item.notes
            };
        })
    };

    $.ajax({
        url: '{{ route('remissions.store') }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: 'application/json',
        data: JSON.stringify(formData),
        success: function(response) {
            $.messager.show({
                title: 'Éxito',
                msg: response.message,
                timeout: 3000,
                showType: 'slide'
            });
            setTimeout(function() {
                window.location.href = '{{ route('remissions.index') }}';
            }, 1500);
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            if (errors) {
                var errorMsg = Object.values(errors).flat().join('<br>');
                $.messager.alert('Error de Validación', errorMsg, 'error');
            } else {
                var error = xhr.responseJSON?.message || 'Error al guardar la remisión';
                $.messager.alert('Error', error, 'error');
            }
        }
    });
}
</script>
@endsection
