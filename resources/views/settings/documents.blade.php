@extends('layouts.app')

@section('title', 'Configuración de Documentos')
@section('page-title', 'Configuración de Documentos')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Numeración de Documentos</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            <i class="bi bi-plus-circle"></i> Nueva Configuración
        </button>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($settings->isEmpty())
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                No hay configuraciones de documentos. Cree una para comenzar.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipo de Documento</th>
                            <th>Prefijo</th>
                            <th>Serie</th>
                            <th>Siguiente N°</th>
                            <th>Relleno</th>
                            <th>Formato</th>
                            <th>Ejemplo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($settings as $setting)
                            <tr>
                                <td>{{ $documentTypes[$setting->document_type] ?? $setting->document_type }}</td>
                                <td>{{ $setting->prefix ?? '-' }}</td>
                                <td>{{ $setting->series ?? '-' }}</td>
                                <td>{{ $setting->next_number }}</td>
                                <td>{{ $setting->padding }} dígitos</td>
                                <td><code>{{ $setting->format }}</code></td>
                                <td>
                                    @php
                                        $parts = [];
                                        if($setting->prefix) $parts[] = $setting->prefix;
                                        if($setting->series) $parts[] = $setting->series;
                                        $parts[] = str_pad($setting->next_number, $setting->padding, '0', STR_PAD_LEFT);
                                        $example = implode('-', $parts);
                                    @endphp
                                    <span class="badge bg-secondary">{{ $example }}</span>
                                </td>
                                <td>
                                    @if($setting->is_active)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="editSetting({{ $setting->id }}, '{{ $setting->document_type }}', '{{ $setting->prefix }}', '{{ $setting->series }}', {{ $setting->next_number }}, {{ $setting->padding }}, '{{ $setting->format }}', {{ $setting->is_active ? 'true' : 'false' }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('settings.documents.destroy', $setting) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('¿Está seguro de eliminar esta configuración?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <hr class="my-4">

        <div class="alert alert-info">
            <h6><i class="bi bi-info-circle"></i> Información sobre la Numeración</h6>
            <ul class="mb-0">
                <li><strong>Prefijo:</strong> Texto que aparece al inicio del número (ej: FAC, REC, COM)</li>
                <li><strong>Serie:</strong> Serie o sucursal del documento (ej: A, B, 001, 002)</li>
                <li><strong>Siguiente N°:</strong> El próximo número que se asignará automáticamente</li>
                <li><strong>Relleno:</strong> Cantidad de dígitos con ceros a la izquierda (ej: 5 dígitos = 00001)</li>
                <li><strong>Formato:</strong> Combinación de prefijo-serie-número según lo configurado</li>
                <li><strong>Estado Activo:</strong> Solo una configuración por tipo de documento puede estar activa</li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal Crear -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('settings.documents.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Configuración de Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Documento <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="">Seleccione...</option>
                            @foreach($documentTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prefijo</label>
                            <input type="text" name="prefix" class="form-control" placeholder="FAC">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Serie</label>
                            <input type="text" name="series" class="form-control" placeholder="A">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Siguiente Número <span class="text-danger">*</span></label>
                            <input type="number" name="next_number" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Relleno (dígitos) <span class="text-danger">*</span></label>
                            <input type="number" name="padding" class="form-control" value="5" min="1" max="10" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Formato <span class="text-danger">*</span></label>
                        <input type="text" name="format" class="form-control" value="prefix-series-number" required>
                        <small class="text-muted">Puede usar: prefix-series-number, prefix-number, series-number</small>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active_create" checked>
                        <label class="form-check-label" for="is_active_create">
                            Configuración Activa
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
                    <h5 class="modal-title">Editar Configuración de Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Documento</label>
                        <input type="text" id="edit_document_type_label" class="form-control" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prefijo</label>
                            <input type="text" name="prefix" id="edit_prefix" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Serie</label>
                            <input type="text" name="series" id="edit_series" class="form-control">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Siguiente Número <span class="text-danger">*</span></label>
                            <input type="number" name="next_number" id="edit_next_number" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Relleno (dígitos) <span class="text-danger">*</span></label>
                            <input type="number" name="padding" id="edit_padding" class="form-control" min="1" max="10" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Formato <span class="text-danger">*</span></label>
                        <input type="text" name="format" id="edit_format" class="form-control" required>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="edit_is_active">
                        <label class="form-check-label" for="edit_is_active">
                            Configuración Activa
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
function editSetting(id, docType, prefix, series, nextNumber, padding, format, isActive) {
    const documentTypes = @json($documentTypes);

    document.getElementById('editForm').action = '/settings/documents/' + id;
    document.getElementById('edit_document_type_label').value = documentTypes[docType] || docType;
    document.getElementById('edit_prefix').value = prefix || '';
    document.getElementById('edit_series').value = series || '';
    document.getElementById('edit_next_number').value = nextNumber;
    document.getElementById('edit_padding').value = padding;
    document.getElementById('edit_format').value = format;
    document.getElementById('edit_is_active').checked = isActive;

    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endpush
@endsection
