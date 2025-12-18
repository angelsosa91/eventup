@extends('layouts.app')

@section('title', 'Notas de Crédito')
@section('page-title', 'Notas de Crédito')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newCreditNote()">Nueva Nota de Crédito</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-search" onclick="viewCreditNote()">Ver Detalle</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="confirmCreditNote()">Confirmar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelCreditNote()">Anular</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print" onclick="printCreditNote()">Imprimir PDF</a>
    <span style="margin-left: 20px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 300px"
               data-options="prompt:'Buscar por número, cliente o venta...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:700px;"
       data-options="
           url: '{{ route('credit-notes.data') }}',
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
            <th data-options="field:'credit_note_number',width:120,sortable:true">Número NC</th>
            <th data-options="field:'date',width:100,sortable:true">Fecha</th>
            <th data-options="field:'sale_number',width:120">Venta Ref.</th>
            <th data-options="field:'customer_name',width:200">Cliente</th>
            <th data-options="field:'reason_text',width:150">Motivo</th>
            <th data-options="field:'type_text',width:80,align:'center'">Tipo</th>
            <th data-options="field:'total',width:120,align:'right',styler:function(){return 'font-weight:bold;'}">Total</th>
            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
            <th data-options="field:'created_by',width:120">Creado por</th>
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

function newCreditNote() {
    window.location.href = '{{ route('credit-notes.create') }}';
}

function viewCreditNote() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        window.location.href = '{{ url('credit-notes') }}/' + row.id;
    } else {
        $.messager.alert('Información', 'Seleccione una nota de crédito', 'info');
    }
}

function confirmCreditNote() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una nota de crédito', 'info');
        return;
    }
    if (row.status !== 'draft') {
        $.messager.alert('Información', 'Solo se pueden confirmar notas de crédito en borrador', 'warning');
        return;
    }
    $.messager.confirm('Confirmar', '¿Desea confirmar esta nota de crédito? Se devolverá el stock y se creará el asiento contable de reversión.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('credit-notes') }}/' + row.id + '/confirm',
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
                    var error = xhr.responseJSON?.message || 'Error al confirmar la nota de crédito';
                    $.messager.alert('Error', error, 'error');
                }
            });
        }
    });
}

function cancelCreditNote() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una nota de crédito', 'info');
        return;
    }
    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'La nota de crédito ya está anulada', 'warning');
        return;
    }
    if (row.status === 'confirmed') {
        $.messager.alert('Información', 'No se puede anular una nota de crédito confirmada', 'warning');
        return;
    }
    $.messager.confirm('Anular', '¿Desea anular esta nota de crédito?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('credit-notes') }}/' + row.id + '/cancel',
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
                    var error = xhr.responseJSON?.message || 'Error al anular la nota de crédito';
                    $.messager.alert('Error', error, 'error');
                }
            });
        }
    });
}

function printCreditNote() {
    var row = $('#dg').datagrid('getSelected');
    if (row) {
        window.open('{{ url('credit-notes') }}/' + row.id + '/pdf', '_blank');
    } else {
        $.messager.alert('Información', 'Seleccione una nota de crédito', 'info');
    }
}

function doSearch(value, name) {
    $('#dg').datagrid('load', {
        search: value
    });
}
</script>
@endsection
