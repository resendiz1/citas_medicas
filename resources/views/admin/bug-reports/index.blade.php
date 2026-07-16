@extends('layouts.app')

@section('title', 'Reportes de errores')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:#1266f1"><i class="fa fa-bug me-2"></i>Reportes de errores</h4>
    </div>

    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Buscar por título o usuario..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <option value="general" @selected(request('categoria')=='general')>General</option>
                    <option value="error_visual" @selected(request('categoria')=='error_visual')>Error visual / UI</option>
                    <option value="funcionalidad" @selected(request('categoria')=='funcionalidad')>Funcionalidad</option>
                    <option value="rendimiento" @selected(request('categoria')=='rendimiento')>Rendimiento</option>
                    <option value="seguridad" @selected(request('categoria')=='seguridad')>Seguridad</option>
                    <option value="otro" @selected(request('categoria')=='otro')>Otro</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" @selected(request('status')=='pendiente')>Pendiente</option>
                    <option value="en_revision" @selected(request('status')=='en_revision')>En revisión</option>
                    <option value="resuelto" @selected(request('status')=='resuelto')>Resuelto</option>
                    <option value="rechazado" @selected(request('status')=='rechazado')>Rechazado</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fa fa-search me-1"></i>Filtrar</button>
                @if (request('search') || request('categoria') || request('status'))
                    <a href="{{ route('admin.bug-reports') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-rotate-left me-1"></i>Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="card shadow-2 p-4">
        <div class="table-responsive">
            <table class="table neu-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bugReports as $report)
                        <tr>
                            <td class="text-muted">{{ $report->id }}</td>
                            <td>
                                <span class="fw-semibold">{{ $report->user->name }}</span>
                                <br><small class="text-muted">{{ $report->user->email }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $report->titulo }}</span>
                                <br><small class="text-muted">{{ Str::limit($report->descripcion, 100) }}</small>
                            </td>
                            <td>
                                @switch($report->categoria)
                                    @case('general') <span class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem">General</span> @break
                                    @case('error_visual') <span class="badge" style="border:2px solid #a855f7;color:#a855f7;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem">Visual / UI</span> @break
                                    @case('funcionalidad') <span class="badge" style="border:2px solid #f59e0b;color:#f59e0b;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem">Funcionalidad</span> @break
                                    @case('rendimiento') <span class="badge" style="border:2px solid #ef4444;color:#ef4444;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem">Rendimiento</span> @break
                                    @case('seguridad') <span class="badge" style="border:2px solid #dc2626;color:#dc2626;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem">Seguridad</span> @break
                                    @default <span class="badge" style="border:2px solid #6b7280;color:#6b7280;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem">Otro</span>
                                @endswitch
                            </td>
                            <td>
                                @switch($report->status)
                                    @case('pendiente') <span class="badge" style="border:2px solid #f59e0b;color:#f59e0b;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem"><i class="fa fa-clock me-1"></i>Pendiente</span> @break
                                    @case('en_revision') <span class="badge" style="border:2px solid #1266f1;color:#1266f1;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem"><i class="fa fa-search me-1"></i>En revisión</span> @break
                                    @case('resuelto') <span class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem"><i class="fa fa-check me-1"></i>Resuelto</span> @break
                                    @case('rechazado') <span class="badge" style="border:2px solid #ef4444;color:#ef4444;background:transparent;padding:0.4rem 0.6rem;font-size:0.65rem"><i class="fa fa-times me-1"></i>Rechazado</span> @break
                                @endswitch
                            </td>
                            <td class="text-muted">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm" data-mdb-toggle="modal" data-mdb-target="#modal-{{ $report->id }}"><i class="fa fa-reply me-1"></i>Responder</button>

                                <div id="modal-{{ $report->id }}" class="modal fade" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.bug-reports.responder', $report->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="fa fa-reply me-2"></i>Responder reporte #{{ $report->id }}</h5>
                                                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="text-muted mb-1"><strong>Usuario:</strong> {{ $report->user->name }} ({{ $report->user->email }})</p>
                                                    <p class="text-muted mb-3"><strong>Reporte:</strong> {{ $report->titulo }}</p>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nuevo estado</label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="pendiente" @selected($report->status=='pendiente')>Pendiente</option>
                                                            <option value="en_revision" @selected($report->status=='en_revision')>En revisión</option>
                                                            <option value="resuelto" @selected($report->status=='resuelto')>Resuelto</option>
                                                            <option value="rechazado" @selected($report->status=='rechazado')>Rechazado</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label fw-semibold">Mensaje de respuesta</label>
                                                        <textarea name="respuesta" rows="5" class="form-control" placeholder="Escribe tu respuesta al usuario..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-mdb-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane me-1"></i>Enviar respuesta</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5"><div class="d-flex flex-column align-items-center gap-2"><i class="fa fa-bug fa-2x text-muted opacity-50"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">No hay reportes de errores.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bugReports instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 d-flex justify-content-center">
                {{ $bugReports->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
