@extends('layouts.app')

@section('title', 'Asientos Contables')
@section('page-title', 'Asientos Contables - Libro Diario')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="{{ route('journal-entries.create') }}" class="easyui-linkbutton" iconCls="icon-add">Nuevo Asiento</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-tip" onclick="viewEntry()">Ver Detalle</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="postEntry()">Contabilizar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelEntry()">Anular</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deleteEntry()">Eliminar</a>
    <span style="margin-left: 20px;">
        <select id="status_filter" class="easyui-combobox" style="width: 150px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByStatus(value); }
        ">
            <option value="">Todos los estados</option>
            <option value="draft">Borradores</option>
            <option value="posted">Contabilizados</option>
            <option value="cancelled">Anulados</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('journal-entries.data') }}',
           method: 'get',
           toolbar: '#toolbar',
           pagination: true,
           rownumbers: true,
           singleSelect: true,
           fitColumns: true,
           pageSize: 20,
           pageList: [10, 20, 50, 100],
           sortName: 'entry_date',
           sortOrder: 'desc',
           remoteSort: true
       ">
    <thead>
        <tr>
            <th data-options="field:'entry_number',width:120,sortable:true">Número</th>
            <th data-options="field:'entry_date',width:100,sortable:true">Fecha</th>
            <th data-options="field:'description',width:300">Descripción</th>
            <th data-options="field:'total_debit',width:120,align:'right',formatter:formatMoney">Total Débito</th>
            <th data-options="field:'total_credit',width:120,align:'right',formatter:formatMoney">Total Crédito</th>
            <th data-options="field:'is_balanced',width:80,align:'center',formatter:formatBalanced">Balanceado</th>
            <th data-options="field:'entry_type',width:100,align:'center',formatter:formatEntryType">Tipo</th>
            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
            <th data-options="field:'user_name',width:120">Usuario</th>
        </tr>
    </thead>
</table>

<script>
function formatMoney(value) {
    if (!value) return '0';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function formatBalanced(value) {
    return value ? '<i class="bi bi-check-circle text-success"></i>' : '<i class="bi bi-x-circle text-danger"></i>';
}

function formatEntryType(value) {
    return value === 'manual' ?
        '<span class="badge bg-primary">Manual</span>' :
        '<span class="badge bg-info">Automático</span>';
}

function formatStatus(value) {
    const statuses = {
        'draft': '<span class="badge bg-warning">Borrador</span>',
        'posted': '<span class="badge bg-success">Contabilizado</span>',
        'cancelled': '<span class="badge bg-danger">Anulado</span>'
    };
    return statuses[value] || value;
}

function viewEntry() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un asiento', 'info');
        return;
    }
    window.location.href = '{{ url('journal-entries') }}/' + row.id;
}

function postEntry() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un asiento', 'info');
        return;
    }

    if (row.status === 'posted') {
        $.messager.alert('Información', 'El asiento ya está contabilizado', 'info');
        return;
    }

    if (row.status === 'cancelled') {
        $.messager.alert('Advertencia', 'No se puede contabilizar un asiento anulado', 'warning');
        return;
    }

    if (!row.is_balanced) {
        $.messager.alert('Error', 'El asiento no está balanceado. Débitos: ' + formatMoney(row.total_debit) + ', Créditos: ' + formatMoney(row.total_credit), 'error');
        return;
    }

    $.messager.confirm('Confirmar', '¿Desea contabilizar este asiento? Esta acción afectará los saldos de las cuentas.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('journal-entries') }}/' + row.id + '/post',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function cancelEntry() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un asiento', 'info');
        return;
    }

    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'El asiento ya está anulado', 'info');
        return;
    }

    $.messager.confirm('Confirmar', '¿Desea anular este asiento?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('journal-entries') }}/' + row.id + '/cancel',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function deleteEntry() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un asiento', 'info');
        return;
    }

    if (row.status === 'posted') {
        $.messager.alert('Advertencia', 'No se puede eliminar un asiento contabilizado. Debe anularlo primero.', 'warning');
        return;
    }

    $.messager.confirm('Confirmar', '¿Está seguro de eliminar este asiento?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('journal-entries') }}/' + row.id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function filterByStatus(value) {
    $('#dg').datagrid('load', { status: value });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}
</script>
@endsection
