<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Cliente;
use App\Services\BitacoraService;
use App\Services\StripeServiceFallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class PagoController extends Controller
{
    protected $bitacoraService;
    protected $stripeService;

    public function __construct(BitacoraService $bitacoraService, StripeServiceFallback $stripeService)
    {
        $this->bitacoraService = $bitacoraService;
        $this->stripeService = $stripeService;
    }

    // CU29: Registrar pago del pedido (admin)
    public function create($pedidoId)
    {
        $pedido = Pedido::with('cliente')->findOrFail($pedidoId);
        return view('pagos.create', compact('pedido'));
    }

    public function store(Request $request, $pedidoId)
    {
        $pedido = Pedido::with('cliente')->findOrFail($pedidoId);

        $request->validate([
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'nullable|string|max:100',
            'referencia' => 'nullable|string|max:255',
            'fecha_pago' => 'nullable|date',
            'banco_origen' => 'nullable|string|max:100',
            'ultimos_digitos' => 'nullable|string|max:4',
            'tipo_tarjeta' => 'nullable|string|max:50',
            'notas' => 'nullable|string|max:1000',
        ]);

        $cliente = $pedido->cliente;

        // Construir referencia según método de pago (para pasarela)
        $referencia = $request->referencia;
        if ($request->metodo === 'tarjeta' && $request->ultimos_digitos) {
            $referencia = ($request->tipo_tarjeta ?? 'Tarjeta') . ' ****' . $request->ultimos_digitos;
        } elseif ($request->metodo === 'transferencia' && $request->banco_origen) {
            $referencia = $request->banco_origen . ' - ' . ($request->referencia ?? 'Sin referencia');
        }

        $pago = Pago::create([
            'id_pedido' => $pedido->id_pedido,
            'id_cliente' => $cliente->id ?? null,
            'monto' => $request->monto,
            'metodo' => $request->metodo,
            'referencia' => $referencia,
            'fecha_pago' => $request->fecha_pago ?? now(),
            'registrado_por' => auth()->id(),
            'notas' => $request->notas,
        ]);

        // Registrar en bitácora
        $metodoTexto = $request->metodo ? " vía {$request->metodo}" : "";
        $mensaje = auth()->user()->nombre . " registró un pago de Bs. {$pago->monto} para el pedido #{$pedido->id_pedido}{$metodoTexto}";
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PAGOS',
            $mensaje,
            null,
            $pago->toArray()
        );

        // También registrar una entrada en el historial del pedido (PEDIDOS) para ser visible en su historial
        $this->bitacoraService->registrarActividad(
            'CREATE',
            'PEDIDOS',
            $mensaje,
            null,
            array_merge($pago->toArray(), ['pedido_id' => $pedido->id_pedido])
        );

        // Redirigir según origen (pasarela o vista tradicional)
        if ($request->has('from_pasarela') || $request->header('referer') && str_contains($request->header('referer'), 'pasarela')) {
            return redirect()->route('pagos.pasarela')->with('success', 'Pago procesado correctamente.');
        }

        return redirect()->route('pedidos.show', $pedido->id_pedido)->with('success', 'Pago registrado correctamente.');
    }

    /**
     * Procesar pago desde la pasarela
     */
    public function procesarPagoPasarela(Request $request)
    {
        try {
            $request->validate([
                'pedido_id' => 'required|exists:pedido,id_pedido',
                'monto' => 'required|numeric|min:0.01',
                'metodo' => 'required|string',
                'referencia' => 'nullable|string|max:255',
                'fecha_pago' => 'nullable|date',
                'notas' => 'nullable|string|max:1000',
            ]);

            $pedido = Pedido::with('cliente')->findOrFail($request->pedido_id);
            $cliente = $pedido->cliente;

            // Verificar si ya existe un pago similar (anti-duplicados)
            $pagoExistente = Pago::where('id_pedido', $pedido->id_pedido)
                ->where('monto', $request->monto)
                ->where('metodo', $request->metodo)
                ->where(\DB::raw('CAST(anulado AS INTEGER)'), 0)
                ->where('created_at', '>=', now()->subMinutes(5)) // En los últimos 5 minutos
                ->first();

            if ($pagoExistente) {
                return redirect()->route('pago.exitoso')
                    ->with('pago_detalles', [
                        'pedido_id' => $pedido->id_pedido,
                        'monto' => $pagoExistente->monto,
                        'metodo' => $pagoExistente->metodo,
                        'fecha' => $pagoExistente->fecha_pago->format('d/m/Y H:i'),
                        'referencia' => $pagoExistente->referencia
                    ])
                    ->with('warning', "Este pago ya fue procesado anteriormente. No se creó un duplicado.");
            }

            // Crear el pago
            $pago = Pago::create([
                'id_pedido' => $pedido->id_pedido,
                'id_cliente' => $cliente->id ?? null,
                'monto' => $request->monto,
                'metodo' => $request->metodo,
                'referencia' => $request->referencia ?? 'Pago desde pasarela',
                'fecha_pago' => $request->fecha_pago ?? now(),
                'registrado_por' => auth()->id(),
                'notas' => $request->notas,
            ]);

            // Registrar en bitácora
            $mensaje = auth()->user()->nombre . " procesó un pago de Bs. {$pago->monto} para el pedido #{$pedido->id_pedido} vía {$request->metodo}";
            $this->bitacoraService->registrarActividad(
                'CREATE',
                'PAGOS',
                $mensaje,
                null,
                $pago->toArray()
            );

            // Redirigir a página de éxito con detalles del pago
            return redirect()->route('pago.exitoso')
                ->with('pago_detalles', [
                    'pedido_id' => $pedido->id_pedido,
                    'monto' => $pago->monto,
                    'metodo' => $request->metodo,
                    'fecha' => $pago->fecha_pago->format('d/m/Y H:i'),
                    'referencia' => $pago->referencia
                ])
                ->with('success', "¡Pago procesado exitosamente! Bs. {$pago->monto} registrado para el pedido #{$pedido->id_pedido}.");

        } catch (\Exception $e) {
            // En caso de error, redirigir a página de error
            return redirect()->route('pago.error')
                ->with('error_mensaje', 'No se pudo procesar el pago correctamente.')
                ->with('error_detalles', [
                    'error' => $e->getMessage(),
                    'pedido' => $request->pedido_id ?? 'No especificado',
                    'metodo' => $request->metodo ?? 'No especificado'
                ]);
        }
    }

    // CU30: Emitir recibo digital
    public function emitirRecibo($pagoId)
    {
        $pago = Pago::with(['pedido.cliente', 'registradoPor'])->findOrFail($pagoId);

        // Verificar autorización: solo admin, empleado o el propio cliente
        if (auth()->user()->id_rol == 3) {
            $clienteUser = Cliente::where('email', auth()->user()->email)->first();
            // Verificar si el cliente existe y si el pago pertenece a uno de sus pedidos
            if (!$clienteUser || $pago->pedido->id_cliente != $clienteUser->id) {
                abort(403, 'No tiene permiso para descargar este recibo.');
            }
        }
        
        // Use dompdf wrapper and ensure options are set for proper unicode rendering
        $pdfWrapper = app()->make('dompdf.wrapper');
        $pdfWrapper->loadView('pagos.recibo', compact('pago'));
        // Ensure proper parser and font for UTF-8 content
        $pdfWrapper->setOption('isHtml5ParserEnabled', true);
        $pdfWrapper->setOption('isRemoteEnabled', false);
        $pdfWrapper->setOption('defaultFont', 'sans-serif'); 
        // Disable inline PHP in documents for security
        $pdfWrapper->setOption('isPhpEnabled', false);
        $pdfWrapper->setPaper('A4');
        $pdfContent = $pdfWrapper->output();
        $filename = 'recibo_pago_' . $pago->id . '.pdf';
        // Save to storage and link (binary content)
        Storage::put('public/recibos/'.$filename, $pdfContent);
        $pago->recibo_path = 'recibos/'.$filename;
        $pago->save();

        // For normal use we download, but if the request asks for `stream` query param we stream inline
        if (request()->query('view') == 'inline') {
            return $pdfWrapper->stream($filename);
        }
        return $pdfWrapper->download($filename);
    }

    // Helper route for debugging: stream instead of download
    public function emitirReciboStream($pagoId)
    {
        return $this->emitirRecibo($pagoId);
    }

    // CU31: Consultar estado de pago del cliente (admin)
    public function clientePagos($clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        $pagos = Pago::where('id_cliente', $cliente->id)->orderBy('fecha_pago', 'desc')->get();
        $totalPagado = $pagos->where('anulado', false)->sum('monto');
        return view('pagos.cliente-pagos', compact('cliente', 'pagos', 'totalPagado'));
    }

    /**
     * Mostrar historial de pagos del cliente autenticado
     */
    public function misPagos()
    {
        // Verificar que el usuario sea cliente (rol 3)
        if (auth()->user()->id_rol != 3) {
             return redirect()->route('dashboard');
        }

        $cliente = Cliente::where('email', auth()->user()->email)->first();
        
        if (!$cliente) {
             // Si el cliente no existe (raro si es rol 3), mostrar vista vacía
             return view('pagos.mis-pagos', [
                 'cliente' => null,
                 'pagos' => collect(),
                 'totalPagado' => 0
             ]);
        }

        $pagos = Pago::where('id_cliente', $cliente->id)
                     ->with('pedido') // Cargar relación pedido
                     ->orderBy('fecha_pago', 'desc')
                     ->get();
                     
        $totalPagado = $pagos->where('anulado', false)->sum('monto');
        
        return view('pagos.mis-pagos', compact('cliente', 'pagos', 'totalPagado'));
    }

    // CU: Emitir recibo consolidado
    public function emitirReciboConsolidado(Request $request, $clienteId)
    {
        $cliente = Cliente::findOrFail($clienteId);
        
        $query = Pago::where('id_cliente', $cliente->id)
            ->where(\DB::raw('CAST(anulado AS INTEGER)'), 0); // Usar where explícito con cast para PostgreSQL

        // Filtros de fecha
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('fecha_pago', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('fecha_pago', '<=', $request->fecha_fin);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->get();

        if ($pagos->isEmpty()) {
            return redirect()->back()->with('error', 'No hay pagos confirmados para generar el recibo consolidado en el periodo seleccionado.');
        }

        $periodo = null;
        if ($request->filled('fecha_inicio') || $request->filled('fecha_fin')) {
            $inicio = $request->fecha_inicio ? \Carbon\Carbon::parse($request->fecha_inicio)->format('d/m/Y') : 'Inicio';
            $fin = $request->fecha_fin ? \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y') : 'Hoy';
            $periodo = "$inicio - $fin";
        }

        $pdfWrapper = app()->make('dompdf.wrapper');
        $pdfWrapper->loadView('pagos.recibo-consolidado', compact('cliente', 'pagos', 'periodo'));
        $pdfWrapper->setOption('isHtml5ParserEnabled', true);
        $pdfWrapper->setOption('isRemoteEnabled', false);
        $pdfWrapper->setOption('enable_font_subsetting', true);
        $pdfWrapper->setOption('defaultFont', 'DejaVu Sans');
        $pdfWrapper->setPaper('A4');
        
        $filename = 'recibo_consolidado_' . $cliente->id . '_' . now()->format('Ymd') . '.pdf';
        return $pdfWrapper->download($filename);
    }

    // CU32: Anular pago registrado por error o reembolsar
    public function anular(Request $request, $pagoId)
    {
        $pago = Pago::findOrFail($pagoId);
        $request->validate([
            'motivo' => 'sometimes|required|string|max:1000',
            'motivo_anulacion' => 'sometimes|required|string|max:1000'
        ]);
        
        $motivo = $request->motivo ?? $request->motivo_anulacion;

        // Intentar reembolso en Stripe si corresponde
        $refundMsg = "";
        if ($pago->metodo === 'stripe' && $pago->referencia) {
            try {
                $result = $this->stripeService->refundPayment($pago->referencia);
                if ($result['success']) {
                    $refundMsg = " [Reembolso Stripe Exitoso: " . $result['refund_id'] . "]";
                } else {
                    $refundMsg = " [Error Reembolso Stripe: " . $result['error'] . "]";
                }
            } catch (\Exception $e) {
                $refundMsg = " [Error intentando reembolso: " . $e->getMessage() . "]";
            }
        }

        // Usamos SQL explícito para forzar booleano true en PostgreSQL y evitar cast a integer
        DB::update(
            'UPDATE "pago" SET "anulado" = true, "anulado_por" = ?, "anulado_motivo" = ?, "updated_at" = ? WHERE "id" = ?',
            [auth()->id(), $motivo . $refundMsg, now(), $pago->id]
        );

        $pago->refresh();

        $mensaje = auth()->user()->nombre . " anuló/reembolsó el pago #{$pago->id} (Motivo: {$motivo}{$refundMsg})";
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PAGOS',
            $mensaje,
            null,
            $pago->toArray()
        );
        $this->bitacoraService->registrarActividad(
            'UPDATE',
            'PEDIDOS',
            $mensaje,
            null,
            array_merge($pago->toArray(), ['pedido_id' => $pago->id_pedido])
        );

        // Si es una petición AJAX, devolver JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pago anulado/reembolsado correctamente.' . $refundMsg
            ]);
        }
        
        return redirect()->back()->with('success', 'Pago anulado/reembolsado correctamente.' . $refundMsg);
    }

    // Admin listing for management
    public function index()
    {
        $pagos = Pago::with(['pedido', 'cliente', 'registradoPor'])->orderBy('created_at', 'desc')->paginate(25);
        return view('pagos.index', compact('pagos'));
    }

    /**
     * Redirect to payment gateway with specific order pre-selected
     */
    public function checkout($pedidoId)
    {
        // Verificar que el pedido existe
        $pedido = Pedido::findOrFail($pedidoId);
        
        // Establecer el pedido en la sesión para pre-selección
        session(['pedido_creado' => $pedidoId]);
        
        // Redirigir a la pasarela de pagos
        return redirect()->route('pagos.pasarela');
    }

    /**
     * Mostrar interfaz de reembolso para un pedido
     */
    public function mostrarReembolso($pedidoId)
    {
        $pedido = Pedido::with(['cliente'])->findOrFail($pedidoId);
        
        // Obtener solo pagos activos (no anulados) con información del registrador
        $pagosActivos = $pedido->pagos()
            ->where(\DB::raw('CAST(anulado AS INTEGER)'), 0)
            ->with('registradoPor')
            ->orderBy('fecha_pago', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        return view('pagos.reembolso', compact('pedido', 'pagosActivos'));
    }

    /**
     * Limpiar pagos duplicados de un pedido
     */
    public function limpiarPagosDuplicados($pedidoId)
    {
        try {
            $pedido = Pedido::findOrFail($pedidoId);
            
            // Obtener todos los pagos del pedido agrupados por características similares
            $pagos = Pago::where('id_pedido', $pedido->id_pedido)
                ->where(\DB::raw('CAST(anulado AS INTEGER)'), 0)
                ->orderBy('created_at', 'asc')
                ->get();

            $pagosLimpiados = 0;
            $gruposPagos = [];

            // Agrupar pagos por monto, método y fecha (mismo día)
            foreach ($pagos as $pago) {
                $clave = $pago->monto . '|' . $pago->metodo . '|' . $pago->fecha_pago->format('Y-m-d');
                
                if (!isset($gruposPagos[$clave])) {
                    $gruposPagos[$clave] = [];
                }
                
                $gruposPagos[$clave][] = $pago;
            }

            // Para cada grupo, mantener solo el primer pago y anular los duplicados
            foreach ($gruposPagos as $grupo) {
                if (count($grupo) > 1) {
                    // Mantener el primer pago (más antiguo)
                    $pagoOriginal = array_shift($grupo);
                    
                    // Anular los duplicados
                    foreach ($grupo as $pagoDuplicado) {
                        \DB::update(
                            'UPDATE "pago" SET "anulado" = true, "anulado_por" = ?, "anulado_motivo" = ?, "updated_at" = ? WHERE "id" = ?',
                            [
                                auth()->id(), 
                                'Pago duplicado eliminado automáticamente. Pago original ID: ' . $pagoOriginal->id,
                                now(), 
                                $pagoDuplicado->id
                            ]
                        );
                        $pagosLimpiados++;
                    }
                }
            }

            // Registrar en bitácora
            if ($pagosLimpiados > 0) {
                $this->bitacoraService->registrarActividad(
                    'UPDATE',
                    'PAGOS',
                    auth()->user()->nombre . " limpió {$pagosLimpiados} pagos duplicados del pedido #{$pedido->id_pedido}",
                    null,
                    ['pedido_id' => $pedido->id_pedido, 'pagos_limpiados' => $pagosLimpiados]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $pagosLimpiados > 0 
                    ? "Se limpiaron {$pagosLimpiados} pagos duplicados" 
                    : "No se encontraron pagos duplicados",
                'pagos_limpiados' => $pagosLimpiados
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar pagos duplicados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesar reembolso con datos completos
     */
    public function procesarReembolso(Request $request, $pagoId)
    {
        try {
            \Log::info('Procesando reembolso - Pago ID: ' . $pagoId . ' - Datos recibidos: ' . json_encode($request->all()));
            
            $pago = Pago::findOrFail($pagoId);
            
            // Verificar si ya existe un reembolso para este pago
            $reembolsoExistente = \App\Models\SolicitudReembolso::where('pago_id', $pagoId)->first();
            if ($reembolsoExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este pago ya tiene un reembolso registrado. Solo se permite un reembolso por pago.'
                ], 422);
            }
            
            // Validar datos del reembolso
            $request->validate([
                'tipo_reembolso' => 'required|string|in:error_sistema,pedido_cancelado,solicitud_cliente',
                'motivo_detallado' => 'required|string|max:1000',
                'beneficiario_nombre' => 'required|string|max:255',
                'beneficiario_ci' => 'required|string|max:50',
                'beneficiario_telefono' => 'required|string|max:20',
                'beneficiario_email' => 'nullable|email|max:255',
                'metodo_reembolso' => 'required|string|in:efectivo,transferencia',
                'banco' => 'required_if:metodo_reembolso,transferencia|nullable|string|max:100',
                'numero_cuenta' => 'required_if:metodo_reembolso,transferencia|nullable|string|max:50',
            ]);

            // Construir motivo completo
            $tiposReembolso = [
                'error_sistema' => 'Error del Sistema',
                'pedido_cancelado' => 'Pedido Cancelado',
                'solicitud_cliente' => 'Solicitud del Cliente'
            ];
            
            $motivoCompleto = "[{$tiposReembolso[$request->tipo_reembolso]}] {$request->motivo_detallado}";
            $motivoCompleto .= " | Beneficiario: {$request->beneficiario_nombre} (CI: {$request->beneficiario_ci})";
            $motivoCompleto .= " | Método: " . ucfirst($request->metodo_reembolso);
            
            if ($request->metodo_reembolso === 'transferencia') {
                $motivoCompleto .= " | Banco: {$request->banco} | Cuenta: {$request->numero_cuenta}";
            }

            $refundMsg = "";
            $pagoAnulado = false;

            // Lógica según el método de reembolso
            if ($request->metodo_reembolso === 'efectivo') {
                // EFECTIVO: Solo guardar registro como completado (no anular pago para mantener historial)
                
                \App\Models\SolicitudReembolso::create([
                    'pago_id' => $pago->id,
                    'pedido_id' => $pago->id_pedido,
                    'tipo_reembolso' => $request->tipo_reembolso,
                    'motivo_detallado' => $request->motivo_detallado,
                    'beneficiario_nombre' => $request->beneficiario_nombre,
                    'beneficiario_ci' => $request->beneficiario_ci,
                    'beneficiario_telefono' => $request->beneficiario_telefono,
                    'beneficiario_email' => $request->beneficiario_email,
                    'metodo_reembolso' => 'efectivo',
                    'monto' => $pago->monto,
                    'estado' => 'procesado', // Marcado como procesado inmediatamente
                    'solicitado_por' => auth()->id(),
                    'procesado_por' => auth()->id(),
                    'fecha_procesado' => now(),
                    'notas_procesamiento' => 'Reembolso en efectivo entregado inmediatamente'
                ]);
                
                $refundMsg = " - Reembolso en efectivo registrado como completado";

            } else {
                // TRANSFERENCIA: Solo guardar datos para procesamiento manual
                \App\Models\SolicitudReembolso::create([
                    'pago_id' => $pago->id,
                    'pedido_id' => $pago->id_pedido,
                    'tipo_reembolso' => $request->tipo_reembolso,
                    'motivo_detallado' => $request->motivo_detallado,
                    'beneficiario_nombre' => $request->beneficiario_nombre,
                    'beneficiario_ci' => $request->beneficiario_ci,
                    'beneficiario_telefono' => $request->beneficiario_telefono,
                    'beneficiario_email' => $request->beneficiario_email,
                    'metodo_reembolso' => $request->metodo_reembolso,
                    'banco' => $request->banco,
                    'numero_cuenta' => $request->numero_cuenta,
                    'monto' => $pago->monto,
                    'solicitado_por' => auth()->id(),
                ]);
                
                $motivoCompleto .= " [PENDIENTE - Requiere procesamiento manual de transferencia]";
                $refundMsg = " - Solicitud guardada para procesamiento manual";
                // NO anular el pago todavía, solo guardar la información
            }

            $pago->refresh();

            // Registrar en bitácora
            $mensaje = auth()->user()->nombre . " procesó reembolso del pago #{$pago->id}";
            $mensaje .= " - Tipo: {$tiposReembolso[$request->tipo_reembolso]}";
            $mensaje .= " - Método: " . ucfirst($request->metodo_reembolso);
            
            if ($request->metodo_reembolso === 'efectivo') {
                $mensaje .= " - Estado: COMPLETADO (efectivo entregado)";
            } else {
                $mensaje .= " - Estado: PENDIENTE (requiere procesamiento manual)";
            }
            
            $mensaje .= $refundMsg;

            $this->bitacoraService->registrarActividad(
                'CREATE',
                'REEMBOLSOS',
                $mensaje,
                null,
                [
                    'pago_id' => $pago->id,
                    'pedido_id' => $pago->id_pedido,
                    'monto_reembolsado' => $pago->monto,
                    'metodo_reembolso' => $request->metodo_reembolso,
                    'estado_procesamiento' => $request->metodo_reembolso === 'efectivo' ? 'completado' : 'pendiente',
                    'datos_reembolso' => $request->only([
                        'tipo_reembolso', 'beneficiario_nombre', 'beneficiario_ci', 
                        'beneficiario_telefono', 'metodo_reembolso', 'banco', 'numero_cuenta'
                    ])
                ]
            );

            $mensaje = $request->metodo_reembolso === 'efectivo' 
                ? 'Reembolso en efectivo completado exitosamente' . $refundMsg
                : 'Solicitud de reembolso guardada exitosamente' . $refundMsg;

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'tipo_procesamiento' => $request->metodo_reembolso === 'efectivo' ? 'completado' : 'pendiente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación en reembolso: ' . json_encode($e->errors()));
            
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', array_flatten($e->errors()))
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error procesando reembolso: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el reembolso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener pagos de un pedido específico (API)
     */
    public function obtenerPagosPedido($pedidoId)
    {
        try {
            $pedido = Pedido::findOrFail($pedidoId);
            
            $pagos = $pedido->pagos()->with('registradoPor')->get()->map(function($pago) {
                return [
                    'id' => $pago->id,
                    'monto' => $pago->monto,
                    'metodo' => $pago->metodo,
                    'referencia' => $pago->referencia,
                    'fecha_pago' => $pago->fecha_pago->format('d/m/Y H:i'),
                    'anulado' => $pago->anulado,
                    'registrado_por' => $pago->registradoPor ? $pago->registradoPor->nombre : 'N/A'
                ];
            });
            
            return response()->json([
                'success' => true,
                'pagos' => $pagos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    // Pasarela de pagos para empleados y administradores
    // Pasarela de pagos para empleados, administradores y clientes
    public function pasarela()
    {
        // Query base para pedidos
        $query = Pedido::with(['cliente', 'pagos' => function($query) {
                $query->anulado(false); // Usar el scope que maneja PostgreSQL correctamente
            }])
            ->whereIn('estado', ['En proceso', 'Asignado', 'En producción', 'Terminado', 'Entregado']); // Added 'En proceso' and 'Asignado' so new orders appear

        // Si es cliente, SOLO ver sus propios pedidos
        if (auth()->check() && auth()->user()->id_rol == 3) {
            $cliente = Cliente::where('email', auth()->user()->email)->first();
            if ($cliente) {
                $query->where('id_cliente', $cliente->id);
            } else {
                // Si no se encuentra el cliente asociado, no mostrar nada
                $query->where('id_pedido', -1);
            }
        }

        // Obtener pedidos
        $todosPedidos = $query->orderBy('created_at', 'desc')->get();

        // Filtrar pedidos que necesitan pago (sin pagos o con pago parcial)
        $pedidosPendientes = $todosPedidos->filter(function($pedido) {
            $totalPagado = $pedido->pagos->sum('monto');
            // Allow small float difference
            return ($pedido->total - $totalPagado) > 0.01;
        });
        
        // Si no es cliente, limitar a 10 para no saturar la vista admin
        if (!auth()->check() || auth()->user()->id_rol != 3) {
            $pedidosPendientes = $pedidosPendientes->take(10);
        }

        // Obtener métodos de pago activos
        $metodosPago = \App\Models\MetodoPago::activos()->get();

        // Verificar si hay un pedido recién creado
    $pedidoRecienCreadoId = session('pedido_creado');
    $pedidoSeleccionado = null;

    if ($pedidoRecienCreadoId) {
        $pedidoSeleccionado = Pedido::with(['cliente', 'pagos' => function($query) {
            $query->anulado(false);
        }])->find($pedidoRecienCreadoId);
    }

    return view('pagos.pasarela', [
        'pedidosPendientes' => $pedidosPendientes,
        'metodosPago' => $metodosPago,
        'pedidoRecienCreado' => $pedidoRecienCreadoId,
        'pedidoSeleccionado' => $pedidoSeleccionado
    ]);
}
    // API para buscar pedidos
    public function buscarPedido($numero)
    {
        try {
            $pedido = Pedido::with('cliente')
                ->where('id_pedido', $numero)
                ->first();

            if (!$pedido) {
                return response()->json(['success' => false, 'message' => 'Pedido no encontrado']);
            }

            // Calcular monto pendiente
            $totalPagado = $pedido->pagos()->where('anulado', false)->sum('monto');
            $montoPendiente = $pedido->total - $totalPagado;

            return response()->json([
                'success' => true,
                'pedido' => [
                    'id' => $pedido->id_pedido,
                    'cliente' => $pedido->cliente->nombre ?? 'N/A',
                    'total' => $pedido->total,
                    'total_pagado' => $totalPagado,
                    'monto_pendiente' => $montoPendiente,
                    'estado' => $pedido->estado
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al buscar el pedido']);
        }
    }

    // Crear Payment Intent para Stripe
    public function createPaymentIntent(Request $request)
    {
        try {
            // Debug completo
            \Log::info('CreatePaymentIntent - All request data:', [
                'all' => $request->all(),
                'input' => $request->input(),
                'json' => $request->json()->all(),
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type')
            ]);
            
            // Verificar si los datos vienen en JSON
            $data = $request->json()->all() ?: $request->all();
            
            // Validar usando los datos correctos
            $validator = \Validator::make($data, [
                'pedido_id' => 'required|exists:pedido,id_pedido',
                'amount' => 'required|numeric|min:0.01'
            ]);
            
            if ($validator->fails()) {
                \Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed: ' . $validator->errors()->first(),
                    'received_data' => $data
                ]);
            }
            
            $pedidoId = $data['pedido_id'];
            $amount = $data['amount'];

            $pedido = Pedido::with('cliente')->findOrFail($pedidoId);

            $result = $this->stripeService->createPaymentIntent(
                $amount,
                'usd', // Cambiar según tu moneda
                [
                    'pedido_id' => $pedido->id_pedido,
                    'cliente_id' => $pedido->cliente->id ?? null,
                    'cliente_nombre' => $pedido->cliente->nombre ?? 'N/A'
                ]
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al crear el payment intent: ' . $e->getMessage()
            ]);
        }
    }

    // Generar QR para pago
    public function generatePaymentQR(Request $request)
    {
        try {
            $request->validate([
                'pedido_id' => 'required|exists:pedido,id_pedido',
                'amount' => 'required|numeric|min:0.01'
            ]);

            $pedido = Pedido::with('cliente')->findOrFail($request->pedido_id);

            // Crear Payment Link de Stripe
            $linkResult = $this->stripeService->createPaymentLink(
                $request->amount,
                'usd',
                [
                    'pedido_id' => $pedido->id_pedido,
                    'cliente_id' => $pedido->cliente->id ?? null,
                    'cliente_nombre' => $pedido->cliente->nombre ?? 'N/A'
                ]
            );

            if (!$linkResult['success']) {
                return response()->json($linkResult);
            }

            // Generar QR Code
            $qrResult = $this->stripeService->generateQRCode($linkResult['payment_link_url']);

            return response()->json([
                'success' => true,
                'payment_url' => $linkResult['payment_link_url'],
                'qr_code_url' => $qrResult['qr_code_url'],
                'payment_link_id' => $linkResult['payment_link_id']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al generar QR: ' . $e->getMessage()
            ]);
        }
    }

    // Confirmar pago de Stripe
    public function confirmStripePayment(Request $request)
    {
        try {
            $request->validate([
                'payment_intent_id' => 'required|string',
                'pedido_id' => 'required|exists:pedido,id_pedido'
            ]);

            $result = $this->stripeService->confirmPaymentIntent($request->payment_intent_id);

            if ($result['success'] && $result['status'] === 'succeeded') {
                $pedido = Pedido::with('cliente')->findOrFail($request->pedido_id);
                $paymentIntent = $result['payment_intent'];

                // Registrar el pago en la base de datos
                $pago = Pago::create([
                    'id_pedido' => $pedido->id_pedido,
                    'id_cliente' => $pedido->cliente->id ?? null,
                    'monto' => ($paymentIntent['amount'] ?? $paymentIntent->amount ?? 0) / 100, // Convertir de centavos
                    'metodo' => 'stripe',
                    'referencia' => $paymentIntent['id'] ?? $paymentIntent->id ?? 'stripe_' . time(),
                    'fecha_pago' => now(),
                    'registrado_por' => auth()->id(),
                    'notas' => 'Pago procesado vía Stripe'
                ]);

                // Registrar en bitácora
                $mensaje = "Pago de Bs. {$pago->monto} procesado vía Stripe para el pedido #{$pedido->id_pedido}";
                $this->bitacoraService->registrarActividad(
                    'CREATE',
                    'PAGOS',
                    $mensaje,
                    null,
                    $pago->toArray()
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Pago confirmado exitosamente',
                    'pago_id' => $pago->id,
                    'redirect_url' => route('pago.exitoso'),
                    'pago_detalles' => [
                        'pedido_id' => $pedido->id_pedido,
                        'monto' => $pago->monto,
                        'metodo' => 'Stripe',
                        'fecha' => $pago->fecha_pago->format('d/m/Y H:i'),
                        'referencia' => $pago->referencia
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => 'El pago no pudo ser confirmado',
                'redirect_url' => route('pago.error')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error al confirmar el pago: ' . $e->getMessage(),
                'redirect_url' => route('pago.error')
            ]);
        }
    }
}
