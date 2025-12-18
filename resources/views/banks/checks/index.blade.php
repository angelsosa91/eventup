@extends('layouts.app')

@section('title', 'Gestión de Cheques')
@section('page-title', 'Gestión de Cheques')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newCheck()">Nuevo Cheque</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" onclick="depositCheck()">Depositar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="cashCheck()">Marcar Cobrado</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-no" onclick="bounceCheck()">Marcar Rechazado</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelCheck()">Anular</a>
    <span style="margin-left: 20px;">
        <select id="type_filter" class="easyui-combobox" style="width: 150px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByType(value); }
        ">
            <option value="">Todos los tipos</option>
            <option value="issued">Emitidos</option>
            <option value="received">Recibidos</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <select id="status_filter" class="easyui-combobox" style="width: 150px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByStatus(value); }
        ">
            <option value="">Todos los estados</option>
            <option value="pending">Pendiente</option>
            <option value="deposited">Depositado</option>
            <option value="cashed">Cobrado</option>
            <option value="bounced">Rechazado</option>
            <option value="cancelled">Anulado</option>
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 220px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('checks.data') }}',
           method: 'get',
           toolbar: '#toolbar',
           pagination: true,
           rownumbers: true,
           singleSelect: true,
           fitColumns: true,
           pageSize: 20,
           pageList: [10, 20, 50, 100],
           sortName: 'issue_date',
           sortOrder: 'desc',
           remoteSort: true,
           rowStyler: function(index, row) {
               if (row.is_overdue) {
                   return 'background-color:#ffebee;';
               }
           }
       ">
    <thead>
        <tr>
            <th data-options="field:'check_number',width:100">Número</th>
            <th data-options="field:'issue_date',width:80,sortable:true">Fecha Emisión</th>
            <th data-options="field:'due_date',width:80">Vencimiento</th>
            <th data-options="field:'type',width:80,align:'center',formatter:formatCheckType">Tipo</th>
            <th data-options="field:'bank_account_name',width:150">Banco/Cuenta</th>
            <th data-options="field:'payee_issuer',width:150">Beneficiario/Emisor</th>
            <th data-options="field:'concept',width:200">Concepto</th>
            <th data-options="field:'amount',width:100,align:'right',formatter:formatMoney">Monto</th>
            <th data-options="field:'status',width:90,align:'center',formatter:formatCheckStatus">Estado</th>
        </tr>
    </thead>
</table>

<!-- Check Dialog -->
<div id="checkDlg" class="easyui-dialog" style="width:600px;padding:20px;" closed="true" buttons="#checkDlg-buttons">
    <h5 class="mb-3">Nuevo Cheque</h5>
    <form id="checkForm">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Tipo de Cheque <span class="text-danger">*</span></label>
                <select class="form-select" id="type" required onchange="toggleCheckType()">
                    <option value="issued">Emitido (Propio)</option>
                    <option value="received">Recibido (Terceros)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Número de Cheque <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="check_number" required>
            </div>
        </div>

        <!-- Para cheques emitidos -->
        <div id="issued_fields">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">Cuenta Bancaria <span class="text-danger">*</span></label>
                    <select class="form-select" id="bank_account_id">
                        <option value="">Seleccione...</option>
                        @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">A la Orden de (Beneficiario) <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="payee">
                </div>
            </div>
        </div>

        <!-- Para cheques recibidos -->
        <div id="received_fields" style="display: none;">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Banco <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="bank_name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Emisor <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="issuer">
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Fecha Emisión <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="issue_date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Vencimiento</label>
                <input type="date" class="form-control" id="due_date">
            </div>
            <div class="col-md-4">
                <label class="form-label">Monto <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="amount" step="0.01" min="0.01" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Concepto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="concept" maxlength="255" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Notas</label>
            <textarea class="form-control" id="notes" rows="2"></textarea>
        </div>
    </form>
</div>
<div id="checkDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitCheck()">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#checkDlg').dialog('close')">Cancelar</a>
</div>

<!-- Deposit Check Dialog -->
<div id="depositCheckDlg" class="easyui-dialog" style="width:500px;padding:20px;" closed="true" buttons="#depositCheckDlg-buttons">
    <h5 class="mb-3">Depositar Cheque</h5>
    <form id="depositCheckForm">
        <div class="mb-3">
            <label class="form-label">Cuenta Bancaria para Depósito <span class="text-danger">*</span></label>
            <select class="form-select" id="deposit_bank_account_id" required>
                <option value="">Seleccione...</option>
                @foreach($accounts as $account)
                <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha de Depósito <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="deposit_date" value="{{ date('Y-m-d') }}" required>
        </div>
    </form>
</div>
<div id="depositCheckDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitDepositCheck()">Depositar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#depositCheckDlg').dialog('close')">Cancelar</a>
</div>

<!-- Cash Check Dialog -->
<div id="cashCheckDlg" class="easyui-dialog" style="width:400px;padding:20px;" closed="true" buttons="#cashCheckDlg-buttons">
    <h5 class="mb-3">Marcar Cheque como Cobrado</h5>
    <form id="cashCheckForm">
        <div class="mb-3">
            <label class="form-label">Fecha de Cobro <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="cashed_date" value="{{ date('Y-m-d') }}" required>
        </div>
    </form>
