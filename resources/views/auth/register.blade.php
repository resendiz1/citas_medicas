@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card shadow-2 p-4 mt-5">
                <h4 class="text-center mb-4">Registro</h4>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label">Nombre completo</label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" id="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control" required>
                        </div>

                        <div class="mb-4">
                            <label for="role" class="form-label">Tipo de usuario</label>
                            <select id="role" name="role"
                                    class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                <option value="paciente" {{ old('role') === 'paciente' ? 'selected' : '' }}>Paciente</option>
                                <option value="medico" {{ old('role') === 'medico' ? 'selected' : '' }}>Médico</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3"><i class="fa fa-user-plus me-1"></i>Crear cuenta</button>

                        <div class="text-center mb-3">
                            <span style="font-size:0.85rem;color:var(--text-secondary)">o</span>
                        </div>

                        <a href="#" id="google-register-btn" class="btn btn-outline-secondary w-100 mb-3 d-flex align-items-center justify-content-center gap-2">
                            <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
                            Registrarse con Google
                        </a>

                        <p class="text-center mb-0">
                            ¿Ya tienes cuenta?
                            <a href="{{ route('login') }}">Inicia sesión</a>
                        </p>
                    </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.getElementById('google-register-btn').addEventListener('click', function (e) {
    e.preventDefault();
    var role = document.getElementById('role').value;
    if (!role) {
        alert('Debes seleccionar un tipo de usuario (Médico o Paciente) antes de registrarte con Google.');
        return;
    }
    window.location.href = '{{ route('google.redirect') }}?role=' + role;
});
</script>
@endpush
@endsection
