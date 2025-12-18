@extends('layouts.app')

@section('title', 'Caja del Día')
@section('page-title', 'Caja del Día')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Caja Abierta - {{ $register->register_number }}</h5>
        <div>
            <button type="button" class="btn btn-light btn-sm" onclick="window.location.href='{{ route('cash-registers.index') }}'">
                <i class="bi bi-list"></i> Historial
            </button>
            <button type="button" class="btn btn-warning" onclick="closeCashRegister()">
                <i class="bi bi-lock"></i> Cerrar Caja
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <small class="text-muted">Saldo Inicial</small>
                        <h4 class="mb-0">{{ number_format($register->opening_balance, 0, ',', '.') }}</h4>
                        <small>Gs.</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <small>Ingresos</small>
                        <h4 class="mb-0">{{ number_format($register->sales_cash + $register->collections, 0, ',', '.') }}</h4>
                        <small>Gs.</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <small>Egresos</small>
                        <h4 class="mb-0">{{ number_format($register->payments + $register->expenses, 0, ',', '.') }}</h4>
                        <small>Gs.</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <small>Saldo Esperado</small>
                        <h4 class="mb-0">{{ number_format($register->expected_balance, 0, ',', '.') }}</h4>
                        <small>Gs.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-12">
                <button type="button" class="btn btn-success" onclick="showMovementDialog('income')">
                    <i class="bi bi-plus-circle"></i> Registrar Ingreso
                </button>
                <button type="button" class="btn btn-danger" onclick="showMovementDialog('expense')">
                    <i class="bi bi-dash-circle"></i> Registrar Egreso
                </button>
            </div>
        </div>

        <h6>Movimientos del Día</h6>
        <table class="table table-bordered table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Concepto</th>
                    <th>Descripción</th>
                    <th>Referencia</th>
                    <th class="text-end">Monto</th>
                </tr>
            </thead>
            <tbody>
                @forelse($register->movements as $movement)
                <tr class="{{ $movement->type === 'income' ? 'table-success' : 'table-danger' }}">
                    <td>{{ $movement->created_at->format('H:i') }}</td>
                    <td>
                        @if($movement->type === 'income')
                            <span class="badge bg-success">Ingreso</span>
                        @else
                            <span class="badge bg-danger">Egreso</span>
                        @endif
                    </td>
                    <td>
                        @if($movement->concept === 'sale') Venta
                        @elseif($movement->concept === 'collection') Cobro
                        @elseif($movement->concept === 'payment') Pago
                        @elseif($movement->concept === 'expense') Gasto
                        @else Otro
                        @endif
                    </td>
                    <td>{{ $movement->description }}</td>
                    <td>{{ $movement->reference }}</td>
                    <td class="text-end">
                        @if($movement->type === 'income')
                            <span class="text-success">+{{ number_format($movement->amount, 0, ',', '.') }}</span>
                        @else
                            <span class="text-danger">-{{ number_format($movement->amount, 0, ',', '.') }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No hay movimientos registrados</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Movement Dialog -->
<div id="movementDlg" class="easyui-dialog" style="width:500px;padding:20px;" closed="true" buttons="#movementDlg-buttons">
    <h5 id="movementTitle" class="mb-3"></h5>
    <form id="movementForm">
        <input type="hidden" id="movement_type">
        <div class="mb-3">
            <label class="form-label">Concepto <span class="text-danger">*</span></label>
            <select class="form-select" id="concept" required>
                <option value="">Seleccione...</option>
                <option value="sale">Venta</option>
                <option value="collection">Cobro</option>
                <option value="payment">Pago</option>
                <option value="expense">Gasto</option>
                <option value="other">Otro</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Descripción <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="description" maxlength="255" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Monto (Gs.) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="amount" step="1" min="1" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Referencia</label>
            <input type="text" class="form-control" id="reference" maxlength="100">
        </div>
    </form>
</div>
<div id="movementDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitMovement()">Guardar</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#movementDlg').dialog('close')">Cancelar</a>
</div>

<!-- Close Dialog -->
<div id="closeDlg" class="easyui-dialog" style="width:400px;padding:20px;" closed="true" buttons="#closeDlg-buttons">
    <h5 class="mb-3"><i class="bi bi-lock"></i> Cerrar Caja</h5>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle"></i> Está a punto de cerrar la caja. Esta acción no se puede deshacer.
    </div>
    <form id="closeForm">
        <div class="mb-3">
            <label class="form-label"><strong>Saldo Esperado:</strong></label>
            <h4 class="text-primary">{{ number_format($register->expected_balance, 0, ',', '.') }} Gs.</h4>
        </div>
        <div class="mb-3">
            <label class="form-label">Saldo Real (Efectivo contado) <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="actual_balance" step="1" min="0" required>
        </div>
        <div id="difference_display" class="mb-3" style="display:none;">
            <label class="form-label"><strong>Diferencia:</strong></label>
            <h4 id="difference_value"></h4>
        </div>
    </form>
</div>
<div id="closeDlg-buttons">
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-ok" onclick="submitClose()">Cerrar Caja</a>
    <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" onclick="$('#closeDlg').dialog('close')">Cancelar</a>
</div>

<script>
function showMovementDialog(type) {
    $('#movement_type').val(type);
    $('#movementTitle').text(type === 'income' ? 'Registrar Ingreso' : 'Registrar Egreso');
    $('#movementForm')[0].reset();
    $('#movementDlg').dialog('open');
}

function submitMovement() {
    if (!$('#movementForm')[0].checkValidity()) {
        $('#movementForm')[0].reportValidity();
        return;
    }

    var data = {
        type: $('#movement_type').val(),
        concept: $('#concept').val(),
        description: $('#description').val(),
        amount: $('#amount').val(),
        reference: $('#reference').val()
    };

    $.ajax({
        url: '{{ route('cash-registers.add-movement', $register->id) }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.show({ title: 'Éxito', msg: response.message, timeout: 3000, showType: 'slide' });
            $('#movementDlg').dialog('close');
            location.reload();
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al registrar movimiento';
            $.messager.alert('Error', msg, 'error');
        }
    });
}

