<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InspectorController extends Controller
{
    // Función para mostrar el panel con filtros
    public function index(Request $request)
    {
        $query = DB::table('establecimientos');

        // Filtro 1: Por Zona / Ubicación
        if ($request->filled('zona')) {
            $query->where('ubicacion', 'like', '%'.$request->zona.'%');
        }

        // Filtro 2: Por Destino de Vuelco
        if ($request->filled('destino')) {
            $query->where('tipo_destino_vuelco', $request->destino);
        }

        // Filtro 3: Por Estado de Cumplimiento conectado a analisis_laboratorio
        if ($request->filled('estado') && Schema::hasTable('analisis_laboratorio')) {
            $estadosBuscados = match ($request->estado) {
                'verde' => ['aprobado', 'cumple'],
                'amarillo' => ['alerta'],
                'rojo' => ['rechazado', 'incumple'],
                default => [],
            };

            if (! empty($estadosBuscados)) {
                $todosAnalisis = DB::table('analisis_laboratorio')->get();
                $idsEstablecimientos = $todosAnalisis
                    ->filter(function ($a) use ($estadosBuscados) {
                        $estado = strtolower((string) ($a->resultado_final ?? $a->resultado_sugerido ?? $a->resultado ?? ''));
                        return in_array($estado, $estadosBuscados, true);
                    })
                    ->pluck('establecimiento_id')
                    ->unique()
                    ->values()
                    ->all();

                $pk = Schema::hasColumn('establecimientos', 'id') ? 'id' : 'id_establecimiento';
                $query->whereIn($pk, $idsEstablecimientos);
            }
        }

        $establecimientos = $query->get();

        return view('admin.inspectores', compact('establecimientos'));
    }

    // Función para ver el detalle completo de un establecimiento
    public function show($id)
    {
        $pk = Schema::hasColumn('establecimientos', 'id') ? 'id' : 'id_establecimiento';
        $establecimiento = DB::table('establecimientos')->where($pk, $id)->first();

        if (! $establecimiento) {
            return redirect()->route('admin.inspectores')->with('error', 'Establecimiento no encontrado.');
        }

        // Historial real de análisis del establecimiento (sin depender de columna id)
        $historial_analisis = collect();
        if (Schema::hasTable('analisis_laboratorio')) {
            $historial_analisis = DB::table('analisis_laboratorio')
                ->where('establecimiento_id', $id)
                ->get()
                ->sortByDesc(function ($item) {
                    return $item->fecha_muestra ?? $item->fecha ?? $item->created_at ?? 0;
                })
                ->values();
        }

        // Alertas reales del establecimiento
        $alertas_establecimiento = collect();
        if (Schema::hasTable('alertas_climaticas')) {
            $alertas_establecimiento = DB::table('alertas_climaticas')
                ->where('establecimiento_id', $id)
                ->get();
        }

        return view('admin.detalle-establecimiento', compact('establecimiento', 'historial_analisis', 'alertas_establecimiento'));
    }

    // Función para crear un nuevo establecimiento (con validación del servidor)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'cuit' => ['required', 'string', 'max:20'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'rubro' => ['required', 'string', 'max:100'],
            'capacidad_maxima' => ['nullable', 'integer', 'min:0'],
            'capacidad_biodigestor' => ['nullable', 'numeric', 'min:0'],
            'tipo_destino_vuelco' => ['required', 'string', 'max:100'],
        ]);

        DB::table('establecimientos')->insert([
            'user_id' => auth()->id() ?? 1,
            'nombre' => $validated['nombre'],
            'cuit' => $validated['cuit'],
            'ubicacion' => $validated['ubicacion'],
            'rubro' => $validated['rubro'],
            'latitud' => 0,
            'longitud' => 0,
            'capacidad_maxima' => $validated['capacidad_maxima'] ?? 0,
            'capacidad_biodigestor' => $validated['capacidad_biodigestor'] ?? 0,
            'tipo_destino_vuelco' => $validated['tipo_destino_vuelco'],
        ]);

        return redirect()->back()->with('success', '¡Establecimiento creado correctamente!');
    }

    // Muestra la vista del formulario de análisis
    public function createAnalisis()
    {
        $establecimientos = DB::table('establecimientos')->get();

        return view('admin.cargar-analisis', compact('establecimientos'));
    }

    // Guarda el análisis de laboratorio en la base de datos
    public function storeAnalisis(Request $request)
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

    // Función para actualizar los datos (con validación del servidor)
    public function updateEstablecimiento(Request $request, $id)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'cuit' => ['required', 'string', 'max:20'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'rubro' => ['required', 'string', 'max:100'],
            'capacidad_maxima' => ['nullable', 'integer', 'min:0'],
            'capacidad_biodigestor' => ['nullable', 'numeric', 'min:0'],
            'tipo_destino_vuelco' => ['required', 'string', 'max:100'],
        ]);

        $pk = Schema::hasColumn('establecimientos', 'id') ? 'id' : 'id_establecimiento';

        DB::table('establecimientos')->where($pk, $id)->update([
            'nombre' => $validated['nombre'],
            'cuit' => $validated['cuit'],
            'ubicacion' => $validated['ubicacion'],
            'rubro' => $validated['rubro'],
            'capacidad_maxima' => $validated['capacidad_maxima'] ?? 0,
            'capacidad_biodigestor' => $validated['capacidad_biodigestor'] ?? 0,
            'tipo_destino_vuelco' => $validated['tipo_destino_vuelco'],
        ]);

        return redirect()->back()->with('success', '¡Establecimiento actualizado correctamente!');
    }

    // Función para eliminar
    public function destroyEstablecimiento($id)
    {
        $pk = Schema::hasColumn('establecimientos', 'id') ? 'id' : 'id_establecimiento';
        DB::table('establecimientos')->where($pk, $id)->delete();

        return redirect()->back()->with('success', '¡Establecimiento eliminado del sistema!');
    }

    // Función para el Dashboard de Resumen con datos reales
    public function dashboard()
    {
        $establecimientos = DB::table('establecimientos')->get();
        $total_establecimientos = $establecimientos->count();
        $en_norma = 0;
        $en_alerta = 0;
        $incumplen = 0;
        $idsEnNorma = [];

        if (Schema::hasTable('analisis_laboratorio')) {
            $analisis = DB::table('analisis_laboratorio')->get();

            $ultimosPorEstablecimiento = $analisis->groupBy('establecimiento_id')->map(function ($grupo) {
                return $grupo->sortByDesc(function ($item) {
                    return $item->fecha_muestra ?? $item->fecha ?? $item->created_at ?? 0;
                })->first();
            });

            foreach ($ultimosPorEstablecimiento as $estId => $item) {
                $estadoActual = strtolower((string) ($item->resultado_final ?? $item->resultado_sugerido ?? $item->resultado ?? ''));

                if (in_array($estadoActual, ['aprobado', 'cumple'], true)) {
                    $en_norma++;
                    $idsEnNorma[] = $estId;
                } elseif ($estadoActual === 'alerta') {
                    $en_alerta++;
                } elseif (in_array($estadoActual, ['rechazado', 'incumple'], true)) {
                    $incumplen++;
                }
            }
        }

        $alertas_recientes = $this->obtenerAlertasReales()->take(3);

        $recomendados = $establecimientos->filter(function ($est) use ($idsEnNorma) {
            $idEst = $est->id ?? $est->id_establecimiento ?? null;
            return in_array($idEst, $idsEnNorma);
        })->take(3)->values();

        return view('admin.dashboard', compact(
            'total_establecimientos',
            'en_norma',
            'en_alerta',
            'incumplen',
            'alertas_recientes',
            'recomendados'
        ));
    }

    // Función para el panel de Alertas Climáticas y de Riesgo con datos reales
    public function alertas()
    {
        $alertas = $this->obtenerAlertasReales();

        return view('admin.alertas', compact('alertas'));
    }

    // Método auxiliar seguro para armar las alertas climáticas reales
    private function obtenerAlertasReales()
    {
        if (! Schema::hasTable('alertas_climaticas')) {
            return collect();
        }

        $establecimientos = DB::table('establecimientos')->get()->keyBy(function ($est) {
            return $est->id ?? $est->id_establecimiento;
        });

        return DB::table('alertas_climaticas')
            ->get()
            ->sortByDesc(function ($item) {
                return $item->created_at ?? 0;
            })
            ->values()
            ->map(function ($item, $index) use ($establecimientos) {
                $nivel = strtolower((string) ($item->nivel ?? $item->prioridad ?? 'media'));
                $esAlta = in_array($nivel, ['alta', 'critica', 'peligro', 'rojo'], true);
                $est = $establecimientos->get($item->establecimiento_id ?? null);

                return (object) [
                    'id' => $item->id ?? $item->id_alerta ?? ($index + 1),
                    'establecimiento' => $est->nombre ?? 'Establecimiento #'.($item->establecimiento_id ?? '-'),
                    'mensaje' => $item->mensaje ?? $item->descripcion ?? 'Alerta climática registrada',
                    'ultimo_analisis' => isset($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->diffForHumans() : 'Reciente',
                    'prioridad' => $esAlta ? 'Alta' : 'Media',
                    'color' => $esAlta ? 'danger' : 'warning',
                    'icono' => $esAlta ? 'bi-cloud-rain-heavy-fill' : 'bi-droplet-half',
                ];
            });
    }

    // Función para descargar el reporte CSV
    public function exportarCsv()
    {
        $establecimientos = DB::table('establecimientos')->get();
        $analisisPorEst = Schema::hasTable('analisis_laboratorio')
            ? DB::table('analisis_laboratorio')->get()->groupBy('establecimiento_id')
            : collect();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=reporte_icaa_establecimientos.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($establecimientos, $analisisPorEst) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Nombre', 'CUIT', 'Ubicación', 'Rubro', 'Destino Vuelco', 'Estado']);

            foreach ($establecimientos as $est) {
                $idEst = $est->id ?? $est->id_establecimiento ?? null;
                $ultimoEstado = 'Sin análisis';

                if ($idEst && $analisisPorEst->has($idEst)) {
                    $ultimo = $analisisPorEst->get($idEst)->sortByDesc(function ($item) {
                        return $item->fecha_muestra ?? $item->fecha ?? $item->created_at ?? 0;
                    })->first();

                    if ($ultimo) {
                        $ultimoEstado = ucfirst((string) ($ultimo->resultado_final ?? $ultimo->resultado_sugerido ?? 'En revisión'));
                    }
                }

                fputcsv($file, [
                    $idEst,
                    $est->nombre,
                    $est->cuit,
                    $est->ubicacion,
                    $est->rubro,
                    $est->tipo_destino_vuelco,
                    $ultimoEstado,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}