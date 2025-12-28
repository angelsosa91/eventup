@extends('layouts.app')

@section('title', 'Nueva Compra')
@section('page-title', 'Nueva Compra')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Nueva Compra - {{ $purchaseNumber }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('purchases.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            <button type="button" class="btn btn-primary" onclick="savePurchase()">
                <i class="bi bi-save"></i> Guardar
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="purchaseForm">
            @csrf
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="purchase_date" name="purchase_date"
                           value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                    <input id="supplier_id" name="supplier_id" style="width: 100%;height:38px;">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo de Compra <span class="text-danger">*</span></label>
                    <select class="form-select" id="payment_type" name="payment_type" onchange="toggleCreditFields()">
                        <option value="cash">Contado</option>
                        <option value="credit">Crédito</option>
                    </select>
                </div>
                <div class="col-md-2" id="credit_days_field" style="display:none;">
                    <label class="form-label">Días de Crédito <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="credit_days" name="credit_days" min="1" value="30">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Nro. Factura Proveedor</label>
                    <input type="text" class="form-control" id="invoice_number" name="invoice_number">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="form-label">Forma de Pago</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Tarjeta">Tarjeta</option>
                    </select>
                </div>
                <!-- Agregar productos -->
                <div class="col-md-4">
                    <label class="form-label">Agregar Producto</label>
                    <input id="product_search" style="width: 100%;height:38px;">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control" id="product_quantity" value="1" min="0.01" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Precio Compra</label>
                    <input type="number" class="form-control" id="product_price" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-success w-100" onclick="addItem()">
                        <i class="bi bi-plus"></i> Agregar
                    </button>
                </div>
            </div>

            <!-- Items de la compra -->
            <table id="itemsGrid" class="easyui-datagrid" style="width:100%;height:300px"
                   data-options="
                       singleSelect: true,
                       fitColumns: true,
                       showFooter: true
                   ">
                <thead>
                    <tr>
                        <th data-options="field:'product_name',width:250">Producto</th>
                        <th data-options="field:'quantity',width:80,align:'right'">Cantidad</th>
                        <th data-options="field:'unit_price',width:100,align:'right',formatter:formatNumber">Precio</th>
                        <th data-options="field:'tax_rate',width:80,align:'center',formatter:formatTaxRate">IVA</th>
                        <th data-options="field:'subtotal',width:120,align:'right',formatter:formatNumber">Subtotal</th>
                        <th data-options="field:'action',width:80,align:'center',formatter:formatAction">Acción</th>
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
                            <td>Total Exento:</td>
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
                        <tr class="table-primary">
                            <td><strong>TOTAL:</strong></td>
                            <td class="text-end"><strong id="total_general">0</strong></td>
                        </tr>
                    </table>
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
    // ComboGrid de proveedores
    $('#supplier_id').combogrid({
        panelWidth: 500,
        idField: 'id',
        textField: 'name',
        url: '{{ route('suppliers.list') }}',
        mode: 'remote',
        delay: 500,
        fitColumns: true,
        required: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('suppliers.list') }}',
                data: { q: param.q || '' },
                dataType: 'json',
                success: function(data) {
                    success(data);
                },
                error: function() {
                    error.apply(this, arguments);
                }
            });
        },
        columns: [[
            {field: 'name', title: 'Nombre', width: 200},
            {field: 'ruc', title: 'RUC', width: 100},
            {field: 'phone', title: 'Teléfono', width: 100}
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
        fitColumns: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('products.list') }}',
                data: { q: param.q || '' },
                dataType: 'json',
                success: function(data) {
                    success(data);
                },
                error: function() {
                    error.apply(this, arguments);
                }
            });
        },
        columns: [[
            {field: 'code', title: 'Código', width: 80},
            {field: 'name', title: 'Nombre', width: 200},
            {field: 'stock', title: 'Stock', width: 80, align: 'right'},
            {field: 'cost_price', title: 'Costo', width: 100, align: 'right'},
            {field: 'tax_rate', title: 'IVA', width: 60, align: 'center', formatter: function(value) {
                return value + '%';
            }}
        ]],
        onSelect: function(index, row) {
            selectedProduct = row;
            $('#product_price').val(row.cost_price || 0);
            $('#product_quantity').focus().select();
        }
    });

    // Enter en cantidad agrega el item
    $('#product_quantity').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault();
            addItem();
        }
    });
});

