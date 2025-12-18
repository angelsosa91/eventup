@extends('layouts.app')

@section('title', 'Reporte de Gastos')
@section('page-title', 'Reporte de Gastos')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" class="form-control" id="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" class="form-control" id="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select class="form-select" id="status">
                    <option value="">Todos</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Pagado</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Anulado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <input id="category_id" style="width: 100%;height:38px;">
            </div>
            <div class="col-md-3">
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
                    <h6 class="text-muted">Cantidad</h6>
                    <h4 id="total_count">0</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted">IVA Crédito</h6>
                    <h4 id="total_tax">0</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center bg-warning">
                    <h6>TOTAL GASTOS</h6>
                    <h4 id="total_amount">0</h4>
                </div>
            </div>
        </div>

        <!-- Gastos por categoría -->
        <div class="mb-4">
            <h6>Gastos por Categoría</h6>
            <div id="categoryChart" class="row"></div>
        </div>

        <!-- Tabla -->
        <table id="reportGrid" class="easyui-datagrid" style="width:100%;height:350px"
               data-options="singleSelect:true,fitColumns:false,rownumbers:true">
            <thead>
                <tr>
                    <th data-options="field:'expense_number',width:100">Número</th>
                    <th data-options="field:'expense_date',width:100">Fecha</th>
                    <th data-options="field:'category_name',width:150">Categoría</th>
                    <th data-options="field:'description',width:250">Descripción</th>
                    <th data-options="field:'supplier_name',width:150">Proveedor</th>
                    <th data-options="field:'amount',width:120,align:'right',formatter:formatNumber,styler:function(){return 'font-weight:bold;'}">Monto</th>
                    <th data-options="field:'tax_amount',width:100,align:'right',formatter:formatNumber">IVA</th>
                    <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
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
        url: '{{ route('expense-categories.list') }}',
        mode: 'remote',
        delay: 500,
        fitColumns: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('expense-categories.list') }}',
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
        url: '{{ route('reports.expenses') }}',
        data: {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            status: $('#status').val(),
            category_id: $('#category_id').combogrid('getValue')
        },
        success: function(response) {
            $('#reportGrid').datagrid('loadData', response.expenses);
            $('#total_count').text(response.totals.count);
            $('#total_tax').text(formatCurrency(response.totals.tax_amount));
            $('#total_amount').text(formatCurrency(response.totals.amount));

            // Mostrar gastos por categoría
            var html = '';
            response.byCategory.forEach(function(cat) {
                html += '<div class="col-md-3 mb-2">';
                html += '<div class="border rounded p-2">';
                html += '<small class="text-muted">' + cat.category + '</small><br>';
                html += '<strong>' + formatCurrency(cat.amount) + '</strong>';
                html += ' <small>(' + cat.count + ')</small>';
                html += '</div></div>';
            });
            $('#categoryChart').html(html);
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

function formatStatus(value) {
    switch(value) {
        case 'pending': return '<span class="badge bg-warning">Pendiente</span>';
        case 'paid': return '<span class="badge bg-success">Pagado</span>';
        case 'cancelled': return '<span class="badge bg-danger">Anulado</span>';
        default: return value;
    }
}
</script>
@endpush
