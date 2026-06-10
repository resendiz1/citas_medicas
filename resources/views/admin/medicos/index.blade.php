@extends('layouts.app')

@section('title', 'Gestionar Médicos')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:var(--yellow)">Médicos</h4>
        <a href="{{ route('admin.medicos.create') }}" class="neu-btn neu-btn-primary neu-btn-sm">+ Nuevo médico</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto flex-grow-1">
                <input type="text" name="search" class="neu-input form-control" placeholder="Buscar por nombre o email..." value="{{ request('search') }}" style="color:var(--text-primary)">
            </div>
            <div class="col-auto">
                <button type="submit" class="neu-btn neu-btn-sm">Buscar</button>
                @if (request('search'))
                    <a href="{{ route('admin.medicos') }}" class="neu-btn neu-btn-sm" style="background:#ff4444;color:#fff">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="neu-card p-4">
        <div class="table-responsive">
            <table class="table neu-table align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width:50px">Foto</th>
                        <th>Nombre</th>
                        <th>Especialidad</th>
                        <th>Cédula</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($medicos as $medico)
                        <tr>
                            <td>
                                @if ($medico->foto_url)
                                    <img src="{{ Storage::url($medico->foto_url) }}" alt="Foto"
                                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;cursor:pointer"
                                         onclick="window.open('{{ Storage::url($medico->foto_url) }}','_blank')">
                                @else
                                    <span style="display:inline-flex;width:36px;height:36px;border-radius:50%;background:var(--yellow);color:#121212;align-items:center;justify-content:center;font-size:0.8rem;font-weight:bold">
                                        {{ strtoupper(substr($medico->name, 0, 1)) }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $medico->name }}</td>
                            <td class="text-muted">{{ $medico->medicoPerfil->tipoMedico->nombre_tipo_medico ?? '—' }}</td>
                            <td class="text-muted">{{ $medico->medicoPerfil->cedula_profesional ?? '—' }}</td>
                            <td>
                                @if ($medico->medicoPerfil->activo ?? true)
                                    <span class="neu-badge" style="background:#00b894;color:#fff;font-size:0.65rem">Activo</span>
                                @else
                                    <span class="neu-badge" style="background:#ff4444;color:#fff;font-size:0.65rem">Inactivo</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="neu-btn neu-btn-sm dropdown-toggle d-flex align-items-center gap-1" data-mdb-toggle="dropdown" style="background:var(--yellow);color:#121212">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end neu-dropdown" style="min-width:170px">
                                        <li><a class="dropdown-item" href="{{ route('admin.medicos.show', $medico->id) }}"><i class="fa-regular fa-eye fa-fw me-1"></i> Perfil</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.medicos.edit', $medico->id) }}"><i class="fa-regular fa-pen-to-square fa-fw me-1"></i> Editar</a></li>
                                        <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.08)"></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.medicos.horarios', $medico->id) }}"><i class="fa-regular fa-clock fa-fw me-1"></i> Horarios</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.medicos.bloqueos', $medico->id) }}"><i class="fa-regular fa-calendar-xmark fa-fw me-1"></i> Bloqueos</a></li>
                                        <li><hr class="dropdown-divider" style="border-color:rgba(255,255,255,0.08)"></li>
                                        <li>
                                            <form action="{{ route('admin.medicos.destroy', $medico->id) }}" method="POST" class="d-inline w-100" onsubmit="return confirm('¿Eliminar médico?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item" style="background:none;border:none;width:100%;text-align:left;color:var(--text-primary)"><i class="fa-regular fa-trash-can fa-fw me-1" style="color:#ff4444"></i> Eliminar</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">No hay médicos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <br><br><br><br>
        @if ($medicos instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 d-flex justify-content-center">
                {{ $medicos->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
