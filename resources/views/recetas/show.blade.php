@extends('layouts.app')

@section('title', 'Receta Médica')

@section('content')
<div class="container">
    @php $user = auth()->user(); @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:var(--yellow)">Receta Médica</h4>
        <a href="{{ route('dashboard') }}" class="neu-btn neu-btn-sm" style="color:var(--yellow)">&larr; Volver al dashboard</a>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="neu-card p-4 h-100">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Información</h5>
                <table class="table neu-table align-middle mb-0">
                    <tbody>
                        <tr><th style="width:120px">Paciente</th><td style="color:var(--text-emphasis);font-weight:500">{{ $receta->cita->paciente->name }}</td></tr>
                        <tr><th>Médico</th><td style="color:var(--text-emphasis);font-weight:500">{{ $receta->cita->medico->name }}</td></tr>
                        <tr><th>Fecha cita</th><td style="color:var(--text-emphasis);font-weight:500">{{ $receta->cita->fecha_hora->format('d/m/Y H:i') }}</td></tr>
                        <tr><th>Emisión</th><td style="color:var(--text-emphasis);font-weight:500">{{ $receta->fecha_emision->format('d/m/Y') }}</td></tr>
                    </tbody>
                </table>
                <br><br><br><br>
            </div>

            <div class="neu-card p-4 mt-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Documentos adjuntos</h5>
                @if ($receta->documentos->isEmpty())
                    <p class="text-muted mb-0">Sin documentos adjuntos.</p>
                @else
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($receta->documentos as $doc)
                            <div class="text-center" style="width:120px">
                                @if (str_starts_with($doc->tipo_mime, 'image/'))
                                    <a href="{{ route('recetas.documento.download', $doc->id) }}" target="_blank">
                                        <img src="{{ route('recetas.documento.download', $doc->id) }}"
                                             alt="{{ $doc->nombre_original }}"
                                             class="rounded mb-1"
                                             style="width:100px;height:100px;object-fit:cover;box-shadow:3px 3px 6px var(--neu-shadow-dark),-3px -3px 6px var(--neu-shadow-light)">
                                    </a>
                                @else
                                    <a href="{{ route('recetas.documento.download', $doc->id) }}"
                                       class="d-block neu-btn neu-btn-sm mb-1" style="font-size:0.75rem">
                                        📄 PDF
                                    </a>
                                @endif
                                <small class="text-muted" style="font-size:0.65rem;word-break:break-word">{{ $doc->nombre_original }}</small>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-7">
            <div class="neu-card p-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Diagnóstico</h5>
                <p style="color:var(--text-emphasis);font-weight:400;line-height:1.7">{{ $receta->diagnostico }}</p>
            </div>

            <div class="neu-card p-4 mt-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Indicaciones generales</h5>
                <p style="color:var(--text-emphasis);font-weight:400;line-height:1.7">{{ $receta->indicaciones_generales }}</p>
            </div>

            @if ($receta->medicamentos->isNotEmpty())
            <div class="neu-card p-4 mt-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Medicamentos</h5>
                <div class="table-responsive">
                    <table class="table neu-table align-middle mb-0">
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
                                    <td style="color:var(--text-emphasis);font-weight:500">{{ $med->medicamento }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->dosis ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->frecuencia ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->duracion ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->indicaciones ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <br><br><br><br>
            </div>
            @endif

            @if ($receta->notas)
            <div class="neu-card p-4 mt-4">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Notas adicionales</h5>
                <p style="color:var(--text-primary);line-height:1.7;white-space:pre-wrap">{{ $receta->notas }}</p>
            </div>
            @endif

            @if ($user->esMedico() || $user->esAdmin())
            <div class="mt-4 text-end">
                <a href="{{ route('recetas.create', $receta->cita->id) }}" class="neu-btn neu-btn-primary neu-btn-sm">+ Nueva receta para esta cita</a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
