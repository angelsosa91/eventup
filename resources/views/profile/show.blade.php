@extends('layouts.app')

@section('title', 'Mi Perfil')
@section('page-title', 'Mi Perfil')

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <!-- Información del Perfil -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Información Personal</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <input type="text" class="form-control" value="{{ $user->role->name ?? 'Sin rol' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Empresa</label>
                        <input type="text" class="form-control" value="{{ $user->tenant->name ?? 'Sin empresa' }}" disabled>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Actualizar Perfil
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cambiar Contraseña -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Cambiar Contraseña</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update-password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" name="current_password"
                               class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Mínimo 8 caracteres</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key"></i> Cambiar Contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Sesiones -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Últimas Sesiones</h5>
        <a href="{{ route('profile.login-history') }}" class="btn btn-sm btn-outline-primary">
            Ver Historial Completo
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>IP</th>
                        <th>Navegador</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loginLogs as $log)
                        <tr>
                            <td>{{ $log->logged_at->format('d/m/Y H:i:s') }}</td>
                            <td><code>{{ $log->ip_address }}</code></td>
                            <td>
                                <small class="text-muted">{{ Str::limit($log->user_agent, 50) }}</small>
                            </td>
                            <td>
                                @if($log->status === 'success')
                                    <span class="badge bg-success">Exitoso</span>
                                @else
                                    <span class="badge bg-danger">Fallido</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay registros de sesión</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
