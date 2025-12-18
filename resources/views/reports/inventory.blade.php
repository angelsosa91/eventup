@extends('layouts.app')

@section('title', 'Reporte de Inventario')
@section('page-title', 'Reporte de Inventario')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <input id="category_id" style="width: 100%;height:38px;">
            </div>
            <div class="col-md-3">
                <label class="form-label">Filtro de Stock</label>
                <select class="form-select" id="stock_filter">
                    <option value="" {{ $stockFilter == '' ? 'selected' : '' }}>Todos</option>
                    <option value="low" {{ $stockFilter == 'low' ? 'selected' : '' }}>Stock Bajo</option>
                    <option value="zero" {{ $stockFilter == 'zero' ? 'selected' : '' }}>Sin Stock</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="button" class="btn btn-primary" onclick="loadReport()">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Resumen -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted">Total Productos</h6>
                    <h4 id="total_count">0</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted">Valor Costo</h6>
                    <h4 id="total_stock_value">0</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center bg-info text-white">
                    <h6>Valor Venta</h6>
                    <h4 id="total_sale_value">0</h4>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <table id="reportGrid" class="easyui-datagrid" style="width:100%;height:400px"
               data-options="singleSelect:true,fitColumns:false,rownumbers:true">
            <thead>
                <tr>
                    <th data-options="field:'code',width:80">Código</th>
                    <th data-options="field:'name',width:250">Producto</th>
                    <th data-options="field:'category_name',width:150">Categoría</th>
                    <th data-options="field:'stock',width:100,align:'right',formatter:formatStock">Stock</th>
                    <th data-options="field:'min_stock',width:80,align:'right'">Mín.</th>
                    <th data-options="field:'unit',width:60">Unid.</th>
                    <th data-options="field:'purchase_price',width:100,align:'right',formatter:formatNumber">P. Costo</th>
                    <th data-options="field:'sale_price',width:100,align:'right',formatter:formatNumber">P. Venta</th>
                    <th data-options="field:'stock_value',width:120,align:'right',formatter:formatNumber,styler:function(){return 'font-weight:bold;'}">Valor Stock</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#category_id').combogrid({
        panelWidth: 300,
        idField: 'id',
        textField: 'name',
        url: '{{ route('categories.list') }}',
        mode: 'remote',
        delay: 500,
        fitColumns: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('categories.list') }}',
                data: { q: param.q || '' },
                dataType: 'json',
                success: function(data) { success(data); },
                error: function() { error.apply(this, arguments); }
            });
        },
        columns: [[
            {field: 'name', title: 'Nombre', width: 250}
        ]]
    });
    loadReport();
});

function loadReport() {
    $.ajax({
        url: '{{ route('reports.inventory') }}',
        data: {
            category_id: $('#category_id').combogrid('getValue'),
            stock_filter: $('#stock_filter').val()
        },
        success: function(response) {
            $('#reportGrid').datagrid('loadData', response.products);
            $('#total_count').text(response.totals.count);
            $('#total_stock_value').text(formatCurrency(response.totals.total_stock_value));
            $('#total_sale_value').text(formatCurrency(response.totals.total_sale_value));
        }
    });
}

function formatNumber(value) {
    if (value == null) return '';
    return new Intl.NumberFormat('es-PY').format(value);
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-PY').format(value);
}

function formatStock(value, row) {
    var html = formatNumber(value);
    if (row.is_zero) {
        html = '<span class="text-danger fw-bold">' + html + '</span>';
    } else if (row.is_low) {
        html = '<span class="text-warning fw-bold">' + html + '</span>';
    }
    return html;
}
</script>
@endpush
