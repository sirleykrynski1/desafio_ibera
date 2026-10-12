@extends('layouts.app')

@section('title', 'Insignias - Panel del Hotel')

@section('content')
<div class="container-fluid py-2">
    <div class="mb-4">
        <h2 class="fw-bold text-dark mb-1">Tus insignias de cumplimiento</h2>
        <p class="text-muted small">Mantené tus análisis en norma durante 6 meses consecutivos y obtené la insignia de "Hotel Recomendado".</p>
    </div>

    <!-- Progreso Actual -->
    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4 text-center">
        <div class="mx-auto mb-3 text-success display-4">
            <i class="bi bi-patch-check-fill"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">¡En camino!</h4>
        <p class="text-muted small mb-3">Te faltan 3 meses consecutivos de análisis en norma para obtener la insignia de "Hotel Recomendado".</p>
        
        <div class="progress mx-auto style-bar" style="max-width: 400px; height: 10px;">
            <div class="progress-bar bg-success" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <small class="text-muted mt-2 d-block">3/6 meses</small>
    </div>

    <!-- Listado de Insignias -->
    <h5 class="fw-bold text-dark mb-3">Insignias disponibles</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center opacity-75 bg-light">
                <i class="bi bi-award text-secondary display-6 mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Hotel Recomendado</h6>
                <small class="text-muted">6 meses consecutivos en norma</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center opacity-75 bg-light">
                <i class="bi bi-shield-check text-secondary display-6 mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Guardián del Iberá</h6>
                <small class="text-muted">Protección sostenida del ecosistema</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center bg-success-subtle text-success border-success">
                <i class="bi bi-tree text-success display-6 mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">Compromiso Ambiental</h6>
                <small class="text-success font-semibold">Insignia obtenida (3 meses en norma)</small>
            </div>
        </div>
    </div>
</div>
@endsection
