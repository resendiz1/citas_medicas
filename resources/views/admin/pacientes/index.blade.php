@extends('layouts.app')

@section('title', 'Gestionar Pacientes')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:var(--yellow)">Pacientes</h4>
        <a href="{{ route('admin.pacientes.create') }}" class="neu-btn neu-btn-primary neu-btn-sm">+ Nuevo paciente</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto flex-grow-1">
                <input type="text" name="search" class="neu-input form-control" placeholder="Buscar por nombre o email..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="neu-btn neu-btn-sm">Buscar</button>
                @if (request('search'))
                    <a href="{{ route('admin.pacientes') }}" class="neu-btn neu-btn-sm" style="background:#ff4444;color:#fff">Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="neu-card p-4">
        <div class="table-responsive">
            <table class="table neu-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pacientes as $paciente)
                        <tr>
                            <td>{{ $paciente->name }}</td>
                            <td class="text-muted">{{ $paciente->email }}</td>
                            <td class="text-muted">{{ $paciente->telefono ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.pacientes.edit', $paciente->id) }}" class="neu-btn neu-btn-sm neu-btn-warning">Editar</a>
                                <form action="{{ route('admin.pacientes.destroy', $paciente->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="neu-btn neu-btn-sm neu-btn-danger" onclick="return confirm('¿Eliminar paciente?')">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No hay pacientes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pacientes instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 d-flex justify-content-center">
                {{ $pacientes->appends(request()->query())->links() }}
            </div>
        @endif
        <br><br><br><br>
    </div>
</div>
@endsection
