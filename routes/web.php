<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\PrendaController;
use App\Http\Controllers\ControlNotificacionesController;


Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        
        // Redirigir según el rol del usuario
        switch ($user->id_rol) {
            case 1: // Administrador
                return redirect()->route('dashboard');
            case 2: // Empleado
                return redirect()->route('pedidos.index');
            case 3: // Cliente
                return redirect()->route('cliente.dashboard');
            default:
                return view('welcome');
        }
    }
    
    return view('welcome');
});

// Rutas públicas del catálogo
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::post('/catalogo/pedido', [CatalogoController::class, 'crearPedido'])->name('catalogo.crear-pedido');
Route::get('/catalogo/pedido-confirmado/{id}', [CatalogoController::class, 'pedidoConfirmado'])->name('catalogo.pedido-confirmado');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'user.enabled', 'admin.role', 'redirect.role'])->name('dashboard');

// [DEBUG ONLY] Routes registered outside auth middleware to help debugging locally
if (app()->environment('local')) {
    // Debug - route for checking dompdf binding
    Route::get('debug/dompdf', function () {
        if (!auth()->check() || auth()->user()->id_rol !== 1) {
            abort(403);
        }
        $bound = app()->bound('dompdf.wrapper') ? 'bound' : 'not bound';
        $existsFacade = class_exists(Barryvdh\DomPDF\Facade\Pdf::class) ? 'facade exists' : 'facade missing';

        return response()->json([
            'dompdf_wrapper' => $bound,
            'facade_status' => $existsFacade,
        ]);
    })->name('debug.dompdf');

    // Debug - route to generate test PDF and force download
    Route::get('debug/dompdf-generate', function() {
        if (!auth()->check() || auth()->user()->id_rol !== 1) {
            abort(403);
        }
        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(120);
            \Log::info('debug/dompdf-generate - memory/time limits aumentados temporalmente');
            $pdfWrapper = app()->make('dompdf.wrapper');
            $pdf = $pdfWrapper->loadHTML('<!doctype html><html lang="es"><head><meta charset="utf-8"><title>PDF Test</title></head><body><h1>Prueba de DomPDF</h1><p>Si ves esto, la generación fue correcta.</p></body></html>');
            // Forzamos que el PDF sea descargado
            return $pdf->download('test.pdf');
        } catch (\Throwable $t) {
            \Log::error('debug/dompdf-generate - Error generando PDF de prueba: ' . $t->getMessage());
            \Log::error($t->getTraceAsString());
            return response()->json(['error' => 'Error generando PDF de prueba: ' . $t->getMessage()], 500);
        }
    })->name('debug.dompdf.generate');

    // Debug - stream recibo inline for a given pago (admin only) to visually confirm font rendering
    Route::get('debug/pagos/{id}/recibo/stream', [App\Http\Controllers\PagoController::class, 'emitirReciboStream'])
        ->name('debug.pagos.recibo.stream');

    // Debug route to check export capabilities and generate PDF/CSV without auth for testing
    Route::get('debug/clientes-export-check', function() {
        try {
            $result = [];
            $result['dompdf_bound'] = app()->bound('dompdf.wrapper');
            $clientes = App\Models\Cliente::limit(5)->get();
            $result['clientes_count'] = $clientes->count();
            $result['memory_limit_before'] = ini_get('memory_limit');
            $result['max_execution_time'] = ini_get('max_execution_time');
            $html = view('clientes.pdf.lista-clientes-pdf', compact('clientes'))->render();
            $result['debug_html_length'] = strlen($html);
            if ($result['dompdf_bound']) {
                $pdfWrapper = app()->make('dompdf.wrapper');
                $pdfWrapper->loadHTML($html);
                $output = $pdfWrapper->output();
                $result['pdf_preview_start'] = is_string($output) ? substr($output, 0, 8) : null;
                $result['pdf_preview_size'] = is_string($output) ? strlen($output) : 0;
            }
            return response()->json($result);
        } catch (\Throwable $t) {
            \Log::error('debug/clientes-export-check - Error: ' . $t->getMessage());
            return response()->json(['error' => $t->getMessage()], 500);
        }
    })->name('debug.clientes.export.check');

    Route::get('debug/clientes-exportar-noauth', function(Illuminate\Http\Request $request) {
        try {
            $clientes = App\Models\Cliente::orderBy('nombre')->get();
            $maxForPdfWeb = config('app.max_clients_pdf_web', 200);
            if ($request->get('format') === 'json') {
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

            if ($request->get('format') === 'csv' || $clientes->count() > $maxForPdfWeb) {
                $filename = 'clientes_' . now()->format('Ymd_His') . '.csv';
                $delimiter = $request->get('delimiter', config('exports.csv_delimiter', ';'));
                $callback = function() use ($clientes, $delimiter) {
                    $FH = fopen('php://output', 'w');
                    echo chr(0xEF) . chr(0xBB) . chr(0xBF);
                    fputcsv($FH, ['#', 'Nombre Completo', 'CI/NIT', 'Email', 'Teléfono'], $delimiter);
                    foreach ($clientes as $index => $cliente) {
                        fputcsv($FH, [
                            $index + 1,
                            trim($cliente->nombre . ' ' . $cliente->apellido),
                            $cliente->ci_nit,
                            $cliente->email ?? 'N/A',
                            $cliente->telefono ?? 'N/A'
                        ], $delimiter);
                    }
                    fclose($FH);
                };
                return response()->stream($callback, 200, [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }
            $html = view('clientes.pdf.lista-clientes-pdf', compact('clientes'))->render();
            if (app()->bound('dompdf.wrapper')) {
                $pdfWrapper = app()->make('dompdf.wrapper');
                $pdfWrapper->loadHTML($html);
                $pdfWrapper->setOption('isHtml5ParserEnabled', true);
                $pdfWrapper->setOption('isRemoteEnabled', false);
                $pdfWrapper->setOption('defaultFont', 'DejaVu Sans');
                while (ob_get_level()) { ob_end_clean(); }
                return $pdfWrapper->download('debug-clientes.pdf');
            }
            return response($html, 200, ['Content-Type' => 'text/html']);
        } catch (\Throwable $e) {
            \Log::error('debug/clientes-exportar-noauth - Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('debug.clientes.export.noauth');

    // Debug - export bitacora without auth (local only)
    Route::post('debug/bitacora-exportar-noauth', function (Illuminate\Http\Request $request) {
        try {
            $query = App\Models\Bitacora::query();
            if ($request->filled('fecha_desde') || $request->filled('fecha_hasta')) {
                $query->byFechas($request->get('fecha_desde'), $request->get('fecha_hasta'));
            }
            if ($request->filled('id_usuario')) {
                $query->byUsuario($request->get('id_usuario'));
            }
            if ($request->filled('accion')) {
                $query->byAccion($request->get('accion'));
            }
            if ($request->filled('modulo')) {
                $query->byModulo($request->get('modulo'));
            }
            $registros = $query->with('usuario')->orderBy('created_at', 'desc')->get();

            $filename = 'bitacora_' . now()->format('Ymd_His') . '.csv';
            $delimiter = $request->get('delimiter', config('exports.csv_delimiter', ';'));
            $callback = function() use ($registros, $delimiter) {
                $FH = fopen('php://output', 'w');
                echo chr(0xEF) . chr(0xBB) . chr(0xBF);
                fputcsv($FH, ['Fecha', 'Usuario', 'Acción', 'Módulo', 'Descripción', 'IP'], $delimiter);
                foreach ($registros as $registro) {
                    fputcsv($FH, [
                        $registro->created_at->format('d/m/Y H:i:s'),
                        $registro->nombre_usuario,
                        $registro->accion,
                        $registro->modulo,
                        $registro->descripcion,
                        $registro->ip_address,
                    ], $delimiter);
                }
                fclose($FH);
            };
            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (Throwable $t) {
            \Log::error('debug/bitacora-exportar-noauth - Error: ' . $t->getMessage());
            return response()->json(['error' => $t->getMessage()], 500);
        }
    })->name('debug.bitacora.export.noauth');

    // Debug - call controller exportarPdf without middleware to test controller logic
    // If EXPORT_NOAUTH_ENABLED is true and the environment is local, register a no-auth export route
    if (config('exports.noauth_enabled', false) === true) {
        Route::get('debug/clientes-export-test', [App\Http\Controllers\ClienteController::class, 'exportarPdf'])
            ->withoutMiddleware(['auth', 'user.enabled', 'admin.role'])
            ->name('debug.clientes.export.test.noauth');
    } else {
        Route::get('debug/clientes-export-test', [App\Http\Controllers\ClienteController::class, 'exportarPdf'])
            ->name('debug.clientes.export.test');
    }
}

// Register noauth export routes outside middleware group when explicitly enabled for local dev
if (config('exports.noauth_enabled', false) === true && app()->environment('local')) {
    Route::get('clientes/exportar-pdf', [App\Http\Controllers\ClienteController::class, 'exportarPdf'])
        ->withoutMiddleware(['auth', 'user.enabled', 'admin.role'])
        ->name('clientes.exportar-pdf');

    Route::post('bitacora/exportar', [App\Http\Controllers\BitacoraController::class, 'exportar'])
        ->withoutMiddleware(['auth', 'user.enabled', 'admin.role'])
        ->name('bitacora.exportar');
}

Route::middleware(['auth', 'user.enabled'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('clientes', [ClienteController::class, 'index'])->name('clientes.index');
    // Endpoint for clients to expose their own info for chat name resolution
    Route::get('clientes/info/json', [ClienteController::class, 'infoJson'])->name('clientes.info.json');
    
    // Rutas de pedidos para empleados y administradores
    Route::resource('pedidos', PedidoController::class);
    Route::get('pedidos-calendario', [PedidoController::class, 'calendar'])->name('pedidos.calendar');
    Route::get('pedidos-calendario/json', [PedidoController::class, 'calendarJson'])->name('pedidos.calendar-json');
    Route::get('pedidos/{id}/historial', [PedidoController::class, 'historial'])->name('pedidos.historial');
    Route::get('clientes/{id}/pedidos', [PedidoController::class, 'clienteHistorial'])->name('pedidos.cliente-historial');
    
    // Rutas específicas para empleados y administradores - crear pedidos para clientes
    Route::get('crear-pedido-cliente', [PedidoController::class, 'empleadoCrear'])->name('pedidos.empleado-crear');
    Route::post('crear-pedido-cliente', [PedidoController::class, 'empleadoStore'])->name('pedidos.empleado-store');
    
    // Presupuestos de Producción - Gestión de costos de confección (Admin y Empleados)
    Route::resource('presupuestos-produccion', App\Http\Controllers\PresupuestoProduccionController::class);
    Route::post('presupuestos-produccion/{id}/cambiar-estado', [App\Http\Controllers\PresupuestoProduccionController::class, 'cambiarEstado'])->name('presupuestos-produccion.cambiar-estado');
    Route::get('presupuestos-produccion/{id}/duplicar', [App\Http\Controllers\PresupuestoProduccionController::class, 'duplicar'])->name('presupuestos-produccion.duplicar');
    
    // Dashboard específico para clientes
    Route::get('cliente/dashboard', [App\Http\Controllers\ClienteDashboardController::class, 'index'])
        ->middleware('redirect.role')->name('cliente.dashboard');
    
    // Rutas específicas para clientes - crear y ver sus propios pedidos
    Route::get('hacer-pedido', [PedidoController::class, 'clienteCrear'])->name('pedidos.cliente-crear');
    Route::post('hacer-pedido', [PedidoController::class, 'clienteStore'])->name('pedidos.cliente-store');
    Route::get('mis-pedidos', [PedidoController::class, 'misPedidos'])->name('pedidos.mis-pedidos');
    Route::post('pedidos/{id}/calificar', [PedidoController::class, 'calificar'])->name('pedidos.calificar');
    
    // Rutas AJAX para verificación de stock
    Route::post('pedidos/verificar-stock', [PedidoController::class, 'verificarStock'])->name('pedidos.verificar-stock');
    Route::get('pedidos/stock/{id}', [PedidoController::class, 'obtenerStock'])->name('pedidos.obtener-stock');
    
    // Rutas para reprogramar entrega - administradores y clientes (CU19)
    Route::middleware('admin.cliente.role')->group(function () {
        Route::get('pedidos/{id}/reprogramar-entrega', [PedidoController::class, 'reprogramarEntrega'])->name('pedidos.reprogramar-entrega');
        Route::post('pedidos/{id}/reprogramar-entrega', [PedidoController::class, 'procesarReprogramacion'])->name('pedidos.procesar-reprogramacion');
        // Historial de reprogramaciones
        Route::get('pedidos/{id}/historial-reprogramaciones', [PedidoController::class, 'historialReprogramaciones'])->name('pedidos.historial-reprogramaciones');
    });
    
    // Rutas para registrar avance de producción - solo administradores (CU20)
    Route::middleware('admin.role')->group(function () {
        Route::get('pedidos/{id}/registrar-avance', [PedidoController::class, 'registrarAvance'])->name('pedidos.registrar-avance');
        Route::post('pedidos/{id}/registrar-avance', [PedidoController::class, 'procesarAvance'])->name('pedidos.procesar-avance');
        Route::get('pedidos/{id}/historial-avances', [PedidoController::class, 'historialAvances'])->name('pedidos.historial-avances');
    });
    
    // Rutas para observaciones de calidad - solo empleados
    Route::middleware('vendedor.role')->group(function () {
        Route::get('pedidos/{id}/registrar-observacion-calidad', [PedidoController::class, 'registrarObservacionCalidad'])->name('pedidos.registrar-observacion-calidad');
        Route::post('pedidos/{id}/registrar-observacion-calidad', [PedidoController::class, 'procesarObservacionCalidad'])->name('pedidos.procesar-observacion-calidad');
        Route::get('pedidos/{id}/historial-observaciones-calidad', [PedidoController::class, 'historialObservacionesCalidad'])->name('pedidos.historial-observaciones-calidad');
        Route::put('pedidos/{id}/observaciones-calidad/{observacionId}', [PedidoController::class, 'actualizarObservacionCalidad'])->name('pedidos.actualizar-observacion-calidad');
    });
    
    // Rutas para notificaciones por email - solo empleados
    Route::middleware('vendedor.role')->group(function () {
        Route::get('pedidos/{id}/cambiar-estado', [PedidoController::class, 'mostrarCambiarEstado'])->name('pedidos.cambiar-estado');
        Route::post('pedidos/{id}/cambiar-estado', [PedidoController::class, 'cambiarEstadoConNotificacion'])->name('pedidos.cambiar-estado-con-notificacion');
        Route::post('pedidos/{id}/probar-email', [PedidoController::class, 'probarEmail'])->name('pedidos.probar-email');
    });
    
    // Ruta para confirmar recepción - administradores y clientes propietarios (CU22)
    Route::middleware('admin.cliente.role')->group(function () {
        Route::post('pedidos/{id}/confirmar-recepcion', [PedidoController::class, 'confirmarRecepcion'])->name('pedidos.confirmar-recepcion');
    });
    
    // Ruta del catálogo de productos
    Route::get('catalogo', [CatalogoController::class, 'index'])->name('catalogo.index');
    // Ranking de productos más vendidos para usuarios autenticados (clientes/empleados/admin)
    Route::get('prendas/ranking', [App\Http\Controllers\PrendaController::class, 'ranking'])->name('prendas.ranking');
    
    // Rutas de solo lectura para empleados
    Route::get('roles', [RolController::class, 'index'])->name('roles.index.readonly');
    Route::get('roles/{rol}', [RolController::class, 'show'])->name('roles.show.readonly');
    
    // Ruta de debug temporal
    Route::get('debug-roles', function() {
        $user = auth()->user();
        return response()->json([
            'authenticated' => auth()->check(),
            'user_email' => $user->email ?? null,
            'user_role_id' => $user->id_rol ?? null,
            'user_role_name' => $user->rol->nombre ?? null,
            'is_admin' => $user && $user->id_rol == 1,
            'can_edit_roles' => $user && $user->id_rol == 1
        ]);
    });
        
});

Route::middleware(['auth', 'user.enabled', 'admin.role'])->group(function () {
    Route::resource('clientes', ClienteController::class)->except(['index']);
    Route::resource('users', UserController::class);
    Route::resource('roles', RolController::class);
    Route::resource('prendas', PrendaController::class);

    // (Ranking route registered under auth middleware above) 
    
    // Rutas de bitácora - solo para administradores
    Route::get('bitacora', [BitacoraController::class, 'index'])->name('bitacora.index');
    Route::get('bitacora/limpiar-filtros', [BitacoraController::class, 'limpiarFiltros'])->name('bitacora.limpiar-filtros');
    // bitacora export: register here when not using noauth debug mode
    if (!(config('exports.noauth_enabled', false) === true && app()->environment('local'))) {
        Route::post('bitacora/exportar', [BitacoraController::class, 'exportar'])->name('bitacora.exportar');
    }
    
    // Rutas del sistema - solo para administradores
    Route::get('sistema', [\App\Http\Controllers\SistemaController::class, 'index'])->name('sistema.index');
    Route::get('sistema/diagrama', [\App\Http\Controllers\SistemaController::class, 'diagrama'])->name('sistema.diagrama');
    Route::get('sistema/estadisticas', [\App\Http\Controllers\SistemaController::class, 'estadisticas'])->name('sistema.estadisticas');
    
    // Rutas adicionales de pedidos - solo para administradores
    Route::post('pedidos/{id}/asignar', [PedidoController::class, 'asignar'])->name('pedidos.asignar');
    Route::get('pedidos-por-operario', [PedidoController::class, 'porOperario'])->name('pedidos.por-operario');
    
    // Exportar lista de clientes a PDF (CU25)
    // clientes export: register here when not using noauth debug mode
    if (!(config('exports.noauth_enabled', false) === true && app()->environment('local'))) {
        Route::get('clientes/exportar-pdf', [App\Http\Controllers\ClienteController::class, 'exportarPdf'])->name('clientes.exportar-pdf');
    }
});

// Rutas de pagos
Route::middleware(['auth', 'user.enabled'])->group(function () {
    // Rutas para empleados y administradores
    // Rutas para empleados, administradores y clientes (Proceso de Pago)
    Route::middleware(['role:Administrador,Empleado,Cliente'])->group(function () {
        Route::get('pagos/pasarela', [App\Http\Controllers\PagoController::class, 'pasarela'])->name('pagos.pasarela');
        Route::get('pagos/checkout/{pedido}', [App\Http\Controllers\PagoController::class, 'checkout'])->name('pagos.checkout');
        Route::post('pagos/procesar-pasarela', [App\Http\Controllers\PagoController::class, 'procesarPagoPasarela'])->name('pagos.procesar-pasarela');
        
        // APIs de pago
        Route::get('api/pedidos/buscar/{numero}', [App\Http\Controllers\PagoController::class, 'buscarPedido'])->name('api.pedidos.buscar');
        Route::post('api/stripe/create-payment-intent', [App\Http\Controllers\PagoController::class, 'createPaymentIntent'])->name('api.stripe.create-payment-intent');
        Route::post('api/stripe/generate-qr', [App\Http\Controllers\PagoController::class, 'generatePaymentQR'])->name('api.stripe.generate-qr');
        Route::post('api/stripe/confirm-payment', [App\Http\Controllers\PagoController::class, 'confirmStripePayment'])->name('api.stripe.confirm-payment');
    });

    // Rutas exclusivas para empleados y administradores (Gestión y Reembolsos)
    Route::middleware(['role:Administrador,Empleado'])->group(function () {
        Route::get('pagos/reembolso/{pedido}', [App\Http\Controllers\PagoController::class, 'mostrarReembolso'])->name('pagos.reembolso');
        Route::post('pagos/{pago}/reembolsar', [App\Http\Controllers\PagoController::class, 'procesarReembolso'])->name('pagos.procesar-reembolso');
        Route::post('pagos/limpiar-duplicados/{pedido}', [App\Http\Controllers\PagoController::class, 'limpiarPagosDuplicados'])->name('pagos.limpiar-duplicados');
        Route::get('api/pedidos/{id}/pagos', [App\Http\Controllers\PagoController::class, 'obtenerPagosPedido'])->name('api.pedidos.pagos');
        
        // Debug temporal
        Route::post('debug/stripe-data', function(Request $request) {
            return response()->json([
                'method' => $request->method(),
                'all' => $request->all(),
                'input' => $request->input(),
                'json' => $request->json() ? $request->json()->all() : null,
                'content_type' => $request->header('Content-Type'),
                'raw_content' => $request->getContent()
            ]);
        })->name('debug.stripe-data');
        Route::get('pagos/test-stripe', function() { return view('pagos.test-stripe'); })->name('pagos.test-stripe');
    });
    
    // Admin-only payment routes
    Route::middleware(['admin.role'])->group(function () {
        Route::get('pedidos/{id}/pagos/create', [App\Http\Controllers\PagoController::class, 'create'])->name('pedidos.pagos.create');
        Route::post('pedidos/{id}/pagos', [App\Http\Controllers\PagoController::class, 'store'])->name('pedidos.pagos.store');
        Route::get('pagos', [App\Http\Controllers\PagoController::class, 'index'])->name('pagos.index');
        Route::post('pagos/{id}/anular', [App\Http\Controllers\PagoController::class, 'anular'])->name('pagos.anular');
        
        // CRUD Métodos de Pago
        Route::resource('metodos-pago', App\Http\Controllers\MetodoPagoController::class);
        Route::patch('metodos-pago/{metodos_pago}/toggle-active', [App\Http\Controllers\MetodoPagoController::class, 'toggleActive'])->name('metodos-pago.toggle-active');
        
        // Gestión de Solicitudes de Reembolso
        Route::get('solicitudes-reembolso', [App\Http\Controllers\SolicitudReembolsoController::class, 'index'])->name('solicitudes-reembolso.index');
        Route::get('solicitudes-reembolso/{id}', [App\Http\Controllers\SolicitudReembolsoController::class, 'show'])->name('solicitudes-reembolso.show');
        Route::post('solicitudes-reembolso/{id}/procesar', [App\Http\Controllers\SolicitudReembolsoController::class, 'marcarProcesada'])->name('solicitudes-reembolso.procesar');
        Route::patch('reembolsos/{id}/completar', [App\Http\Controllers\SolicitudReembolsoController::class, 'marcarCompletado'])->name('reembolsos.completar');
        Route::patch('reembolsos/{id}/cambiar-estado', [App\Http\Controllers\SolicitudReembolsoController::class, 'cambiarEstado'])->name('reembolsos.cambiar-estado');
    });
    
    // Ver mis pagos (Cliente)
    Route::get('mis-pagos', [App\Http\Controllers\PagoController::class, 'misPagos'])->name('pagos.mis-pagos');

    // Recibo (puede ser solicitado por cliente o admin si está autenticado)
    Route::get('pagos/{id}/recibo', [App\Http\Controllers\PagoController::class, 'emitirRecibo'])->name('pagos.recibo');

    // Consulta de pagos del cliente (admin)
    Route::get('clientes/{id}/pagos', [App\Http\Controllers\PagoController::class, 'clientePagos'])->middleware('admin.role')->name('clientes.pagos');
    Route::get('clientes/{id}/recibo-consolidado', [App\Http\Controllers\PagoController::class, 'emitirReciboConsolidado'])->middleware('admin.role')->name('clientes.recibo-consolidado');
});

// Rutas para telar y stock - solo administradores
Route::middleware(['auth', 'user.enabled', 'admin.role'])->group(function () {
    Route::get('telas', [App\Http\Controllers\TelaController::class, 'index'])->name('telas.index');
    Route::get('telas/create', [App\Http\Controllers\TelaController::class, 'create'])->name('telas.create');
    Route::post('telas', [App\Http\Controllers\TelaController::class, 'store'])->name('telas.store');
    Route::get('telas/{id}/edit', [App\Http\Controllers\TelaController::class, 'edit'])->name('telas.edit');
    Route::put('telas/{id}', [App\Http\Controllers\TelaController::class, 'update'])->name('telas.update');
    // Consumo tras producción (resta stock)
    Route::post('telas/{id}/consumir', [App\Http\Controllers\TelaController::class, 'consumir'])->name('telas.consumir');

    // Compras de insumos - inventario
    Route::get('inventario/compras', [App\Http\Controllers\CompraInsumoController::class, 'index'])->name('compras.index');
    Route::get('inventario/compras/create', [App\Http\Controllers\CompraInsumoController::class, 'create'])->name('compras.create');
    Route::post('inventario/compras', [App\Http\Controllers\CompraInsumoController::class, 'store'])->name('compras.store');
    Route::get('inventario/compras/proveedor/{id}', [App\Http\Controllers\CompraInsumoController::class, 'historialPorProveedor'])->name('compras.historial.proveedor');
    Route::get('inventario/compras/auditar/ultima-semana', [App\Http\Controllers\CompraInsumoController::class, 'auditarUltimaSemana'])->name('compras.auditar.ultima-semana');

    // Proveedores CRUD (admin)
    Route::resource('proveedores', App\Http\Controllers\ProveedorController::class);
    // Reportes - CU39
    Route::post('reportes/generate', [App\Http\Controllers\ReportController::class, 'generate'])->name('reportes.generate');
    
    // Análisis de Productos Estrella y Hueso
    Route::get('reportes/analisis-productos', [App\Http\Controllers\ReportController::class, 'analisisProductos'])->name('reportes.analisis-productos');
    Route::get('reportes/rentabilidad', [App\Http\Controllers\ReportController::class, 'rentabilidad'])->name('reportes.rentabilidad');
    
    // Reportes de Producción - Pago a Destajo
    Route::get('reportes/produccion', [App\Http\Controllers\ReporteProduccionController::class, 'index'])->name('reportes.produccion.index');
    Route::get('reportes/produccion/rendimiento', [App\Http\Controllers\ReporteProduccionController::class, 'rendimientoPorOperario'])->name('reportes.produccion.rendimiento');
    Route::get('reportes/produccion/exportar-pdf', [App\Http\Controllers\ReporteProduccionController::class, 'exportarPDF'])->name('reportes.produccion.exportar-pdf');
    Route::get('reportes/produccion/exportar-csv', [App\Http\Controllers\ReporteProduccionController::class, 'exportarCSV'])->name('reportes.produccion.exportar-csv');
    Route::get('reportes/produccion/exportar-excel', [App\Http\Controllers\ReporteProduccionController::class, 'exportarExcel'])->name('reportes.produccion.exportar-excel');
    
    // Reporte de Pedidos Entregados
    Route::get('reportes/pedidos-entregados', [App\Http\Controllers\ReportePedidosEntregadosController::class, 'index'])->name('reportes.pedidos-entregados');
    Route::get('reportes/pedidos-entregados/pdf', [App\Http\Controllers\ReportePedidosEntregadosController::class, 'exportarPDF'])->name('reportes.pedidos-entregados.pdf');
    Route::get('reportes/pedidos-entregados/csv', [App\Http\Controllers\ReportePedidosEntregadosController::class, 'exportarCSV'])->name('reportes.pedidos-entregados.csv');
    // Control de Notificaciones - UI admin para controlar la conexión con el servicio de notificaciones/wa
    Route::get('control-notificaciones', [ControlNotificacionesController::class, 'index'])->name('control-notificaciones');
    // Proxy endpoints to the notifications service (admin only)
    Route::prefix('admin/notificaciones')->group(function(){
        Route::get('status', [App\Http\Controllers\NotificationProxyController::class, 'status']);
        Route::get('me', [App\Http\Controllers\NotificationProxyController::class, 'me']);
        Route::get('qr', [App\Http\Controllers\NotificationProxyController::class, 'qr']);
        Route::post('generate-qr', [App\Http\Controllers\NotificationProxyController::class, 'generateQr']);
        Route::post('delete-session', [App\Http\Controllers\NotificationProxyController::class, 'deleteSession']);
        Route::get('chats', [App\Http\Controllers\NotificationProxyController::class, 'chats']);
        Route::get('chats/{jid}', [App\Http\Controllers\NotificationProxyController::class, 'chatMessages']);
        Route::delete('chats/{jid}', [App\Http\Controllers\NotificationProxyController::class, 'deleteChat']);
        Route::post('send', [App\Http\Controllers\NotificationProxyController::class, 'send']);
        Route::post('send-file', [App\Http\Controllers\NotificationProxyController::class, 'sendFile']);
        Route::post('block', [App\Http\Controllers\NotificationProxyController::class, 'block']);
        // Route removed: reject-call handled by the notifications layer (Baileys) by default. Not exposing an endpoint.
    });
    // JSON map to lookup clients by phone (admin-only)
    Route::get('admin/clientes/json', [App\Http\Controllers\ClienteController::class, 'json'])->name('clientes.json');
});


// Rutas para registrar devoluciones - solo administradores
Route::middleware(['auth', 'user.enabled', 'admin.role'])->group(function () {
    Route::get('pedidos/{id}/devoluciones/create', [App\Http\Controllers\DevolucionController::class, 'create'])->name('pedidos.devoluciones.create');
    Route::post('pedidos/{id}/devoluciones', [App\Http\Controllers\DevolucionController::class, 'store'])->name('pedidos.devoluciones.store');
    Route::get('devoluciones', [App\Http\Controllers\DevolucionController::class, 'index'])->name('devoluciones.index');
    Route::get('devoluciones/{id}', [App\Http\Controllers\DevolucionController::class, 'show'])->name('devoluciones.show');
});

// Ruta de dashboard para empleados - redirige a pedidos
Route::get('/empleado-dashboard', function () {
    return redirect()->route('pedidos.index');
})->middleware(['auth', 'verified', 'user.enabled'])->name('empleado.dashboard');

require __DIR__.'/auth.php';
// Rutas para QR de Banco Ganadero
Route::get('/admin/upload-qr', [App\Http\Controllers\QRController::class, 'showUploadForm'])->name('admin.upload-qr.form')->middleware(['auth', 'admin.role']);
Route::post('/admin/upload-qr', [App\Http\Controllers\QRController::class, 'uploadQR'])->name('admin.upload-qr')->middleware(['auth', 'admin.role']);
// Rutas para confirmación de pagos
Route::get('/pago-exitoso', function() {
    return view('pagos.pago-exitoso');
})->name('pago.exitoso')->middleware('auth');

Route::get('/pago-error', function() {
    return view('pagos.pago-error');
})->name('pago.error')->middleware('auth');

Route::get('/reembolso-exitoso', function() {
    return view('pagos.reembolso-exitoso');
})->name('reembolso.exitoso')->middleware('auth');

Route::get('/reembolso-error', function() {
    return view('pagos.reembolso-error');
})->name('reembolso.error')->middleware('auth');
