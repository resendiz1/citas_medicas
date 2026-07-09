@extends('layouts.app')

@section('title', 'Estadísticas')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-2 p-4 d-flex align-items-center gap-3">
                <div style="font-size:2rem;color:#1266f1"><i class="fa fa-chart-simple"></i></div>
                <div>
                    <h3 class="mb-1">Estadísticas</h3>
                    <p class="mb-0 text-muted">
                        @switch(auth()->user()->role)
                            @case('admin') Panorama general del sistema @break
                            @case('medico') Mis indicadores clínicos @break
                            @case('paciente') Mi actividad @break
                        @endswitch
                    </p>
                </div>
            </div>
        </div>
    </div>

    @include('estadisticas._charts', ['statsUrl' => $statsUrl])
</div>
@endsection
