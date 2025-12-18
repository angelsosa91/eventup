@extends('layouts.app')

@section('title', 'Configuración de Impuestos')
@section('page-title', 'Configuración de Impuestos')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tasas de Impuestos (IVA)</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-circle"></i> Nuevo Impuesto
        </button>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tasa (%)</th>
                        <th>Código</th>
                        <th>Estado</th>
                        <th>Predeterminado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taxes as $tax)
                        <tr>
                            <td>{{ $tax->name }}</td>
                            <td><span class="badge bg-info">{{ number_format($tax->rate, 2) }}%</span></td>
                            <td><code>{{ $tax->code ?? '-' }}</code></td>
                            <td>
                                @if($tax->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                @if($tax->is_default)
                                    <i class="bi bi-check-circle-fill text-success"></i> Sí
                                @else
                                    <i class="bi bi-circle text-muted"></i> No
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="editTax({{ $tax->id }}, '{{ $tax->name }}', {{ $tax->rate }}, '{{ $tax->code }}', {{ $tax->is_default ? 'true' : 'false' }}, {{ $tax->is_active ? 'true' : 'false' }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('settings.taxes.destroy', $tax) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('¿Está seguro de eliminar este impuesto?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No hay impuestos configurados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <hr class="my-4">

        <div class="alert alert-info">
            <h6><i class="bi bi-info-circle"></i> Información sobre Impuestos</h6>
            <ul class="mb-0">
                <li><strong>Tasa:</strong> Porcentaje del impuesto (0, 5, 10, etc.)</li>
                <li><strong>Código:</strong> Identificador único para el impuesto (IVA10, IVA5, EXE)</li>
                <li><strong>Predeterminado:</strong> El impuesto que se aplicará por defecto en nuevos productos y transacciones</li>
                <li><strong>Paraguay:</strong> Las tasas de IVA estándar son 10% (general), 5% (reducido) y 0% (exento)</li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('settings.taxes.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Impuesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="IVA 10%" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tasa (%) <span class="text-danger">*</span></label>
                            <input type="number" name="rate" class="form-control" step="0.01" min="0" max="100" placeholder="10" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" name="code" class="form-control" placeholder="IVA10" maxlength="10">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input" id="is_default_create">
                        <label class="form-check-label" for="is_default_create">
                            Marcar como predeterminado
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active_create" checked>
                        <label class="form-check-label" for="is_active_create">
                            Impuesto activo
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Impuesto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tasa (%) <span class="text-danger">*</span></label>
                            <input type="number" name="rate" id="edit_rate" class="form-control" step="0.01" min="0" max="100" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" name="code" id="edit_code" class="form-control" maxlength="10">
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_default" value="1" class="form-check-input" id="edit_is_default">
                        <label class="form-check-label" for="edit_is_default">
                            Marcar como predeterminado
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_is_active">
                        <label class="form-check-label" for="edit_is_active">
                            Impuesto activo
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editTax(id, name, rate, code, isDefault, isActive) {
    document.getElementById('editForm').action = '/settings/taxes/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_rate').value = rate;
    document.getElementById('edit_code').value = code || '';
    document.getElementById('edit_is_default').checked = isDefault;
    document.getElementById('edit_is_active').checked = isActive;

    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
@endsection
