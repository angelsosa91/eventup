@extends('layouts.app')

@section('title', 'Movimientos Bancarios')
@section('page-title', 'Movimientos Bancarios')

@section('content')
<div id="toolbar" style="padding: 10px;">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" onclick="newTransaction()">Nueva Transacción</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-redo" onclick="transfer()">Transferencia</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" onclick="cashDeposit()">Depósito desde Caja</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-back" onclick="cashWithdrawal()">Retiro a Caja</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="cancelTransaction()">Anular</a>
    <span style="margin-left: 20px;">
        <select id="account_filter" class="easyui-combobox" style="width: 200px;" data-options="
            panelHeight: 'auto',
            editable: false,
            onChange: function(value) { filterByAccount(value); }
        ">
            <option value="">Todas las cuentas</option>
            @foreach($accounts as $account)
            <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
            @endforeach
        </select>
    </span>
    <span style="margin-left: 10px;">
        <input id="searchbox" class="easyui-searchbox" style="width: 250px"
               data-options="prompt:'Buscar...',searcher:doSearch">
    </span>
</div>

<table id="dg" class="easyui-datagrid" style="width:100%;height:600px;"
       data-options="
           url: '{{ route('bank-transactions.data') }}',
           method: 'get',
           toolbar: '#toolbar',
           pagination: true,
           rownumbers: true,
           singleSelect: true,
           fitColumns: true,
           pageSize: 20,
           pageList: [10, 20, 50, 100],
           sortName: 'transaction_date',
           sortOrder: 'desc',
           remoteSort: true
       ">
    <thead>
        <tr>
            <th data-options="field:'transaction_date',width:80,sortable:true">Fecha</th>
            <th data-options="field:'transaction_number',width:100">Número</th>
            <th data-options="field:'bank_account_name',width:150">Cuenta Bancaria</th>
            <th data-options="field:'type',width:100,formatter:formatType">Tipo</th>
            <th data-options="field:'concept',width:200">Concepto</th>
            <th data-options="field:'reference',width:100">Referencia</th>
            <th data-options="field:'amount',width:100,align:'right',formatter:formatMoney">Monto</th>
            <th data-options="field:'balance_after',width:100,align:'right',formatter:formatMoney">Saldo</th>
            <th data-options="field:'status',width:80,align:'center',formatter:formatStatus">Estado</th>
            <th data-options="field:'reconciled',width:60,align:'center',formatter:formatReconciled">Conc.</th>
        </tr>
    </thead>
</table>

<!-- Transaction Dialog -->
<div id="transactionDlg" class="easyui-dialog" style="width:600px;padding:20px;" closed="true" buttons="#transactionDlg-buttons">
    <h5 id="transactionTitle" class="mb-3">Nueva Transacción</h5>
    <form id="transactionForm">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Cuenta Bancaria <span class="text-danger">*</span></label>
                <select class="form-select" id="bank_account_id" required>
                    <option value="">Seleccione...</option>
                    @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="transaction_date" value="{{ date('Y-m-d') }}" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                <select class="form-select" id="type" required>
                    <option value="deposit">Depósito</option>
                    <option value="withdrawal">Retiro</option>
                    <option value="charge">Cargo Bancario</option>
                    <option value="interest">Interés</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Monto <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="amount" step="0.01" min="0.01" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Concepto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="concept" maxlength="255" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Referencia</label>
            <input type="text" class="form-control" id="reference" maxlength="100">
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" id="description" rows="2"></textarea>
        </div>
    </form>
</div>
<div id="transactionDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitTransaction()">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#transactionDlg').dialog('close')">Cancelar</a>
</div>

<!-- Transfer Dialog -->
<div id="transferDlg" class="easyui-dialog" style="width:600px;padding:20px;" closed="true" buttons="#transferDlg-buttons">
    <h5 class="mb-3">Transferencia Entre Cuentas</h5>
    <form id="transferForm">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Cuenta Origen <span class="text-danger">*</span></label>
                <select class="form-select" id="from_account_id" required>
                    <option value="">Seleccione...</option>
                    @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Cuenta Destino <span class="text-danger">*</span></label>
                <select class="form-select" id="to_account_id" required>
                    <option value="">Seleccione...</option>
                    @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="transfer_date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Monto <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="transfer_amount" step="0.01" min="0.01" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Concepto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="transfer_concept" maxlength="255" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Referencia</label>
            <input type="text" class="form-control" id="transfer_reference" maxlength="100">
        </div>
    </form>
