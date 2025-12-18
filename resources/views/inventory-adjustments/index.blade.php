@extends('layouts.app')

@section('title', 'Ajustes de Inventario')
@section('page-title', 'Ajustes de Inventario')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newAdjustment()">Nuevo Ajuste</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="confirmAdjustment()">Confirmar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelAdjustment()">Anular</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deleteAdjustment()">Eliminar</a>
    <span style="margin-left: 20px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar ajuste...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('inventory-adjustments.data') }}',
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
            <th data-options="field:'adjustment_number',width:120,sortable:true">Número</th>
            <th data-options="field:'adjustment_date',width:100,sortable:true">Fecha</th>
            <th data-options="field:'type',width:80,align:'center',formatter:formatType">Tipo</th>
            <th data-options="field:'reason',width:200">Motivo</th>
            <th data-options="field:'items_count',width:80,align:'center'">Items</th>
            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
            <th data-options="field:'user_name',width:120">Usuario</th>
        </tr>
    </thead>
</table>

<script>
function formatType(value) {
    if (value === 'in') {
        return '<span class="badge bg-success">Entrada</span>';
    } else {
        return '<span class="badge bg-danger">Salida</span>';
    }
}

function formatStatus(value) {
    switch(value) {
        case 'draft': return '<span class="badge bg-secondary">Borrador</span>';
        case 'confirmed': return '<span class="badge bg-success">Confirmado</span>';
        case 'cancelled': return '<span class="badge bg-danger">Anulado</span>';
        default: return value;
    }
}

function newAdjustment() {
    window.location.href = '{{ route('inventory-adjustments.create') }}';
}

function confirmAdjustment() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un ajuste', 'info');
        return;
    }
    if (row.status !== 'draft') {
        $.messager.alert('Información', 'Solo se pueden confirmar ajustes en borrador', 'warning');
        return;
    }
    $.messager.confirm('Confirmar', '¿Desea confirmar este ajuste? Se actualizará el stock.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('inventory-adjustments') }}/' + row.id + '/confirm',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Error';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function cancelAdjustment() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un ajuste', 'info');
        return;
    }
    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'El ajuste ya está anulado', 'warning');
        return;
    }
    $.messager.confirm('Anular', '¿Desea anular este ajuste?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('inventory-adjustments') }}/' + row.id + '/cancel',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Error';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function deleteAdjustment() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un ajuste', 'info');
        return;
    }
    if (row.status !== 'draft') {
        $.messager.alert('Información', 'Solo se pueden eliminar ajustes en borrador', 'warning');
        return;
    }
    $.messager.confirm('Eliminar', '¿Desea eliminar este ajuste?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('inventory-adjustments') }}/' + row.id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Error';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}
</script>
@endsection
