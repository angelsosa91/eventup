@extends('layouts.app')

@section('title', 'Gastos')
@section('page-title', 'Gastos')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newExpense()">Nuevo Gasto</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" onclick="editExpense()">Editar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="payExpense()">Marcar Pagado</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelExpense()">Anular</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deleteExpense()">Eliminar</a>
    <span style="margin-left: 20px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar gasto...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:700px;"
       data-options="
           url: '{{ route('expenses.data') }}',
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
            <th data-options="field:'expense_number',width:100,sortable:true">Número</th>
            <th data-options="field:'expense_date',width:100,sortable:true">Fecha</th>
            <th data-options="field:'category_name',width:150">Categoría</th>
            <th data-options="field:'description',width:250">Descripción</th>
            <th data-options="field:'supplier_name',width:150">Proveedor</th>
            <th data-options="field:'document_number',width:100">Documento</th>
            <th data-options="field:'amount',width:120,align:'right',styler:function(){return 'font-weight:bold;'}">Monto</th>
            <th data-options="field:'tax_rate',width:60,align:'center',formatter:formatTaxRate">IVA</th>
            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
            <th data-options="field:'payment_method',width:100">Pago</th>
            <th data-options="field:'user_name',width:120">Usuario</th>
        </tr>
    </thead>
</table>

<script>
function formatStatus(value, row) {
    switch(value) {
        case 'pending':
            return '<span class="badge bg-warning">Pendiente</span>';
        case 'paid':
            return '<span class="badge bg-success">Pagado</span>';
        case 'cancelled':
            return '<span class="badge bg-danger">Anulado</span>';
        default:
            return value;
    }
}

function formatTaxRate(value) {
    return value + '%';
}

function newExpense() {
    window.location.href = '{{ route('expenses.create') }}';
}

function editExpense() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        if (row.status === 'paid') {
            $.messager.alert('Información', 'No se pueden editar gastos pagados', 'warning');
            return;
        }
        window.location.href = '{{ url('expenses') }}/' + row.id + '/edit';
    } else {
        $.messager.alert('Información', 'Seleccione un gasto', 'info');
    }
}

function payExpense() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un gasto', 'info');
        return;
    }
    if (row.status !== 'pending') {
        $.messager.alert('Información', 'Solo se pueden pagar gastos pendientes', 'warning');
        return;
    }
    $.messager.confirm('Confirmar', '¿Desea marcar este gasto como pagado?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('expenses') }}/' + row.id + '/pay',
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

function cancelExpense() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un gasto', 'info');
        return;
    }
    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'El gasto ya está anulado', 'warning');
        return;
    }
    $.messager.confirm('Anular', '¿Desea anular este gasto?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('expenses') }}/' + row.id + '/cancel',
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

function deleteExpense() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un gasto', 'info');
        return;
    }
    if (row.status === 'paid') {
        $.messager.alert('Información', 'No se pueden eliminar gastos pagados', 'warning');
        return;
    }
    $.messager.confirm('Eliminar', '¿Desea eliminar este gasto?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('expenses') }}/' + row.id,
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
