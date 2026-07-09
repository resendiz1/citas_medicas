@extends('layouts.app')

@section('title', 'Nuevo Médico')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card shadow-2 p-4">
                <h4 class="mb-4 fw-bold">Nuevo Médico</h4>
                    <form method="POST" action="{{ route('admin.medicos.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Especialidad</label>
                            <select name="tipo_medico_id" class="form-select @error('tipo_medico_id') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($tiposMedico as $tipo)
                                    <option value="{{ $tipo->id }}" {{ old('tipo_medico_id') == $tipo->id ? 'selected' : '' }}>{{ $tipo->nombre_tipo_medico }}</option>
                                @endforeach
                            </select>
                            @error('tipo_medico_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}">
                            @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Cédula profesional</label>
                            <input type="text" name="cedula_profesional" class="form-control @error('cedula_profesional') is-invalid @enderror" value="{{ old('cedula_profesional') }}">
                            @error('cedula_profesional') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Universidad</label>
                            <input type="text" name="universidad" class="form-control @error('universidad') is-invalid @enderror" value="{{ old('universidad') }}">
                            @error('universidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Años de experiencia</label>
                            <input type="number" name="experiencia_anios" class="form-control @error('experiencia_anios') is-invalid @enderror" value="{{ old('experiencia_anios') }}" min="0">
                            @error('experiencia_anios') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="activoSwitch" name="activo" value="1"
                                       {{ old('activo', true) ? 'checked' : '' }}
                                       style="width:3rem;height:1.5rem;cursor:pointer;background:#ccc">
                                <label class="form-label ms-2" for="activoSwitch">Médico activo</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.medicos') }}" class="btn btn-outline-secondary"><i class="fa fa-xmark me-1"></i>Cancelar</a>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-disk me-1"></i>Guardar</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection
