@extends('layouts.app')

@section('title', 'Gestionar Pacientes')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold" style="color:#1266f1">Pacientes</h4>
        <a href="{{ route('admin.pacientes.create') }}" class="btn btn-primary neu-btn-sm"><i class="fa fa-plus me-1"></i>+ Nuevo paciente</a>
    </div>

    <form method="GET" class="mb-3">
        <div class="row g-2 align-items-center">
            <div class="col-auto flex-grow-1">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nombre o email..." value="{{ request('search') }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="fa fa-search me-1"></i>Buscar</button>
                @if (request('search'))
                    <a href="{{ route('admin.pacientes') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-rotate-left me-1"></i>Limpiar</a>
                @endif
            </div>
        </div>
    </form>

    <div class="card shadow-2 p-4">
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
                                <a href="{{ route('admin.pacientes.edit', $paciente->id) }}" class="btn btn-outline-secondary btn-sm neu-btn-warning"><i class="fa fa-pen-to-square me-1"></i>Editar</a>
                                <form action="{{ route('admin.pacientes.destroy', $paciente->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-secondary btn-sm neu-btn-danger" onclick="return confirm('¿Eliminar paciente?')"><i class="fa fa-trash-can me-1"></i>Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-5"><div class="d-flex flex-column align-items-center gap-2"><i class="fa fa-users fa-2x text-muted opacity-50"></i><p class="fw-bold text-muted mb-0" style="font-size:1.1rem">No hay pacientes registrados.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pacientes instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="mt-3 d-flex justify-content-center">
                {{ $pacientes->appends(request()->query())->links() }}
            </div>
        @endif
        <br><br>
    </div>
</div>
@endsection