function closeCashRegister() {
    $('#closeForm')[0].reset();
    $('#difference_display').hide();
    $('#closeDlg').dialog('open');
}

$('#actual_balance').on('input', function() {
    var expected = {{ $register->expected_balance }};
    var actual = parseFloat($(this).val()) || 0;
    var difference = actual - expected;

    if (actual > 0) {
        $('#difference_display').show();
        var formatted = Math.abs(difference).toLocaleString('es-PY', {minimumFractionDigits: 0, maximumFractionDigits: 0});

        if (difference > 0) {
            $('#difference_value').html('<span class="text-success">+' + formatted + ' Gs. (Sobrante)</span>');
        } else if (difference < 0) {
            $('#difference_value').html('<span class="text-danger">-' + formatted + ' Gs. (Faltante)</span>');
        } else {
            $('#difference_value').html('<span class="text-success">Sin diferencia</span>');
        }
    }
});

function submitClose() {
    if (!$('#closeForm')[0].checkValidity()) {
        $('#closeForm')[0].reportValidity();
        return;
    }

    $.messager.confirm('Confirmar', '¿Está seguro que desea cerrar la caja?', function(r) {
        if (r) {
            var data = {
                actual_balance: $('#actual_balance').val()
            };

            $.ajax({
                url: '{{ route('cash-registers.close', $register->id) }}',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: data,
                success: function(response) {
                    $.messager.alert('Éxito', response.message, 'info', function() {
                        window.location.href = '{{ route('cash-registers.show', $register->id) }}';
                    });
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Error al cerrar la caja';
                    $.messager.alert('Error', msg, 'error');
                }
            });
        }
    });
}
</script>
@endsection
