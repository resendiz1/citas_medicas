@extends('layouts.app')

@section('title', 'Detalle de Cita')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Detalle de Cita</h4>
        <a href="{{ route('dashboard') }}" class="neu-btn neu-btn-sm">Volver</a>
    </div>

    <div class="neu-card p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Información de la Cita</h6>
        <div class="row g-3">
            <div class="col-md-4">
                <strong class="text-muted small">Fecha y hora</strong><br>
                {{ $cita->fecha_hora->format('d/m/Y H:i') }}
            </div>
            <div class="col-md-4">
                <strong class="text-muted small">Estado</strong><br>
                @switch($cita->estado)
                    @case('pendiente') <span id="estado-badge-{{ $cita->id }}" class="neu-badge" style="background:var(--yellow);color:#121212">Pendiente</span> @break
                    @case('confirmada') <span id="estado-badge-{{ $cita->id }}" class="neu-badge" style="background:#00b894;color:#fff">Confirmada</span> @break
                    @case('en_espera') <span id="estado-badge-{{ $cita->id }}" class="neu-badge" style="background:#ffa500;color:#121212">En espera</span> @break
                    @case('en_consulta') <span id="estado-badge-{{ $cita->id }}" class="neu-badge" style="background:#1e90ff;color:#fff">En consulta</span> @break
                    @case('finalizada') <span id="estado-badge-{{ $cita->id }}" class="neu-badge" style="background:#555;color:#fff">Finalizada</span> @break
                    @case('cancelada') <span id="estado-badge-{{ $cita->id }}" class="neu-badge" style="background:#ff4444;color:#fff">Cancelada</span> @break
                    @case('no_asistio') <span id="estado-badge-{{ $cita->id }}" class="neu-badge" style="background:#dc143c;color:#fff">No asistió</span> @break
                    @case('reprogramada') <span id="estado-badge-{{ $cita->id }}" class="neu-badge" style="background:#9370db;color:#fff">Reprogramada</span> @break
                @endswitch
            </div>
            <div class="col-md-4">
                <strong class="text-muted small">Motivo</strong><br>
                {{ $cita->motivo }}
            </div>
            @if ($cita->fecha_reprogramada)
            <div class="col-md-4">
                <strong class="text-muted small">Fecha reprogramada</strong><br>
                {{ $cita->fecha_reprogramada->format('d/m/Y H:i') }}
            </div>
            @endif
            @if ($cita->notas_paciente)
            <div class="col-12">
                <strong class="text-muted small">Notas del paciente</strong><br>
                {{ $cita->notas_paciente }}
            </div>
            @endif
            @if ($cita->notas_medico)
            <div class="col-12">
                <strong class="text-muted small">Notas del médico</strong><br>
                {{ $cita->notas_medico }}
            </div>
            @endif
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="neu-card p-4 h-100">
                <h6 class="fw-bold mb-3" style="color:var(--yellow)">Paciente</h6>
                <div class="mb-2"><strong class="text-muted small">Nombre:</strong><br>{{ $cita->paciente->name }}</div>
                <div class="mb-2"><strong class="text-muted small">Email:</strong><br>{{ $cita->paciente->email }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="neu-card p-4 h-100">
                <h6 class="fw-bold mb-3" style="color:var(--yellow)">Médico</h6>
                <div class="mb-2"><strong class="text-muted small">Nombre:</strong><br>{{ $cita->medico->name }}</div>
                <div class="mb-2"><strong class="text-muted small">Email:</strong><br>{{ $cita->medico->email }}</div>
                @if ($cita->medico->medicoPerfil && $cita->medico->medicoPerfil->tipoMedico)
                <div class="mb-2"><strong class="text-muted small">Especialidad:</strong><br>{{ $cita->medico->medicoPerfil->tipoMedico->nombre_tipo_medico }}</div>
                @endif
            </div>
        </div>
    </div>

    @if ($cita->recetas->count())
    @foreach ($cita->recetas as $receta)
    <div class="neu-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0" style="color:var(--yellow)">Receta del {{ $receta->fecha_emision->format('d/m/Y') }}</h6>
            <a href="{{ route('recetas.show', $receta->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem">Abrir receta</a>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-3"><strong class="text-muted small">Paciente:</strong><br>{{ $receta->cita->paciente->name }}</div>
            <div class="col-md-3"><strong class="text-muted small">Médico:</strong><br>{{ $receta->cita->medico->name }}</div>
            <div class="col-md-3"><strong class="text-muted small">Fecha cita:</strong><br>{{ $receta->cita->fecha_hora->format('d/m/Y') }}</div>
            <div class="col-md-3"><strong class="text-muted small">Emisión:</strong><br>{{ $receta->fecha_emision->format('d/m/Y') }}</div>
        </div>
        @if ($receta->diagnostico)
        <div class="mb-3">
            <strong class="text-muted small">Diagnóstico</strong>
            <p class="mb-0" style="color:var(--text-emphasis);line-height:1.6">{{ $receta->diagnostico }}</p>
        </div>
        @endif
        @if ($receta->indicaciones_generales)
        <div class="mb-3">
            <strong class="text-muted small">Indicaciones generales</strong>
            <p class="mb-0" style="color:var(--text-emphasis);line-height:1.6">{{ $receta->indicaciones_generales }}</p>
        </div>
        @endif
        @if ($receta->medicamentos->count())
        <div class="mb-3">
            <strong class="text-muted small">Medicamentos</strong>
            <div class="table-responsive mt-1">
                <table class="table table-sm neu-table mb-0">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Dosis</th>
                            <th>Frecuencia</th>
                            <th>Duración</th>
                            <th>Indicaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($receta->medicamentos as $med)
                        <tr>
                            <td style="color:var(--text-emphasis)">{{ $med->medicamento }}</td>
                            <td>{{ $med->dosis ?? '—' }}</td>
                            <td>{{ $med->frecuencia ?? '—' }}</td>
                            <td>{{ $med->duracion ?? '—' }}</td>
                            <td>{{ $med->indicaciones ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @if ($receta->notas)
        <br><br><br><br>
        <div class="mb-3">
            <strong class="text-muted small">Notas adicionales</strong>
            <p class="mb-0" style="color:var(--text-primary);line-height:1.6;white-space:pre-wrap">{{ $receta->notas }}</p>
        </div>
        @endif
        @if ($receta->documentos->count())
        <div>
            <strong class="text-muted small">Documentos adjuntos</strong>
            <div class="d-flex flex-wrap gap-3 mt-1">
                @foreach ($receta->documentos as $doc)
                    <div class="text-center" style="width:100px">
                        @if (str_starts_with($doc->tipo_mime, 'image/'))
                            <a href="{{ route('recetas.documento.download', $doc->id) }}" target="_blank">
                                <img src="{{ route('recetas.documento.download', $doc->id) }}"
                                     alt="{{ $doc->nombre_original }}"
                                     class="rounded mb-1"
                                     style="width:80px;height:80px;object-fit:cover;box-shadow:3px 3px 6px var(--neu-shadow-dark),-3px -3px 6px var(--neu-shadow-light)">
                            </a>
                        @else
                            <a href="{{ route('recetas.documento.download', $doc->id) }}"
                               class="d-block neu-btn neu-btn-sm mb-1" style="font-size:0.65rem">📄 PDF</a>
                        @endif
                        <small class="text-muted" style="font-size:0.6rem;word-break:break-word">{{ $doc->nombre_original }}</small>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
    @endforeach
    @endif

    @if ($cita->consultaMedica)
    @php $consulta = $cita->consultaMedica; @endphp
    <div class="neu-card p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Consulta Médica</h6>

        @if ($consulta->motivo_consulta || $consulta->sintomas || $consulta->tiempo_evolucion)
        <div class="mb-3">
            <strong class="text-muted small">Motivo y síntomas</strong>
            <div class="row g-2 mt-1">
                @if ($consulta->motivo_consulta)<div class="col-12"><strong class="text-muted small">Motivo:</strong> {{ $consulta->motivo_consulta }}</div>@endif
                @if ($consulta->sintomas)<div class="col-md-8"><strong class="text-muted small">Síntomas:</strong> {{ $consulta->sintomas }}</div>@endif
                @if ($consulta->tiempo_evolucion)<div class="col-md-4"><strong class="text-muted small">Tiempo evolución:</strong> {{ $consulta->tiempo_evolucion }}</div>@endif
            </div>
        </div>
        @endif

        @if ($consulta->dolores->count())
        <div class="mb-3">
            <strong class="text-muted small">Dolores</strong>
            <div class="table-responsive mt-1">
                <table class="table table-sm neu-table mb-0">
                    <thead>
                        <tr>
                            <th>Ubicación</th>
                            <th>Intensidad</th>
                            <th>Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($consulta->dolores as $dolor)
                        <tr>
                            <td>{{ $dolor->ubicacion }}</td>
                            <td>{{ $dolor->intensidad }}</td>
                            <td>{{ $dolor->duracion }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        <br><br><br><br>

        @if ($consulta->presion_arterial || $consulta->temperatura || $consulta->frecuencia_cardiaca || $consulta->frecuencia_respiratoria || $consulta->saturacion_oxigeno || $consulta->peso || $consulta->estatura || $consulta->imc)
        <div class="mb-3">
            <strong class="text-muted small">Signos vitales</strong>
            <div class="row g-2 mt-1">
                @if ($consulta->presion_arterial)<div class="col-md-3"><strong class="text-muted small">Presión arterial:</strong> {{ $consulta->presion_arterial }} mmHg</div>@endif
                @if ($consulta->temperatura)<div class="col-md-3"><strong class="text-muted small">Temperatura:</strong> {{ $consulta->temperatura }} °C</div>@endif
                @if ($consulta->frecuencia_cardiaca)<div class="col-md-3"><strong class="text-muted small">Frec. cardíaca:</strong> {{ $consulta->frecuencia_cardiaca }} lpm</div>@endif
                @if ($consulta->frecuencia_respiratoria)<div class="col-md-3"><strong class="text-muted small">Frec. respiratoria:</strong> {{ $consulta->frecuencia_respiratoria }} rpm</div>@endif
                @if ($consulta->saturacion_oxigeno)<div class="col-md-3"><strong class="text-muted small">Saturación O₂:</strong> {{ $consulta->saturacion_oxigeno }} %</div>@endif
                @if ($consulta->peso)<div class="col-md-3"><strong class="text-muted small">Peso:</strong> {{ $consulta->peso }} kg</div>@endif
                @if ($consulta->estatura)<div class="col-md-3"><strong class="text-muted small">Estatura:</strong> {{ $consulta->estatura }} cm</div>@endif
                @if ($consulta->imc)<div class="col-md-3"><strong class="text-muted small">IMC:</strong> {{ $consulta->imc }}</div>@endif
            </div>
        </div>
        @endif

        @if ($consulta->exploracion_fisica || $consulta->observaciones || $consulta->diagnostico_probable || $consulta->diagnostico_final || $consulta->codigo_cie10)
        <div>
            <strong class="text-muted small">Exploración y diagnóstico</strong>
            <div class="mt-1">
                @if ($consulta->exploracion_fisica)<div class="mb-2"><strong class="text-muted small">Exploración física:</strong><br>{{ $consulta->exploracion_fisica }}</div>@endif
                @if ($consulta->observaciones)<div class="mb-2"><strong class="text-muted small">Observaciones:</strong><br>{{ $consulta->observaciones }}</div>@endif
                <div class="row g-2">
                    @if ($consulta->diagnostico_probable)<div class="col-md-6"><strong class="text-muted small">Diagnóstico probable:</strong><br>{{ $consulta->diagnostico_probable }}</div>@endif
                    @if ($consulta->diagnostico_final)<div class="col-md-6"><strong class="text-muted small">Diagnóstico final:</strong><br>{{ $consulta->diagnostico_final }}</div>@endif
                    @if ($consulta->codigo_cie10)<div class="col-md-4"><strong class="text-muted small">Código CIE-10:</strong><br>{{ $consulta->codigo_cie10 }}</div>@endif
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    @if ($cita->historiales->count())
    <div class="neu-card p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Historial de cambios</h6>
        <div class="table-responsive">
            <table class="table table-sm neu-table mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Estado anterior</th>
                        <th>Estado nuevo</th>
                        <th>Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cita->historiales as $h)
                    <tr>
                        <td style="font-size:0.8rem">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                        <td style="font-size:0.8rem">{{ optional($h->user)->name ?? '—' }}</td>
                        <td style="font-size:0.8rem">{{ $h->estado_anterior ?? '—' }}</td>
                        <td style="font-size:0.8rem">{{ $h->estado_nuevo }}</td>
                        <td style="font-size:0.8rem">{{ $h->comentario }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <br><br><br><br>
    </div>
    @endif

</div>
@endsection
