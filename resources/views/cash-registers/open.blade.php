@extends('layouts.app')

@section('title', 'Abrir Caja')
@section('page-title', 'Abrir Caja')

@section('content')
<div class="card" style="max-width: 600px; margin: 50px auto;">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Apertura de Caja</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No hay una caja abierta para hoy. Por favor, ingrese el monto inicial para abrir la caja.
        </div>

        <form id="openForm">
            @csrf
            <div class="mb-3">
                <label class="form-label">Fecha</label>
                <input type="date" class="form-control" value="{{ date('Y-m-d') }}" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Saldo Inicial (Gs.) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="opening_balance" step="1" min="0" value="0" required autofocus>
                <small class="text-muted">Ingrese el monto en efectivo con el que inicia la caja</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Notas</label>
                <textarea class="form-control" id="notes" rows="2"></textarea>
            </div>
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-primary btn-lg" onclick="openCashRegister()">
                    <i class="bi bi-unlock"></i> Abrir Caja
                </button>
                <a href="{{ route('cash-registers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function openCashRegister() {
    if (!$('#openForm')[0].checkValidity()) {
        $('#openForm')[0].reportValidity();
        return;
    }

    var data = {
        opening_balance: $('#opening_balance').val(),
        notes: $('#notes').val()
    };

    $.ajax({
        url: '{{ route('cash-registers.open') }}',
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: data,
        success: function(response) {
            $.messager.alert('Éxito', response.message, 'info', function() {
                window.location.href = '{{ route('cash-registers.current') }}';
            });
        },
        error: function(xhr) {
            var msg = xhr.responseJSON?.message || 'Error al abrir la caja';
            $.messager.alert('Error', msg, 'error');
        }
    });
}
</script>
@endsection
