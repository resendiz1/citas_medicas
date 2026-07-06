@extends('layouts.app')

@section('title', 'Página no encontrada')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height:70vh">
    <div class="text-center">
        <div style="font-size:6rem;font-weight:800;color:#1266f1;line-height:1;margin-bottom:1rem">404</div>
        <h4 class="fw-bold mb-2" style="color:var(--text-primary)">Página no encontrada</h4>
        <p class="text-muted mb-4" style="max-width:400px;margin:0 auto">La página que buscas no existe o ha sido movida.</p>
        <a href="{{ url('/') }}" class="btn btn-primary">
            <i class="fa-solid fa-arrow-left me-1"></i>Volver al inicio
        </a>
    </div>
</div>
@endsection