</div>
<div id="cashCheckDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitCashCheck()">Confirmar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#cashCheckDlg').dialog('close')">Cancelar</a>
</div>

@push('scripts')
<script>
var selectedCheckId = null;

function formatMoney(value) {
    if (!value) return '0';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function formatCheckType(value) {
    return value === 'issued' ? '<span class="badge bg-primary">Emitido</span>' : '<span class="badge bg-info">Recibido</span>';
}

function formatCheckStatus(value) {
    switch(value) {
        case 'pending': return '<span class="badge bg-warning">Pendiente</span>';
        case 'deposited': return '<span class="badge bg-info">Depositado</span>';
        case 'cashed': return '<span class="badge bg-success">Cobrado</span>';
        case 'bounced': return '<span class="badge bg-danger">Rechazado</span>';
        case 'cancelled': return '<span class="badge bg-secondary">Anulado</span>';
        default: return value;
    }
}

function toggleCheckType() {
    var type = $('#type').val();
    if (type === 'issued') {
        $('#issued_fields').show();
        $('#received_fields').hide();
        $('#bank_account_id').prop('required', true);
        $('#payee').prop('required', true);
        $('#bank_name').prop('required', false);
        $('#issuer').prop('required', false);
    } else {
        $('#issued_fields').hide();
        $('#received_fields').show();
        $('#bank_account_id').prop('required', false);
        $('#payee').prop('required', false);
        $('#bank_name').prop('required', true);
        $('#issuer').prop('required', true);
    }
}

function newCheck() {
    $('#checkForm')[0].reset();
    $('#issue_date').val('{{ date('Y-m-d') }}');
    toggleCheckType();
    $('#checkDlg').dialog('open');
}

function submitCheck() {
    if (!$('#checkForm')[0].checkValidity()) {
        $('#checkForm')[0].reportValidity();
        return;
    }

    var data = {
        check_number: $('#check_number').val(),
        issue_date: $('#issue_date').val(),
        due_date: $('#due_date').val(),
        amount: $('#amount').val(),
        type: $('#type').val(),
        concept: $('#concept').val(),
        notes: $('#notes').val()
    };

    if (data.type === 'issued') {
        data.bank_account_id = $('#bank_account_id').val();
        data.payee = $('#payee').val();
    } else {
        data.bank_name = $('#bank_name').val();
        data.issuer = $('#issuer').val();
    }

    $.ajax({
        url: '{{ route('checks.store') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#checkDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al guardar';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function depositCheck() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un cheque', 'info');
        return;
    }

    if (row.type !== 'received') {
        $.messager.alert('Información', 'Solo se pueden depositar cheques recibidos', 'warning');
        return;
    }

    if (row.status !== 'pending') {
        $.messager.alert('Información', 'El cheque ya fue procesado', 'warning');
        return;
    }

    selectedCheckId = row.id;
    $('#depositCheckForm')[0].reset();
    $('#deposit_date').val('{{ date('Y-m-d') }}');
    $('#depositCheckDlg').dialog('open');
}

function submitDepositCheck() {
    if (!$('#depositCheckForm')[0].checkValidity()) {
        $('#depositCheckForm')[0].reportValidity();
        return;
    }

    var data = {
        bank_account_id: $('#deposit_bank_account_id').val(),
        deposit_date: $('#deposit_date').val()
    };

    $.ajax({
        url: '{{ url('checks') }}/' + selectedCheckId + '/deposit',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#depositCheckDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al depositar';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function cashCheck() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un cheque', 'info');
        return;
    }

    if (!['pending', 'deposited'].includes(row.status)) {
        $.messager.alert('Información', 'El cheque no puede ser cobrado en su estado actual', 'warning');
        return;
    }

    selectedCheckId = row.id;
    $('#cashCheckForm')[0].reset();
    $('#cashed_date').val('{{ date('Y-m-d') }}');
    $('#cashCheckDlg').dialog('open');
}

function submitCashCheck() {
    if (!$('#cashCheckForm')[0].checkValidity()) {
        $('#cashCheckForm')[0].reportValidity();
        return;
    }

    var data = {
        cashed_date: $('#cashed_date').val()
    };

    $.ajax({
        url: '{{ url('checks') }}/' + selectedCheckId + '/cash',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#cashCheckDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function bounceCheck() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un cheque', 'info');
        return;
    }

    if (row.status === 'cashed') {
        $.messager.alert('Información', 'No se puede rechazar un cheque cobrado', 'warning');
        return;
    }

    $.messager.confirm('Rechazar', '¿Desea marcar este cheque como rechazado?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('checks') }}/' + row.id + '/bounce',
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

function cancelCheck() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione un cheque', 'info');
        return;
    }

    if (row.status === 'cashed') {
        $.messager.alert('Información', 'No se puede anular un cheque cobrado', 'warning');
        return;
    }

    $.messager.confirm('Anular', '¿Desea anular este cheque?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('checks') }}/' + row.id + '/cancel',
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

function filterByType(value) {
    $('#dg').datagrid('load', { type: value });
}

function filterByStatus(value) {
    $('#dg').datagrid('load', { status: value });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}
</script>
@endpush
@endsection
