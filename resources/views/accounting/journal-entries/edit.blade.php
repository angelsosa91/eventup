@extends('layouts.app')

@section('title', 'Editar Asiento Contable')
@section('page-title', 'Editar Asiento Contable')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Editar Asiento N° {{ $entry->entry_number }}</h5>
    </div>
    <div class="card-body">
        <form id="entryForm">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="entry_date" value="{{ $entry->entry_date->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Descripción <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="description" value="{{ $entry->description }}" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Notas</label>
                <textarea class="form-control" id="notes" rows="2">{{ $entry->notes }}</textarea>
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Líneas del Asiento</h5>
                <button type="button" class="btn btn-sm btn-primary" onclick="addLine()">
                    <i class="bi bi-plus-circle"></i> Agregar Línea
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="linesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35%">Cuenta</th>
                            <th style="width: 30%">Descripción</th>
                            <th style="width: 15%" class="text-end">Débito</th>
                            <th style="width: 15%" class="text-end">Crédito</th>
                            <th style="width: 5%"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        <!-- Las líneas se agregarán dinámicamente -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="2" class="text-end"><strong>TOTALES:</strong></td>
                            <td class="text-end"><strong><span id="totalDebit">0</span></strong></td>
                            <td class="text-end"><strong><span id="totalCredit">0</span></strong></td>
                            <td></td>
                        </tr>
                        <tr id="balanceRow" style="display: none;">
                            <td colspan="5" class="text-center">
                                <span class="badge bg-danger">NO BALANCEADO</span>
                                <span id="balanceMessage"></span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Cambios
                </button>
                <a href="{{ route('journal-entries.show', $entry->id) }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var lineCounter = 0;
var accounts = [];
var existingLines = @json($entry->lines);

// Cargar cuentas al iniciar
$(document).ready(function() {
    loadAccounts();
});

function loadAccounts() {
    $.ajax({
        url: '{{ route('account-chart.detail-accounts') }}',
        method: 'GET',
        success: function(data) {
            accounts = data;
            // Cargar líneas existentes DESPUÉS de cargar las cuentas
            loadExistingLines();
        },
        error: function() {
            $.messager.alert('Error', 'Error al cargar las cuentas', 'error');
        }
    });
}

function loadExistingLines() {
    existingLines.forEach(function(line) {
        addLine(line);
    });
    calculateTotals();
}

function addLine(existingLine = null) {
    lineCounter++;
    var accountOptions = '<option value="">Seleccione una cuenta...</option>';
    accounts.forEach(function(account) {
        var selected = existingLine && existingLine.account_id === account.id ? 'selected' : '';
        accountOptions += '<option value="' + account.id + '" ' + selected + '>' + account.name + '</option>';
    });

    var debitValue = existingLine ? existingLine.debit : 0;
    var creditValue = existingLine ? existingLine.credit : 0;
    var descriptionValue = existingLine ? (existingLine.description || '') : '';

    var debitReadonly = creditValue > 0 ? 'readonly' : '';
    var creditReadonly = debitValue > 0 ? 'readonly' : '';

    var row = '<tr id="line_' + lineCounter + '">' +
        '<td><select class="form-select form-select-sm account-select" name="lines[' + lineCounter + '][account_id]" required onchange="calculateTotals()">' + accountOptions + '</select></td>' +
        '<td><input type="text" class="form-control form-control-sm" name="lines[' + lineCounter + '][description]" value="' + descriptionValue + '"></td>' +
        '<td><input type="number" class="form-control form-control-sm text-end debit-input" name="lines[' + lineCounter + '][debit]" value="' + debitValue + '" step="0.01" min="0" onchange="handleDebitChange(this)" onkeyup="calculateTotals()" ' + debitReadonly + '></td>' +
        '<td><input type="number" class="form-control form-control-sm text-end credit-input" name="lines[' + lineCounter + '][credit]" value="' + creditValue + '" step="0.01" min="0" onchange="handleCreditChange(this)" onkeyup="calculateTotals()" ' + creditReadonly + '></td>' +
        '<td class="text-center"><button type="button" class="btn btn-sm btn-danger" onclick="removeLine(' + lineCounter + ')"><i class="bi bi-trash"></i></button></td>' +
        '</tr>';

    $('#linesBody').append(row);
    calculateTotals();
}

function removeLine(id) {
    if ($('#linesBody tr').length <= 2) {
        $.messager.alert('Advertencia', 'Debe haber al menos 2 líneas en el asiento', 'warning');
        return;
    }
    $('#line_' + id).remove();
    calculateTotals();
}

function handleDebitChange(input) {
    var row = $(input).closest('tr');
    var creditInput = row.find('.credit-input');

    if (parseFloat(input.value) > 0) {
        creditInput.val(0);
        creditInput.prop('readonly', true);
    } else {
        creditInput.prop('readonly', false);
    }
    calculateTotals();
}

function handleCreditChange(input) {
    var row = $(input).closest('tr');
    var debitInput = row.find('.debit-input');

    if (parseFloat(input.value) > 0) {
        debitInput.val(0);
        debitInput.prop('readonly', true);
    } else {
        debitInput.prop('readonly', false);
    }
    calculateTotals();
}

function calculateTotals() {
    var totalDebit = 0;
    var totalCredit = 0;

    $('.debit-input').each(function() {
        totalDebit += parseFloat($(this).val()) || 0;
    });

    $('.credit-input').each(function() {
        totalCredit += parseFloat($(this).val()) || 0;
    });

    $('#totalDebit').text(formatMoney(totalDebit));
    $('#totalCredit').text(formatMoney(totalCredit));

    var difference = Math.abs(totalDebit - totalCredit);
    if (difference > 0.01) {
        $('#balanceRow').show();
        $('#balanceMessage').text(' Diferencia: ' + formatMoney(difference));
    } else {
        $('#balanceRow').hide();
    }
}

function formatMoney(value) {
    return parseFloat(value).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

$('#entryForm').submit(function(e) {
    e.preventDefault();

    if (!this.checkValidity()) {
        this.reportValidity();
        return;
    }

    // Validar que al menos haya 2 líneas
    if ($('#linesBody tr').length < 2) {
        $.messager.alert('Validación', 'Debe agregar al menos 2 líneas al asiento', 'warning');
        return;
    }

    // Recopilar datos
    var lines = [];
    $('#linesBody tr').each(function() {
        var accountId = $(this).find('.account-select').val();
        var description = $(this).find('input[name*="[description]"]').val();
        var debit = parseFloat($(this).find('.debit-input').val()) || 0;
        var credit = parseFloat($(this).find('.credit-input').val()) || 0;

        if (accountId && (debit > 0 || credit > 0)) {
            lines.push({
                account_id: accountId,
                description: description,
                debit: debit,
                credit: credit
            });
        }
    });

    if (lines.length < 2) {
        $.messager.alert('Validación', 'Debe tener al menos 2 líneas con valores', 'warning');
        return;
    }

    var data = {
        entry_date: $('#entry_date').val(),
        description: $('#description').val(),
        notes: $('#notes').val(),
        lines: lines
    };

    $.ajax({
        url: '{{ route('journal-entries.update', $entry->id) }}',
        method: 'PUT',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            setTimeout(function() {
                window.location.href = '{{ route('journal-entries.show', $entry->id) }}';
            }, 1500);
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al guardar';
            if (xhr.responseJSON?.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
            }
            $.messager.alert('Error', msg, 'error');
        }
    });
});
</script>
@endpush
