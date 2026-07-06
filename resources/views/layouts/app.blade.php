<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @auth
        <meta name="notificaciones-poll" content="{{ route('notificaciones.poll') }}">
        <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    @php
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    $cssFile = $manifest['resources/css/app.css']['file'] ?? '';
    $jsFile = $manifest['resources/js/app.js']['file'] ?? '';
    @endphp
    <link rel="stylesheet" href="/build/{{ $cssFile }}">
    <script src="/build/{{ $jsFile }}" defer></script>
    @stack('head')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-2">
        <div class="container">
            <a class="navbar-brand fw-bold me-3 d-flex flex-column align-items-start text-primary" href="{{ route('dashboard') }}" style="line-height:1.2">Citas Médicas<small style="font-size:0.65rem;font-weight:400;opacity:0.8">&lt;JuanPancho's/&gt;</small></a>
            <div class="d-flex flex-grow-1 align-items-center justify-content-between flex-wrap" id="navbarNav">
                <ul class="navbar-nav flex-row align-items-center gap-1 mb-0">
                    @auth
                            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Inicio</a></li>
                        @if (auth()->user()->esAdmin())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-mdb-toggle="dropdown">Gestión</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('admin.citas') }}">Citas</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.medicos') }}">Médicos</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pacientes') }}">Pacientes</a></li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->esMedico())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-mdb-toggle="dropdown">Mi agenda</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('medico.horarios') }}">Horarios</a></li>
                                    <li><a class="dropdown-item" href="{{ route('medico.bloqueos') }}">Bloqueos</a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav flex-row align-items-center gap-1 mb-0">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-mdb-toggle="dropdown">
                                <span class="me-2">{{ auth()->user()->name }}</span>
                                @switch(auth()->user()->role)
                                    @case('admin') <span class="badge" style="border:2px solid #e4a11b;color:#e4a11b;background:transparent;padding:0.5rem 0.75rem"><i class="fa-solid fa-shield-halved me-1"></i>Admin</span> @break
                                    @case('medico') <span class="badge" style="border:2px solid #14a44d;color:#14a44d;background:transparent;padding:0.5rem 0.75rem"><i class="fa-solid fa-user-doctor me-1"></i>Médico</span> @break
                                    @case('paciente') <span class="badge" style="border:2px solid #3b71ca;color:#3b71ca;background:transparent;padding:0.5rem 0.75rem"><i class="fa-solid fa-user me-1"></i>Paciente</span> @break
                                    @case('recepcionista') <span class="badge" style="border:2px solid #54b4d3;color:#54b4d3;background:transparent;padding:0.5rem 0.75rem"><i class="fa-solid fa-phone me-1"></i>Recepcionista</span> @break
                                @endswitch
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a></li>
                            </ul>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        @if (session('success'))
            <div id="flash-success" data-message="{{ session('success') }}" style="display:none"></div>
        @endif
        @if (session('error'))
            <div id="flash-error" data-message="{{ session('error') }}" style="display:none"></div>
        @endif
        @yield('content')
    </main>

    @auth
        @if (auth()->user()->esPaciente() || auth()->user()->esMedico())
            @include('partials.chat-widget')
        @endif
    @endauth
    @stack('scripts')
</body>
</html>
