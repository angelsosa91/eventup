@extends('layouts.app')

@section('title', 'Nuevo Ajuste de Inventario')
@section('page-title', 'Nuevo Ajuste de Inventario')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Nuevo Ajuste - {{ $adjustmentNumber }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('inventory-adjustments.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            <button type="button" class="btn btn-primary" onclick="saveAdjustment()">
                <i class="bi bi-save"></i> Guardar
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="adjustmentForm">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="adjustment_date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select class="form-select" id="type" required>
                        <option value="in">Entrada (aumenta stock)</option>
                        <option value="out">Salida (disminuye stock)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Motivo <span class="text-danger">*</span></label>
                    <select class="form-select" id="reason" required>
                        <option value="Inventario Físico">Inventario Físico</option>
                        <option value="Merma">Merma</option>
                        <option value="Rotura">Rotura</option>
                        <option value="Vencimiento">Vencimiento</option>
                        <option value="Devolución">Devolución</option>
                        <option value="Error de Sistema">Error de Sistema</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
            </div>

            <!-- Agregar productos -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Agregar Producto</label>
                    <input id="product_search" style="width: 100%;height:38px;">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control" id="product_quantity" value="1" min="0.01" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-success w-100" onclick="addItem()">
                        <i class="bi bi-plus"></i> Agregar
                    </button>
                </div>
            </div>

            <!-- Items -->
            <table id="itemsGrid" class="easyui-datagrid" style="width:100%;height:300px"
                   data-options="singleSelect:true,fitColumns:true">
                <thead>
                    <tr>
                        <th data-options="field:'product_name',width:300">Producto</th>
                        <th data-options="field:'quantity',width:100,align:'right'">Cantidad</th>
                        <th data-options="field:'action',width:80,align:'center',formatter:formatAction">Acción</th>
                    </tr>
                </thead>
            </table>

            <div class="row mt-3">
                <div class="col-md-12">
                    <label class="form-label">Notas</label>
                    <textarea class="form-control" id="notes" rows="2"></textarea>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var items = [];
var selectedProduct = null;

$(function() {
    $('#product_search').combogrid({
        panelWidth: 600,
        idField: 'id',
        textField: 'name',
        url: '{{ route('products.list') }}',
        mode: 'remote',
        delay: 500,
        fitColumns: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('products.list') }}',
                data: { q: param.q || '' },
                dataType: 'json',
                success: function(data) { success(data); },
                error: function() { error.apply(this, arguments); }
            });
        },
        columns: [[
            {field: 'code', title: 'Código', width: 80},
            {field: 'name', title: 'Nombre', width: 250},
            {field: 'stock', title: 'Stock', width: 80, align: 'right'}
        ]],
        onSelect: function(index, row) {
            selectedProduct = row;
            $('#product_quantity').focus().select();
        }
    });

    $('#product_quantity').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault();
            addItem();
        }
    });
});

function addItem() {
    if (!selectedProduct) {
        $.messager.alert('Información', 'Seleccione un producto', 'info');
        return;
    }

    var quantity = parseFloat($('#product_quantity').val()) || 0;
    if (quantity <= 0) {
        $.messager.alert('Error', 'La cantidad debe ser mayor a 0', 'error');
        return;
    }

    var exists = items.find(function(item) {
        return item.product_id == selectedProduct.id;
    });

    if (exists) {
        exists.quantity += quantity;
    } else {
        items.push({
            product_id: selectedProduct.id,
            product_name: selectedProduct.name,
            quantity: quantity
        });
    }

    $('#itemsGrid').datagrid('loadData', items);

    selectedProduct = null;
    $('#product_search').combogrid('clear');
    $('#product_quantity').val(1);
    $('#product_search').combogrid('textbox').focus();
}

function removeItem(index) {
    items.splice(index, 1);
    $('#itemsGrid').datagrid('loadData', items);
}

function formatAction(value, row, index) {
    return '<a href="javascript:void(0)" onclick="removeItem(' + index + ')" class="text-danger"><i class="bi bi-trash"></i></a>';
}

function saveAdjustment() {
    if (items.length === 0) {
        $.messager.alert('Error', 'Agregue al menos un producto', 'error');
        return;
    }

    var data = {
        adjustment_date: $('#adjustment_date').val(),
        type: $('#type').val(),
        reason: $('#reason').val(),
        notes: $('#notes').val(),
        items: items
    };

    $.ajax({
        url: '{{ route('inventory-adjustments.store') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $.messager.alert('Éxito', response.message, 'info', function() {
                window.location.href = '{{ route('inventory-adjustments.index') }}';
            });
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Error al guardar';
            $.messager.alert('Error', msg, 'error');
        }
    });
}
</script>
@endpush
