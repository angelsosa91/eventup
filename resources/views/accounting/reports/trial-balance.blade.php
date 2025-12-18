@extends('layouts.app')

@section('title', 'Balance de Comprobación')
@section('page-title', 'Balance de Comprobación - Trial Balance')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Fecha Desde <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date_from" value="{{ date('Y-m-01') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Hasta <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="date_to" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de Cuenta</label>
                <select id="account_type" class="form-select">
                    <option value="">Todos los tipos</option>
                    <option value="asset">Activo</option>
                    <option value="liability">Pasivo</option>
                    <option value="equity">Patrimonio</option>
                    <option value="income">Ingreso</option>
                    <option value="expense">Gasto</option>
                </select>
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
            <h5 class="mb-0">Balance de Comprobación</h5>
            <small id="periodTitle"></small>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-hover" id="trialBalanceTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%">Código</th>
                            <th style="width: 30%">Cuenta</th>
                            <th style="width: 10%" class="text-center">Tipo</th>
                            <th style="width: 12%" class="text-end">Débito</th>
                            <th style="width: 12%" class="text-end">Crédito</th>
                            <th style="width: 13%" class="text-end">Saldo Deudor</th>
                            <th style="width: 13%" class="text-end">Saldo Acreedor</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Los datos se llenarán dinámicamente -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end"><strong>TOTALES:</strong></td>
                            <td class="text-end"><strong><span id="totalDebit">0</span></strong></td>
                            <td class="text-end"><strong><span id="totalCredit">0</span></strong></td>
                            <td class="text-end"><strong><span id="totalBalanceDebit">0</span></strong></td>
                            <td class="text-end"><strong><span id="totalBalanceCredit">0</span></strong></td>
                        </tr>
                        <tr id="balanceStatusRow">
                            <td colspan="7" class="text-center">
                                <span id="balanceStatus"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="noDataMessage" class="alert alert-info">
    <i class="bi bi-info-circle"></i> Seleccione un rango de fechas para consultar el balance de comprobación.
</div>
@endsection

@push('scripts')
<script>
var hasData = false;

$('#filterForm').submit(function(e) {
    e.preventDefault();

    if (!this.checkValidity()) {
        this.reportValidity();
        return;
    }

    var dateFrom = $('#date_from').val();
    var dateTo = $('#date_to').val();
    var accountType = $('#account_type').val();

    $.ajax({
        url: '{{ route('trial-balance.data') }}',
        method: 'GET',
        data: {
            date_from: dateFrom,
            date_to: dateTo,
            account_type: accountType
        },
        success: function(response) {
            hasData = true;
            displayReport(response, dateFrom, dateTo);
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

function displayReport(data, dateFrom, dateTo) {
    $('#noDataMessage').hide();
    $('#reportContainer').show();

    // Período
    $('#periodTitle').text('Período: ' + dateFrom + ' al ' + dateTo);

    // Limpiar tabla
    $('#tableBody').empty();

    // Agregar filas
    if (data.rows.length === 0) {
        $('#tableBody').append(
            '<tr><td colspan="7" class="text-center text-muted">No hay movimientos en el período seleccionado</td></tr>'
        );
    } else {
        var filteredRows = data.rows;
        var accountTypeFilter = $('#account_type').val();

        if (accountTypeFilter) {
            filteredRows = data.rows.filter(function(row) {
                return row.account_type === accountTypeFilter;
            });
        }

        filteredRows.forEach(function(row) {
            var accountTypeBadge = formatAccountType(row.account_type);

            var tr = '<tr>' +
                '<td>' + row.code + '</td>' +
                '<td>' + row.name + '</td>' +
                '<td class="text-center">' + accountTypeBadge + '</td>' +
                '<td class="text-end">' + formatMoney(row.debit) + '</td>' +
                '<td class="text-end">' + formatMoney(row.credit) + '</td>' +
                '<td class="text-end">' + (row.balance_debit > 0 ? formatMoney(row.balance_debit) : '') + '</td>' +
                '<td class="text-end">' + (row.balance_credit > 0 ? formatMoney(row.balance_credit) : '') + '</td>' +
                '</tr>';

            $('#tableBody').append(tr);
        });
    }

    // Totales
    $('#totalDebit').text(formatMoney(data.totals.debit));
    $('#totalCredit').text(formatMoney(data.totals.credit));
    $('#totalBalanceDebit').text(formatMoney(data.totals.balance_debit));
    $('#totalBalanceCredit').text(formatMoney(data.totals.balance_credit));

    // Estado de balance
    if (data.totals.is_balanced) {
        $('#balanceStatus').html('<span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> BALANCEADO - Los totales de débito y crédito coinciden</span>');
    } else {
        var difference = Math.abs(data.totals.debit - data.totals.credit);
        $('#balanceStatus').html('<span class="badge bg-danger fs-6"><i class="bi bi-exclamation-triangle"></i> NO BALANCEADO - Diferencia: ' + formatMoney(difference) + '</span>');
    }
}

function formatAccountType(value) {
    const types = {
        'asset': '<span class="badge bg-success">Activo</span>',
        'liability': '<span class="badge bg-danger">Pasivo</span>',
        'equity': '<span class="badge bg-primary">Patrimonio</span>',
        'income': '<span class="badge bg-info">Ingreso</span>',
        'expense': '<span class="badge bg-warning">Gasto</span>'
    };
    return types[value] || value;
}

function formatMoney(value) {
    if (!value || value === 0) return '0';
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

function exportReport() {
    if (!hasData) {
        $.messager.alert('Información', 'Primero debe consultar el reporte', 'info');
        return;
    }

    var dateFrom = $('#date_from').val();
    var dateTo = $('#date_to').val();
    var accountType = $('#account_type').val();

    var url = '{{ url('trial-balance/export') }}?date_from=' + dateFrom + '&date_to=' + dateTo;
    if (accountType) {
        url += '&account_type=' + accountType;
    }

    window.open(url, '_blank');
}
</script>
@endpush
