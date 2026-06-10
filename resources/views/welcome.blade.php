<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Citas Médicas') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 text-center">
                <div class="neu-card p-5">
                    <h1 class="display-4 mb-3 fw-bold" style="color:var(--yellow)">Citas Médicas</h1>
                    <p class="lead mb-4 text-muted">Sistema de gestión de citas médicas</p>
                    <div class="d-flex justify-content-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="neu-btn neu-btn-primary">Ir al Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="neu-btn neu-btn-primary">Iniciar sesión</a>
                            <a href="{{ route('register') }}" class="neu-btn">Registrarse</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
