@extends('layouts.app')

@section('title', 'Nueva Nota de Crédito')
@section('page-title', 'Nueva Nota de Crédito')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Nueva Nota de Crédito</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('credit-notes.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            <button type="button" class="btn btn-primary" onclick="saveCreditNote()">
                <i class="bi bi-save"></i> Guardar
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="creditNoteForm">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="date" name="date"
                           value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Venta de Referencia <span class="text-danger">*</span></label>
                    <input id="sale_search" style="width: 100%;height:38px;" placeholder="Buscar venta...">
                    <input type="hidden" id="sale_id" name="sale_id">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Motivo <span class="text-danger">*</span></label>
                    <select class="form-select" id="reason" name="reason" required>
                        <option value="return">Devolución de mercadería</option>
                        <option value="discount">Descuento</option>
                        <option value="error">Error en facturación</option>
                        <option value="cancellation">Anulación</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select class="form-select" id="type" name="type" required onchange="updateItemsEnabled()">
                        <option value="total">Total</option>
                        <option value="partial">Parcial</option>
                    </select>
                </div>
            </div>

            <!-- Información de la venta -->
            <div id="sale_info" style="display:none;" class="alert alert-info mb-3">
                <h6>Información de la Venta</h6>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Número:</strong> <span id="info_sale_number"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha:</strong> <span id="info_sale_date"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Cliente:</strong> <span id="info_customer_name"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Total:</strong> <span id="info_sale_total"></span>
                    </div>
                </div>
            </div>

            <!-- Items de la venta -->
            <h6>Items a Devolver/Anular</h6>
            <table id="itemsGrid" class="easyui-datagrid" style="width:100%;height:300px"
                   data-options="
                       singleSelect: false,
                       fitColumns: true,
                       showFooter: true
                   ">
                <thead>
                    <tr>
                        <th data-options="field:'ck',checkbox:true"></th>
                        <th data-options="field:'product_name',width:250">Producto</th>
                        <th data-options="field:'original_quantity',width:100,align:'right'">Cant. Original</th>
                        <th data-options="field:'quantity',width:100,align:'right',editor:{type:'numberbox',options:{precision:2,min:0.01}}">Cantidad NC</th>
                        <th data-options="field:'price',width:120,align:'right',formatter:formatNumber">Precio</th>
                        <th data-options="field:'iva_type',width:80,align:'center',formatter:formatIvaType">IVA</th>
                        <th data-options="field:'subtotal',width:120,align:'right',formatter:formatNumber">Subtotal</th>
                    </tr>
                </thead>
            </table>

            <!-- Totales -->
            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr>
                            <td>Total Exento (0%):</td>
                            <td class="text-end" id="total_exento">0</td>
                        </tr>
                        <tr>
                            <td>Gravado 5%:</td>
                            <td class="text-end" id="total_gravado_5">0</td>
                        </tr>
                        <tr>
                            <td>IVA 5%:</td>
                            <td class="text-end" id="total_iva_5">0</td>
                        </tr>
                        <tr>
                            <td>Gravado 10%:</td>
                            <td class="text-end" id="total_gravado_10">0</td>
                        </tr>
                        <tr>
                            <td>IVA 10%:</td>
                            <td class="text-end" id="total_iva_10">0</td>
                        </tr>
                        <tr class="table-danger">
                            <td><strong>TOTAL A DEVOLVER:</strong></td>
                            <td class="text-end"><strong id="total_general">0</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
var saleItems = [];
var selectedSale = null;

$(function() {
    // Combogrid para buscar ventas confirmadas
    $('#sale_search').combogrid({
        panelWidth: 600,
        idField: 'id',
        textField: 'sale_number',
        url: '{{ route('sales.list') }}?status=confirmed',
        mode: 'remote',
        delay: 500,
        columns: [[
            {field:'sale_number',title:'Número',width:120},
            {field:'sale_date',title:'Fecha',width:100},
            {field:'customer_name',title:'Cliente',width:200},
            {field:'total',title:'Total',width:100,align:'right'}
        ]],
        onSelect: function(index, row) {
            loadSaleDetails(row.id);
        }
    });

    // Inicializar DataGrid
    $('#itemsGrid').datagrid({
        onClickCell: function(index, field, value) {
            if (field === 'quantity' && $('#type').val() === 'partial') {
                $('#itemsGrid').datagrid('beginEdit', index);
            }
        },
        onEndEdit: function(index, row) {
            // Validar que la cantidad no exceda la original
            if (parseFloat(row.quantity) > parseFloat(row.original_quantity)) {
                $.messager.alert('Error', 'La cantidad no puede exceder la cantidad original', 'error');
                row.quantity = row.original_quantity;
            }
            calculateTotals();
        }
    });

    @if(isset($sale))
        // Si se pasó un ID de venta, cargarla automáticamente
        $('#sale_search').combogrid('setValue', {{ $sale->id }});
        loadSaleDetails({{ $sale->id }});
    @endif
});

function loadSaleDetails(saleId) {
    $.ajax({
        url: '{{ url('credit-notes/sale-details') }}/' + saleId,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                selectedSale = response.sale;
                $('#sale_id').val(selectedSale.id);

                // Mostrar información de la venta
                $('#info_sale_number').text(selectedSale.sale_number);
                $('#info_sale_date').text(selectedSale.sale_date);
                $('#info_customer_name').text(selectedSale.customer_name);
                $('#info_sale_total').text(formatCurrency(selectedSale.total));
                $('#sale_info').show();

                // Cargar items
                saleItems = selectedSale.items.map(function(item) {
                    return {
                        product_id: item.product_id,
                        product_name: item.product_name,
                        original_quantity: item.quantity,
                        quantity: item.quantity,
                        price: item.price,
                        iva_type: item.iva_type,
                        subtotal: item.subtotal
                    };
                });

                $('#itemsGrid').datagrid('loadData', saleItems);
                $('#itemsGrid').datagrid('selectAll');
                calculateTotals();
            }
        },
        error: function(xhr) {
            var error = xhr.responseJSON?.message || 'Error al cargar los detalles de la venta';
            $.messager.alert('Error', error, 'error');
        }
    });
}

