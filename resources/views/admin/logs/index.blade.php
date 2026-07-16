@extends('layouts.app')

@section('title', 'Logs de Actividad')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0" style="color:var(--text-primary)"><i class="fa fa-clipboard-list me-2"></i>Logs de Actividad</h4>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="location.reload()"><i class="fa fa-rotate me-1"></i>Recargar</button>
        </div>
    </div>

    <div class="card shadow-2 p-3 mb-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small mb-1">Buscar</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="IP, URL, SO, navegador, usuario..." value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small mb-1">Usuario</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small mb-1">Acción</label>
                <select name="accion" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="visita" {{ request('accion') == 'visita' ? 'selected' : '' }}>Visita</option>
                    <option value="create" {{ request('accion') == 'create' ? 'selected' : '' }}>Crear</option>
                    <option value="update" {{ request('accion') == 'update' ? 'selected' : '' }}>Actualizar</option>
                    <option value="delete" {{ request('accion') == 'delete' ? 'selected' : '' }}>Eliminar</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small mb-1">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm" value="{{ request('desde') }}">
            </div>
            <div class="col-6 col-md-1 d-grid">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i></button>
            </div>
        </form>
    </div>

    <div class="card shadow-2 p-0">
        <div class="table-responsive">
            <table class="table neu-table align-middle mb-0" style="font-size:0.82rem">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>IP</th>
                        <th>SO</th>
                        <th>Navegador</th>
                        <th>Acción</th>
                        <th>URL / Ruta</th>
                        <th>Ubicación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                    <tr>
                        <td style="white-space:nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>
                            @if ($log->user)
                                <span class="fw-bold">{{ $log->user->name }}</span>
                                <br><small class="text-muted" style="font-size:0.7rem">{{ $log->user->role }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><code style="font-size:0.75rem">{{ $log->ip ?? '—' }}</code></td>
                        <td>{{ $log->so ?? '—' }}</td>
                        <td>{{ $log->navegador ?? '—' }}</td>
                        <td>
                            @switch($log->accion)
                                @case('visita') <span class="badge bg-transparent border border-secondary text-secondary" style="font-size:0.7rem">VISITA</span> @break
                                @case('create') <span class="badge bg-success" style="font-size:0.7rem">CREAR</span> @break
                                @case('update') <span class="badge bg-primary" style="font-size:0.7rem">ACTUALIZAR</span> @break
                                @case('delete') <span class="badge bg-danger" style="font-size:0.7rem">ELIMINAR</span> @break
                                @default <span class="badge bg-secondary" style="font-size:0.7rem">{{ $log->accion }}</span>
                            @endswitch
                        </td>
                        <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="{{ $log->url }}">
                            @if ($log->route_name)
                                <small class="text-muted" style="font-size:0.65rem">{{ $log->route_name }}</small><br>
                            @endif
                            <span style="font-size:0.72rem">{{ Str::limit($log->url, 60) }}</span>
                        </td>
                        <td style="font-size:0.7rem;white-space:nowrap">
                            @if ($log->lat && $log->lng)
                                <a href="https://www.google.com/maps?q={{ $log->lat }},{{ $log->lng }}" target="_blank" class="text-decoration-none">
                                    <i class="fa fa-map-marker-alt text-danger me-1"></i>{{ number_format($log->lat, 2) }}, {{ number_format($log->lng, 2) }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="fa fa-clipboard-list fa-2x text-muted opacity-50 mb-2"></i>
                            <p class="fw-bold text-muted mb-0">No hay registros de actividad.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
