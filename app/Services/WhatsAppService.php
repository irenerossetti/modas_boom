<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Prenda;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class WhatsAppService
{
    protected $accountSid;
    protected $authToken;
    protected $whatsappFrom;
    protected $notificationsBaseUrl;
    protected $notificationsApiKey;
    protected $notificationsEnabled;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.sid');
        $this->authToken = config('services.twilio.token');
        $this->whatsappFrom = config('services.twilio.whatsapp_from');
        $this->notificationsBaseUrl = config('services.notifications.base_url') ?? env('NOTIFICATIONS_URL_BASE');
        $this->notificationsApiKey = config('services.notifications.api_key') ?? env('NOTIFICATIONS_API_KEY');
        $this->notificationsEnabled = env('NOTIFICATIONS_ENABLED', true);
    }

    /**
     * Enviar notificación cuando el pedido está terminado
     */
    public function enviarNotificacionTerminado(Pedido $pedido, array $camposCambiados = []): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene número de teléfono registrado.'
                ];
            }

            $mensaje = $this->prepararMensajeTerminado($pedido, $camposCambiados);
            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            
            $resultado = $this->enviarViaProxy($telefono, $mensaje);
            
            if ($resultado['success']) {
                Log::info("Notificación 'Terminado' enviada", [
                    'pedido_id' => $pedido->id_pedido,
                    'cliente' => $pedido->cliente->nombre,
                    'telefono' => $telefono,
                    'campos_cambiados' => array_keys($camposCambiados)
                ]);
            }

            return $resultado;

        } catch (\Exception $e) {
            Log::error("Error enviando notificación 'Terminado'", [
                'pedido_id' => $pedido->id_pedido,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar notificación cuando el pedido está entregado
     */
    public function enviarNotificacionEntregado(Pedido $pedido, array $camposCambiados = []): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene número de teléfono registrado.'
                ];
            }

            $mensaje = $this->prepararMensajeEntregado($pedido, $camposCambiados);
            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            
            $resultado = $this->enviarViaProxy($telefono, $mensaje);
            
            if ($resultado['success']) {
                Log::info("Notificación 'Entregado' enviada", [
                    'pedido_id' => $pedido->id_pedido,
                    'cliente' => $pedido->cliente->nombre,
                    'telefono' => $telefono,
                    'campos_cambiados' => array_keys($camposCambiados)
                ]);
            }

            return $resultado;

        } catch (\Exception $e) {
            Log::error("Error enviando notificación 'Entregado'", [
                'pedido_id' => $pedido->id_pedido,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Enviar confirmación de pedido creado
     */
    public function enviarConfirmacionPedido(Pedido $pedido): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene número de teléfono registrado.'
                ];
            }

            $mensaje = $this->prepararMensajeConfirmacionPedido($pedido);
            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            
            $resultado = $this->enviarViaProxy($telefono, $mensaje);
            
            if ($resultado['success']) {
                Log::info("Confirmación de pedido enviada por WhatsApp", [
                    'pedido_id' => $pedido->id_pedido,
                    'cliente' => $pedido->cliente->nombre,
                    'telefono' => $telefono
                ]);
            }

            return $resultado;

        } catch (\Exception $e) {
            Log::error("Error enviando confirmación de pedido por WhatsApp", [
                'pedido_id' => $pedido->id_pedido,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Notificar al cliente que su pago fue recibido y el pedido aceptado
     */
    public function enviarNotificacionPagoRecibido(Pedido $pedido, Pago $pago): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return ['success' => false, 'message' => 'El cliente no tiene teléfono registrado.'];
            }

            // Calcular total pagado hasta ahora (sin anulados)
            $totalPagado = DB::table('pago')
                ->where('id_pedido', $pedido->id_pedido)
                ->where(DB::raw('CAST(anulado AS INTEGER)'), 0)
                ->sum('monto');

            $saldo = max(0, $pedido->total - $totalPagado);

            $mensaje = "✅ *Pago recibido* ✅\n\n";
            $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
            $mensaje .= "Hemos recibido su pago de *Bs. " . number_format($pago->monto, 2) . "* para el pedido #{$pedido->id_pedido}.\n";
            $mensaje .= "• Método: " . ($pago->metodo ?? 'N/A') . "\n";
            $mensaje .= "• Referencia: " . ($pago->referencia ?? 'N/A') . "\n\n";
            $mensaje .= "• Total pedido: Bs. " . number_format($pedido->total, 2) . "\n";
            $mensaje .= "• Total pagado: Bs. " . number_format($totalPagado, 2) . "\n";
            $mensaje .= "• Saldo pendiente: Bs. " . number_format($saldo, 2) . "\n\n";
            $mensaje .= "Su pedido ha sido aceptado y está en proceso. ¡Gracias por su pago!\n\n";
            $mensaje .= "---\n*Modas Boom*\n📞 +591 76720864";

            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            $resultado = $this->enviarViaProxy($telefono, $mensaje);

            if ($resultado['success']) {
                Log::info('Notificación de pago recibido enviada', ['pedido_id' => $pedido->id_pedido, 'pago_id' => $pago->id]);
                // Marcar que se envió notificación de WhatsApp para este pedido (no crítico)
                try { $pedido->update(['notificacion_whatsapp_enviada' => true]); } catch (\Throwable $e) { /* ignorar */ }
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Error en enviarNotificacionPagoRecibido', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    /**
     * Notificar al cliente que el pago fue anulado/reembolsado
     */
    public function enviarNotificacionPagoAnulado(Pedido $pedido, Pago $pago, string $motivo = null): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return ['success' => false, 'message' => 'El cliente no tiene teléfono registrado.'];
            }

            $mensaje = "⚠️ *Pago anulado/reembolsado* ⚠️\n\n";
            $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
            $mensaje .= "El pago de *Bs. " . number_format($pago->monto, 2) . "* para el pedido #{$pedido->id_pedido} ha sido anulado/reembolsado.\n";
            if ($motivo) $mensaje .= "Motivo: {$motivo}\n\n";
            $mensaje .= "Si tienes dudas, por favor contáctanos.\n\n";
            $mensaje .= "---\n*Modas Boom*\n📞 +591 76720864";

            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            $resultado = $this->enviarViaProxy($telefono, $mensaje);

            if ($resultado['success']) {
                Log::info('Notificación de pago anulado enviada', ['pedido_id' => $pedido->id_pedido, 'pago_id' => $pago->id]);
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Error en enviarNotificacionPagoAnulado', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    /**
     * Notificar al cliente que su pedido fue cancelado
     */
    public function enviarNotificacionPedidoCancelado(Pedido $pedido, string $motivo = null): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return ['success' => false, 'message' => 'El cliente no tiene teléfono registrado.'];
            }

            $mensaje = "⚠️ *Pedido cancelado* ⚠️\n\n";
            $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
            $mensaje .= "Lamentamos informarle que su pedido #{$pedido->id_pedido} ha sido cancelado.\n";
            if ($motivo) $mensaje .= "Motivo: {$motivo}\n\n";
            $mensaje .= "Si necesitas más información, contáctanos y con gusto te ayudaremos.\n\n";
            $mensaje .= "---\n*Modas Boom*\n📞 +591 76720864";

            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            $resultado = $this->enviarViaProxy($telefono, $mensaje);

            if ($resultado['success']) {
                Log::info('Notificación de pedido cancelado enviada', ['pedido_id' => $pedido->id_pedido]);
                try { $pedido->update(['notificacion_whatsapp_enviada' => true]); } catch (\Throwable $e) { /* ignorar */ }
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Error en enviarNotificacionPedidoCancelado', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    /**
     * Enviar confirmación de recepción
     */
    public function enviarConfirmacionRecepcion(Pedido $pedido): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene número de teléfono registrado.'
                ];
            }

            if (!$pedido->recepcion_confirmada) {
                return [
                    'success' => false,
                    'message' => 'La recepción del pedido no ha sido confirmada.'
                ];
            }

            $mensaje = $this->prepararMensajeRecepcionConfirmada($pedido);
            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            
            $resultado = $this->enviarViaProxy($telefono, $mensaje);
            
            if ($resultado['success']) {
                // Marcar como enviado
                $pedido->update(['notificacion_whatsapp_enviada' => true]);
                
                Log::info("Confirmación de recepción enviada", [
                    'pedido_id' => $pedido->id_pedido,
                    'cliente' => $pedido->cliente->nombre,
                    'telefono' => $telefono
                ]);
            }

            return $resultado;

        } catch (\Exception $e) {
            Log::error("Error enviando confirmación de recepción", [
                'pedido_id' => $pedido->id_pedido,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Preparar mensaje para pedido terminado
     */
    private function prepararMensajeTerminado(Pedido $pedido, array $camposCambiados = []): string
    {
        $mensaje = "🎉 *¡Tu pedido está listo!* 🎉\n\n";
        $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
        $mensaje .= "Te tenemos excelentes noticias. Tu pedido #{$pedido->id_pedido} está completamente terminado y se ve increíble.\n\n";
        $mensaje .= "📋 *Detalles:*\n";
        $mensaje .= "• Pedido: #{$pedido->id_pedido}\n";

        if (empty($camposCambiados) || isset($camposCambiados['total'])) {
            $mensaje .= "• Total: {$pedido->total_formateado}\n";
        }

        $mensaje .= "• Estado: ✅ Terminado\n\n";

        // Incluir fecha estimada de entrega solo si no se pasó campos (compatibilidad) o si la fecha cambió
        if (empty($camposCambiados) || isset($camposCambiados['fecha_entrega_programada'])) {
            if ($pedido->fecha_entrega_programada) {
                $mensaje .= "• Fecha estimada de entrega: " . $pedido->fecha_entrega_programada->setTimezone('America/La_Paz')->format('d/m/Y') . "\n\n";
            }
        }

        $mensaje .= "🏪 *¿Cómo recoger tu pedido?*\n";
        $mensaje .= "• Puedes pasar cuando gustes\n";
        $mensaje .= "• Horarios: Lun-Sáb 9:00-18:00\n";
        $mensaje .= "• También podemos coordinar entrega\n\n";
        $mensaje .= "¡Estamos seguros de que te va a encantar! 💫\n\n";
        $mensaje .= "---\n";
        $mensaje .= "*Modas Boom*\n";
        $mensaje .= "📞 +591 76720864";

        return $mensaje;
    }

    /**
     * Preparar mensaje para pedido entregado
     */
    private function prepararMensajeEntregado(Pedido $pedido, array $camposCambiados = []): string
    {
        $mensaje = "🚚 *¡Pedido entregado exitosamente!* 🎉\n\n";
        $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
        $mensaje .= "Confirmamos que tu pedido #{$pedido->id_pedido} ha sido entregado exitosamente.\n\n";
        $mensaje .= "📋 *Detalles de la entrega:*\n";
        $mensaje .= "• Pedido: #{$pedido->id_pedido}\n";

        if (empty($camposCambiados) || isset($camposCambiados['total'])) {
            $mensaje .= "• Total: {$pedido->total_formateado}\n";
        }

        $mensaje .= "• Estado: ✅ Entregado\n";

        if (empty($camposCambiados) || isset($camposCambiados['fecha_entrega_programada'])) {
            $mensaje .= "• Fecha: " . now('America/La_Paz')->format('d/m/Y H:i') . "\n\n";
        } else {
            $mensaje .= "\n";
        }
        
        if ($pedido->observaciones_recepcion) {
            $mensaje .= "📝 *Observaciones:*\n";
            $mensaje .= $pedido->observaciones_recepcion . "\n\n";
        }
        
        $mensaje .= "¡Esperamos que disfrutes mucho tu nueva ropa! ✨\n\n";
        $mensaje .= "⭐ *¿Te gustó nuestro servicio?*\n";
        $mensaje .= "Nos encantaría conocer tu opinión y que nos recomiendes con tus amigos.\n\n";
        $mensaje .= "¡Gracias por confiar en Modas Boom! 💖\n\n";
        $mensaje .= "---\n";
        $mensaje .= "*Modas Boom*\n";
        $mensaje .= "📞 +591 76720864";

        return $mensaje;
    }

    /**
     * Preparar mensaje de confirmación de pedido
     */
    private function prepararMensajeConfirmacionPedido(Pedido $pedido): string
    {
        // Asegurar que las prendas estén cargadas para incluir detalles
        $pedido->loadMissing('prendas');
        $mensaje = "📋 *CONFIRMACIÓN DE PEDIDO* 📋\n\n";
        $mensaje .= "¡Hola {$pedido->cliente->nombre}! 👋\n\n";
        $mensaje .= "✅ Tu pedido ya está en nuestras manos y nuestro equipo ya está trabajando para crear algo increíble para ti.\n\n";
        $mensaje .= "📋 *Detalles del pedido:*\n";
        $mensaje .= "• Número: #{$pedido->id_pedido}\n";
        $mensaje .= "• Total: {$pedido->total_formateado}\n";
        $mensaje .= "• Fecha: " . $pedido->created_at->setTimezone('America/La_Paz')->format('d/m/Y H:i') . "\n";

        // Listado de productos (si están cargados en la relación)
        if ($pedido->relationLoaded('prendas') || $pedido->prendas()->exists()) {
            $mensaje .= "\n*Productos: *\n";
            foreach ($pedido->prendas as $prenda) {
                $cantidadUnidades = $prenda->pivot->cantidad ?? 0;
                $docenas = intval($cantidadUnidades / 12);
                $mensaje .= "• {$prenda->nombre} ({$prenda->categoria}) - ";
                $mensaje .= "{$docenas} docena" . ($docenas > 1 ? 's' : '') . " ({$cantidadUnidades} unidades) - Bs. " . number_format($prenda->pivot->precio_unitario * $docenas, 2) . "\n";
            }
        }
        
        if ($pedido->fecha_entrega_programada) {
            $mensaje .= "• Entrega programada: " . $pedido->fecha_entrega_programada->setTimezone('America/La_Paz')->format('d/m/Y') . "\n";
        }
        
        $mensaje .= "• Estado: En proceso\n\n";
        
        $mensaje .= "⏰ *Tiempo estimado de producción:*\n";
        $mensaje .= "• Pedidos normales: 1-2 semanas\n";
        $mensaje .= "• Pedidos grandes (4+ docenas): 3-4 semanas\n\n";
        
        $mensaje .= "📞 *Próximos pasos:*\n";
        $mensaje .= "• Te mantendremos informado del progreso\n";
        $mensaje .= "• Recibirás notificaciones cuando esté listo\n";
        $mensaje .= "• Puedes contactarnos en cualquier momento\n\n";
        
        $mensaje .= "¡Prepárate para verte espectacular! ✨\n\n";
        $mensaje .= "💖 Gracias por elegirnos.\n\n";
        $mensaje .= "---\n";
        $mensaje .= "*Modas Boom*\n";
        $mensaje .= "📞 +591 76720864\n";
        $mensaje .= "🕒 Horarios: Lun-Sáb 9:00-18:00";

        return $mensaje;
    }

    /**
     * Enviar notificación genérica por cambio de estado
     */
    public function enviarNotificacionEstado(Pedido $pedido, string $estado, int $porcentaje = null, array $camposCambiados = []): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return ['success' => false, 'message' => 'El cliente no tiene teléfono registrado.'];
            }

            $mensaje = "🔔 *Actualización de estado de pedido* 🔔\n\n";
            $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
            $mensaje .= "El pedido #{$pedido->id_pedido} cambió a estado: *{$estado}*.\n";

            if (!is_null($porcentaje)) {
                $mensaje .= "Progreso: {$porcentaje}%\n";
            }

            $mensaje .= "\n📋 Detalles: \n";

            // Sólo incluir campos si no se pasó $camposCambiados (compatibilidad) o si están dentro de los campos cambiados
            if (empty($camposCambiados) || isset($camposCambiados['total'])) {
                $mensaje .= "• Total: {$pedido->total_formateado}\n";
            }

            if (empty($camposCambiados) || isset($camposCambiados['fecha_entrega_programada'])) {
                // Mostrar la fecha de entrega programada solo cuando tiene sentido
                if ($pedido->fecha_entrega_programada) {
                    $mensaje .= "• Fecha de entrega estimada: " . $pedido->fecha_entrega_programada->setTimezone('America/La_Paz')->format('d/m/Y') . "\n";
                }
            }

            $mensaje .= "\n---\n*Modas Boom*\n📞 +591 76720864";

            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            $resultado = $this->enviarViaProxy($telefono, $mensaje);
            if ($resultado['success']) {
                Log::info("Notificación de estado enviada", ['pedido_id' => $pedido->id_pedido, 'estado' => $estado, 'campos_cambiados' => array_keys($camposCambiados)]);
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de estado', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Notificación cuando se programa o se reprograma la entrega
     */
    public function enviarNotificacionEntregaProgramada(Pedido $pedido, ?Carbon $fechaAnterior = null, Carbon $nuevaFecha = null, string $motivo = null): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return ['success' => false, 'message' => 'El cliente no tiene teléfono registrado.'];
            }

            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            $mensaje = "📅 *Programación de entrega* 📅\n\n";
            $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
            if ($fechaAnterior) {
                $mensaje .= "La entrega del pedido #{$pedido->id_pedido} fue reprogramada.\n";
                $mensaje .= "• Fecha anterior: " . $fechaAnterior->setTimezone('America/La_Paz')->format('d/m/Y') . "\n";
                $mensaje .= "• Nueva fecha: " . $nuevaFecha->setTimezone('America/La_Paz')->format('d/m/Y') . "\n";
                if ($motivo) $mensaje .= "• Motivo: {$motivo}\n";
            } else {
                $mensaje .= "Se ha programado la entrega del pedido #{$pedido->id_pedido}.\n";
                $mensaje .= "• Fecha programada: " . ($nuevaFecha ? $nuevaFecha->setTimezone('America/La_Paz')->format('d/m/Y') : 'Sin fecha') . "\n";
            }

            $mensaje .= "\n---\n*Modas Boom*\n📞 +591 76720864";

            $resultado = $this->enviarViaProxy($telefono, $mensaje);
            if ($resultado['success']) {
                Log::info('Notificación entrega programada enviada', ['pedido_id' => $pedido->id_pedido]);
            }
            return $resultado;
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de entrega programada', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Notificación cuando se registra devolución
     */
    public function enviarNotificacionDevolucion(Pedido $pedido, Prenda $prenda, int $cantidadUnidades, string $motivo = null): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return ['success' => false, 'message' => 'El cliente no tiene teléfono registrado.'];
            }

            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            $mensaje = "↩️ *Devolución registrada* ↩️\n\n";
            $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
            $mensaje .= "Se ha registrado una devolución en tu pedido #{$pedido->id_pedido}.\n";
            $mensaje .= "• Prenda: {$prenda->nombre}\n";
            $mensaje .= "• Cantidad: {$cantidadUnidades} unidades\n";
            if ($motivo) $mensaje .= "• Motivo: {$motivo}\n";
            $mensaje .= "\n---\n*Modas Boom*\n📞 +591 76720864";

            $resultado = $this->enviarViaProxy($telefono, $mensaje);
            if ($resultado['success']) {
                Log::info('Notificación de devolución enviada', ['pedido_id' => $pedido->id_pedido, 'prenda' => $prenda->nombre]);
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Error enviando notificación de devolución', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Preparar mensaje de recepción confirmada
     */
    private function prepararMensajeRecepcionConfirmada(Pedido $pedido): string
    {
        return $this->prepararMensajeEntregado($pedido);
    }

    /**
     * Formatear número de teléfono
     */
    private function formatearTelefono(string $telefono): string
    {
        // Limpiar el número
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);
        
        // Si no empieza con +, agregar código de Bolivia
        if (!str_starts_with($telefono, '+')) {
            $telefono = '+591' . ltrim($telefono, '0');
        }
        
        return $telefono;
    }

    /**
     * Simular envío de WhatsApp (para desarrollo)
     */
    private function simularEnvioWhatsApp(string $telefono, string $mensaje): array
    {
        // En desarrollo, simular el envío
        Log::info("WhatsApp simulado enviado", [
            'telefono' => $telefono,
            'mensaje' => substr($mensaje, 0, 100) . '...'
        ]);

        return [
            'success' => true,
            'message' => 'WhatsApp enviado exitosamente (simulado)',
            'telefono' => $telefono
        ];
    }

    /**
     * Send via the Notifications proxy (Baileys/Twilio/other) configured in NOTIFICATIONS_URL_BASE.
     * Falls back to the local simulator if NOTIFICATIONS_URL_BASE is not set.
     * 
     * Implements Circuit Breaker pattern with 2-second timeout and graceful failure handling.
     * The main application flow continues even if notifications fail.
     */
    private function enviarViaProxy(string $telefono, string $mensaje): array
    {
        // If no base configured, fallback to simulator
        if (!$this->notificationsEnabled || empty($this->notificationsBaseUrl)) {
            return $this->simularEnvioWhatsApp($telefono, $mensaje);
        }

        // Normalize the 'to' value for the proxy: remove '+' and non-digit chars
        $to = preg_replace('/[^0-9]/', '', $telefono);

        $url = rtrim($this->notificationsBaseUrl, '\/') . '/send';
        $payload = ['to' => $to, 'message' => $mensaje];

        try {
            // Circuit Breaker: 2-second timeout with 1 retry
            $client = Http::timeout(2)
                ->connectTimeout(2)
                ->retry(1, 100); // 1 retry with 100ms delay
            
            if ($this->notificationsApiKey) {
                $client = $client->withHeaders(['X-API-KEY' => $this->notificationsApiKey]);
            }
            
            $res = $client->post($url, $payload);

            // Check if response failed (4xx, 5xx)
            if ($res->failed()) {
                Log::warning('WhatsAppService: Notifications proxy returned error status', [
                    'url' => $url,
                    'status' => $res->status(),
                    'telefono' => $telefono
                ]);
                
                // Fail silently - don't block main flow
                return [
                    'success' => false,
                    'message' => 'Servicio de notificaciones no disponible temporalmente, pero el proceso continuó',
                    'status' => $res->status()
                ];
            }

            // Try to parse result
            $json = null;
            try { 
                $json = $res->json(); 
            } catch (\Throwable $t) { 
                $json = null; 
            }

            // If the upstream returned a JSON response with success, use it
            if (is_array($json) || is_object($json)) {
                $data = (array)$json;
                if (isset($data['error']) && $data['error']) {
                    Log::warning('WhatsAppService: Notifications proxy responded with error', [
                        'url' => $url,
                        'telefono' => $telefono,
                        'response' => $data
                    ]);
                    
                    // Fail silently
                    return [
                        'success' => false,
                        'message' => 'Servicio de notificaciones no disponible temporalmente, pero el proceso continuó',
                        'details' => $data
                    ];
                }
                return [
                    'success' => true,
                    'message' => $data['message'] ?? 'Notificación enviada correctamente',
                    'response' => $data
                ];
            }

            // Otherwise, if raw string or non-json response, consider success when HTTP OK
            if ($res->successful()) {
                return [
                    'success' => true,
                    'message' => 'Notificación enviada correctamente (proxy)'
                ];
            }

            // Unexpected response format
            Log::warning('WhatsAppService: Unexpected response format from notifications proxy', [
                'url' => $url,
                'status' => $res->status()
            ]);
            
            return [
                'success' => false,
                'message' => 'Servicio de notificaciones no disponible temporalmente, pero el proceso continuó',
                'status' => $res->status()
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Connection timeout or refused - fail silently
            Log::error('WhatsAppService: Connection failed to notifications proxy', [
                'url' => $url,
                'telefono' => $telefono,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Servicio de notificaciones no disponible temporalmente, pero el proceso continuó',
                'reason' => 'connection_timeout'
            ];
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            // Request exception - fail silently
            Log::error('WhatsAppService: Request exception to notifications proxy', [
                'url' => $url,
                'telefono' => $telefono,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Servicio de notificaciones no disponible temporalmente, pero el proceso continuó',
                'reason' => 'request_failed'
            ];
            
        } catch (\Throwable $e) {
            // Unexpected error - fail silently
            Log::error('WhatsAppService: Unexpected error sending notification', [
                'url' => $url,
                'telefono' => $telefono,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Servicio de notificaciones no disponible temporalmente, pero el proceso continuó',
                'reason' => 'unexpected_error'
            ];
        }
    }

    /**
     * Obtener estadísticas de notificaciones
     */
    public function obtenerEstadisticas(): array
    {
        $totalConfirmados = Pedido::where('recepcion_confirmada', true)->count();
        $totalNotificacionesEnviadas = Pedido::where('notificacion_whatsapp_enviada', true)->count();
        $pendientesNotificacion = Pedido::where('recepcion_confirmada', true)
                                       ->where('notificacion_whatsapp_enviada', false)
                                       ->count();

        // Estadísticas de notificaciones automáticas
        $notificacionesAutomaticas = \App\Models\Bitacora::where('modulo', 'NOTIFICACIONES_AUTOMATICAS')
                                                         ->where('accion', 'CREATE')
                                                         ->count();

        // Estadísticas de confirmaciones de pedido
        $confirmacionesPedido = \App\Models\Bitacora::where('modulo', 'CONFIRMACIONES_PEDIDO')
                                                    ->where('accion', 'CREATE')
                                                    ->count();

        return [
            'total_confirmados' => $totalConfirmados,
            'total_notificaciones_enviadas' => $totalNotificacionesEnviadas,
            'pendientes_notificacion' => $pendientesNotificacion,
            'notificaciones_automaticas' => $notificacionesAutomaticas,
            'confirmaciones_pedido' => $confirmacionesPedido,
            'tasa_envio' => $totalConfirmados > 0 ? round(($totalNotificacionesEnviadas / $totalConfirmados) * 100, 2) : 0
        ];
    }
}