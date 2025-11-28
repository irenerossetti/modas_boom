<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::with('usuario');

        // Búsqueda por nombre, apellido o CI/NIT
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('apellido', 'LIKE', "%{$search}%")
                  ->orWhere('ci_nit', 'LIKE', "%{$search}%");
            });
        }

        $clientes = $query->paginate(10); // Paginación para mejor rendimiento

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Exportar clientes a PDF (CU25)
     */
    public function exportarPdf(Request $request)
    {
        Log::info('ClienteController::exportarPdf - entrada. auth=' . (Auth::check() ? 'si' : 'no') . ', user_id=' . (Auth::id() ?? 'null') . ', id_rol=' . (Auth::user()->id_rol ?? 'null') . ', format=' . ($request->get('format') ?? 'pdf'));

        // If exports noauth is enabled (local only), add an early log and bypass authentication/role checks
        if (config('exports.noauth_enabled', false) === true && app()->environment('local')) {
            Log::warning('ClienteController::exportarPdf - EXPORT_NOAUTH_ENABLED: serving export without authentication (local only)');
        } else {
            // Solo administradores en entornos normales
            if (!Auth::check() || Auth::user()->id_rol !== 1) {
                abort(403, 'No tienes permisos para exportar la lista de clientes.');
            }
        }

        $query = Cliente::orderBy('nombre', 'asc');

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                  ->orWhere('apellido', 'LIKE', "%{$search}%")
                  ->orWhere('ci_nit', 'LIKE', "%{$search}%");
            });
        }

        $clientes = $query->get();
        $delimiter = $request->get('delimiter', config('exports.csv_delimiter', ';'));

        // --------------- Mitigaciones contra memory exhausted ---------------
        // Si el dataset es demasiado grande, no intentamos generar un PDF grande
        // Evitar intentos de renderizar PDFs grandes en peticiones web que puedan agotar memoria
        $maxForPdfWeb = config('app.max_clients_pdf_web', 200); // configurable (por defecto 200 para web)
        $maxForPdf = config('app.max_clients_pdf', 1000); // límite global por si acaso
        $clientesCount = $clientes->count();
        Log::info('ClienteController::exportarPdf - clientes total: ' . $clientesCount);

                        // stray / accidental line removed
        if ($request->get('format') === 'json') {
            Log::info('ClienteController::exportarPdf - formato json explícito pedido por usuario; devolviendo JSON');
            $filename = 'clientes_' . now()->format('Ymd_His') . '.json';
            $data = $clientes->map(function($cliente, $index) {
                return [
                    'num' => $index + 1,
                    'nombre_completo' => trim($cliente->nombre . ' ' . $cliente->apellido),
                    'ci_nit' => $cliente->ci_nit,
                    'email' => $cliente->email ?? null,
                    'telefono' => $cliente->telefono ?? null,
                ];
            })->toArray();
            $callback = function() use ($data) {
                echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            };
            return response()->stream($callback, 200, [
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }
        // Si el usuario solicita CSV explícitamente, devolvemos CSV aunque el conteo sea pequeño
        if ($request->get('format') === 'csv') {
            Log::info('ClienteController::exportarPdf - formato csv explícito pedido por usuario; devolviendo CSV');
            $filename = 'clientes_' . now()->format('Ymd_His') . '.csv';
            $columns = ['#', 'Nombre Completo', 'CI/NIT', 'Email', 'Teléfono'];

            $delimiter = $request->get('delimiter', config('exports.csv_delimiter', ';'));
            $callback = function() use ($clientes, $columns, $delimiter) {
                $FH = fopen('php://output', 'w');
                echo chr(0xEF) . chr(0xBB) . chr(0xBF);
                fputcsv($FH, $columns, $delimiter);
                foreach ($clientes as $index => $cliente) {
                    $row = [
                        $index + 1,
                        trim($cliente->nombre . ' ' . $cliente->apellido),
                        $cliente->ci_nit,
                        $cliente->email ?? 'N/A',
                        $cliente->telefono ?? 'N/A',
                    ];
                    fputcsv($FH, $row, $delimiter);
                }
                fclose($FH);
            };
            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        if ($clientesCount > $maxForPdfWeb) {
            Log::warning("ClienteController::exportarPdf - La lista contiene {$clientesCount} clientes; excede el máximo permitido ({$maxForPdf}). Generando CSV como fallback.");

            // Generar CSV como fallback y devolverlo al usuario (más ligero que PDF)
            $filename = 'clientes_' . now()->format('Ymd_His') . '.csv';
            $columns = ['#', 'Nombre Completo', 'CI/NIT', 'Email', 'Teléfono'];

            $delimiter = $request->get('delimiter', config('exports.csv_delimiter', ';'));
            $callback = function() use ($clientes, $columns, $delimiter) {
                $FH = fopen('php://output', 'w');
                // BOM for UTF-8 to ensure Excel opens it correctly
                echo chr(0xEF) . chr(0xBB) . chr(0xBF);
                fputcsv($FH, $columns, $delimiter);
                foreach ($clientes as $index => $cliente) {
                    $row = [
                        $index + 1,
                        trim($cliente->nombre . ' ' . $cliente->apellido),
                        $cliente->ci_nit,
                        $cliente->email ?? 'N/A',
                        $cliente->telefono ?? 'N/A',
                    ];
                    fputcsv($FH, $row, $delimiter);
                }
                fclose($FH);
            };

                return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Incrementar temporalmente límite de memoria y tiempo de ejecución para permitir la generación de PDFs grandes
        // Esto es solo una mitigación temporal en entornos controlados.
        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(300);
            Log::info('ClienteController::exportarPdf - memory_limit y time_limit aumentados temporalmente para generación PDF.');
        } catch (\Throwable $e) {
            Log::warning('ClienteController::exportarPdf - No se pudo ajustar memory_limit/time_limit: ' . $e->getMessage());
        }

        // Si DomPDF está disponible, generar PDF; sino, mostrar vista HTML con instrucción
        // Prefer using the registered service if it's bound into the container for greater compatibility
        Log::info('ClienteController::exportarPdf - comprobando dompdf: bound=' . (app()->bound('dompdf.wrapper') ? 'true' : 'false') . ' facade=' . (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class) ? 'true' : 'false'));
        if (app()->bound('dompdf.wrapper') || class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            // Logging to help debugging whether wrapper is bound and PDF creation triggered
            Log::info('ClienteController::exportarPdf - dompdf wrapper bound: ' . (app()->bound('dompdf.wrapper') ? 'true' : 'false'));
            Log::info('ClienteController::exportarPdf - clientes count: ' . $clientes->count());

            $pdfWrapper = app()->make('dompdf.wrapper');
            try {
                // Log environment limits for web request
                Log::info('ClienteController::exportarPdf - php memory_limit=' . ini_get('memory_limit') . ', max_execution_time=' . ini_get('max_execution_time'));
                // Renderizar HTML y guardarlo para inspección de fallos
                $html = view('clientes.pdf.lista-clientes-pdf', compact('clientes'))->render();
                $debugFilename = 'debug/clientes-html-' . now()->format('YmdHis') . '.html';
                try {
                    // Asegurar que la carpeta 'debug' existe
                    if (!Storage::disk('local')->exists('debug')) {
                        Storage::disk('local')->makeDirectory('debug');
                        Log::info('ClienteController::exportarPdf - Directorio debug creado.');
                    }
                    Storage::disk('local')->put($debugFilename, $html);
                    $fileSize = Storage::disk('local')->size($debugFilename);
                    Log::info('ClienteController::exportarPdf - HTML de la vista guardado en ' . $debugFilename . ' (size=' . $fileSize . ' bytes)');
                } catch (\Throwable $s) {
                    Log::warning('ClienteController::exportarPdf - No se pudo guardar HTML de debug: ' . $s->getMessage());
                }

                // Cargar el HTML manualmente para evitar doble render
                $pdf = $pdfWrapper->loadHTML($html);
                // Ajustar algunas opciones que ayudan a la generación
                $pdf->setOption('isHtml5ParserEnabled', true);
                $pdf->setOption('isRemoteEnabled', false);
                $pdf->setOption('debugKeepTemp', true);
                $pdf->setOption('defaultFont', 'DejaVu Sans');
                Log::info('ClienteController::exportarPdf - memory usage before render: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB');
                // Evitar cargar grandes PDFs en memoria; streaming es preferible para peticiones web
                // Si la lista es pequeña, podemos revisar el output y validar el inicio con "%PDF".
                $bufferThreshold = config('app.max_clients_pdf_buffer', 50);
                if ($clientesCount <= $bufferThreshold) {
                    // Generar el PDF y devolver como descarga forzada
                    $output = $pdf->output();
                    Log::info('ClienteController::exportarPdf - memory usage after render: ' . round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB');
                    $size = is_string($output) ? strlen($output) : 0;
                    Log::info('ClienteController::exportarPdf - PDF generado; tamaño=' . $size);
                    // Detectar si el contenido parece un PDF (comienza con %PDF)
                    if ($size > 4 && is_string($output) && substr($output, 0, 4) === "%PDF") {
                        Log::info('ClienteController::exportarPdf - PDF válido detectado');
                        // Limpiar buffers de salida para evitar corromper el PDF con contenido adicional
                        while (ob_get_level()) { ob_end_clean(); }
                        // Use the wrapper's download method to force attachment headers
                        return $pdfWrapper->download('clientes.pdf');
                    }
                } else {
                    // Para conjuntos medianos/grandes, evitar buffer y stream directamente
                    Log::info('ClienteController::exportarPdf - lista grande ('.$clientesCount.'), usando stream/descarga para evitar uso de memoria elevado');
                    while (ob_get_level()) { ob_end_clean(); }
                    // Forzamos la descarga en lugar de abrir inline
                    return $pdf->download('clientes.pdf');
                }

                // Si llegamos aquí significa que el output no parece un PDF válido (tamaño pequeño o no comienza con %PDF)
                Log::warning('ClienteController::exportarPdf - salida PDF no válida (no comienza con "%PDF"). tamaño=' . $size);
                // Loguear los primeros bytes para diagnosticar si hay HTML o un error inyectado en el flujo
                $preview = is_string($output) ? substr($output, 0, 512) : '';
                $previewSnippet = preg_replace('/\s+/', ' ', substr($preview, 0, 512));
                Log::warning('ClienteController::exportarPdf - PDF output preview: ' . $previewSnippet);
                // Como fallback más robusto, servir un CSV para evitar devolver HTML y evitar Quirks mode
                try {
                    $filename = 'clientes_' . now()->format('Ymd_His') . '.csv';
                    $callback = function() use ($clientes, $delimiter) {
                        $FH = fopen('php://output', 'w');
                        echo chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM
                        fputcsv($FH, ['#', 'Nombre Completo', 'CI/NIT', 'Email', 'Teléfono'], $delimiter);
                        foreach ($clientes as $index => $cliente) {
                            fputcsv($FH, [
                                $index + 1,
                                trim($cliente->nombre . ' ' . $cliente->apellido),
                                $cliente->ci_nit,
                                $cliente->email ?? 'N/A',
                                $cliente->telefono ?? 'N/A',
                            ], $delimiter);
                        }
                        fclose($FH);
                    };
                    Log::info('ClienteController::exportarPdf - PDF inválido; devolviendo CSV fallback con tamaño de clientes ' . $clientesCount);
                    return response()->stream($callback, 200, [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                } catch (\Throwable $e) {
                    Log::error('ClienteController::exportarPdf - Error al generar CSV fallback tras PDF inválido: ' . $e->getMessage());
                    // Si algo falla al generar CSV, fallback a redirección con error (para no devolver HTML crudo)
                    return redirect()->route('clientes.index')
                        ->with('error', 'No se pudo generar el PDF ni el CSV. Revisa los logs del servidor para más detalles.');
                }
            } catch (\Throwable $t) {
                // Registrar message completo y stack trace para diagnosticar errores en tiempo de ejecución
                Log::error('ClienteController::exportarPdf - Error generando PDF: ' . $t->getMessage());
                Log::error('ClienteController::exportarPdf - trace: ' . $t->getTraceAsString());

                // Si estamos en local, re-lanzamos para facilitar debug
                if (app()->environment('local')) {
                    throw $t; // durante dev queremos ver el error
                }

                // Intentar CSV fallback si hay contenido (evita devolver HTML en blanco)
                try {
                    $filename = 'clientes_' . now()->format('Ymd_His') . '.csv';
                    $callback = function() use ($clientes, $delimiter) {
                        $FH = fopen('php://output', 'w');
                        echo chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM
                        fputcsv($FH, ['#', 'Nombre Completo', 'CI/NIT', 'Email', 'Teléfono'], $delimiter);
                        foreach ($clientes as $index => $cliente) {
                            fputcsv($FH, [
                                $index + 1,
                                trim($cliente->nombre . ' ' . $cliente->apellido),
                                $cliente->ci_nit,
                                $cliente->email ?? 'N/A',
                                $cliente->telefono ?? 'N/A',
                            ], $delimiter);
                        }
                        fclose($FH);
                    };
                    Log::info('ClienteController::exportarPdf - PDF falló; devolviendo CSV fallback');
                    return response()->stream($callback, 200, [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                } catch (\Throwable $e) {
                    Log::error('ClienteController::exportarPdf - Error generando CSV fallback: ' . $e->getMessage());
                }
            }
        }

        Log::warning('ClienteController::exportarPdf - dompdf no disponible o falló. Generando CSV de fallback');
        // Si no existe la librería, devolver CSV para que el usuario tenga un archivo útil en lugar de HTML
        $filename = 'clientes_' . now()->format('Ymd_His') . '.csv';
        $callback = function() use ($clientes, $delimiter) {
            $FH = fopen('php://output', 'w');
            echo chr(0xEF) . chr(0xBB) . chr(0xBF); // BOM
            fputcsv($FH, ['#', 'Nombre Completo', 'CI/NIT', 'Email', 'Teléfono'], $delimiter);
            foreach ($clientes as $index => $cliente) {
                fputcsv($FH, [
                    $index + 1,
                    trim($cliente->nombre . ' ' . $cliente->apellido),
                    $cliente->ci_nit,
                    $cliente->email ?? 'N/A',
                    $cliente->telefono ?? 'N/A',
                ], $delimiter);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci_nit' => 'required|string|max:20|unique:clientes',
            'telefono' => 'nullable|string|max:15|unique:clientes',
            'email' => 'nullable|email|max:255|unique:clientes',
            'direccion' => 'nullable|string',
        ]);

        $data = $request->validated();
        $data['id_usuario'] = auth()->id();

        Cliente::create($data);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'ci_nit' => 'required|string|max:20|unique:clientes,ci_nit,' . $cliente->id,
            'telefono' => 'nullable|string|max:15|unique:clientes,telefono,' . $cliente->id,
            'email' => 'nullable|email|max:255|unique:clientes,email,' . $cliente->id,
            'direccion' => 'nullable|string',
        ]);

        $cliente->update($request->all());

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return redirect()->route('clientes.index')
                         ->with('success', 'Cliente eliminado exitosamente.');
    }

    /**
     * Return a JSON map of clients keyed by phone number (digits only) for AJAX lookup.
     * Only available to admin users by default (rir route protected by admin middleware).
     */
    public function json(Request $request)
    {
        $clientes = Cliente::all(['id', 'nombre', 'apellido', 'telefono']);
        $map = [];
        foreach ($clientes as $c) {
            if (!$c->telefono) continue;
            $phone = preg_replace('/[^0-9]/', '', $c->telefono);
            if (!$phone) continue;
            // Create both normalized forms: without country code and with country code (591)
            $without591 = preg_replace('/^591/', '', $phone);
            $with591 = preg_replace('/^/', '591', $without591);
            // Ensure both forms are valid digits-only
            $entry = [
                'id' => $c->id,
                'nombre_completo' => trim(($c->nombre ?? '') . ' ' . ($c->apellido ?? '')),
                'telefono' => $phone,
            ];
            $map[$without591] = $entry;
            $map[$with591] = $entry;
        }
        return response()->json($map);
    }

    /**
     * Return JSON map with only the current authenticated client (if any).
     * This is used by non-admin UIs to personalize chat names without exposing all clients.
     */
    public function infoJson(Request $request)
    {
        $user = auth()->user();
        if (!$user) return response()->json([]);
        // Try to find a Cliente associated with the current user; fallback to user.telefono
        $cliente = Cliente::where('id_usuario', $user->id_usuario)->first();
        $phoneRaw = $cliente ? ($cliente->telefono ?? null) : ($user->telefono ?? null);
        $phone = preg_replace('/[^0-9]/', '', $phoneRaw ?? '');
        if (!$phone) return response()->json([]);
        $nombre = trim(($cliente->nombre ?? $user->nombre ?? '') . ' ' . ($cliente->apellido ?? $user->apellido ?? '')) ?: ($user->email ?? null);
        // Add both keys (with and without 591 prefix) for robustness
        $without591 = preg_replace('/^591/', '', $phone);
        $with591 = preg_replace('/^/', '591', $without591);
        return response()->json([
            $without591 => [
                'id' => $cliente ? $cliente->id : null,
                'nombre_completo' => $nombre,
                'telefono' => $phone,
            ],
            $with591 => [
                'id' => $cliente ? $cliente->id : null,
                'nombre_completo' => $nombre,
                'telefono' => $phone,
            ],
        ]);
    }
}
