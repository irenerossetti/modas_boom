<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Enviar notificación cuando el pedido está terminado
     */
    public function enviarNotificacionTerminado(Pedido $pedido): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->email) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene email registrado.'
                ];
            }

            Mail::to($pedido->cliente->email)
                ->send(new \App\Mail\PedidoTerminadoMail($pedido));

            Log::info("Notificación 'Terminado' enviada por Email", [
                'pedido_id' => $pedido->id_pedido,
                'cliente' => $pedido->cliente->nombre,
                'email' => $pedido->cliente->email
            ]);

            return [
                'success' => true,
                'message' => 'Email de pedido terminado enviado exitosamente',
                'email' => $pedido->cliente->email
            ];

        } catch (\Exception $e) {
            Log::error("Error enviando email 'Terminado'", [
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
            if (!$pedido->cliente || !$pedido->cliente->email) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene email registrado.'
                ];
            }

            Mail::to($pedido->cliente->email)
                ->send(new \App\Mail\PedidoEntregadoMail($pedido));

            Log::info("Notificación 'Entregado' enviada por Email", [
                'pedido_id' => $pedido->id_pedido,
                'cliente' => $pedido->cliente->nombre,
                'email' => $pedido->cliente->email
            ]);

            return [
                'success' => true,
                'message' => 'Email de pedido entregado enviado exitosamente',
                'email' => $pedido->cliente->email
            ];

        } catch (\Exception $e) {
            Log::error("Error enviando email 'Entregado'", [
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
            if (!$pedido->cliente || !$pedido->cliente->email) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene email registrado.'
                ];
            }

            Mail::to($pedido->cliente->email)
                ->send(new \App\Mail\ConfirmacionPedidoMail($pedido));

            Log::info("Confirmación de pedido enviada por Email", [
                'pedido_id' => $pedido->id_pedido,
                'cliente' => $pedido->cliente->nombre,
                'email' => $pedido->cliente->email
            ]);

            return [
                'success' => true,
                'message' => 'Email de confirmación enviado exitosamente',
                'email' => $pedido->cliente->email
            ];

        } catch (\Exception $e) {
            Log::error("Error enviando confirmación de pedido por Email", [
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
     * Enviar confirmación de recepción/entrega
     */
    public function enviarConfirmacionRecepcion(Pedido $pedido): array
    {
        try {
            if (!$pedido->cliente || !$pedido->cliente->email) {
                return [
                    'success' => false,
                    'message' => 'El cliente no tiene email registrado.'
                ];
            }

            Mail::to($pedido->cliente->email)
                ->send(new \App\Mail\PedidoEntregadoMail($pedido));

            Log::info("Confirmación de recepción enviada por Email", [
                'pedido_id' => $pedido->id_pedido,
                'cliente' => $pedido->cliente->nombre,
                'email' => $pedido->cliente->email
            ]);

            return [
                'success' => true,
                'message' => 'Email de confirmación de entrega enviado exitosamente',
                'email' => $pedido->cliente->email
            ];

        } catch (\Exception $e) {
            Log::error("Error enviando confirmación de recepción por Email", [
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
     * Obtener estadísticas de emails
     */
    public function obtenerEstadisticas(): array
    {
        $emailsEnviados = \App\Models\Bitacora::where('modulo', 'NOTIFICACIONES_EMAIL')
                                             ->where('accion', 'CREATE')
                                             ->count();

        $emailsErrores = \App\Models\Bitacora::where('modulo', 'NOTIFICACIONES_EMAIL')
                                            ->where('accion', 'ERROR')
                                            ->count();

        // Estadísticas de confirmaciones de pedido
        $confirmacionesPedido = \App\Models\Bitacora::where('modulo', 'CONFIRMACIONES_PEDIDO')
                                                    ->where('accion', 'CREATE')
                                                    ->where('descripcion', 'like', '%Email%')
                                                    ->count();

        return [
            'emails_enviados' => $emailsEnviados,
            'emails_errores' => $emailsErrores,
            'confirmaciones_pedido' => $confirmacionesPedido,
            'tasa_exito' => $emailsEnviados > 0 ? round((($emailsEnviados - $emailsErrores) / $emailsEnviados) * 100, 2) : 0
        ];
    }

    /**
     * Probar configuración de email
     */
    public function probarConfiguracion(): array
    {
        try {
            // Verificar configuración básica
            $mailer = config('mail.default');
            $host = config('mail.mailers.smtp.host');
            $port = config('mail.mailers.smtp.port');
            $username = config('mail.mailers.smtp.username');

            if (!$mailer || !$host || !$port || !$username) {
                return [
                    'success' => false,
                    'message' => 'Configuración de email incompleta'
                ];
            }

            return [
                'success' => true,
                'message' => 'Configuración de email correcta',
                'config' => [
                    'mailer' => $mailer,
                    'host' => $host,
                    'port' => $port,
                    'username' => $username
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error verificando configuración: ' . $e->getMessage()
            ];
        }
    }
}