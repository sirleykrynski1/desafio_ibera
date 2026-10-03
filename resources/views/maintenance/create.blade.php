@extends('layouts.app')

@section('title', 'Registrar Limpieza de Fosa - Mantenimiento Ambiental')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <!-- Tarjeta de formulario -->
        <div class="card card-eco shadow-sm">
            <div class="card-header card-eco-header py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h2 class="h4 mb-0 fw-bold d-flex align-items-center gap-2">
                        <span>🐸</span> Registrar Limpieza de Fosa Séptica
                    </h2>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                        🌿 Declaración Jurada
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                <p class="text-muted mb-4 small">
                    Completa el formulario para registrar la extracción de lixiviados y mantenimiento sanitario del establecimiento. Este comprobante será auditado por las autoridades ambientales del Parque Iberá.
                </p>
                @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

                <form action="{{ route('mantenimiento.guardar') }}" method="POST" enctype="multipart/form-data">
                    <!-- Token CSRF simulado para Blade -->
                    @csrf

                    <div class="row g-3">
                        <!-- Selección de Establecimiento -->
                        <div class="col-md-12">
                            <label for="establecimiento_id" class="form-label fw-semibold text-dark">
                                🦦 Establecimiento / Complejo Turístico <span class="text-danger">*</span>
                            </label>
                            <select class="form-select border-success-subtle shadow-none" id="establecimiento_id" name="establecimiento_id" required>
                                <option value="" selected disabled>Selecciona el establecimiento...</option>
                                <option value="1">Eco-Lodge El Yacaré Dorado (Portal Carambola)</option>
                                <option value="2">Posada de las Garzas Rosadas (Portal Laguna Iberá)</option>
                                <option value="3">Cabañas del Humedal Esteros (Portal San Nicolás)</option>
                            </select>
                        </div>

                        <!-- Fecha de la Limpieza -->
                        <div class="col-md-6">
                            <label for="maintenance_date" class="form-label fw-semibold text-dark">
                                📅 Fecha de Realización del Servicio <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control border-success-subtle shadow-none" id="maintenance_date" name="fecha_mantenimiento" value="{{ date('Y-m-d') }}" required>
                            <div class="form-text">Fecha en la que la empresa atmosférica ejecutó la descarga.</div>
                        </div>

                        <!-- Volumen Extraído -->
                        <div class="col-md-6">
                            <label for="volume_extracted" class="form-label fw-semibold text-dark">
                                💧 Volumen Vaciado (Litros / m³)
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control border-success-subtle shadow-none" id="volume_extracted" name="volumen_extraido" placeholder="Ej: 3500" min="100" step="50">
                                <span class="input-group-text bg-light text-muted">Litros</span>
                            </div>
                        </div>

                        <!-- Empresa / Operador Certificado -->
                        <div class="col-md-12">
                            <label for="operadora_empresa" class="form-label fw-semibold text-dark">
                                🚛 Empresa / Camión Atmosférico Habilitado
                            </label>
                            <input type="text" class="form-control border-success-subtle shadow-none" id="operadora_empresa" name="operadora_empresa" placeholder="Ej: Servicios Ambientales Correntinos S.A.">
                        </div>

                        <!-- Input de archivo: Comprobante / Manifiesto -->
                        <div class="col-12">
                            <label for="archivo_comprobante" class="form-label fw-semibold text-dark">
                                📄 Comprobante / Manifiesto de Disposición Final <span class="text-danger">*</span>
                            </label>
                            <input class="form-control border-success-subtle shadow-none" type="file" id="comprobante_foto" name="comprobante_foto" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="form-text">
                                Formatos admitidos: PDF, JPG, PNG (Remito firmado por el transportista autorizado, máx. 10MB).
                            </div>
                        </div>

                        <!-- Observaciones adicionales -->
                        <div class="col-12">
                            <label for="observaciones" class="form-label fw-semibold text-dark">
                                📝 Observaciones o Estado del Sistema
                            </label>
                            <textarea class="form-control border-success-subtle shadow-none" id="observaciones" name="observaciones" rows="3" placeholder="Indicar si se realizó inspección de cámaras desgrasadoras, estado de las tapas o filtros..."></textarea>
                        </div>

                        <!-- Alerta informativa ecológica -->
                        <div class="col-12">
                            <div class="alert alert-success d-flex align-items-center gap-2 mb-0 py-2" role="alert">
                                <span class="fs-5">🦩</span>
                                <small>
                                    Al enviar este registro, certificas bajo declaración jurada la correcta disposición final de efluentes conforme a la normativa de protección de los Esteros del Iberá.
                                </small>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="col-12 d-flex justify-content-end gap-2 pt-3 border-top border-success-subtle">
                            <a href="{{ url('/establishments') }}" class="btn btn-outline-secondary px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-eco px-4 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                                <span>🐸</span> Guardar Registro de Limpieza
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
