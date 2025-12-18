@extends('layouts.app')

@section('title', 'Conciliaciones Bancarias')
@section('page-title', 'Conciliaciones Bancarias')

@section('content')
<div id="toolbar" style="padding: 10px;">
    @can('bank-reconciliations.create')
    <a href="{{ route('bank-reconciliations.create') }}" class="easyui-linkbutton" iconCls="icon-add">Nueva Conciliación</a>
    @endcan
    @can('bank-reconciliations.view')
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-tip" onclick="viewDetail()">Ver Detalle</a>
    @endcan
    @can('bank-reconciliations.edit')
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-edit" onclick="editReconciliation()">Editar</a>
    @endcan
    @can('bank-reconciliations.post')
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="postReconciliation()">Publicar</a>
    @endcan
    @can('bank-reconciliations.cancel')
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelReconciliation()">Cancelar</a>
    @endcan
    @can('bank-reconciliations.delete')
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" onclick="deleteReconciliation()">Eliminar</a>
    @endcan
    @can('bank-reconciliations.view')
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-print" onclick="printReport()">Imprimir</a>
    @endcan
    <span style="margin-left: 20px;">
        <select id="status_filter" class="easyui-combobox" style="width: 150px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByStatus(value); }
        ">
            <option value="">Todos los estados</option>
            <option value="draft">Borrador</option>
            <option value="posted">Publicado</option>
            <option value="cancelled">Cancelado</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <select id="account_filter" class="easyui-combobox" style="width: 250px;" data-options="
            url: '{{ route('bank-accounts.list') }}',
            method: 'get',
            valueField: 'id',
            textField: 'account_name',
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByAccount(value); }
        ">
            <option value="">Todas las cuentas</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('bank-reconciliations.data') }}',
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
            <th data-options="field:'reconciliation_number',width:120,sortable:true">Número</th>
            <th data-options="field:'bank_account',width:180,formatter:formatBankAccount">Cuenta Bancaria</th>
            <th data-options="field:'reconciliation_date',width:100,align:'center',formatter:formatDate">Fecha</th>
            <th data-options="field:'statement_start_date',width:100,align:'center',formatter:formatDate">Desde</th>
            <th data-options="field:'statement_end_date',width:100,align:'center',formatter:formatDate">Hasta</th>
            <th data-options="field:'closing_balance',width:120,align:'right',formatter:formatMoney">Saldo Estado Cuenta</th>
            <th data-options="field:'system_balance',width:120,align:'right',formatter:formatMoney">Saldo Sistema</th>
            <th data-options="field:'difference',width:100,align:'right',formatter:formatDifference">Diferencia</th>
            <th data-options="field:'status',width:100,align:'center',formatter:formatStatus">Estado</th>
        </tr>
    </thead>
</table>

<script>
function formatMoney(value) {
    if (!value) return '0';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function formatDate(value) {
    if (!value) return '';
    var date = new Date(value);
    return date.toLocaleDateString('es-PY');
}

function formatBankAccount(value, row) {
    if (row.bank_account) {
        return row.bank_account.account_name + ' (' + row.bank_account.account_number + ')';
    }
    return '';
}

function formatStatus(value) {
    switch(value) {
        case 'draft': return '<span class="badge bg-secondary">Borrador</span>';
        case 'posted': return '<span class="badge bg-success">Publicado</span>';
        case 'cancelled': return '<span class="badge bg-danger">Cancelado</span>';
        default: return value;
    }
}

function formatDifference(value) {
    if (!value) return '<span class="badge bg-success">0</span>';
    var num = parseFloat(value);
    if (Math.abs(num) < 1) {
        return '<span class="badge bg-success">0</span>';
    }
    var formatted = num.toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
    if (num > 0) {
        return '<span class="badge bg-warning">+' + formatted + '</span>';
    } else {
        return '<span class="badge bg-danger">' + formatted + '</span>';
    }
}

function viewDetail() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una conciliación', 'info');
        return;
    }
    window.location.href = '{{ url('bank-reconciliations') }}/' + row.id;
}

function editReconciliation() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una conciliación', 'info');
        return;
    }

    if (row.status !== 'draft') {
        $.messager.alert('Advertencia', 'Solo se pueden editar conciliaciones en borrador', 'warning');
        return;
    }

    window.location.href = '{{ url('bank-reconciliations') }}/' + row.id + '/edit';
}

function postReconciliation() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una conciliación', 'info');
        return;
    }

    if (row.status !== 'draft') {
        $.messager.alert('Advertencia', 'Solo se pueden publicar conciliaciones en borrador', 'warning');
        return;
    }

    var diff = parseFloat(row.difference);
    var warningMsg = '';
    if (Math.abs(diff) >= 1) {
        warningMsg = '<br><span class="text-danger">ADVERTENCIA: Existe una diferencia de ' +
                     diff.toLocaleString('es-PY') + ' entre el estado de cuenta y el sistema.</span>';
    }

    $.messager.confirm('Confirmar', '¿Desea publicar esta conciliación?' + warningMsg +
                       '<br><br>Una vez publicada, las transacciones quedarán marcadas como conciliadas.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('bank-reconciliations') }}/' + row.id + '/post',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al publicar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function cancelReconciliation() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una conciliación', 'info');
        return;
    }

    if (row.status !== 'posted') {
        $.messager.alert('Advertencia', 'Solo se pueden cancelar conciliaciones publicadas', 'warning');
        return;
    }

    $.messager.confirm('Confirmar', '¿Desea cancelar esta conciliación?<br><br>' +
                       'Las transacciones volverán a quedar como NO conciliadas.', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('bank-reconciliations') }}/' + row.id + '/cancel',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al cancelar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function deleteReconciliation() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una conciliación', 'info');
        return;
    }

    if (row.status !== 'draft') {
        $.messager.alert('Advertencia', 'Solo se pueden eliminar conciliaciones en borrador', 'warning');
        return;
    }

    $.messager.confirm('Confirmar', '¿Desea eliminar esta conciliación?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('bank-reconciliations') }}/' + row.id,
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
                    $('#dg').datagrid('reload');
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al eliminar';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}

function printReport() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una conciliación', 'info');
        return;
    }
    window.open('{{ url('bank-reconciliations') }}/' + row.id + '/report', '_blank');
}

function filterByStatus(value) {
    $('#dg').datagrid('load', { status: value });
}

function filterByAccount(value) {
    $('#dg').datagrid('load', { bank_account_id: value });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}
</script>
@endsection
