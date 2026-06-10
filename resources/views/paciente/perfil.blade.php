@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="neu-card p-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:64px;height:64px;background:var(--yellow);color:#121212;font-size:1.5rem;font-weight:bold;overflow:hidden">
                        @if ($user->foto_url)
                            <img src="{{ Storage::url($user->foto_url) }}" alt="Foto"
                                 style="width:100%;height:100%;object-fit:cover;cursor:pointer"
                                 onclick="window.open('{{ Storage::url($user->foto_url) }}','_blank')">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <h3 class="mb-1">Mi Perfil</h3>
                        <p class="mb-0 text-muted">Gestiona tu información personal</p>
                    </div>
                </div>
                <button type="button" id="btn-editar" class="neu-btn" style="background:var(--yellow);color:#121212" onclick="toggleEdit()">Editar</button>
            </div>
        </div>
    </div>

    <div class="neu-card p-4 mb-4">
        <h5 class="fw-bold mb-3" style="color:var(--yellow)">Información Personal</h5>
        <div class="row g-3 view-mode">
            <div class="col-md-6">
                <label class="form-label text-muted small">Nombre</label>
                <p class="fw-bold mb-0">{{ $user->name }}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label text-muted small">Email</label>
                <p class="fw-bold mb-0">{{ $user->email }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Fecha de Nacimiento</label>
                <p class="fw-bold mb-0">{{ optional($user->fecha_nacimiento)->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Teléfono</label>
                <p class="fw-bold mb-0">{{ $user->telefono ?? '—' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Dirección</label>
                <p class="fw-bold mb-0">{{ $user->direccion ?? '—' }}</p>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small">Observaciones</label>
                <p class="fw-bold mb-0">{{ $user->observaciones ?? '—' }}</p>
            </div>
        </div>

        <form action="{{ route('paciente.perfil.update') }}" method="POST" enctype="multipart/form-data" id="edit-form" style="display:none">
            @csrf @method('PUT')
            <div class="row g-3 edit-mode">
                    <div class="col-12">
                        <label class="form-label text-muted small">Foto de perfil</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 id="foto-preview-container"
                                 style="width:56px;height:56px;background:var(--yellow);color:#121212;font-size:1.3rem;font-weight:bold;overflow:hidden">
                                @if ($user->foto_url)
                                    <img id="foto-preview" src="{{ Storage::url($user->foto_url) }}" alt="Foto"
                                         style="width:100%;height:100%;object-fit:cover;cursor:pointer"
                                         onclick="window.open('{{ Storage::url($user->foto_url) }}','_blank')">
                                @else
                                    <span id="foto-preview-inicial">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <input type="file" name="foto" accept="image/jpeg,image/png,image/gif,image/webp"
                                   class="neu-input form-control @error('foto') is-invalid @enderror"
                                   onchange="previewFoto(this)">
                            @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">JPG, PNG, GIF, WebP — Máx. 2MB</small>
                        </div>
                    </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Nombre</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="neu-input form-control @error('name') is-invalid @enderror">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="neu-input form-control @error('email') is-invalid @enderror">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($user->fecha_nacimiento)->format('Y-m-d')) }}"
                           class="neu-input form-control @error('fecha_nacimiento') is-invalid @enderror">
                    @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $user->telefono) }}"
                           class="neu-input form-control @error('telefono') is-invalid @enderror">
                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Dirección</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $user->direccion) }}"
                           class="neu-input form-control @error('direccion') is-invalid @enderror">
                    @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small">Observaciones</label>
                    <textarea name="observaciones" rows="2"
                              class="neu-input form-control @error('observaciones') is-invalid @enderror">{{ old('observaciones', $user->observaciones) }}</textarea>
                    @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mt-4 text-end d-flex gap-2 justify-content-end edit-mode">
                <button type="button" class="neu-btn neu-btn-sm" onclick="toggleEdit()">Cancelar</button>
                <button type="submit" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212">Guardar cambios</button>
            </div>
        </form>
    </div>

    {{-- Contactos de Emergencia --}}
    <div class="neu-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="color:var(--yellow)">Contactos de Emergencia</h5>
            <button type="button" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212" data-mdb-toggle="modal" data-mdb-target="#contactoModal" onclick="resetContactoModal()">
                + Agregar
            </button>
        </div>
        @if ($user->contactosEmergencia->isEmpty())
            <p class="text-muted mb-0">No registrado.</p>
        @else
        <div class="table-responsive">
            <table class="table neu-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Parentesco</th>
                        <th>Email</th>
                        <th>Dirección</th>
                        <th style="width:100px">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user->contactosEmergencia as $contacto)
                    <tr>
                        <td>{{ $contacto->nombre_completo }}</td>
                        <td>{{ $contacto->telefono ?? '—' }}</td>
                        <td>{{ $contacto->parentesco ?? '—' }}</td>
                        <td>{{ $contacto->email ?? '—' }}</td>
                        <td>{{ $contacto->direccion ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:var(--yellow);color:#121212"
                                        onclick="editarContacto({{ $contacto->id }}, '{{ addslashes($contacto->nombre_completo) }}', '{{ addslashes($contacto->telefono) }}', '{{ addslashes($contacto->parentesco ?? '') }}', '{{ addslashes($contacto->email ?? '') }}', '{{ addslashes($contacto->direccion ?? '') }}')">
                                    Editar
                                </button>
                                <form action="{{ route('paciente.contactos.destroy', $contacto) }}" method="POST" onsubmit="return confirm('¿Eliminar este contacto?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <br><br><br><br>
    </div>

    {{-- Alergias --}}
    <div class="neu-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="color:var(--yellow)">Alergias</h5>
            <button type="button" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212" data-mdb-toggle="modal" data-mdb-target="#alergiaModal" onclick="resetAlergiaModal()">
                + Agregar
            </button>
        </div>
        @if ($user->alergias->isEmpty())
            <p class="text-muted mb-0">Sin alergias registradas.</p>
        @else
        <div class="table-responsive">
            <table class="table neu-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Alergia</th>
                        <th>Gravedad</th>
                        <th>Observaciones</th>
                        <th style="width:100px">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user->alergias as $alergia)
                    <tr>
                        <td>{{ $alergia->nombre }}</td>
                        <td>{{ $alergia->pivot->gravedad ?? '—' }}</td>
                        <td>{{ $alergia->pivot->observaciones ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:var(--yellow);color:#121212"
                                        onclick="editarAlergia({{ $alergia->id }}, {{ $alergia->pivot->gravedad ? "'" . addslashes($alergia->pivot->gravedad) . "'" : "''" }}, {{ $alergia->pivot->observaciones ? "'" . addslashes($alergia->pivot->observaciones) . "'" : "''" }})">
                                    Editar
                                </button>
                                <form action="{{ route('paciente.alergias.destroy', $alergia) }}" method="POST" onsubmit="return confirm('¿Eliminar esta alergia?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <br><br><br><br>
    </div>

    {{-- Enfermedades Importantes --}}
    <div class="neu-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="color:var(--yellow)">Enfermedades Importantes</h5>
            <button type="button" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212" data-mdb-toggle="modal" data-mdb-target="#enfermedadModal" onclick="resetEnfermedadModal()">
                + Agregar
            </button>
        </div>
        @if ($user->enfermedadesImportantes->isEmpty())
            <p class="text-muted mb-0">Sin enfermedades registradas.</p>
        @else
        <div class="table-responsive">
            <table class="table neu-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Enfermedad</th>
                        <th>Fecha Diagnóstico</th>
                        <th>Tratamiento Actual</th>
                        <th>Observaciones</th>
                        <th style="width:100px">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user->enfermedadesImportantes as $enfermedad)
                    <tr>
                        <td>{{ $enfermedad->nombre }}</td>
                        <td>{{ $enfermedad->pivot->fecha_diagnostico ?? '—' }}</td>
                        <td>{{ $enfermedad->pivot->tratamiento_actual ?? '—' }}</td>
                        <td>{{ $enfermedad->pivot->observaciones ?? '—' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <button type="button" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:var(--yellow);color:#121212"
                                        onclick="editarEnfermedad({{ $enfermedad->id }}, '{{ addslashes($enfermedad->pivot->fecha_diagnostico ?? '') }}', {{ $enfermedad->pivot->tratamiento_actual ? "'" . addslashes($enfermedad->pivot->tratamiento_actual) . "'" : "''" }}, {{ $enfermedad->pivot->observaciones ? "'" . addslashes($enfermedad->pivot->observaciones) . "'" : "''" }})">
                                    Editar
                                </button>
                                <form action="{{ route('paciente.enfermedades.destroy', $enfermedad) }}" method="POST" onsubmit="return confirm('¿Eliminar esta enfermedad?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        <br><br><br><br>
    </div>

    {{-- Modal Contacto --}}
    <div class="modal fade" id="contactoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="contactoForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="contactoMethod" value="POST">
                    <input type="hidden" id="contactoId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contactoModalTitle" style="color:var(--yellow)">Nuevo Contacto</h5>
                        <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="nombre_completo" id="contactoNombre" class="neu-input form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" id="contactoTelefono" class="neu-input form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parentesco</label>
                            <input type="text" name="parentesco" id="contactoParentesco" class="neu-input form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="contactoEmail" class="neu-input form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" id="contactoDireccion" class="neu-input form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="neu-btn neu-btn-sm" data-mdb-dismiss="modal">Cancelar</button>
                        <button type="submit" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Alergia --}}
    <div class="modal fade" id="alergiaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="alergiaForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="alergiaMethod" value="POST">
                    <input type="hidden" id="alergiaId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="alergiaModalTitle" style="color:var(--yellow)">Nueva Alergia</h5>
                        <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Alergia</label>
                            <select name="alergia_id" id="alergiaSelect" class="neu-input form-control" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($catalogoAlergias as $alergia)
                                    <option value="{{ $alergia->id }}">{{ $alergia->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gravedad</label>
                            <select name="gravedad" id="alergiaGravedad" class="neu-input form-control">
                                <option value="">Seleccionar...</option>
                                <option value="Leve">Leve</option>
                                <option value="Moderada">Moderada</option>
                                <option value="Grave">Grave</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="alergiaObservaciones" rows="2" class="neu-input form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="neu-btn neu-btn-sm" data-mdb-dismiss="modal">Cancelar</button>
                        <button type="submit" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Enfermedad --}}
    <div class="modal fade" id="enfermedadModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="enfermedadForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="enfermedadMethod" value="POST">
                    <input type="hidden" id="enfermedadId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="enfermedadModalTitle" style="color:var(--yellow)">Nueva Enfermedad</h5>
                        <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Enfermedad</label>
                            <select name="enfermedad_importante_id" id="enfermedadSelect" class="neu-input form-control" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($catalogoEnfermedades as $enfermedad)
                                    <option value="{{ $enfermedad->id }}">{{ $enfermedad->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha de Diagnóstico</label>
                            <input type="date" name="fecha_diagnostico" id="enfermedadFecha" class="neu-input form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tratamiento Actual</label>
                            <textarea name="tratamiento_actual" id="enfermedadTratamiento" rows="2" class="neu-input form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="enfermedadObservaciones" rows="2" class="neu-input form-control"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="neu-btn neu-btn-sm" data-mdb-dismiss="modal">Cancelar</button>
                        <button type="submit" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($citas->count())
    <div class="neu-card p-4 mb-4">
        <h6 class="fw-bold mb-3" style="color:var(--yellow)">Mis Citas</h6>
        <div class="table-responsive">
            <table class="table neu-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Médico</th>
                        <th>Especialidad</th>
                        <th>Motivo</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($citas as $cita)
                    <tr>
                        <td>{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                        <td>{{ $cita->medico->name }}</td>
                        <td>{{ optional(optional($cita->medico->medicoPerfil)->tipoMedico)->nombre_tipo_medico ?? 'General' }}</td>
                        <td class="text-muted">{{ Str::limit($cita->motivo, 40) }}</td>
                        <td>
                            @switch($cita->estado)
                                @case('pendiente') <span class="neu-badge" style="background:var(--yellow);color:#121212">Pendiente</span> @break
                                @case('confirmada') <span class="neu-badge" style="background:#00b894;color:#fff">Confirmada</span> @break
                                @case('en_espera') <span class="neu-badge" style="background:#ffa500;color:#121212">En espera</span> @break
                                @case('en_consulta') <span class="neu-badge" style="background:#1e90ff;color:#fff">En consulta</span> @break
                                @case('finalizada') <span class="neu-badge" style="background:#555;color:#fff">Finalizada</span> @break
                                @case('cancelada') <span class="neu-badge" style="background:#ff4444;color:#fff">Cancelada</span> @break
                                @case('no_asistio') <span class="neu-badge" style="background:#dc143c;color:#fff">No asistió</span> @break
                                @case('reprogramada') <span class="neu-badge" style="background:#9370db;color:#fff">Reprogramada</span> @break
                            @endswitch
                        </td>
                        <td>
                            <a href="{{ route('citas.show', $cita->id) }}" class="neu-btn neu-btn-sm" style="font-size:0.65rem">Ver detalles</a>
                        </td>
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

@push('scripts')
<script>
function toggleEdit() {
    const view = document.querySelectorAll('.view-mode');
    const edit = document.querySelectorAll('.edit-mode');
    const form = document.getElementById('edit-form');
    const btn = document.getElementById('btn-editar');
    const isEditing = form.style.display !== 'none';

    if (isEditing) {
        form.style.display = 'none';
        view.forEach(el => el.style.display = '');
        btn.textContent = 'Editar';
        btn.style.background = 'var(--yellow)';
    } else {
        form.style.display = '';
        view.forEach(el => el.style.display = 'none');
        btn.textContent = 'Cancelar';
        btn.style.background = '#ff4444';
        btn.style.color = '#fff';
    }
}

function previewFoto(input) {
    const file = input.files && input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const container = document.getElementById('foto-preview-container');
        const img = document.getElementById('foto-preview');
        const inicial = document.getElementById('foto-preview-inicial');
        if (img) {
            img.src = e.target.result;
        } else {
            if (inicial) inicial.remove();
            const newImg = document.createElement('img');
            newImg.id = 'foto-preview';
            newImg.src = e.target.result;
            newImg.alt = 'Foto';
            newImg.style.cssText = 'width:100%;height:100%;object-fit:cover;cursor:pointer';
            container.appendChild(newImg);
        }
    };
    reader.readAsDataURL(file);
}

function resetContactoModal() {
    document.getElementById('contactoForm').action = '{{ route("paciente.contactos.store") }}';
    document.getElementById('contactoMethod').value = 'POST';
    document.getElementById('contactoModalTitle').textContent = 'Nuevo Contacto';
    document.getElementById('contactoNombre').value = '';
    document.getElementById('contactoTelefono').value = '';
    document.getElementById('contactoParentesco').value = '';
    document.getElementById('contactoEmail').value = '';
    document.getElementById('contactoDireccion').value = '';
    document.getElementById('contactoId').value = '';
}

function editarContacto(id, nombre, telefono, parentesco, email, direccion) {
    document.getElementById('contactoForm').action = '/paciente/contactos/' + id;
    document.getElementById('contactoMethod').value = 'PUT';
    document.getElementById('contactoModalTitle').textContent = 'Editar Contacto';
    document.getElementById('contactoNombre').value = nombre;
    document.getElementById('contactoTelefono').value = telefono;
    document.getElementById('contactoParentesco').value = parentesco;
    document.getElementById('contactoEmail').value = email;
    document.getElementById('contactoDireccion').value = direccion;
    document.getElementById('contactoId').value = id;
    new mdb.Modal(document.getElementById('contactoModal')).show();
}

function resetAlergiaModal() {
    document.getElementById('alergiaForm').action = '{{ route("paciente.alergias.store") }}';
    document.getElementById('alergiaMethod').value = 'POST';
    document.getElementById('alergiaModalTitle').textContent = 'Nueva Alergia';
    document.getElementById('alergiaSelect').value = '';
    document.getElementById('alergiaSelect').disabled = false;
    document.getElementById('alergiaGravedad').value = '';
    document.getElementById('alergiaObservaciones').value = '';
    document.getElementById('alergiaId').value = '';
}

function editarAlergia(id, gravedad, observaciones) {
    document.getElementById('alergiaForm').action = '/paciente/alergias/' + id;
    document.getElementById('alergiaMethod').value = 'PUT';
    document.getElementById('alergiaModalTitle').textContent = 'Editar Alergia';
    document.getElementById('alergiaSelect').value = id;
    document.getElementById('alergiaSelect').disabled = true;
    document.getElementById('alergiaGravedad').value = gravedad;
    document.getElementById('alergiaObservaciones').value = observaciones;
    document.getElementById('alergiaId').value = id;
    new mdb.Modal(document.getElementById('alergiaModal')).show();
}

function resetEnfermedadModal() {
    document.getElementById('enfermedadForm').action = '{{ route("paciente.enfermedades.store") }}';
    document.getElementById('enfermedadMethod').value = 'POST';
    document.getElementById('enfermedadModalTitle').textContent = 'Nueva Enfermedad';
    document.getElementById('enfermedadSelect').value = '';
    document.getElementById('enfermedadSelect').disabled = false;
    document.getElementById('enfermedadFecha').value = '';
    document.getElementById('enfermedadTratamiento').value = '';
    document.getElementById('enfermedadObservaciones').value = '';
    document.getElementById('enfermedadId').value = '';
}

function editarEnfermedad(id, fecha, tratamiento, observaciones) {
    document.getElementById('enfermedadForm').action = '/paciente/enfermedades/' + id;
    document.getElementById('enfermedadMethod').value = 'PUT';
    document.getElementById('enfermedadModalTitle').textContent = 'Editar Enfermedad';
    document.getElementById('enfermedadSelect').value = id;
    document.getElementById('enfermedadSelect').disabled = true;
    document.getElementById('enfermedadFecha').value = fecha;
    document.getElementById('enfermedadTratamiento').value = tratamiento;
    document.getElementById('enfermedadObservaciones').value = observaciones;
    document.getElementById('enfermedadId').value = id;
    new mdb.Modal(document.getElementById('enfermedadModal')).show();
}
</script>
@endpush
