@extends('layouts.app')

@section('title', 'Ventas')
@section('page-title', 'Ventas')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newSale()">Nueva Venta</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="viewSale()">Ver Detalle</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="confirmSale()">Confirmar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelSale()">Anular</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deleteSale()">Eliminar</a>
    <span style="margin-left: 20px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar por número o cliente...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:700px;"
       data-options="
           url: '{{ route('sales.data') }}',
           method: 'get',
           toolbar: '#toolbar',
           pagination: true,
           rownumbers: true,
           singleSelect: true,
           fitColumns: false,
           pageSize: 20,
           pageList: [10, 20, 50, 100],
           sortName: 'id',
           sortOrder: 'desc',
           remoteSort: true
       "><!-- calc(100vh - 200px) -->
    <thead>
        <tr>
            <th data-options="field:'sale_number',width:120,sortable:true">Número</th>
            <th data-options="field:'sale_date',width:100,sortable:true">Fecha</th>
            <th data-options="field:'customer_name',width:200">Cliente</th>
            <th data-options="field:'subtotal_exento',width:100,align:'right'">Exento</th>
            <th data-options="field:'subtotal_5',width:100,align:'right'">Grav. 5%</th>
            <th data-options="field:'iva_5',width:80,align:'right'">IVA 5%</th>
            <th data-options="field:'subtotal_10',width:100,align:'right'">Grav. 10%</th>
            <th data-options="field:'iva_10',width:80,align:'right'">IVA 10%</th>
            <th data-options="field:'total',width:120,align:'right',styler:function(){return 'font-weight:bold;'}">Total</th>
            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
            <th data-options="field:'payment_method',width:100">Pago</th>
            <th data-options="field:'user_name',width:120">Vendedor</th>
        </tr>
    </thead>
</table>

<script>
function formatStatus(value, row) {
    switch(value) {
        case 'draft':
            return '<span class="badge bg-secondary">Borrador</span>';
        case 'confirmed':
            return '<span class="badge bg-success">Confirmada</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Anulada</span>';
        default:
            return value;
    }
}

function newSale() {
    window.location.href = '{{ route('sales.create') }}';
}

function viewSale() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        window.location.href = '{{ url('sales') }}/' + row.id + '/detail';
    } else {
        $.messager.alert('Información', 'Seleccione una venta', 'info');
    }
}

function confirmSale() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una venta', 'info');
        return;
    }
    if (row.status !== 'draft') {
        $.messager.alert('Información', 'Solo se pueden confirmar ventas en borrador', 'warning');
        return;
    }
    $.messager.confirm('Confirmar', '¿Desea confirmar esta venta? Se descontará el stock.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('sales') }}/' + row.id + '/confirm',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.show({
                        title: 'Éxito',
                        msg: response.message,
                        timeout: 3000,
                        showType: 'slide'
                    });
                    $('#dg').datagrid('reload');
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
    });
}

function cancelSale() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una venta', 'info');
        return;
    }
    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'La venta ya está anulada', 'warning');
        return;
    }
    $.messager.confirm('Anular', '¿Desea anular esta venta? Se devolverá el stock si estaba confirmada.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('sales') }}/' + row.id + '/cancel',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.show({
                        title: 'Éxito',
                        msg: response.message,
                        timeout: 3000,
                        showType: 'slide'
                    });
                    $('#dg').datagrid('reload');
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
    });
}

function deleteSale() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una venta', 'info');
        return;
    }
    if (row.status !== 'draft') {
        $.messager.alert('Información', 'Solo se pueden eliminar ventas en borrador', 'warning');
        return;
    }
    $.messager.confirm('Eliminar', '¿Desea eliminar esta venta?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('sales') }}/' + row.id,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $.messager.show({
                        title: 'Éxito',
                        msg: response.message,
                        timeout: 3000,
                        showType: 'slide'
                    });
                    $('#dg').datagrid('reload');
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
    });
}

function doSearch(value) {
    $('#dg').datagrid('load', {
        search: value
    });
}
</script>
@endsection
