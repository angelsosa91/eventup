@extends('layouts.app')

@section('title', 'Editar Conciliación Bancaria')
@section('page-title', 'Editar Conciliación Bancaria')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Editar Conciliación {{ $reconciliation->reconciliation_number }}</h5>
    </div>
    <div class="card-body">
        <form id="reconciliationForm">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Cuenta Bancaria <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="{{ $reconciliation->bankAccount->account_name }} ({{ $reconciliation->bankAccount->account_number }})" readonly style="background-color: #e9ecef;">
                    <input type="hidden" id="bank_account_id" value="{{ $reconciliation->bank_account_id }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de Conciliación <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="reconciliation_date" required value="{{ $reconciliation->reconciliation_date->format('Y-m-d') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Período Estado de Cuenta - Desde <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="statement_start_date" required value="{{ $reconciliation->statement_start_date->format('Y-m-d') }}" onchange="loadTransactions()">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Período Estado de Cuenta - Hasta <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="statement_end_date" required value="{{ $reconciliation->statement_end_date->format('Y-m-d') }}" onchange="loadTransactions()">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Saldo Inicial (Estado de Cuenta) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="opening_balance" step="0.01" required value="{{ $reconciliation->opening_balance }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Saldo Final (Estado de Cuenta) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="closing_balance" step="0.01" required value="{{ $reconciliation->closing_balance }}" onchange="calculateDifference()">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Saldo en Sistema (Calculado)</label>
                    <input type="text" class="form-control" id="system_balance_display" readonly style="background-color: #e9ecef;" value="{{ number_format($reconciliation->system_balance, 0, ',', '.') }}">
                    <input type="hidden" id="system_balance" value="{{ $reconciliation->system_balance }}">
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
                <textarea class="form-control" id="notes" rows="2" placeholder="Observaciones de la conciliación...">{{ $reconciliation->notes }}</textarea>
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
                       onLoadSuccess: function() { preselectTransactions(); },
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
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="{{ route('bank-reconciliations.show', $reconciliation->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
var allTransactions = [];
var selectedTransactionIds = @json($reconciliation->lines->pluck('bank_transaction_id')->toArray());

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

function loadTransactions() {
    var accountId = $('#bank_account_id').val();
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
        },
        error: function(xhr) {
            $.messager.alert('Error', 'Error al cargar transacciones', 'error');
        }
    });
}

function preselectTransactions() {
    selectedTransactionIds.forEach(function(id) {
        var rowIndex = $('#transactionsDg').datagrid('getRowIndex', id);
        if (rowIndex >= 0) {
            $('#transactionsDg').datagrid('checkRow', rowIndex);
        }
    });
    updateSelectedCount();
    calculateDifference();
}

function selectAll() {
    $('#transactionsDg').datagrid('checkAll');
}

function unselectAll() {
    $('#transactionsDg').datagrid('uncheckAll');
}

function calculateSystemBalance() {
    var selected = $('#transactionsDg').datagrid('getChecked');
    updateSelectedCount();

    if (selected.length > 0) {
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

    var selected = $('#transactionsDg').datagrid('getChecked');
    var transactionIds = selected.map(function(t) { return t.id; });

    var data = {
        reconciliation_date: $('#reconciliation_date').val(),
        statement_start_date: $('#statement_start_date').val(),
        statement_end_date: $('#statement_end_date').val(),
        opening_balance: $('#opening_balance').val(),
        closing_balance: $('#closing_balance').val(),
        transaction_ids: transactionIds,
        notes: $('#notes').val()
    };

    $.ajax({
        url: '{{ route('bank-reconciliations.update', $reconciliation->id) }}',
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            window.location.href = '{{ route('bank-reconciliations.show', $reconciliation->id) }}';
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al guardar';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

// Cargar transacciones al inicio
$(document).ready(function() {
    loadTransactions();
});
</script>
@endsection