function toggleCreditFields() {
    var paymentType = $('#payment_type').val();
    if (paymentType === 'credit') {
        $('#credit_days_field').show();
    } else {
        $('#credit_days_field').hide();
    }
}

function addItem() {
    if (!selectedProduct) {
        $.messager.alert('Información', 'Seleccione un producto', 'info');
        return;
    }

    var quantity = parseFloat($('#product_quantity').val()) || 0;
    var price = parseFloat($('#product_price').val()) || 0;

    if (quantity <= 0) {
        $.messager.alert('Error', 'La cantidad debe ser mayor a 0', 'error');
        return;
    }

    if (price <= 0) {
        $.messager.alert('Error', 'El precio debe ser mayor a 0', 'error');
        return;
    }

    // Verificar si el producto ya está en la lista
    var exists = items.find(function(item) {
        return item.product_id == selectedProduct.id;
    });

    if (exists) {
        exists.quantity += quantity;
        exists.subtotal = exists.quantity * exists.unit_price;
    } else {
        var subtotal = quantity * price;
        items.push({
            product_id: selectedProduct.id,
            product_name: selectedProduct.name,
            quantity: quantity,
            unit_price: price,
            tax_rate: selectedProduct.tax_rate,
            subtotal: subtotal
        });
    }

    updateGrid();

    // Limpiar selección
    selectedProduct = null;
    $('#product_search').combogrid('clear');
    $('#product_price').val('');
    $('#product_quantity').val(1);
    $('#product_search').combogrid('textbox').focus();
}

function removeItem(index) {
    items.splice(index, 1);
    updateGrid();
}

function updateGrid() {
    $('#itemsGrid').datagrid('loadData', items);
    calculateTotals();
}

function calculateTotals() {
    var totalExento = 0;
    var totalGravado5 = 0;
    var totalIva5 = 0;
    var totalGravado10 = 0;
    var totalIva10 = 0;

    items.forEach(function(item) {
        var subtotal = item.quantity * item.unit_price;

        switch(item.tax_rate) {
            case 0:
                totalExento += subtotal;
                break;
            case 5:
                totalGravado5 += subtotal;
                // IVA incluido: subtotal * tasa / (100 + tasa)
                totalIva5 += subtotal * 5 / 105;
                break;
            case 10:
                totalGravado10 += subtotal;
                totalIva10 += subtotal * 10 / 110;
                break;
        }
    });

    var total = totalExento + totalGravado5 + totalGravado10;

    $('#total_exento').text(formatCurrency(totalExento));
    $('#total_gravado_5').text(formatCurrency(totalGravado5));
    $('#total_iva_5').text(formatCurrency(totalIva5));
    $('#total_gravado_10').text(formatCurrency(totalGravado10));
    $('#total_iva_10').text(formatCurrency(totalIva10));
    $('#total_general').text(formatCurrency(total));
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function formatNumber(value) {
    if (value == null) return '';
    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function formatTaxRate(value) {
    return value + '%';
}

function formatAction(value, row, index) {
    return '<a href="javascript:void(0)" onclick="removeItem(' + index + ')" class="text-danger"><i class="bi bi-trash"></i></a>';
}

function savePurchase() {
    var supplierId = $('#supplier_id').combogrid('getValue');
    if (!supplierId) {
        $.messager.alert('Error', 'Seleccione un proveedor', 'error');
        return;
    }

    if (items.length === 0) {
        $.messager.alert('Error', 'Agregue al menos un producto', 'error');
        return;
    }

    var data = {
        supplier_id: supplierId,
        purchase_date: $('#purchase_date').val(),
        payment_type: $('#payment_type').val(),
        credit_days: $('#credit_days').val(),
        invoice_number: $('#invoice_number').val(),
        payment_method: $('#payment_method').val(),
        notes: $('#notes').val(),
        items: items
    };

    $.ajax({
        url: '{{ route('purchases.store') }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $.messager.alert('Éxito', response.message, 'info', function() {
                window.location.href = '{{ route('purchases.index') }}';
            });
        },
        error: function(xhr) {
            var errors = xhr.responseJSON.errors;
            var message = '';
            for (var key in errors) {
                message += errors[key].join('<br>') + '<br>';
            }
            $.messager.alert('Error', message, 'error');
        }
    });
}
</script>
@endpush
