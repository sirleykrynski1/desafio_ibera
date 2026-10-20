<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarEstablecimientoGobiernoRequest;
use App\Models\AlertaClimatica;
use App\Models\AnalisisLaboratorio;
use App\Models\Establecimiento;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InspectorController extends Controller
{
    public function index(Request $request): View
    {
        $datos = $request->validate([
            'zona' => ['nullable', 'string', 'max:255'],
            'destino' => ['nullable', 'in:cursos_agua,laguna,conducto_pluvial,absorcion_suelo'],
            'estado' => ['nullable', 'in:verde,amarillo,rojo'],
        ]);
        $establecimientos = $this->establecimientosConUltimoAnalisis()
            ->when($datos['zona'] ?? null, fn (Builder $query, string $zona) => $query->where('ubicacion', 'like', '%'.$zona.'%'))
            ->when($datos['destino'] ?? null, fn (Builder $query, string $destino) => $query->where('tipo_destino_vuelco', $destino))
            ->get();
        $resultado = match ($datos['estado'] ?? null) {
            'verde' => 'cumple', 'amarillo' => 'alerta', 'rojo' => 'incumple', default => null,
        };
        if ($resultado !== null) {
            $establecimientos = $establecimientos->filter(fn (Establecimiento $hotel) => $this->evaluacion($hotel->analisisLaboratorios->first()) === $resultado)->values();
        }
        $propietarios = User::where('rol', 'propietario')->orderBy('nombre')->get(['id', 'nombre', 'apellido', 'email']);

        return view('admin.inspectores', compact('establecimientos', 'propietarios'));
    }

    public function show(int $id): View
    {
        $establecimiento = Establecimiento::findOrFail($id);
        $historial_analisis = $establecimiento->analisisLaboratorios()
            ->orderByDesc('fecha_muestra')->orderByDesc('analisis_laboratorio_id')->get();
        $alertas_establecimiento = $this->obtenerAlertasReales($id);

        return view('admin.detalle-establecimiento', compact('establecimiento', 'historial_analisis', 'alertas_establecimiento'));
    }

    public function store(GuardarEstablecimientoGobiernoRequest $request): RedirectResponse
    {
        Establecimiento::create($request->validated());

        return back()->with('success', '¡Establecimiento creado correctamente!');
    }

    // Muestra la vista del formulario de análisis
    public function createAnalisis(): View
    {
        $establecimientos = DB::table('establecimientos')->get();

        return view('admin.cargar-analisis', compact('establecimientos'));
    }

    // Guarda el análisis de laboratorio en la base de datos
    public function storeAnalisis(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'establecimiento_id' => ['required', 'integer'],
            'fecha_muestra' => ['required', 'date'],
            'laboratorio' => ['required', 'string', 'max:255'],
            'resultado_sugerido' => ['nullable', 'string', 'max:50'],
            'estado' => ['nullable', 'string', 'max:50'],
            'resultado_final' => ['nullable', 'string', 'max:50'],
            'ruta_pdf' => ['nullable', 'string', 'max:255'],
            'revisado_por' => ['nullable', 'string', 'max:255'],
            'observaciones_revision' => ['nullable', 'string'],
        ]);

        DB::table('analisis_laboratorio')->insert([
            'establecimiento_id' => $validated['establecimiento_id'],
            'fecha_muestra' => $validated['fecha_muestra'],
            'laboratorio' => $validated['laboratorio'],
            'resultado_sugerido' => $validated['resultado_sugerido'] ?? 'pendiente',
            'estado' => $validated['estado'] ?? 'evaluado',
            'resultado_final' => $validated['resultado_final'] ?? null,
            'ruta_pdf' => $validated['ruta_pdf'] ?? 'sin_archivo.pdf',
            'revisado_por' => $validated['revisado_por'] ?? 'Inspector',
            'revisado_en' => now(),
            'observaciones_revision' => $validated['observaciones_revision'] ?? '-',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', '¡Análisis guardado con éxito!');
    }

    public function updateEstablecimiento(GuardarEstablecimientoGobiernoRequest $request, int $id): RedirectResponse
    {
        Establecimiento::findOrFail($id)->update($request->validated());

        return back()->with('success', '¡Establecimiento actualizado correctamente!');
    }

    public function destroyEstablecimiento(int $id): RedirectResponse
    {
        Establecimiento::findOrFail($id)->delete();

        return back()->with('success', '¡Establecimiento eliminado del sistema!');
    }

    public function dashboard(): View
    {
        $establecimientos = $this->establecimientosConUltimoAnalisis()->get();
        $total_establecimientos = $establecimientos->count();
        $evaluaciones = $establecimientos->map(fn (Establecimiento $hotel) => $this->evaluacion($hotel->analisisLaboratorios->first()));
        $en_norma = $evaluaciones->filter(fn ($valor) => $valor === 'cumple')->count();
        $en_alerta = $evaluaciones->filter(fn ($valor) => $valor === 'alerta')->count();
        $incumplen = $evaluaciones->filter(fn ($valor) => $valor === 'incumple')->count();
        $sin_evaluacion = $evaluaciones->filter(fn ($valor) => $valor === null)->count();
        $alertas_recientes = $this->obtenerAlertasReales()->take(3);
        $recomendados = $establecimientos->filter(fn (Establecimiento $hotel) => $this->evaluacion($hotel->analisisLaboratorios->first()) === 'cumple')->take(3)->values();

        return view('admin.dashboard', compact('total_establecimientos', 'en_norma', 'en_alerta', 'incumplen', 'sin_evaluacion', 'alertas_recientes', 'recomendados'));
    }

    public function alertas(): View
    {
        $alertas = $this->obtenerAlertasReales();

        return view('admin.alertas', compact('alertas'));
    }

    /** @return Builder<Establecimiento> */
    private function establecimientosConUltimoAnalisis(): Builder
    {
        return Establecimiento::with(['analisisLaboratorios' => fn ($query) => $query
            ->orderByDesc('fecha_muestra')->orderByDesc('analisis_laboratorio_id')->limit(1)])->orderBy('id');
    }

    private function evaluacion(?AnalisisLaboratorio $analisis): ?string
    {
        if ($analisis === null || in_array($analisis->estado, ['observado', 'pendiente_lectura'], true)) {
            return null;
        }

        return in_array($analisis->resultado_sugerido, ['cumple', 'alerta', 'incumple'], true)
            ? $analisis->resultado_sugerido : null;
    }

    /** @return Collection<int, object> */
    private function obtenerAlertasReales(?int $establecimientoId = null): Collection
    {
        return AlertaClimatica::with('establecimiento')
            ->when($establecimientoId !== null, fn (Builder $query) => $query->where('establecimiento_id', $establecimientoId))
            ->whereNull('revisada_en')
            ->where(function (Builder $query): void {
                $query->whereDate('periodo_hasta', '>=', today())
                    ->orWhere(function (Builder $query): void {
                        $query->whereNull('periodo_hasta')->whereDate('fecha_evento', '>=', today());
                    });
            })
            ->orderByDesc('consultado_en')->orderByDesc('alerta_climatica_id')->get()
            ->map(fn (AlertaClimatica $alerta) => (object) [
                'id' => $alerta->getKey(),
                'establecimiento' => $alerta->establecimiento?->nombre ?? 'Establecimiento no disponible',
                'mensaje' => (collect($alerta->detalle['alerta']['motivos'] ?? [])->filter(fn ($motivo) => is_string($motivo))->implode(' ') ?: $alerta->tipo).' · '.$alerta->milimetros_lluvia.' mm de lluvia',
                'ultimo_analisis' => $alerta->consultado_en?->diffForHumans() ?? 'Sin fecha de consulta',
                'prioridad' => 'Preventiva',
                'color' => 'warning',
                'icono' => 'bi-cloud-rain-heavy-fill',
            ]);
    }

    public function exportarCsv(): StreamedResponse
    {
        $establecimientos = $this->establecimientosConUltimoAnalisis()->get();

        return response()->streamDownload(function () use ($establecimientos): void {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nombre', 'CUIT', 'Ubicación', 'Rubro', 'Destino Vuelco', 'Evaluación automática', 'Dictamen final'], ',', '"', '');
            foreach ($establecimientos as $est) {
                $ultimo = $est->analisisLaboratorios->first();
                $fila = [
                    $est->id, $est->nombre, $est->cuit, $est->ubicacion, $est->rubro, $est->tipo_destino_vuelco,
                    ucfirst($this->evaluacion($ultimo) ?? ($ultimo === null ? 'Sin análisis' : 'Sin evaluación automática')),
                    $ultimo?->resultado_final ?? 'Sin dictamen',
                ];
                $fila = array_map(fn ($valor) => preg_match('/^[=+@\\-\\t\\r\\n]/', (string) $valor) ? "'".$valor : $valor, $fila);
                fputcsv($file, $fila, ',', '"', '');
            }
            fclose($file);
        }, 'reporte_icaa_establecimientos.csv', ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
