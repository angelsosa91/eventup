@extends('layouts.app')

@section('title', 'Nuevo Gasto')
@section('page-title', 'Nuevo Gasto')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Nuevo Gasto - {{ $expenseNumber }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('expenses.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            <button type="button" class="btn btn-primary" onclick="saveExpense()">
                <i class="bi bi-save"></i> Guardar
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="expenseForm">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="expense_date" name="expense_date"
                           value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Categoría <span class="text-danger">*</span></label>
                    <input id="expense_category_id" name="expense_category_id" style="width: 100%;">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Proveedor</label>
                    <input id="supplier_id" name="supplier_id" style="width: 100%;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Descripción <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="description" name="description" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nro. Documento</label>
                    <input type="text" class="form-control" id="document_number" name="document_number" maxlength="50">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Monto <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="0.01" required onchange="calculateTax()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">IVA <span class="text-danger">*</span></label>
                    <select class="form-select" id="tax_rate" name="tax_rate" onchange="calculateTax()">
                        <option value="10">10%</option>
                        <option value="5">5%</option>
                        <option value="0">Exento</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">IVA Calculado</label>
                    <input type="text" class="form-control" id="tax_display" readonly>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Forma de Pago</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Cheque">Cheque</option>
                        <option value="Tarjeta">Tarjeta</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select class="form-select" id="status" name="status">
                        <option value="pending">Pendiente</option>
                        <option value="paid">Pagado</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Notas</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    // ComboGrid de categorías de gastos
    $('#expense_category_id').combogrid({
        panelWidth: 400,
        idField: 'id',
        textField: 'name',
        url: '{{ route('expense-categories.list') }}',
        mode: 'remote',
        delay: 500,
        fitColumns: true,
        required: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('expense-categories.list') }}',
                data: { q: param.q || '' },
                dataType: 'json',
                success: function(data) {
                    success(data);
                },
                error: function() {
                    error.apply(this, arguments);
                }
            });
        },
        columns: [[
            {field: 'name', title: 'Nombre', width: 300}
        ]]
    });

    // ComboGrid de proveedores
    $('#supplier_id').combogrid({
        panelWidth: 500,
        idField: 'id',
        textField: 'name',
        url: '{{ route('suppliers.list') }}',
        mode: 'remote',
        delay: 500,
        fitColumns: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('suppliers.list') }}',
                data: { q: param.q || '' },
                dataType: 'json',
                success: function(data) {
                    success(data);
                },
                error: function() {
                    error.apply(this, arguments);
                }
            });
        },
        columns: [[
            {field: 'name', title: 'Nombre', width: 200},
            {field: 'ruc', title: 'RUC', width: 100},
            {field: 'phone', title: 'Teléfono', width: 100}
        ]]
    });

    calculateTax();
});

function calculateTax() {
    var amount = parseFloat($('#amount').val()) || 0;
    var taxRate = parseInt($('#tax_rate').val()) || 0;
    var taxAmount = 0;

    if (taxRate > 0) {
        taxAmount = amount * taxRate / (100 + taxRate);
    }

    $('#tax_display').val(formatCurrency(taxAmount));
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

function saveExpense() {
    var categoryId = $('#expense_category_id').combogrid('getValue');
    if (!categoryId) {
        $.messager.alert('Error', 'Seleccione una categoría', 'error');
        return;
    }

    var description = $('#description').val().trim();
    if (!description) {
        $.messager.alert('Error', 'Ingrese una descripción', 'error');
        return;
    }

    var amount = parseFloat($('#amount').val()) || 0;
    if (amount <= 0) {
        $.messager.alert('Error', 'El monto debe ser mayor a 0', 'error');
        return;
    }

    var data = {
        expense_date: $('#expense_date').val(),
        expense_category_id: categoryId,
        supplier_id: $('#supplier_id').combogrid('getValue') || null,
        document_number: $('#document_number').val(),
        description: description,
        amount: amount,
        tax_rate: $('#tax_rate').val(),
        payment_method: $('#payment_method').val(),
        status: $('#status').val(),
        notes: $('#notes').val()
    };

    $.ajax({
        url: '{{ route('expenses.store') }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            $.messager.alert('Éxito', response.message, 'info', function() {
                window.location.href = '{{ route('expenses.index') }}';
            });
        },
        error: function(xhr) {
            var errors = xhr.responseJSON.errors;
            var message = '';
            for (var key in errors) {
                message += errors[key].join('<br>') + '<br>';
            }
            $.messager.alert('Error', message, 'error');
        }
    });
}
</script>
@endpush