function updateItemsEnabled() {
    var type = $('#type').val();
    if (type === 'total') {
        // Seleccionar todos los items
        $('#itemsGrid').datagrid('selectAll');
        // Restaurar cantidades originales
        var rows = $('#itemsGrid').datagrid('getRows');
        rows.forEach(function(row, index) {
            row.quantity = row.original_quantity;
            $('#itemsGrid').datagrid('refreshRow', index);
        });
    }
    calculateTotals();
}

function calculateTotals() {
    var type = $('#type').val();
    var selectedRows = type === 'total' ?
        $('#itemsGrid').datagrid('getRows') :
        $('#itemsGrid').datagrid('getSelections');

    var total_0 = 0;
    var total_5 = 0;
    var iva_5 = 0;
    var total_10 = 0;
    var iva_10 = 0;

    selectedRows.forEach(function(row) {
        var quantity = parseFloat(row.quantity) || 0;
        var price = parseFloat(row.price) || 0;
        var subtotal = quantity * price;

        switch(row.iva_type) {
            case '0':
                total_0 += subtotal;
                break;
            case '5':
                total_5 += subtotal;
                iva_5 += subtotal * 0.05;
                break;
            case '10':
                total_10 += subtotal;
                iva_10 += subtotal * 0.10;
                break;
        }
    });

    var total = total_0 + total_5 + total_10;

    $('#total_exento').text(formatCurrency(total_0));
    $('#total_gravado_5').text(formatCurrency(total_5));
    $('#total_iva_5').text(formatCurrency(iva_5));
    $('#total_gravado_10').text(formatCurrency(total_10));
    $('#total_iva_10').text(formatCurrency(iva_10));
    $('#total_general').text(formatCurrency(total));
}

function saveCreditNote() {
    // Validar sale_id
    if (!$('#sale_id').val()) {
        $.messager.alert('Error', 'Debe seleccionar una venta de referencia', 'error');
        return;
    }

    var type = $('#type').val();
    var selectedItems = type === 'total' ?
        $('#itemsGrid').datagrid('getRows') :
        $('#itemsGrid').datagrid('getSelections');

    if (selectedItems.length === 0) {
        $.messager.alert('Error', 'Debe seleccionar al menos un item', 'error');
        return;
    }

    // Preparar datos
    var items = selectedItems.map(function(item) {
        return {
            product_id: item.product_id,
            quantity: parseFloat(item.quantity),
            price: parseFloat(item.price),
            iva_type: item.iva_type
        };
    });

    var formData = {
        sale_id: $('#sale_id').val(),
        date: $('#date').val(),
        reason: $('#reason').val(),
        type: type,
        notes: $('#notes').val(),
        items: items
    };

    $.ajax({
        url: '{{ route('credit-notes.store') }}',
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
                window.location.href = '{{ route('credit-notes.index') }}';
            }, 1500);
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            if (errors) {
                var errorMsg = Object.values(errors).flat().join('<br>');
                $.messager.alert('Error de Validación', errorMsg, 'error');
            } else {
                var error = xhr.responseJSON?.message || 'Error al guardar la nota de crédito';
                $.messager.alert('Error', error, 'error');
            }
        }
    });
}

function formatNumber(value) {
    if (!value) return '0';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 2});
}

function formatIvaType(value) {
    return value + '%';
}

function formatCurrency(value) {
    return parseFloat(value || 0).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}
</script>
@endsection
