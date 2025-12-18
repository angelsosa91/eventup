@extends('layouts.app')

@section('title', 'Mi Historial de Cajas')
@section('page-title', 'Mi Historial de Cajas')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="{{ route('cash-registers.current') }}" class="easyui-linkbutton" iconCls="icon-add">Mi Caja del Día</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-tip" onclick="viewDetail()">Ver Detalle</a>
    <span style="margin-left: 20px;">
        <select id="status_filter" class="easyui-combobox" style="width: 150px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByStatus(value); }
        ">
            <option value="">Todos los estados</option>
            <option value="open">Abierta</option>
            <option value="closed">Cerrada</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('cash-registers.data') }}',
           method: 'get',
           toolbar: '#toolbar',
           pagination: true,
           rownumbers: true,
           singleSelect: true,
           fitColumns: true,
           pageSize: 20,
           pageList: [10, 20, 50, 100],
           sortName: 'id',
           sortOrder: 'desc',
           remoteSort: true
       ">
    <thead>
        <tr>
            <th data-options="field:'register_number',width:100,sortable:true">Número</th>
            <th data-options="field:'register_date',width:100,sortable:true">Fecha</th>
            <th data-options="field:'user_name',width:150">Usuario</th>
            <th data-options="field:'opening_balance',width:120,align:'right',formatter:formatMoney">Saldo Inicial</th>
            <th data-options="field:'expected_balance',width:120,align:'right',formatter:formatMoney">Saldo Esperado</th>
            <th data-options="field:'actual_balance',width:120,align:'right',formatter:formatMoney">Saldo Real</th>
            <th data-options="field:'difference',width:120,align:'right',formatter:formatDifference">Diferencia</th>
            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
        </tr>
    </thead>
</table>

<script>
function formatMoney(value) {
    if (value == null) return '-';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function formatDifference(value, row) {
    if (value == null || value == 0) return '-';
    var formatted = parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    if (value > 0) {
        return '<span class="text-success">+' + formatted + '</span>';
    } else {
        return '<span class="text-danger">' + formatted + '</span>';
    }
}

function formatStatus(value) {
    switch(value) {
        case 'open': return '<span class="badge bg-success">Abierta</span>';
        case 'closed': return '<span class="badge bg-secondary">Cerrada</span>';
        default: return value;
    }
}

function viewDetail() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un arqueo', 'info');
        return;
    }
    window.location.href = '{{ url('cash-registers') }}/' + row.id;
}

function filterByStatus(value) {
    $('#dg').datagrid('load', { status: value });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}
</script>
@endsection
