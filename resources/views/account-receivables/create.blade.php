@extends('layouts.app')

@section('title', 'Nueva Cuenta por Cobrar')
@section('page-title', 'Nueva Cuenta por Cobrar')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Nueva Cuenta por Cobrar - {{ $documentNumber }}</h5>
        <div>
            <button type="button" class="btn btn-secondary" onclick="window.location.href='{{ route('account-receivables.index') }}'">
                <i class="bi bi-arrow-left"></i> Volver
            </button>
            <button type="button" class="btn btn-primary" onclick="saveReceivable()">
                <i class="bi bi-save"></i> Guardar
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="receivableForm">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha de Documento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="document_date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha de Vencimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cliente <span class="text-danger">*</span></label>
                    <input id="customer_search" style="width: 100%;height:38px;">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label">Monto <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="amount" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Descripción</label>
                    <input type="text" class="form-control" id="description" maxlength="255">
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
var selectedCustomer = null;

$(function() {
    $('#customer_search').combogrid({
        panelWidth: 600,
        idField: 'id',
        textField: 'name',
        url: '{{ route('customers.list') }}',
        mode: 'remote',
        delay: 500,
        fitColumns: true,
        loader: function(param, success, error) {
            $.ajax({
                url: '{{ route('customers.list') }}',
                data: { q: param.q || '' },
                dataType: 'json',
                success: function(data) { success(data); },
                error: function() { error.apply(this, arguments); }
            });
        },
        columns: [[
            {field: 'code', title: 'Código', width: 80},
            {field: 'name', title: 'Nombre', width: 250},
            {field: 'ruc', title: 'RUC/CI', width: 120}
        ]],
        onSelect: function(index, row) {
            selectedCustomer = row;
        }
    });
});

function saveReceivable() {
    if (!selectedCustomer) {
        $.messager.alert('Información', 'Seleccione un cliente', 'info');
        return;
    }

    if (!$('#receivableForm')[0].checkValidity()) {
        $('#receivableForm')[0].reportValidity();
        return;
    }

    var data = {
        document_date: $('#document_date').val(),
        due_date: $('#due_date').val(),
        customer_id: selectedCustomer.id,
        amount: $('#amount').val(),
        description: $('#description').val()
    };

    $.ajax({
        url: '{{ route('account-receivables.store') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.alert('Éxito', response.message, 'info', function() {
                window.location.href = '{{ route('account-receivables.index') }}';
            });
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join('<br>') : 'Error al guardar';
            $.messager.alert('Error', msg, 'error');
        }
    });
}
</script>
@endpush
