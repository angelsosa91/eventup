@extends('layouts.app')

@section('title', 'Compras')
@section('page-title', 'Compras')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newPurchase()">Nueva Compra</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="viewPurchase()">Ver Detalle</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="confirmPurchase()">Confirmar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelPurchase()">Anular</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deletePurchase()">Eliminar</a>
    <span style="margin-left: 20px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar por número o proveedor...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:700px;"
       data-options="
           url: '{{ route('purchases.data') }}',
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
       ">
    <thead>
        <tr>
            <th data-options="field:'purchase_number',width:120,sortable:true">Número</th>
            <th data-options="field:'invoice_number',width:120,sortable:true">Fact. Proveedor</th>
            <th data-options="field:'purchase_date',width:100,sortable:true">Fecha</th>
            <th data-options="field:'supplier_name',width:200">Proveedor</th>
            <th data-options="field:'subtotal_exento',width:100,align:'right'">Exento</th>
            <th data-options="field:'subtotal_5',width:100,align:'right'">Grav. 5%</th>
            <th data-options="field:'iva_5',width:80,align:'right'">IVA 5%</th>
            <th data-options="field:'subtotal_10',width:100,align:'right'">Grav. 10%</th>
            <th data-options="field:'iva_10',width:80,align:'right'">IVA 10%</th>
            <th data-options="field:'total',width:120,align:'right',styler:function(){return 'font-weight:bold;'}">Total</th>
            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
            <th data-options="field:'payment_method',width:100">Pago</th>
            <th data-options="field:'user_name',width:120">Usuario</th>
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

function newPurchase() {
    window.location.href = '{{ route('purchases.create') }}';
}

function viewPurchase() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        window.location.href = '{{ url('purchases') }}/' + row.id + '/detail';
    } else {
        $.messager.alert('Información', 'Seleccione una compra', 'info');
    }
}

function confirmPurchase() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una compra', 'info');
        return;
    }
    if (row.status !== 'draft') {
        $.messager.alert('Información', 'Solo se pueden confirmar compras en borrador', 'warning');
        return;
    }
    $.messager.confirm('Confirmar', '¿Desea confirmar esta compra? Se incrementará el stock.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('purchases') }}/' + row.id + '/confirm',
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

function cancelPurchase() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una compra', 'info');
        return;
    }
    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'La compra ya está anulada', 'warning');
        return;
    }
    $.messager.confirm('Anular', '¿Desea anular esta compra? Se revertirá el stock si estaba confirmada.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('purchases') }}/' + row.id + '/cancel',
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

function deletePurchase() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una compra', 'info');
        return;
    }
    if (row.status !== 'draft') {
        $.messager.alert('Información', 'Solo se pueden eliminar compras en borrador', 'warning');
        return;
    }
    $.messager.confirm('Eliminar', '¿Desea eliminar esta compra?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('purchases') }}/' + row.id,
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
