@extends('layouts.app')

@section('title', 'Nueva Conciliación Bancaria')
@section('page-title', 'Nueva Conciliación Bancaria')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Crear Conciliación Bancaria</h5>
    </div>
    <div class="card-body">
        <form id="reconciliationForm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Cuenta Bancaria <span class="text-danger">*</span></label>
                    <select id="bank_account_id" class="easyui-combobox" style="width: 100%;" data-options="
                        url: '{{ route('bank-accounts.list') }}',
                        method: 'get',
                        valueField: 'id',
                        textField: 'account_name',
                        panelHeight: 'auto',
                        required: true,
                        editable: false,
                        onSelect: function(record) { onAccountSelect(record); }
                    "></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de Conciliación <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="reconciliation_date" required value="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Período Estado de Cuenta - Desde <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="statement_start_date" required onchange="loadTransactions()">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Período Estado de Cuenta - Hasta <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="statement_end_date" required onchange="loadTransactions()">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Saldo Inicial (Estado de Cuenta) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="opening_balance" step="0.01" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Saldo Final (Estado de Cuenta) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="closing_balance" step="0.01" required onchange="calculateDifference()">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Saldo en Sistema (Calculado)</label>
                    <input type="text" class="form-control" id="system_balance_display" readonly style="background-color: #e9ecef;">
                    <input type="hidden" id="system_balance">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <div id="difference_alert" class="alert d-none" role="alert">
                        <strong>Diferencia:</strong> <span id="difference_amount"></span>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notas</label>
                <textarea class="form-control" id="notes" rows="2" placeholder="Observaciones de la conciliación..."></textarea>
            </div>

            <hr>

            <h5 class="mb-3">Transacciones a Conciliar</h5>
            <p class="text-muted">Seleccione las transacciones que aparecen en el estado de cuenta bancario.</p>

            <table id="transactionsDg" class="easyui-datagrid" style="width:100%;height:400px;"
                   data-options="
                       toolbar: '#transactionsToolbar',
                       singleSelect: false,
                       fitColumns: true,
                       rownumbers: true,
                       checkOnSelect: true,
                       selectOnCheck: true,
                       onCheck: function() { calculateSystemBalance(); },
                       onUncheck: function() { calculateSystemBalance(); },
                       onCheckAll: function() { calculateSystemBalance(); },
                       onUncheckAll: function() { calculateSystemBalance(); }
                   ">
                <thead>
                    <tr>
                        <th data-options="field:'ck',checkbox:true"></th>
                        <th data-options="field:'transaction_date',width:100,formatter:formatDate">Fecha</th>
                        <th data-options="field:'transaction_number',width:120">Número</th>
                        <th data-options="field:'type',width:100,formatter:formatType">Tipo</th>
                        <th data-options="field:'concept',width:200">Concepto</th>
                        <th data-options="field:'reference',width:100">Referencia</th>
                        <th data-options="field:'amount',width:120,align:'right',formatter:formatAmount">Monto</th>
                        <th data-options="field:'balance_after',width:120,align:'right',formatter:formatMoney">Saldo</th>
                    </tr>
                </thead>
            </table>

            <div id="transactionsToolbar" style="padding: 10px;">
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="selectAll()">Seleccionar Todas</a>
                <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="unselectAll()">Deseleccionar Todas</a>
                <span id="selected_count" class="ms-3 text-muted"></span>
            </div>

            <div class="mt-4">
                <button type="button" class="btn btn-primary" onclick="submitReconciliation()">
                    <i class="fas fa-save"></i> Guardar Conciliación
                </button>
                <a href="{{ route('bank-reconciliations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
var allTransactions = [];

