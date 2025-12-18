@extends('layouts.app')

@section('title', 'Libro Mayor')
@section('page-title', 'Libro Mayor - General Ledger')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Cuenta <span class="text-danger">*</span></label>
                <select id="account_id" class="form-select" required>
                    <option value="">Seleccione una cuenta...</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Desde <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date_from" value="{{ date('Y-m-01') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Hasta <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date_to" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Consultar
                </button>
                <button type="button" class="btn btn-success" onclick="exportReport()">
                    <i class="bi bi-file-earmark-excel"></i> Exportar
                </button>
            </div>
        </form>
    </div>
</div>

<div id="reportContainer" style="display: none;">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0" id="accountTitle"></h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" id="ledgerTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%">Fecha</th>
                            <th style="width: 12%">N° Asiento</th>
                            <th style="width: 43%">Descripción</th>
                            <th style="width: 12%" class="text-end">Débito</th>
                            <th style="width: 12%" class="text-end">Crédito</th>
                            <th style="width: 12%" class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="openingBalanceRow" class="table-secondary">
                            <td colspan="3"><strong>SALDO INICIAL</strong></td>
                            <td class="text-end"></td>
                            <td class="text-end"></td>
                            <td class="text-end"><strong><span id="openingBalance">0</span></strong></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end"><strong>TOTALES DEL PERÍODO:</strong></td>
                            <td class="text-end"><strong><span id="totalDebit">0</span></strong></td>
                            <td class="text-end"><strong><span id="totalCredit">0</span></strong></td>
                            <td class="text-end"><strong><span id="finalBalance">0</span></strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="noDataMessage" class="alert alert-info" style="display: none;">
    <i class="bi bi-info-circle"></i> Seleccione una cuenta y un rango de fechas para consultar el libro mayor.
</div>
@endsection

@push('scripts')
<script>
var currentAccount = null;

$(document).ready(function() {
    loadAccounts();
    $('#noDataMessage').show();
});

function loadAccounts() {
    $.ajax({
        url: '{{ route('account-chart.detail-accounts') }}',
        method: 'GET',
        success: function(data) {
            console.log('Cuentas cargadas en libro mayor:', data);
            console.log('Total de cuentas:', data.length);
            var options = '<option value="">Seleccione una cuenta...</option>';
            data.forEach(function(account) {
                options += '<option value="' + account.id + '">' + account.name + '</option>';
            });
            $('#account_id').html(options);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar cuentas:', xhr, status, error);
            $.messager.alert('Error', 'Error al cargar las cuentas: ' + error, 'error');
        }
    });
}

$('#filterForm').submit(function(e) {
    e.preventDefault();

    if (!this.checkValidity()) {
        this.reportValidity();
        return;
    }

    var accountId = $('#account_id').val();
    var dateFrom = $('#date_from').val();
    var dateTo = $('#date_to').val();

    if (!accountId) {
        $.messager.alert('Validación', 'Debe seleccionar una cuenta', 'warning');
        return;
    }

    $.ajax({
        url: '{{ route('general-ledger.data') }}',
        method: 'GET',
        data: {
            account_id: accountId,
            date_from: dateFrom,
            date_to: dateTo
        },
        success: function(response) {
            currentAccount = response.account;
            displayReport(response);
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al consultar el reporte';
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            }
            $.messager.alert('Error', msg, 'error');
        }
    });
});

function displayReport(data) {
    $('#noDataMessage').hide();
    $('#reportContainer').show();

    // Título
    var accountInfo = data.account.code + ' - ' + data.account.name +
                     ' (Naturaleza: ' + (data.account.nature === 'debit' ? 'Deudora' : 'Acreedora') + ')';
    $('#accountTitle').text(accountInfo);

    // Saldo inicial
    $('#openingBalance').text(formatMoney(data.opening_balance));

    // Limpiar tabla
    $('#ledgerTable tbody tr:not(#openingBalanceRow)').remove();

    var totalDebit = 0;
    var totalCredit = 0;
    var finalBalance = data.opening_balance;

    // Agregar movimientos
    if (data.rows.length === 0) {
        $('#ledgerTable tbody').append(
            '<tr><td colspan="6" class="text-center text-muted">No hay movimientos en el período seleccionado</td></tr>'
        );
    } else {
        data.rows.forEach(function(row) {
            totalDebit += parseFloat(row.debit);
            totalCredit += parseFloat(row.credit);
            finalBalance = row.balance;

            var tr = '<tr>' +
                '<td>' + row.entry_date + '</td>' +
                '<td>' + row.entry_number + '</td>' +
                '<td>' + row.description + '</td>' +
                '<td class="text-end">' + (row.debit > 0 ? formatMoney(row.debit) : '') + '</td>' +
                '<td class="text-end">' + (row.credit > 0 ? formatMoney(row.credit) : '') + '</td>' +
                '<td class="text-end">' + formatMoney(row.balance) + '</td>' +
                '</tr>';

            $('#ledgerTable tbody').append(tr);
        });
    }

    // Totales
    $('#totalDebit').text(formatMoney(totalDebit));
    $('#totalCredit').text(formatMoney(totalCredit));
    $('#finalBalance').text(formatMoney(finalBalance));
}

function formatMoney(value) {
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function exportReport() {
    if (!currentAccount) {
        $.messager.alert('Información', 'Primero debe consultar el reporte', 'info');
        return;
    }

    var dateFrom = $('#date_from').val();
    var dateTo = $('#date_to').val();

    window.open('{{ url('general-ledger/export') }}?account_id=' + $('#account_id').val() +
                '&date_from=' + dateFrom + '&date_to=' + dateTo, '_blank');
}
</script>
@endpush