</div>
<div id="transferDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitTransfer()">Transferir</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#transferDlg').dialog('close')">Cancelar</a>
</div>

<!-- Cash Deposit Dialog -->
<div id="cashDepositDlg" class="easyui-dialog" style="width:600px;padding:20px;" closed="true" buttons="#cashDepositDlg-buttons">
    <h5 class="mb-3">Depósito desde Caja a Banco</h5>
    <form id="cashDepositForm">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Caja Actual <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="cash_register_display" readonly>
                <input type="hidden" id="cash_register_id_deposit">
            </div>
            <div class="col-md-6">
                <label class="form-label">Cuenta Bancaria <span class="text-danger">*</span></label>
                <select class="form-select" id="bank_account_id_deposit" required>
                    <option value="">Seleccione...</option>
                    @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="deposit_date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Monto <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="deposit_amount" step="0.01" min="0.01" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Concepto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="deposit_concept" maxlength="255" value="Depósito desde caja" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Referencia</label>
            <input type="text" class="form-control" id="deposit_reference" maxlength="100">
        </div>
    </form>
</div>
<div id="cashDepositDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitCashDeposit()">Depositar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#cashDepositDlg').dialog('close')">Cancelar</a>
</div>

<!-- Cash Withdrawal Dialog -->
<div id="cashWithdrawalDlg" class="easyui-dialog" style="width:600px;padding:20px;" closed="true" buttons="#cashWithdrawalDlg-buttons">
    <h5 class="mb-3">Retiro de Banco a Caja</h5>
    <form id="cashWithdrawalForm">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Cuenta Bancaria <span class="text-danger">*</span></label>
                <select class="form-select" id="bank_account_id_withdrawal" required>
                    <option value="">Seleccione...</option>
                    @foreach($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Caja Actual <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="cash_register_display_withdrawal" readonly>
                <input type="hidden" id="cash_register_id_withdrawal">
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="withdrawal_date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Monto <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="withdrawal_amount" step="0.01" min="0.01" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Concepto <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="withdrawal_concept" maxlength="255" value="Retiro a caja" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Referencia</label>
            <input type="text" class="form-control" id="withdrawal_reference" maxlength="100">
        </div>
    </form>
</div>
<div id="cashWithdrawalDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitCashWithdrawal()">Retirar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#cashWithdrawalDlg').dialog('close')">Cancelar</a>
</div>

@push('scripts')
<script>
function formatMoney(value) {
    if (!value) return '0';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function formatType(value) {
    switch(value) {
        case 'deposit': return '<span class="badge bg-success">Depósito</span>';
        case 'withdrawal': return '<span class="badge bg-danger">Retiro</span>';
        case 'transfer_in': return '<span class="badge bg-info">Transfer. Entrada</span>';
        case 'transfer_out': return '<span class="badge bg-warning">Transfer. Salida</span>';
        case 'check': return '<span class="badge bg-primary">Cheque</span>';
        case 'charge': return '<span class="badge bg-secondary">Cargo</span>';
        case 'interest': return '<span class="badge bg-light text-dark">Interés</span>';
        default: return value;
    }
}

function formatStatus(value) {
    switch(value) {
        case 'pending': return '<span class="badge bg-warning">Pendiente</span>';
        case 'completed': return '<span class="badge bg-success">Completado</span>';
        case 'cancelled': return '<span class="badge bg-danger">Anulado</span>';
        default: return value;
    }
}

function formatReconciled(value) {
    return value ? '<i class="bi bi-check-circle-fill text-success"></i>' : '';
}

function newTransaction() {
    $('#transactionForm')[0].reset();
    $('#transaction_date').val('{{ date('Y-m-d') }}');
    $('#transactionDlg').dialog('open');
}

function submitTransaction() {
    if (!$('#transactionForm')[0].checkValidity()) {
        $('#transactionForm')[0].reportValidity();
        return;
    }

    var data = {
        bank_account_id: $('#bank_account_id').val(),
        transaction_date: $('#transaction_date').val(),
        type: $('#type').val(),
        amount: $('#amount').val(),
        concept: $('#concept').val(),
        reference: $('#reference').val(),
        description: $('#description').val()
    };

    $.ajax({
        url: '{{ route('bank-transactions.store') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#transactionDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al guardar';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function transfer() {
    $('#transferForm')[0].reset();
    $('#transfer_date').val('{{ date('Y-m-d') }}');
    $('#transferDlg').dialog('open');
}

function submitTransfer() {
    if (!$('#transferForm')[0].checkValidity()) {
        $('#transferForm')[0].reportValidity();
        return;
    }

    if ($('#from_account_id').val() === $('#to_account_id').val()) {
        $.messager.alert('Error', 'Las cuentas de origen y destino deben ser diferentes', 'error');
        return;
    }

    var data = {
        from_account_id: $('#from_account_id').val(),
        to_account_id: $('#to_account_id').val(),
        transaction_date: $('#transfer_date').val(),
        amount: $('#transfer_amount').val(),
        concept: $('#transfer_concept').val(),
        reference: $('#transfer_reference').val()
    };

    $.ajax({
        url: '{{ route('bank-transactions.transfer') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#transferDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al realizar la transferencia';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function cashDeposit() {
    // Obtener caja abierta actual
    $.ajax({
        url: '{{ route('cash-registers.current') }}',
        method: 'GET',
        success: function(response) {
            if (response.register) {
                $('#cash_register_id_deposit').val(response.register.id);
                $('#cash_register_display').val(response.register.register_number + ' - ' + response.register.register_date);
                $('#cashDepositForm')[0].reset();
                $('#cash_register_display').val(response.register.register_number + ' - ' + response.register.register_date);
                $('#deposit_date').val('{{ date('Y-m-d') }}');
                $('#cashDepositDlg').dialog('open');
            } else {
                $.messager.alert('Información', 'No hay una caja abierta. Debe abrir una caja primero.', 'info');
            }
        }
    });
}

function submitCashDeposit() {
    if (!$('#cashDepositForm')[0].checkValidity()) {
        $('#cashDepositForm')[0].reportValidity();
        return;
    }

    var data = {
        cash_register_id: $('#cash_register_id_deposit').val(),
        bank_account_id: $('#bank_account_id_deposit').val(),
        transaction_date: $('#deposit_date').val(),
        amount: $('#deposit_amount').val(),
        concept: $('#deposit_concept').val(),
        reference: $('#deposit_reference').val()
    };

    $.ajax({
        url: '{{ route('bank-transactions.cash-deposit') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#cashDepositDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al realizar el depósito';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function cashWithdrawal() {
    // Obtener caja abierta actual
    $.ajax({
        url: '{{ route('cash-registers.current') }}',
        method: 'GET',
        success: function(response) {
            if (response.register) {
                $('#cash_register_id_withdrawal').val(response.register.id);
                $('#cash_register_display_withdrawal').val(response.register.register_number + ' - ' + response.register.register_date);
                $('#cashWithdrawalForm')[0].reset();
                $('#cash_register_display_withdrawal').val(response.register.register_number + ' - ' + response.register.register_date);
                $('#withdrawal_date').val('{{ date('Y-m-d') }}');
                $('#cashWithdrawalDlg').dialog('open');
            } else {
                $.messager.alert('Información', 'No hay una caja abierta. Debe abrir una caja primero.', 'info');
            }
        }
    });
}

function submitCashWithdrawal() {
    if (!$('#cashWithdrawalForm')[0].checkValidity()) {
        $('#cashWithdrawalForm')[0].reportValidity();
        return;
    }

    var data = {
        cash_register_id: $('#cash_register_id_withdrawal').val(),
        bank_account_id: $('#bank_account_id_withdrawal').val(),
        transaction_date: $('#withdrawal_date').val(),
        amount: $('#withdrawal_amount').val(),
        concept: $('#withdrawal_concept').val(),
        reference: $('#withdrawal_reference').val()
    };

    $.ajax({
        url: '{{ route('bank-transactions.cash-withdrawal') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#cashWithdrawalDlg').dialog('close');
            $('#dg').datagrid('reload');
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al realizar el retiro';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function cancelTransaction() {
    var row = $('#dg').datagrid('getSelected');
    if (!row) {
        $.messager.alert('Información', 'Seleccione una transacción', 'info');
        return;
    }

    if (row.status === 'cancelled') {
        $.messager.alert('Información', 'La transacción ya está anulada', 'warning');
        return;
    }

    $.messager.confirm('Anular', '¿Desea anular esta transacción?', function(r) {
        if (r) {
            $.ajax({
                url: '{{ url('bank-transactions') }}/' + row.id + '/cancel',
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

function filterByAccount(value) {
    $('#dg').datagrid('load', { bank_account_id: value });
}

function doSearch(value) {
    $('#dg').datagrid('load', { search: value });
}
</script>
@endpush
@endsection
