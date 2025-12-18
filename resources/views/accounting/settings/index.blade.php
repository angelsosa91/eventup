@extends('layouts.app')

@section('title', 'Configuración Contable')
@section('page-title', 'Configuración Contable')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Configuración de Cuentas Contables Automáticas</h5>
        <small class="text-muted">Configure las cuentas contables que se utilizarán automáticamente en las transacciones del sistema</small>
    </div>
    <div class="card-body">
        <form id="settingsForm">
            @csrf

            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Importante:</strong> Estas cuentas se utilizarán automáticamente cuando se confirmen ventas, compras, pagos y otros movimientos en el sistema.
            </div>

            <!-- Ventas -->
            <h5 class="mt-4 mb-3"><i class="bi bi-cart-check"></i> Ventas</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cuenta de Ingresos por Ventas</label>
                    <select class="form-select" name="settings[sales_income]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Se acreditará al confirmar una venta</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cuenta de IVA Ventas</label>
                    <select class="form-select" name="settings[sales_tax]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para registrar el IVA de las ventas</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cuenta de Descuentos en Ventas</label>
                    <select class="form-select" name="settings[sales_discount]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para registrar descuentos otorgados</small>
                </div>
            </div>

            <!-- Compras -->
            <h5 class="mt-4 mb-3"><i class="bi bi-bag"></i> Compras</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cuenta de Compras / Costo de Ventas</label>
                    <select class="form-select" name="settings[purchases_expense]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Se debitará al confirmar una compra</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cuenta de IVA Compras</label>
                    <select class="form-select" name="settings[purchases_tax]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para registrar el IVA de las compras</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Cuenta de Descuentos en Compras</label>
                    <select class="form-select" name="settings[purchases_discount]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para registrar descuentos recibidos</small>
                </div>
            </div>

            <!-- Cuentas por Cobrar y Pagar -->
            <h5 class="mt-4 mb-3"><i class="bi bi-wallet2"></i> Cuentas por Cobrar y Pagar</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cuenta de Cuentas por Cobrar</label>
                    <select class="form-select" name="settings[accounts_receivable]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para registrar ventas a crédito</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cuenta de Cuentas por Pagar</label>
                    <select class="form-select" name="settings[accounts_payable]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para registrar compras a crédito</small>
                </div>
            </div>

            <!-- Caja y Bancos -->
            <h5 class="mt-4 mb-3"><i class="bi bi-cash-coin"></i> Caja y Bancos</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cuenta de Caja</label>
                    <select class="form-select" name="settings[cash]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para movimientos de efectivo</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cuenta de Banco por Defecto</label>
                    <select class="form-select" name="settings[bank_default]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para movimientos bancarios</small>
                </div>
            </div>

            <!-- Inventario y Gastos -->
            <h5 class="mt-4 mb-3"><i class="bi bi-box-seam"></i> Inventario y Gastos</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cuenta de Inventario</label>
                    <select class="form-select" name="settings[inventory]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para el control de inventarios</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cuenta de Gastos por Defecto</label>
                    <select class="form-select" name="settings[expenses_default]">
                        <option value="">Seleccione una cuenta...</option>
                    </select>
                    <small class="text-muted">Para gastos generales</small>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var accounts = [];
var settings = @json($settings);

$(document).ready(function() {
    loadAccounts();
});

function loadAccounts() {
    $.ajax({
        url: '{{ route('account-chart.detail-accounts') }}',
        method: 'GET',
        success: function(data) {
            accounts = data;
            populateSelects();
        },
        error: function() {
            $.messager.alert('Error', 'Error al cargar las cuentas', 'error');
        }
    });
}

function populateSelects() {
    var accountOptions = '<option value="">Seleccione una cuenta...</option>';
    accounts.forEach(function(account) {
        accountOptions += '<option value="' + account.id + '">' + account.name + '</option>';
    });

    $('select[name^="settings"]').each(function() {
        var $select = $(this);
        var fieldName = $select.attr('name').match(/\[(.*?)\]/)[1];

        $select.html(accountOptions);

        // Establecer valor actual si existe
        if (settings[fieldName] && settings[fieldName].account_id) {
            $select.val(settings[fieldName].account_id);
        }
    });
}

$('#settingsForm').submit(function(e) {
    e.preventDefault();

    var formData = $(this).serialize();

    $.ajax({
        url: '{{ route('accounting-settings.update') }}',
        method: 'POST',
        data: formData,
        success: function(response) {
            $.messager.show({
                title: 'Éxito',
                msg: response.message,
                timeout: 3000,
                showType: 'slide'
            });
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
