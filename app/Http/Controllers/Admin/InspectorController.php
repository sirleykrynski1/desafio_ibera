<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // Necesitamos esto para recibir los datos del formulario
use Illuminate\Support\Facades\DB;

class InspectorController extends Controller
{
    // Función para mostrar el panel con filtros
    public function index(Request $request)
    {
        $query = DB::table('establecimientos');

        // Filtro 1: Por Zona / Ubicación
        if ($request->filled('zona')) {
            $query->where('ubicacion', 'like', '%' . $request->zona . '%');
        }

        // Filtro 2: Por Destino de Vuelco
        if ($request->filled('destino')) {
            $query->where('tipo_destino_vuelco', $request->destino);
        }

        /* 
         * TODO PARA INTEGRACIÓN (Persona 1 / 5): 
         * Descomentar este bloque cuando la tabla 'analisis_laboratorio' esté lista.
         *
         * if ($request->filled('estado')) {
         *     $resultado_bd = '';
         *     if ($request->estado == 'verde') $resultado_bd = 'cumple';
         *     if ($request->estado == 'amarillo') $resultado_bd = 'alerta';
         *     if ($request->estado == 'rojo') $resultado_bd = 'incumple';
         *
         *     $query->whereExists(function ($q) use ($resultado_bd) {
         *         $q->select(DB::raw(1))
         *           ->from('analisis_laboratorio')
         *           ->whereColumn('analisis_laboratorio.establecimiento_id', 'establecimientos.id')
         *           ->where('resultado', $resultado_bd);
         *     });
         * }
         */

        $establecimientos = $query->get();

        return view('admin.inspectores', compact('establecimientos'));
    }

    // Función para ver el detalle completo de un establecimiento
    public function show($id)
    {
        // Buscamos los datos del establecimiento
        $establecimiento = DB::table('establecimientos')->where('id', $id)->first();

        // Si por algún motivo escriben un ID que no existe en la URL, lo devolvemos
        if (!$establecimiento) {
            return redirect('/icaa/inspectores')->with('error', 'Establecimiento no encontrado.');
        }

        /* 
         * TODO PARA INTEGRACIÓN:
         * Cuando la Persona 1 termine, acá buscaremos el historial real:
         * $analisis = DB::table('analisis_laboratorio')->where('establecimiento_id', $id)->orderBy('fecha', 'desc')->get();
         */
        $historial_analisis = []; // Lo dejamos vacío por ahora para que no tire error

        return view('admin.detalle-establecimiento', compact('establecimiento', 'historial_analisis'));
    }

    // Función para crear un nuevo establecimiento
    public function store(Request $request)
    {
        DB::table('establecimientos')->insert([
            'user_id' => auth()->id() ?? 1, 
            'nombre' => $request->nombre,
            'cuit' => $request->cuit,
            'ubicacion' => $request->ubicacion,
            'rubro' => $request->rubro,
            
            // Le mandamos un 0 de forma "invisible" para cumplir con la base de datos
            'latitud' => 0,
            'longitud' => 0,
            
            'capacidad_maxima' => $request->capacidad_maxima ?: 0,
            'capacidad_biodigestor' => $request->capacidad_biodigestor ?: 0,
            'tipo_destino_vuelco' => $request->tipo_destino_vuelco
        ]);

        return redirect('/icaa/inspectores')->with('success', '¡Establecimiento creado correctamente!');
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
        try {
            DB::table('analisis_laboratorio')->insert([
                'establecimiento_id' => $request->establecimiento_id,
                'fecha_muestra' => $request->fecha_muestra,
                'laboratorio' => $request->laboratorio,
                'resultado_sugerido' => $request->resultado_sugerido,
                'estado' => $request->estado,
                'resultado_final' => $request->resultado_final,
                'ruta_pdf' => $request->ruta_pdf ?? 'sin_archivo.pdf',
                'revisado_por' => $request->revisado_por ?? 'Inspector',
                'revisado_en' => now(), // Agregamos este por las dudas
                'observaciones_revision' => $request->observaciones_revision ?? '-',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return redirect('/icaa/inspectores')->with('success', '¡Análisis guardado con éxito!');
            
        } catch (\Exception $e) {
            // Si la base de datos rechaza el guardado, esto va a detener la pantalla 
            // y te va a mostrar exactamente qué columna está causando el problema.
            dd("ERROR AL GUARDAR:", $e->getMessage());
        }
    }
    
    // Función para actualizar los datos
    public function updateEstablecimiento(Request $request, $id)
    {
        DB::table('establecimientos')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'cuit' => $request->cuit,
            'ubicacion' => $request->ubicacion,
            'rubro' => $request->rubro,
            // Eliminamos latitud y longitud de este bloque para no borrar las coordenadas previas
            'capacidad_maxima' => $request->capacidad_maxima ?: 0,
            'capacidad_biodigestor' => $request->capacidad_biodigestor ?: 0,
            'tipo_destino_vuelco' => $request->tipo_destino_vuelco
        ]);

        return redirect('/icaa/inspectores')->with('success', '¡Establecimiento actualizado correctamente!');
    }

