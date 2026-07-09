@extends('layouts.app')

@section('title', 'Perfil del Médico')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-2 p-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:64px;height:64px;background:#1266f1;color:#121212;font-size:1.5rem;font-weight:bold;overflow:hidden">
                        @if ($user->foto_url)
                            <img src="{{ Storage::url($user->foto_url) }}" alt="Foto"
                                 style="width:100%;height:100%;object-fit:cover;cursor:pointer"
                                 onclick="window.open('{{ Storage::url($user->foto_url) }}','_blank')">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h3 class="mb-1">{{ $user->name }}</h3>
                        <p class="mb-0 text-muted">{{ optional(optional($perfil)->tipoMedico)->nombre_tipo_medico ?? 'Sin especialidad' }}</p>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.medicos.edit', $user->id) }}" class="btn btn-outline-secondary"><i class="fa fa-pen-to-square me-1"></i>Editar</a>
                    <a href="{{ route('admin.medicos') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Volver</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-2 p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color:#1266f1">Información Personal</h5>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label text-muted small">Nombre</label>
                <p class="fw-bold mb-0">{{ $user->name }}</p>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label text-muted small">Email</label>
                <p class="fw-bold mb-0">{{ $user->email }}</p>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small">Fecha de Nacimiento</label>
                <p class="fw-bold mb-0">{{ optional($user->fecha_nacimiento)->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small">Teléfono</label>
                <p class="fw-bold mb-0">{{ $user->telefono ?? '—' }}</p>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small">Dirección</label>
                <p class="fw-bold mb-0">{{ $user->direccion ?? '—' }}</p>
            </div>
        </div>

        <h5 class="fw-bold mb-3 mt-4" style="color:#1266f1">Información Profesional</h5>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small">Especialidad</label>
                <p class="fw-bold mb-0">{{ optional(optional($perfil)->tipoMedico)->nombre_tipo_medico ?? '—' }}</p>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small">Cédula Profesional</label>
                <p class="fw-bold mb-0">{{ optional($perfil)->cedula_profesional ?? '—' }}</p>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small">Universidad</label>
                <p class="fw-bold mb-0">{{ optional($perfil)->universidad ?? '—' }}</p>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small">Años de Experiencia</label>
                <p class="fw-bold mb-0">{{ optional($perfil)->experiencia_anios ?? '—' }}</p>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small">Observaciones</label>
                <p class="fw-bold mb-0">{{ $user->observaciones ?? '—' }}</p>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small">Descripción</label>
                <p class="fw-bold mb-0">{{ optional($perfil)->descripcion ?? '—' }}</p>
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Estado</label>
                <p class="fw-bold mb-0">
                    @if (optional($perfil)->activo ?? true)
                        <span class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-check me-1"></i>Activo</span>
                    @else
                        <span class="badge" style="border:2px solid #ff4444;color:#ff4444;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-circle-xmark me-1"></i>Inactivo</span>
                    @endif
                </p>
            </div>
            <div class="col-6">
                <label class="form-label text-muted small">Aprobación</label>
                <p class="fw-bold mb-0">
                    @if (optional($perfil)->aprobado)
                        <span class="badge" style="border:2px solid #00b894;color:#00b894;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-check me-1"></i>Aprobado</span>
                    @else
                        <span class="badge" style="border:2px solid #ffc107;color:#ffc107;background:transparent;padding:0.5rem 0.75rem"><i class="fa fa-clock me-1"></i>Pendiente de aprobación</span>
                    @endif
                </p>
                @if (!optional($perfil)->aprobado)
                    <form action="{{ route('admin.medicos.aprobar', $user->id) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('¿Aprobar este médico? Se le notificará por correo.')">
                            <i class="fa fa-check me-1"></i>Aprobar médico
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    @include('estadisticas._charts', ['statsUrl' => route('estadisticas.admin', $user->id)])

    <div class="card shadow-2 p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color:#1266f1">Documentos</h5>
        @if ($documentos->isEmpty())
            <div class="d-flex flex-column align-items-center py-3"><i class="fa fa-file fa-2x text-muted opacity-50 mb-2"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">Este médico no ha subido documentos.</p></div>
        @else
            <div class="table-responsive">
                <table class="table neu-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Archivo</th>
                            <th>Tipo</th>
                            <th>Tamaño</th>
                            <th>Subido</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documentos as $doc)
                            <tr>
                                <td><strong>{{ $doc->nombre ?? $doc->nombre_original }}</strong></td>
                                <td><small class="text-muted">{{ $doc->nombre_original }}</small></td>
                                <td><small class="text-muted">{{ $doc->tipo_mime }}</small></td>
                                <td>
                                    @php
                                        $tamano = $doc->tamano;
                                        if ($tamano >= 1048576) {
                                            $tamanoStr = round($tamano / 1048576, 1) . ' MB';
                                        } elseif ($tamano >= 1024) {
                                            $tamanoStr = round($tamano / 1024, 1) . ' KB';
                                        } else {
                                            $tamanoStr = $tamano . ' B';
                                        }
                                    @endphp
                                    {{ $tamanoStr }}
                                </td>
                                <td><small class="text-muted">{{ $doc->created_at->format('d/m/Y H:i') }}</small></td>
                                <td>
                                    <a href="{{ Storage::url($doc->ruta_archivo) }}" target="_blank"
                                       class="btn btn-outline-secondary btn-sm" style="font-size:0.65rem;background:#1e90ff;color:#fff">Ver</a>
                                    <a href="{{ route('medico.documentos.download', $doc->id) }}"
                                       class="btn btn-outline-secondary btn-sm" style="font-size:0.65rem">Descargar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <br><br>
    </div>
</div>
@endsection
