@extends('layouts.app')

@section('title', 'Historial de Sesiones')
@section('page-title', 'Historial de Sesiones')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial Completo de Inicios de Sesión</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>IP</th>
                        <th>Navegador / Dispositivo</th>
                        <th>Estado</th>
                        <th>Motivo (si falló)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loginLogs as $log)
                        <tr class="{{ $log->status === 'failed' ? 'table-danger' : '' }}">
                            <td>{{ $log->logged_at->format('d/m/Y H:i:s') }}</td>
                            <td><code>{{ $log->ip_address }}</code></td>
                            <td>
                                <small class="text-muted">{{ $log->user_agent }}</small>
                            </td>
                            <td>
                                @if($log->status === 'success')
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Exitoso
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="bi bi-x-circle"></i> Fallido
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($log->status === 'failed')
                                    <span class="text-danger">{{ $log->failure_reason }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay registros de sesión</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="mt-3">
            {{ $loginLogs->links() }}
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver al Perfil
    </a>
</div>
@endsection
