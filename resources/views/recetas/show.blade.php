@extends('layouts.app')

@section('title', 'Receta Médica')

@section('content')
<div class="container">
    @php $user = auth()->user(); @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:#1266f1">Receta Médica</h4>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i><span class="btn-text">Volver al dashboard</span></a>
    </div>

    <div class="row g-4">
        <div class="col-12 col-md-5">
            <div class="card shadow-2 p-4 h-100">
                <h5 class="mb-3 fw-bold" style="color:#1266f1;border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Información</h5>
                <table class="table neu-table align-middle mb-0">
                    <tbody>
                        <tr><th style="width:120px">Paciente</th><td style="color:var(--text-emphasis);font-weight:500">{{ $receta->cita->paciente->name }}</td></tr>
                        <tr><th>Médico</th><td style="color:var(--text-emphasis);font-weight:500">{{ $receta->cita->medico->name }}</td></tr>
                        <tr><th>Fecha cita</th><td style="color:var(--text-emphasis);font-weight:500">{{ $receta->cita->fecha_hora->format('d/m/Y H:i') }}</td></tr>
                        <tr><th>Emisión</th><td style="color:var(--text-emphasis);font-weight:500">{{ $receta->fecha_emision->format('d/m/Y') }}</td></tr>
                    </tbody>
                </table>
                <br><br>
            </div>

            <div class="card shadow-2 p-4 mt-4">
                <h5 class="mb-3 fw-bold" style="color:#1266f1;border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Documentos adjuntos</h5>
                @if ($receta->documentos->isEmpty())
                    <div class="d-flex flex-column align-items-center py-3"><i class="fa fa-file fa-2x text-muted opacity-50 mb-2"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">Sin documentos adjuntos.</p></div>
                @else
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($receta->documentos as $doc)
                            <div class="text-center" style="width:120px">
                                @if (str_starts_with($doc->tipo_mime, 'image/'))
                                    <a href="{{ route('recetas.documento.download', $doc->id) }}" target="_blank">
                                        <img src="{{ route('recetas.documento.download', $doc->id) }}"
                                             alt="{{ $doc->nombre_original }}"
                                             class="rounded mb-1"
                                             style="width:100px;height:100px;object-fit:cover;box-shadow:3px 3px 6px #ccc,-3px -3px 6px #f5f5f5">
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

        <div class="col-12 col-md-7">
            <div class="card shadow-2 p-4">
                <h5 class="mb-3 fw-bold" style="color:#1266f1;border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Diagnóstico</h5>
                <p style="color:var(--text-emphasis);font-weight:400;line-height:1.7">{{ $receta->diagnostico }}</p>
            </div>

            <div class="card shadow-2 p-4 mt-4">
                <h5 class="mb-3 fw-bold" style="color:#1266f1;border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Indicaciones generales</h5>
                <p style="color:var(--text-emphasis);font-weight:400;line-height:1.7">{{ $receta->indicaciones_generales }}</p>
            </div>

            @if ($receta->medicamentos->isNotEmpty())
            <div class="card shadow-2 p-4 mt-4">
                <h5 class="mb-3 fw-bold" style="color:#1266f1;border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Medicamentos</h5>
                <div class="table-responsive">
                    <table class="table neu-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Medicamento</th>
                                <th>Genérico</th>
                                <th>Comercial</th>
                                <th>Presentación</th>
                                <th>Forma farm.</th>
                                <th>Dosis</th>
                                <th>Vía</th>
                                <th>Frecuencia</th>
                                <th>Duración</th>
                                <th>Cant.</th>
                                <th>Indicaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($receta->medicamentos as $med)
                                <tr>
                                    <td style="color:var(--text-emphasis);font-weight:500">{{ $med->medicamento }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->nombre_generico ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->nombre_comercial ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->presentacion ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->forma_farmaceutica ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->dosis ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->via_administracion ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->frecuencia ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->duracion ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->cantidad_total ?? '—' }}</td>
                                    <td style="color:var(--text-primary)">{{ $med->indicaciones ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <br><br>
            </div>
            @endif

            @if ($receta->notas)
            <div class="card shadow-2 p-4 mt-4">
                <h5 class="mb-3 fw-bold" style="color:#1266f1;border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Notas adicionales</h5>
                <p style="color:var(--text-primary);line-height:1.7;white-space:pre-wrap">{{ $receta->notas }}</p>
            </div>
            @endif

            @if ($user->esMedico() || $user->esAdmin())
            <div class="mt-4 text-end">
                <a href="{{ route('recetas.create', $receta->cita->id) }}" class="btn btn-primary neu-btn-sm"><i class="fa fa-plus me-1"></i><span class="btn-text">Nueva receta para esta cita</span></a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
