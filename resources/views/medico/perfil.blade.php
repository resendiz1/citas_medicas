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
                        <p class="mb-0 text-muted">Gestiona tu información profesional y documentos</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <form action="{{ route('medico.toggle-activo') }}" method="POST" id="toggle-activo-form">
                        @csrf
                        <div class="form-check form-switch mb-0 d-flex align-items-center gap-2" style="cursor:pointer" onclick="document.getElementById('toggle-activo-form').submit();">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   {{ optional($perfil)->activo ?? true ? 'checked' : '' }}
                                   style="width:2.5rem;height:1.3rem;cursor:pointer;background:var(--neu-shadow-dark);pointer-events:none">
                            <span class="small fw-bold" style="color:{{ optional($perfil)->activo ?? true ? 'var(--yellow)' : '#ff4444' }}">
                                {{ optional($perfil)->activo ?? true ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </form>
                    <button type="button" id="btn-editar" class="neu-btn" style="background:var(--yellow);color:#121212" onclick="toggleEdit()">Editar</button>
                </div>
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
        </div>

        <form action="{{ route('medico.perfil.update') }}" method="POST" enctype="multipart/form-data" id="edit-form" style="display:none">
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
            </div>

            <h5 class="fw-bold mb-3 mt-4" style="color:var(--yellow)">Información Profesional</h5>
            <div class="row g-3 edit-mode">
                <div class="col-md-4">
                    <label class="form-label text-muted small">Especialidad</label>
                    <select name="tipo_medico_id"
                            class="neu-input form-control @error('tipo_medico_id') is-invalid @enderror">
                        <option value="">Seleccionar...</option>
                        @foreach ($tiposMedico as $tipo)
                            <option value="{{ $tipo->id }}" {{ old('tipo_medico_id', optional($perfil)->tipo_medico_id) == $tipo->id ? 'selected' : '' }}>
                                {{ $tipo->nombre_tipo_medico }}
                            </option>
                        @endforeach
                    </select>
                    @error('tipo_medico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Cédula Profesional</label>
                    <input type="text" name="cedula_profesional" value="{{ old('cedula_profesional', optional($perfil)->cedula_profesional) }}"
                           class="neu-input form-control @error('cedula_profesional') is-invalid @enderror">
                    @error('cedula_profesional') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Universidad</label>
                    <input type="text" name="universidad" value="{{ old('universidad', optional($perfil)->universidad) }}"
                           class="neu-input form-control @error('universidad') is-invalid @enderror">
                    @error('universidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Años de Experiencia</label>
                    <input type="number" name="experiencia_anios" value="{{ old('experiencia_anios', optional($perfil)->experiencia_anios) }}"
                           class="neu-input form-control @error('experiencia_anios') is-invalid @enderror" min="0" max="100">
                    @error('experiencia_anios') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small">Observaciones</label>
                    <input type="text" name="observaciones" value="{{ old('observaciones', $user->observaciones) }}"
                           class="neu-input form-control @error('observaciones') is-invalid @enderror">
                    @error('observaciones') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small">Descripción</label>
                    <textarea name="descripcion" rows="3"
                              class="neu-input form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', optional($perfil)->descripcion) }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 text-end d-flex gap-2 justify-content-end edit-mode">
                <button type="button" class="neu-btn neu-btn-sm" onclick="toggleEdit()">Cancelar</button>
                <button type="submit" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212">Guardar cambios</button>
            </div>
        </form>

        <div class="row g-3 mt-0 view-mode">
            <div class="col-md-4">
                <label class="form-label text-muted small">Especialidad</label>
                <p class="fw-bold mb-0">{{ optional(optional($perfil)->tipoMedico)->nombre_tipo_medico ?? '—' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Cédula Profesional</label>
                <p class="fw-bold mb-0">{{ optional($perfil)->cedula_profesional ?? '—' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Universidad</label>
                <p class="fw-bold mb-0">{{ optional($perfil)->universidad ?? '—' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Años de Experiencia</label>
                <p class="fw-bold mb-0">{{ optional($perfil)->experiencia_anios ?? '—' }}</p>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small">Observaciones</label>
                <p class="fw-bold mb-0">{{ $user->observaciones ?? '—' }}</p>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small">Descripción</label>
                <p class="fw-bold mb-0">{{ optional($perfil)->descripcion ?? '—' }}</p>
            </div>
            <div class="col-12">
                <label class="form-label text-muted small">Estado</label>
                <p class="fw-bold mb-0">
                    @if (optional($perfil)->activo ?? true)
                        <span class="neu-badge" style="background:#00b894;color:#fff">Activo</span>
                    @else
                        <span class="neu-badge" style="background:#ff4444;color:#fff">Inactivo</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="neu-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0" style="color:var(--yellow)">Mis Documentos</h5>
            <button type="button" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212" data-mdb-toggle="modal" data-mdb-target="#documentosModal">+ Subir</button>
        </div>

        @error('documento')
            <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem;border-radius:8px">{{ $message }}</div>
        @enderror
        @error('nombre')
            <div class="alert alert-danger py-2 mb-3" style="font-size:0.85rem;border-radius:8px">{{ $message }}</div>
        @enderror

        @if ($documentos->isEmpty())
            <p class="text-muted mb-0">No has subido ningún documento aún.</p>
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
                                    <div class="d-flex gap-1">
                                        <a href="{{ Storage::url($doc->ruta_archivo) }}" target="_blank"
                                           class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#1e90ff;color:#fff">Ver</a>
                                        <a href="{{ route('medico.documentos.download', $doc->id) }}"
                                           class="neu-btn neu-btn-sm" style="font-size:0.65rem">Descargar</a>
                                        <form action="{{ route('medico.documentos.destroy', $doc->id) }}" method="POST"
                                              class="d-inline" onsubmit="return confirm('¿Eliminar este documento?')">
                                            @csrf @method('DELETE')
                                            <button class="neu-btn neu-btn-sm" style="font-size:0.65rem;background:#ff4444;color:#fff">Eliminar</button>
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
</div>

<div class="modal fade" id="documentosModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Subir documento</h6>
                <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
            </div>
            <form action="{{ route('medico.documentos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Nombre del documento</label>
                        <input type="text" name="nombre" placeholder="Ej. Cédula profesional"
                               class="neu-input form-control @error('nombre') is-invalid @enderror">
                        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Archivo</label>
                        <input type="file" name="documento"
                               class="neu-input form-control @error('documento') is-invalid @enderror"
                               accept="image/jpeg,image/png,image/gif,image/webp,application/pdf" required>
                        @error('documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">JPG, PNG, GIF, WebP, PDF — Máx. 20MB</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="neu-btn neu-btn-sm" data-mdb-dismiss="modal">Cancelar</button>
                    <button type="submit" class="neu-btn neu-btn-sm" style="background:var(--yellow);color:#121212">Subir</button>
                </div>
            </form>
        </div>
    </div>
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
</script>
@endpush