function formatMoney(value) {
    if (!value) return '0';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function formatDate(value) {
    if (!value) return '';
    var date = new Date(value);
    return date.toLocaleDateString('es-PY');
}

function formatType(value) {
    var types = {
        'deposit': '<span class="badge bg-success">Depósito</span>',
        'withdrawal': '<span class="badge bg-danger">Retiro</span>',
        'transfer_in': '<span class="badge bg-info">Transfer. Entrada</span>',
        'transfer_out': '<span class="badge bg-warning">Transfer. Salida</span>',
        'check': '<span class="badge bg-primary">Cheque</span>',
        'charge': '<span class="badge bg-secondary">Cargo</span>',
        'interest': '<span class="badge bg-success">Interés</span>'
    };
    return types[value] || value;
}

function formatAmount(value, row) {
    var formatted = formatMoney(value);
    if (row.type === 'deposit' || row.type === 'transfer_in' || row.type === 'interest') {
        return '<span class="text-success">+' + formatted + '</span>';
    } else {
        return '<span class="text-danger">-' + formatted + '</span>';
    }
}

function onAccountSelect(record) {
    loadTransactions();
}

function loadTransactions() {
    var accountId = $('#bank_account_id').combobox('getValue');
    var startDate = $('#statement_start_date').val();
    var endDate = $('#statement_end_date').val();

    if (!accountId || !startDate || !endDate) {
        return;
    }

    $.ajax({
        url: '{{ route('bank-reconciliations.unreconciled-transactions') }}',
        method: 'GET',
        data: {
            bank_account_id: accountId,
            start_date: startDate,
            end_date: endDate
        },
        success: function(transactions) {
            allTransactions = transactions;
            $('#transactionsDg').datagrid('loadData', transactions);
            updateSelectedCount();
        },
        error: function(xhr) {
            $.messager.alert('Error', 'Error al cargar transacciones', 'error');
        }
    });
}

function selectAll() {
    $('#transactionsDg').datagrid('checkAll');
}

function unselectAll() {
    $('#transactionsDg').datagrid('uncheckAll');
}

function calculateSystemBalance() {
    var accountId = $('#bank_account_id').combobox('getValue');
    if (!accountId) return;

    var selected = $('#transactionsDg').datagrid('getChecked');
    updateSelectedCount();

    // Obtener datos de la cuenta
    $.ajax({
        url: '{{ url('bank-accounts') }}/' + accountId,
        method: 'GET',
        success: function(response) {
            // Aquí calculamos el saldo basado en las transacciones seleccionadas
            // Por simplicidad, usamos el último balance_after de las transacciones seleccionadas
            if (selected.length > 0) {
                // Ordenar por fecha y tomar el último
                selected.sort(function(a, b) {
                    return new Date(b.transaction_date) - new Date(a.transaction_date);
                });
                var systemBalance = parseFloat(selected[0].balance_after);
                $('#system_balance').val(systemBalance);
                $('#system_balance_display').val(formatMoney(systemBalance));
            } else {
                $('#system_balance').val(0);
                $('#system_balance_display').val('0');
            }

            calculateDifference();
        }
    });
}

function calculateDifference() {
    var closingBalance = parseFloat($('#closing_balance').val()) || 0;
    var systemBalance = parseFloat($('#system_balance').val()) || 0;
    var difference = closingBalance - systemBalance;

    $('#difference_amount').text(formatMoney(difference));

    var alert = $('#difference_alert');
    if (Math.abs(difference) < 1) {
        alert.removeClass('alert-warning alert-danger').addClass('alert-success d-block');
        $('#difference_amount').text('0 - Los saldos coinciden');
    } else if (Math.abs(difference) < 10000) {
        alert.removeClass('alert-success alert-danger').addClass('alert-warning d-block');
    } else {
        alert.removeClass('alert-success alert-warning').addClass('alert-danger d-block');
    }
    alert.removeClass('d-none');
}

function updateSelectedCount() {
    var selected = $('#transactionsDg').datagrid('getChecked');
    $('#selected_count').text('Seleccionadas: ' + selected.length + ' de ' + allTransactions.length + ' transacciones');
}

function submitReconciliation() {
    if (!$('#reconciliationForm')[0].checkValidity()) {
        $('#reconciliationForm')[0].reportValidity();
        return;
    }

    var accountId = $('#bank_account_id').combobox('getValue');
    if (!accountId) {
        $.messager.alert('Validación', 'Debe seleccionar una cuenta bancaria', 'warning');
        return;
    }

    var selected = $('#transactionsDg').datagrid('getChecked');
    var transactionIds = selected.map(function(t) { return t.id; });

    var data = {
        bank_account_id: accountId,
        reconciliation_date: $('#reconciliation_date').val(),
        statement_start_date: $('#statement_start_date').val(),
        statement_end_date: $('#statement_end_date').val(),
        opening_balance: $('#opening_balance').val(),
        closing_balance: $('#closing_balance').val(),
        transaction_ids: transactionIds,
        notes: $('#notes').val()
    };

    $.ajax({
        url: '{{ route('bank-reconciliations.store') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            window.location.href = '{{ route('bank-reconciliations.index') }}';
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al guardar';
            $.messager.alert('Error', msg, 'error');
        }
    });
}
</script>
@endsection
