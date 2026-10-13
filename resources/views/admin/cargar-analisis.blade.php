@extends('layouts.app')

@section('content')
<div class="py-2">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold">Cargar análisis de laboratorio</h2>
                    <p class="text-muted">Registro oficial de muestras y resultados de efluentes.</p>
                </div>
            </div>

            <!-- FORMULARIO OFICIAL -->
            <div class="card card-custom p-4 mb-4">
                <form action="{{ route('analisis.guardar') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-success">Establecimiento Auditado</label>
                        <select name="establecimiento_id" class="form-select form-select-lg" required>
                            <option value="">-- Seleccione el establecimiento --</option>
                            @foreach($establecimientos as $est)
                                <option value="{{ $est->id }}">{{ $est->nombre }} ({{ $est->ubicacion }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold small">Fecha de la Muestra</label>
                            <input type="date" name="fecha_muestra" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold small">Laboratorio Emisor</label>
                            <input type="text" name="laboratorio" class="form-control" required placeholder="Ej: Laboratorio H2O Corrientes">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-semibold small">Resultado Sugerido</label>
                            <select name="resultado_sugerido" class="form-select" required>
                                <option value="apto">Apto / En Norma</option>
                                <option value="observado">Observado</option>
                                <option value="no_apto">No Apto</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-semibold small">Estado</label>
                            <select name="estado" class="form-select" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-semibold small">Resultado Final</label>
                            <select name="resultado_final" class="form-select" required>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold small">Ruta del PDF (Opcional)</label>
                            <input type="text" name="ruta_pdf" class="form-control" placeholder="Ej: /pdfs/analisis_1.pdf">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-semibold small">Revisado Por</label>
                            <input type="text" name="revisado_por" class="form-control" value="Inspector ICAA" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold small">Observaciones de Revisión</label>
                        <textarea name="observaciones_revision" class="form-control" rows="3" placeholder="Detalles sobre los parámetros o el estado del efluente..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="{{ url('/icaa/inspectores') }}" class="btn btn-outline-secondary rounded-pill fw-bold px-4">Cancelar</a>
                        <button type="submit" class="btn btn-success rounded-pill fw-bold px-4">Guardar Análisis</button>
                    </div>
                </form>
            </div>
    </div>
@endsection