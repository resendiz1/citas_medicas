<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @auth
        <meta name="notificaciones-poll" content="{{ route('notificaciones.poll') }}">
    @endauth
    @php
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
    $cssFile = $manifest['resources/css/app.css']['file'] ?? '';
    $jsFile = $manifest['resources/js/app.js']['file'] ?? '';
    @endphp
    <link rel="stylesheet" href="/build/{{ $cssFile }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="/build/{{ $jsFile }}" defer></script>
    @stack('head')
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top" style="position:sticky !important;top:0;z-index:1020">
        <div class="container">
            <a class="navbar-brand fw-bold me-3 d-flex flex-column align-items-start" href="{{ route('dashboard') }}" style="line-height:1.2">Citas Médicas<small style="font-size:0.65rem;font-weight:400;opacity:0.8">&lt;JuanPancho's/&gt;</small></a>
            <div class="d-flex flex-grow-1 align-items-center justify-content-between flex-wrap" id="navbarNav">
                <ul class="navbar-nav flex-row align-items-center gap-1 mb-0">
                    @auth
                            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Home</a></li>
                        @if (auth()->user()->esAdmin())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-mdb-toggle="dropdown">Gestión</a>
                                <ul class="dropdown-menu neu-dropdown">
                                    <li><a class="dropdown-item" href="{{ route('admin.citas') }}">Citas</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.medicos') }}">Médicos</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pacientes') }}">Pacientes</a></li>
                                </ul>
                            </li>
                        @endif
                        @if (auth()->user()->esMedico())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-mdb-toggle="dropdown">Mi agenda</a>
                                <ul class="dropdown-menu neu-dropdown">
                                    <li><a class="dropdown-item" href="{{ route('medico.horarios') }}">Horarios</a></li>
                                    <li><a class="dropdown-item" href="{{ route('medico.bloqueos') }}">Bloqueos</a></li>
                                </ul>
                            </li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav flex-row align-items-center gap-1 mb-0">
                    <li class="nav-item d-flex align-items-center me-2">
                        <button id="theme-toggle" class="neu-btn neu-btn-sm" style="font-size:0.9rem;padding:0.25rem 0.6rem;line-height:1" aria-label="Cambiar tema">
                            <span id="theme-icon">🌙</span>
                        </button>
                    </li>
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-mdb-toggle="dropdown">
                                <span class="me-2">{{ auth()->user()->name }}</span>
                                @switch(auth()->user()->role)
                                    @case('admin') <span class="badge neu-badge" style="background:var(--yellow);color:#121212">Admin</span> @break
                                    @case('medico') <span class="badge neu-badge" style="background:#00b894;color:#fff">Médico</span> @break
                                    @case('paciente') <span class="badge neu-badge" style="background:var(--yellow);color:#121212">Paciente</span> @break
                                    @case('recepcionista') <span class="badge neu-badge" style="background:#1e90ff;color:#fff">Recepcionista</span> @break
                                @endswitch
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end neu-dropdown">
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

    @stack('scripts')
</body>
</html>
