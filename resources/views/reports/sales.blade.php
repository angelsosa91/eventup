@extends('layouts.app')

@section('title', 'Reporte de Ventas')
@section('page-title', 'Reporte de Ventas')

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
                    <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                    <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Anulada</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <input id="customer_id" style="width: 100%;height:38px;">
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
        <div class="row mb-4" id="summary">
            <div class="col-md-2">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted">Cantidad</h6>
                    <h4 id="total_count">0</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted">Exento</h6>
                    <h4 id="total_exento">0</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted">Gravado 5%</h6>
                    <h4 id="total_5">0</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 text-center">
                    <h6 class="text-muted">Gravado 10%</h6>
                    <h4 id="total_10">0</h4>
                </div>
            </div>
            <div class="col-md-2">
                <div class="border rounded p-3 text-center bg-primary text-white">
                    <h6>TOTAL</h6>
                    <h4 id="total_general">0</h4>
                </div>
            </div>
        </div>

        <!-- Tabla de datos -->
        <table id="reportGrid" class="easyui-datagrid" style="width:100%;height:400px"
               data-options="
                   singleSelect: true,
                   fitColumns: false,
                   rownumbers: true
               ">
            <thead>
                <tr>
                    <th data-options="field:'sale_number',width:100">Número</th>
                    <th data-options="field:'sale_date',width:100">Fecha</th>
                    <th data-options="field:'customer_name',width:200">Cliente</th>
                    <th data-options="field:'subtotal_exento',width:100,align:'right',formatter:formatNumber">Exento</th>
                    <th data-options="field:'subtotal_5',width:100,align:'right',formatter:formatNumber">Grav. 5%</th>
                    <th data-options="field:'iva_5',width:80,align:'right',formatter:formatNumber">IVA 5%</th>
                    <th data-options="field:'subtotal_10',width:100,align:'right',formatter:formatNumber">Grav. 10%</th>
                    <th data-options="field:'iva_10',width:80,align:'right',formatter:formatNumber">IVA 10%</th>
                    <th data-options="field:'total',width:120,align:'right',formatter:formatNumber,styler:function(){return 'font-weight:bold;'}">Total</th>
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
    // ComboGrid de clientes
    $('#customer_id').combogrid({
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        url: '{{ route('customers.list') }}',
        mode: 'remote',
        delay: 500,
        fitColumns: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('customers.list') }}',
                data: { q: param.q || '' },
                dataType: 'json',
                success: function(data) { success(data); },
                error: function() { error.apply(this, arguments); }
            });
        },
        columns: [[
            {field: 'name', title: 'Nombre', width: 200},
            {field: 'ruc', title: 'RUC', width: 100}
        ]]
    });

    loadReport();
});

function loadReport() {
    $.ajax({
        url: '{{ route('reports.sales') }}',
        data: {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            status: $('#status').val(),
            customer_id: $('#customer_id').combogrid('getValue')
        },
        success: function(response) {
            $('#reportGrid').datagrid('loadData', response.sales);

            $('#total_count').text(response.totals.count);
            $('#total_exento').text(formatCurrency(response.totals.subtotal_exento));
            $('#total_5').text(formatCurrency(response.totals.subtotal_5));
            $('#total_10').text(formatCurrency(response.totals.subtotal_10));
            $('#total_general').text(formatCurrency(response.totals.total));
        }
    });
}

function formatNumber(value) {
    if (value == null) return '';
    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function formatStatus(value) {
    switch(value) {
        case 'draft': return '<span class="badge bg-secondary">Borrador</span>';
        case 'confirmed': return '<span class="badge bg-success">Confirmada</span>';
        case 'cancelled': return '<span class="badge bg-danger">Anulada</span>';
        default: return value;
    }
}
</script>
@endpush
