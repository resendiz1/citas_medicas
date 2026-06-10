@extends('layouts.app')

@section('title', 'Perfil del Médico')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Perfil del Médico</h4>
                <div>
                    <a href="{{ route('citas.create', ['medico_id' => $medico->id]) }}" class="neu-btn neu-btn-primary neu-btn-sm me-2">Solicitar cita</a>
                    <a href="{{ route('dashboard') }}" class="neu-btn neu-btn-sm">Volver</a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="neu-card p-4 d-flex align-items-center gap-3" style="border-radius:16px;">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:64px;height:64px;background:var(--yellow);color:#121212;font-size:1.5rem;font-weight:bold;overflow:hidden">
                            @if ($medico->foto_url)
                                <img src="{{ Storage::url($medico->foto_url) }}" alt="Foto"
                                     style="width:100%;height:100%;object-fit:cover;cursor:pointer"
                                     onclick="window.open('{{ Storage::url($medico->foto_url) }}','_blank')">
                            @else
                                {{ strtoupper(substr($medico->name, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1" style="color:var(--text-emphasis)">{{ $medico->name }}</h5>
                            <p class="text-muted mb-0">{{ optional(optional($medico->medicoPerfil)->tipoMedico)->nombre_tipo_medico ?? 'General' }} · {{ $medico->email }}</p>
                            @if (isset($medico->medicoPerfil->activo) && $medico->medicoPerfil->activo)
                                <span class="neu-badge mt-1" style="background:#00b894;color:#fff;font-size:0.65rem">Activo</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="neu-card p-4" style="border-radius:16px;">
                        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Información Personal</h6>
                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Nombre</label>
                                <p class="fw-bold mb-0">{{ $medico->name }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Email</label>
                                <p class="fw-bold mb-0">{{ $medico->email }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Teléfono</label>
                                <p class="fw-bold mb-0">{{ $medico->telefono ?? '—' }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Dirección</label>
                                <p class="fw-bold mb-0">{{ $medico->direccion ?? '—' }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Especialidad</label>
                                <p class="fw-bold mb-0">{{ optional(optional($medico->medicoPerfil)->tipoMedico)->nombre_tipo_medico ?? '—' }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Cédula Profesional</label>
                                <p class="fw-bold mb-0">{{ optional($medico->medicoPerfil)->cedula_profesional ?? '—' }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Universidad</label>
                                <p class="fw-bold mb-0">{{ optional($medico->medicoPerfil)->universidad ?? '—' }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Años de Experiencia</label>
                                <p class="fw-bold mb-0">{{ optional($medico->medicoPerfil)->experiencia_anios ?? '—' }}</p>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="form-label text-muted small">Estado</label>
                                <p class="fw-bold mb-0">
                                    @if (isset($medico->medicoPerfil->activo) && $medico->medicoPerfil->activo)
                                        <span class="neu-badge" style="background:#00b894;color:#fff">Activo</span>
                                    @else
                                        <span class="neu-badge" style="background:#ff4444;color:#fff">Inactivo</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small">Descripción</label>
                                <p class="fw-bold mb-0">{{ optional($medico->medicoPerfil)->descripcion ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @php $docs = optional(optional($medico->medicoPerfil)->documentos); @endphp
                @if ($docs && $docs->isNotEmpty())
                <div class="col-12">
                    <div class="neu-card p-4" style="border-radius:16px;">
                        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Documentos</h6>
                        <div class="table-responsive">
                            <table class="table neu-table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Archivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($docs as $doc)
                                    <tr>
                                        <td>{{ $doc->nombre ?? $doc->nombre_original }}</td>
                                        <td>
                                            <a href="{{ Storage::url($doc->ruta_archivo) }}" target="_blank"
                                               class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#1e90ff;color:#fff">Ver</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <br><br><br><br>
                    </div>
                </div>
                @endif

                <div class="col-12">
                    @if ($medico->horarios->where('activo', true)->count())
                    <div class="neu-card p-4 mb-4" style="border-radius:16px;">
                        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Horarios de atención</h6>
                        <div class="table-responsive">
                            <table class="table neu-table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $dias = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
                                    @endphp
                                    @foreach ($medico->horarios->where('activo', true)->sortBy('dia_semana') as $horario)
                                    <tr>
                                        <td>{{ $dias[$horario->dia_semana] ?? $horario->dia_semana }}</td>
                                        <td>{{ substr($horario->hora_inicio, 0, 5) }}</td>
                                        <td>{{ substr($horario->hora_fin, 0, 5) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <br><br><br><br>
                    </div>
                    @endif

                    @if ($medico->bloqueos->count())
                    <div class="neu-card p-4 mb-4" style="border-radius:16px;">
                        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Bloqueos de disponibilidad</h6>
                        <div class="table-responsive">
                            <table class="table neu-table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Inicio</th>
                                        <th>Fin</th>
                                        @if ($medico->bloqueos->firstWhere('motivo')) <th>Motivo</th> @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($medico->bloqueos->sortByDesc('fecha_inicio') as $bloqueo)
                                    <tr>
                                        <td>{{ $bloqueo->fecha_inicio->format('d/m/Y') }}</td>
                                        <td>{{ $bloqueo->fecha_fin->format('d/m/Y') }}</td>
                                        @if ($medico->bloqueos->firstWhere('motivo')) <td>{{ $bloqueo->motivo ?? '—' }}</td> @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <br><br><br><br>
                    </div>
                    @endif

                    @if (!$medico->horarios->where('activo', true)->count() && !$medico->bloqueos->count())
                    <div class="neu-card p-4 text-center" style="border-radius:16px;">
                        <p class="text-muted mb-0">El médico aún no ha configurado su disponibilidad.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection