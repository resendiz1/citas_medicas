@extends('layouts.app')

@section('title', 'Paciente: ' . $paciente->name)

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:var(--yellow)">Perfil del Paciente</h4>
        <a href="{{ route('dashboard') }}" class="neu-btn neu-btn-sm" style="color:var(--yellow)">&larr; Volver al dashboard</a>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="neu-card p-4 h-100">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Datos personales</h5>
                <div class="table-responsive">
                    <table class="table neu-table align-middle mb-0">
                        <tbody>
                            <tr><th style="width:140px">Nombre</th><td style="color:var(--text-emphasis);font-weight:500">{{ $paciente->name }}</td></tr>
                            <tr><th>Email</th><td style="color:var(--text-emphasis);font-weight:500">{{ $paciente->email }}</td></tr>
                            <tr><th>Teléfono</th><td style="color:var(--text-emphasis);font-weight:500">{{ $paciente->telefono ?? '—' }}</td></tr>
                            <tr><th>Fecha nac.</th><td style="color:var(--text-emphasis);font-weight:500">{{ $paciente->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</td></tr>
                            <tr><th>Dirección</th><td style="color:var(--text-emphasis);font-weight:500">{{ $paciente->direccion ?? '—' }}</td></tr>
                            <tr><th>Observaciones</th><td style="color:var(--text-emphasis);font-weight:500">{{ $paciente->observaciones ?? '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                <br><br><br><br>
            </div>
        </div>

        <div class="col-md-6">
            <div class="neu-card p-4 h-100">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Contactos de emergencia</h5>
                @forelse ($paciente->contactosEmergencia as $contacto)
                    <div class="table-responsive mb-2">
                        <table class="table neu-table align-middle mb-0">
                            <tbody>
                                <tr><th style="width:140px">Nombre</th><td style="color:var(--text-emphasis);font-weight:500">{{ $contacto->nombre_completo }}</td></tr>
                                <tr><th>Teléfono</th><td style="color:var(--text-emphasis);font-weight:500">{{ $contacto->telefono }}</td></tr>
                                <tr><th>Parentesco</th><td style="color:var(--text-emphasis);font-weight:500">{{ $contacto->parentesco ?? '—' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                @empty
                    <p class="text-muted mb-0">No registrado.</p>
                @endforelse
                <br><br><br><br>
            </div>
        </div>

        <div class="col-md-6">
            <div class="neu-card p-4 h-100">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Alergias</h5>
                @php $alergias = $paciente->alergias; @endphp
                @if ($alergias->isNotEmpty())
                    @foreach ($alergias as $alergia)
                        <div class="mb-2">
                            <p class="fw-bold mb-0" style="color:var(--text-emphasis);font-size:1.05rem">{{ $alergia->nombre }}</p>
                            @if ($alergia->pivot->gravedad)
                                <small style="color:var(--yellow)">Gravedad: {{ $alergia->pivot->gravedad }}</small>
                            @endif
                            <p style="color:var(--text-primary);margin-bottom:0">{{ $alergia->descripcion ?? '' }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">Sin alergias registradas.</p>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="neu-card p-4 h-100">
                <h5 class="mb-3 fw-bold" style="color:var(--yellow);border-bottom:1px solid rgba(240,192,0,0.2);padding-bottom:0.75rem">Enfermedades importantes</h5>
                @php $enfermedades = $paciente->enfermedadesImportantes; @endphp
                @if ($enfermedades->isNotEmpty())
                    @foreach ($enfermedades as $enf)
                        <div class="mb-2">
                            <p class="fw-bold mb-0" style="color:var(--text-emphasis);font-size:1.05rem">{{ $enf->nombre }}</p>
                            @if ($enf->pivot->fecha_diagnostico)
                                <small style="color:var(--text-secondary)">Diagnosticado: {{ \Carbon\Carbon::parse($enf->pivot->fecha_diagnostico)->format('d/m/Y') }}</small>
                            @endif
                            <p style="color:var(--text-primary);margin-bottom:0">{{ $enf->descripcion ?? '' }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">Sin enfermedades registradas.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