    // Función para eliminar
    public function destroyEstablecimiento($id)
    {
        DB::table('establecimientos')->where('id', $id)->delete();
        
        return redirect('/icaa/inspectores')->with('success', '¡Establecimiento eliminado del sistema!');
    }

    // Función para el Dashboard de Resumen
    public function dashboard()
    {
        // Contamos el total real de tu base de datos
        $total_establecimientos = DB::table('establecimientos')->count();

        /* 
         * TODO PARA INTEGRACIÓN (Persona 1 / 2):
         * Cuando conecten los laboratorios, cambiar estos números fijos 
         * por consultas reales (ej: count() donde resultado = 'cumple').
         */
        $en_norma = 12;   // Valor simulado para tu maquetación
        $en_alerta = 3;   // Valor simulado para tu maquetación
        $incumplen = 1;   // Valor simulado para tu maquetación

        return view('admin.dashboard', compact('total_establecimientos', 'en_norma', 'en_alerta', 'incumplen'));
    }

    // Función para el panel de Alertas Climáticas y de Riesgo
    public function alertas()
    {
        /* 
         * TODO PARA INTEGRACIÓN (Persona 1 / 2):
         * Acá se leerá la tabla de 'alertas_climaticas' generada por el Job de Python.
         */
        
        // Datos simulados para maquetar la vista
        $alertas = [
            (object)[
                'id' => 1,
                'establecimiento' => 'Ecoposada del Estero',
                'mensaje' => 'Alerta climática (lluvia intensa)',
                'ultimo_analisis' => '45 días',
                'prioridad' => 'Alta',
                'color' => 'danger',
                'icono' => 'bi-cloud-rain-heavy-fill'
            ],
            (object)[
                'id' => 2,
                'establecimiento' => 'Hostería Rincón',
                'mensaje' => 'Posible saturación de pozo absorbente',
                'ultimo_analisis' => '12 días',
                'prioridad' => 'Media',
                'color' => 'warning',
                'icono' => 'bi-droplet-half'
            ]
        ];

        return view('admin.alertas', compact('alertas'));
    }

    // Función para descargar el reporte CSV
    public function exportarCsv()
    {
        $establecimientos = DB::table('establecimientos')->get();

        // Configuramos las cabeceras para que el navegador sepa que es una descarga de archivo
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=reporte_icaa_establecimientos.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Función que escribe los datos línea por línea
        $callback = function() use($establecimientos) {
            $file = fopen('php://output', 'w');
            
            // 1. Escribimos los títulos de las columnas
            fputcsv($file, ['ID', 'Nombre', 'CUIT', 'Ubicación', 'Rubro', 'Destino Vuelco', 'Estado (Simulado)']);

            // 2. Recorremos los establecimientos de la BD y los agregamos al archivo
            foreach ($establecimientos as $est) {
                fputcsv($file, [
                    $est->id,
                    $est->nombre,
                    $est->cuit,
                    $est->ubicacion,
                    $est->rubro,
                    $est->tipo_destino_vuelco,
                    'En revisión' // TODO Integración: acá irá el estado real cuando esté listo
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
