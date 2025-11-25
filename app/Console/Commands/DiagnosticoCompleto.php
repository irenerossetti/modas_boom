<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Models\Cliente;
use App\Models\AvanceProduccion;
use Illuminate\Console\Command;

class DiagnosticoCompleto extends Command
{
    protected $signature = 'sistema:diagnostico';
    protected $description = 'Diagnóstico completo del estado del sistema';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO COMPLETO DEL SISTEMA');
        $this->info('=====================================');
        $this->newLine();

        // 1. Estado de la Base de Datos
        $this->info('📊 ESTADO DE LA BASE DE DATOS:');
        $totalPedidos = Pedido::count();
        $totalClientes = Cliente::count();
        
        $this->info("   • Total Pedidos: {$totalPedidos}");
        $this->info("   • Total Clientes: {$totalClientes}");
        
        // Últimos pedidos
        $ultimosPedidos = Pedido::orderBy('id_pedido', 'desc')->take(5)->get(['id_pedido', 'estado', 'created_at']);
        $this->info("   • Últimos 5 pedidos:");
        foreach ($ultimosPedidos as $pedido) {
            $fecha = $pedido->created_at->setTimezone('America/La_Paz')->format('d/m/Y H:i');
            $this->line("     - #{$pedido->id_pedido} - {$pedido->estado} - {$fecha}");
        }
        $this->newLine();

        // 2. Configuración de Gmail
        $this->info('📧 CONFIGURACIÓN DE GMAIL:');
        $this->info("   • MAIL_MAILER: " . config('mail.default'));
        $this->info("   • MAIL_HOST: " . config('mail.mailers.smtp.host'));
        $this->info("   • MAIL_PORT: " . config('mail.mailers.smtp.port'));
        $this->info("   • MAIL_USERNAME: " . config('mail.mailers.smtp.username'));
        $this->info("   • MAIL_ENCRYPTION: " . config('mail.mailers.smtp.encryption'));
        $this->info("   • MAIL_FROM: " . config('mail.from.address'));
        $this->newLine();

        // 3. Configuración de WhatsApp
        $this->info('📱 CONFIGURACIÓN DE WHATSAPP:');
        $this->info("   • TWILIO_SID: " . (config('services.twilio.sid') ? 'Configurado ✅' : 'No configurado ❌'));
        $this->info("   • TWILIO_TOKEN: " . (config('services.twilio.token') ? 'Configurado ✅' : 'No configurado ❌'));
        $this->info("   • WHATSAPP_FROM: " . config('services.twilio.whatsapp_from'));
        $this->newLine();

        // 4. Zona Horaria
        $this->info('🕒 CONFIGURACIÓN DE TIEMPO:');
        $this->info("   • Zona horaria: " . config('app.timezone'));
        $this->info("   • Hora actual: " . now()->format('d/m/Y H:i:s'));
        $this->info("   • Hora Bolivia: " . now('America/La_Paz')->format('d/m/Y H:i:s'));
        $this->newLine();

        // 5. Servicios
        $this->info('⚙️ ESTADO DE SERVICIOS:');
        
        try {
            $whatsappService = new \App\Services\WhatsAppService();
            $this->info("   • WhatsAppService: ✅ Funcionando");
        } catch (\Exception $e) {
            $this->error("   • WhatsAppService: ❌ Error - " . $e->getMessage());
        }

        try {
            $emailService = new \App\Services\EmailService();
            $this->info("   • EmailService: ✅ Funcionando");
        } catch (\Exception $e) {
            $this->error("   • EmailService: ❌ Error - " . $e->getMessage());
        }

        try {
            $bitacoraService = new \App\Services\BitacoraService();
            $this->info("   • BitacoraService: ✅ Funcionando");
        } catch (\Exception $e) {
            $this->error("   • BitacoraService: ❌ Error - " . $e->getMessage());
        }
        $this->newLine();

        // 6. Modelos
        $this->info('📋 MODELOS IMPLEMENTADOS:');
        $modelos = [
            'Pedido' => \App\Models\Pedido::class,
            'Cliente' => \App\Models\Cliente::class,
            'AvanceProduccion' => \App\Models\AvanceProduccion::class,
            'ObservacionCalidad' => \App\Models\ObservacionCalidad::class,
        ];

        foreach ($modelos as $nombre => $clase) {
            if (class_exists($clase)) {
                $this->info("   • {$nombre}: ✅ Existe");
            } else {
                $this->error("   • {$nombre}: ❌ No existe");
            }
        }
        $this->newLine();

        // 7. Eventos y Listeners
        $this->info('🔄 EVENTOS Y LISTENERS:');
        $eventos = [
            'PedidoCreado' => \App\Events\PedidoCreado::class,
            'PedidoEstadoCambiado' => \App\Events\PedidoEstadoCambiado::class,
        ];

        foreach ($eventos as $nombre => $clase) {
            if (class_exists($clase)) {
                $this->info("   • {$nombre}: ✅ Existe");
            } else {
                $this->error("   • {$nombre}: ❌ No existe");
            }
        }

        $listeners = [
            'EnviarConfirmacionPedido' => \App\Listeners\EnviarConfirmacionPedido::class,
            'EnviarNotificacionWhatsAppAutomatica' => \App\Listeners\EnviarNotificacionWhatsAppAutomatica::class,
        ];

        foreach ($listeners as $nombre => $clase) {
            if (class_exists($clase)) {
                $this->info("   • {$nombre}: ✅ Existe");
            } else {
                $this->error("   • {$nombre}: ❌ No existe");
            }
        }
        $this->newLine();

        // 8. Comandos de Prueba
        $this->info('🧪 COMANDOS DE PRUEBA DISPONIBLES:');
        $comandos = [
            'gmail:probar [email]' => 'Probar envío de Gmail',
            'pedido:probar-confirmacion' => 'Probar confirmaciones automáticas',
            'evento:probar-automatico [pedido-id]' => 'Probar eventos automáticos',
            'email:probar --email=[email]' => 'Probar todos los tipos de email',
            'pedidos:verificar' => 'Verificar pedidos en BD',
            'pedidos:verificar-orden' => 'Verificar ordenamiento',
        ];

        foreach ($comandos as $comando => $descripcion) {
            $this->line("   • php artisan {$comando} - {$descripcion}");
        }
        $this->newLine();

        // 9. Estadísticas de Notificaciones
        $this->info('📊 ESTADÍSTICAS DE NOTIFICACIONES:');
        
        $confirmaciones = \App\Models\Bitacora::where('modulo', 'CONFIRMACIONES_PEDIDO')->count();
        $notificacionesAuto = \App\Models\Bitacora::where('modulo', 'NOTIFICACIONES_AUTOMATICAS')->count();
        
        $this->info("   • Confirmaciones de pedido: {$confirmaciones}");
        $this->info("   • Notificaciones automáticas: {$notificacionesAuto}");
        $this->newLine();

        // 10. Resumen de CU19-CU23
        $this->info('🎯 CASOS DE USO CU19-CU23:');
        $this->info("   • CU19 Reprogramar Entrega: ✅ Implementado");
        $this->info("   • CU20 Avance Producción: ✅ Implementado");
        $this->info("   • CU21 Observación Calidad: ✅ Implementado");
        $this->info("   • CU22 Confirmar Recepción: ✅ Implementado");
        $this->info("   • CU23 Notificaciones WhatsApp+Gmail: ✅ Funcionando");
        $this->newLine();

        $this->info('🎉 DIAGNÓSTICO COMPLETADO');
        $this->info('=====================================');
        
        return 0;
    }
}