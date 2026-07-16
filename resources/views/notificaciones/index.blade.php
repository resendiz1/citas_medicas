@extends('layouts.app')

@section('title', 'Historial de Notificaciones')

@push('head')
<style>
.estado-dot { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:6px }
.pendiente { background:#ffc107 }
.confirmada { background:#00b894 }
.en_espera { background:#0dcaf0 }
.en_consulta { background:#0d6efd }
.finalizada { background:#6c757d }
.cancelada { background:#dc3545 }
.no_asistio { background:#dc143c }
.reprogramada { background:#fd7e14 }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card shadow-2 p-4">
                <h4 class="mb-4 fw-bold"><i class="fa fa-bell me-2"></i>Historial de Notificaciones</h4>

                @if ($notifications->isEmpty())
                    <p class="text-muted text-center py-4">No tienes notificaciones.</p>
                @else
                    <div class="list-group">
                        @foreach ($notifications as $n)
                            <a href="{{ route('citas.show', $n->data['cita_id'] ?? 0) }}"
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-start {{ $n->read_at ? '' : 'fw-bold' }}">
                                <div class="ms-2 me-auto">
                                    <div>
                                        @if ($n->data['estado'] ?? null)
                                            <span class="estado-dot {{ $n->data['estado'] }}"></span>
                                        @endif
                                        {{ $n->data['message'] ?? 'Notificación' }}
                                        @if ($n->data['estado'] ?? null)
                                            <small class="text-muted">({{ $n->data['estado'] }})</small>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $n->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                @if (!$n->read_at)
                                    <span class="badge bg-primary rounded-pill">Nueva</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection