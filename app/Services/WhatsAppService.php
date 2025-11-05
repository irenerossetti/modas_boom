<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected $accountSid;
    protected $authToken;
    protected $whatsappFrom;

    public function __construct()
    {
        $this->accountSid = config('services.twilio.sid');
        $this->authToken = config('services.twilio.token');
        $this->whatsappFrom = config('services.twilio.whatsapp_from');
    }

    /**
     * Enviar notificación cuando el pedido está terminado
     */
    public function enviarNotificacionTerminado(Pedido $pedido): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene número de teléfono registrado.'
                ];
            }

            $mensaje = $this->prepararMensajeTerminado($pedido);
            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            
            $resultado = $this->simularEnvioWhatsApp($telefono, $mensaje);
            
            if ($resultado['success']) {
                Log::info("Notificación 'Terminado' enviada", [
                    'pedido_id' => $pedido->id_pedido,
                    'cliente' => $pedido->cliente->nombre,
                    'telefono' => $telefono
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
    public function enviarNotificacionEntregado(Pedido $pedido): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->telefono) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene número de teléfono registrado.'
                ];
            }

            $mensaje = $this->prepararMensajeEntregado($pedido);
            $telefono = $this->formatearTelefono($pedido->cliente->telefono);
            
            $resultado = $this->simularEnvioWhatsApp($telefono, $mensaje);
            
            if ($resultado['success']) {
                Log::info("Notificación 'Entregado' enviada", [
                    'pedido_id' => $pedido->id_pedido,
                    'cliente' => $pedido->cliente->nombre,
                    'telefono' => $telefono
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
            
            $resultado = $this->simularEnvioWhatsApp($telefono, $mensaje);
            
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
            
            $resultado = $this->simularEnvioWhatsApp($telefono, $mensaje);
            
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
    private function prepararMensajeTerminado(Pedido $pedido): string
    {
        $mensaje = "🎉 *¡Tu pedido está listo!* 🎉\n\n";
        $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
        $mensaje .= "Te tenemos excelentes noticias. Tu pedido #{$pedido->id_pedido} está completamente terminado y se ve increíble.\n\n";
        $mensaje .= "📋 *Detalles:*\n";
        $mensaje .= "• Pedido: #{$pedido->id_pedido}\n";
        $mensaje .= "• Total: {$pedido->total_formateado}\n";
        $mensaje .= "• Estado: ✅ Terminado\n\n";
        $mensaje .= "🏪 *¿Cómo recoger tu pedido?*\n";
        $mensaje .= "• Puedes pasar cuando gustes\n";
        $mensaje .= "• Horarios: Lun-Sáb 9:00-18:00\n";
        $mensaje .= "• También podemos coordinar entrega\n\n";
        $mensaje .= "¡Estamos seguros de que te va a encantar! 💫\n\n";
        $mensaje .= "---\n";
        $mensaje .= "*Modas Boom*\n";
        $mensaje .= "📞 +591 70059928";

        return $mensaje;
    }

    /**
     * Preparar mensaje para pedido entregado
     */
    private function prepararMensajeEntregado(Pedido $pedido): string
    {
        $mensaje = "🚚 *¡Pedido entregado exitosamente!* 🎉\n\n";
        $mensaje .= "Hola {$pedido->cliente->nombre},\n\n";
        $mensaje .= "Confirmamos que tu pedido #{$pedido->id_pedido} ha sido entregado exitosamente.\n\n";
        $mensaje .= "📋 *Detalles de la entrega:*\n";
        $mensaje .= "• Pedido: #{$pedido->id_pedido}\n";
        $mensaje .= "• Total: {$pedido->total_formateado}\n";
        $mensaje .= "• Estado: ✅ Entregado\n";
        $mensaje .= "• Fecha: " . now('America/La_Paz')->format('d/m/Y H:i') . "\n\n";
        
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
        $mensaje .= "📞 +591 70059928";

        return $mensaje;
    }

    /**
     * Preparar mensaje de confirmación de pedido
     */
    private function prepararMensajeConfirmacionPedido(Pedido $pedido): string
    {
        $mensaje = "📋 *CONFIRMACIÓN DE PEDIDO* 📋\n\n";
        $mensaje .= "¡Hola {$pedido->cliente->nombre}! 👋\n\n";
        $mensaje .= "✅ Tu pedido ya está en nuestras manos y nuestro equipo ya está trabajando para crear algo increíble para ti.\n\n";
        $mensaje .= "📋 *Detalles del pedido:*\n";
        $mensaje .= "• Número: #{$pedido->id_pedido}\n";
        $mensaje .= "• Total: {$pedido->total_formateado}\n";
        $mensaje .= "• Fecha: " . $pedido->created_at->setTimezone('America/La_Paz')->format('d/m/Y H:i') . "\n";
        
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
        $mensaje .= "📞 +591 70059928\n";
        $mensaje .= "🕒 Horarios: Lun-Sáb 9:00-18:00";

        return $mensaje;
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