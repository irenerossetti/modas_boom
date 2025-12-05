<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cliente;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReactivarClientesInactivos extends Command
{
    protected $signature = 'marketing:reactivar-inactivos 
                            {--dias-inactividad=90 : Días sin pedidos para considerar inactivo}
                            {--dias-entre-avisos=30 : Días mínimos entre avisos}
                            {--dry-run : Simular sin enviar mensajes}';

    protected $description = 'Envía catálogo PDF a clientes inactivos para reactivarlos';

    protected $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        parent::__construct();
        $this->whatsappService = $whatsappService;
    }

    public function handle()
    {
        $diasInactividad = $this->option('dias-inactividad');
        $diasEntreAvisos = $this->option('dias-entre-avisos');
        $dryRun = $this->option('dry-run');

        $this->info("🔍 Buscando clientes inactivos (>{$diasInactividad} días sin pedidos)...");

        // Obtener clientes para reactivar
        $clientes = Cliente::paraReactivar($diasInactividad, $diasEntreAvisos)->get();

        if ($clientes->isEmpty()) {
            $this->info('✅ No hay clientes inactivos que necesiten reactivación.');
            return 0;
        }

        $this->info("📋 Encontrados {$clientes->count()} clientes para reactivar:");
        
        $bar = $this->output->createProgressBar($clientes->count());
        $bar->start();

        $enviados = 0;
        $errores = 0;

        foreach ($clientes as $cliente) {
            try {
                if ($dryRun) {
                    $this->newLine();
                    $this->line("  [DRY-RUN] {$cliente->nombre} {$cliente->apellido} ({$cliente->telefono})");
                    $enviados++;
                } else {
                    // Generar PDF del catálogo
                    $pdfPath = $this->generarCatalogoPDF();

                    // Enviar mensaje con PDF
                    $resultado = $this->enviarMensajeReactivacion($cliente, $pdfPath);

                    if ($resultado['success']) {
                        // Actualizar fecha de último aviso
                        $cliente->update(['ultimo_aviso_inactividad' => now()]);
                        $enviados++;
                        
                        Log::info("Cliente reactivado: {$cliente->nombre} {$cliente->apellido}", [
                            'cliente_id' => $cliente->id,
                            'telefono' => $cliente->telefono
                        ]);
                    } else {
                        $errores++;
                        Log::warning("Error al reactivar cliente: {$cliente->nombre}", [
                            'error' => $resultado['message']
                        ]);
                    }

                    // Limpiar archivo temporal
                    if (file_exists($pdfPath)) {
                        unlink($pdfPath);
                    }

                    // Pausa para no saturar el servicio
                    sleep(2);
                }

                $bar->advance();
            } catch (\Exception $e) {
                $errores++;
                Log::error("Excepción al reactivar cliente {$cliente->id}: " . $e->getMessage());
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Resumen
        $this->info("✅ Proceso completado:");
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Clientes encontrados', $clientes->count()],
                ['Mensajes enviados', $enviados],
                ['Errores', $errores],
            ]
        );

        return 0;
    }

    /**
     * Genera el PDF del catálogo
     */
    protected function generarCatalogoPDF(): string
    {
        $this->line('  📄 Generando catálogo PDF...');

        // Obtener productos activos
        $productos = \App\Models\Prenda::where('activo', true)
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        // Generar PDF
        $pdf = Pdf::loadView('catalogo.pdf', [
            'productos' => $productos,
            'fecha_generacion' => now()->format('d/m/Y H:i'),
        ]);

        // Guardar temporalmente
        $filename = 'catalogo_' . now()->format('YmdHis') . '.pdf';
        $path = storage_path('app/temp/' . $filename);

        // Crear directorio si no existe
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $pdf->save($path);

        return $path;
    }

    /**
     * Envía mensaje de reactivación con catálogo
     */
    protected function enviarMensajeReactivacion(Cliente $cliente, string $pdfPath): array
    {
        $mensaje = "¡Hola {$cliente->nombre}! 👋\n\n";
        $mensaje .= "Te extrañamos en *Modas Boom* 💕\n\n";
        $mensaje .= "Han pasado algunos meses desde tu última compra y queremos compartir contigo nuestro catálogo actualizado con nuevos diseños y promociones especiales.\n\n";
        $mensaje .= "📋 Te enviamos nuestro catálogo completo para que veas todas las novedades.\n\n";
        $mensaje .= "¿Te gustaría hacer un nuevo pedido? Estamos aquí para ayudarte. 😊";

        // Enviar archivo usando el servicio de WhatsApp
        return $this->whatsappService->enviarArchivo(
            $cliente->telefono,
            $pdfPath,
            $mensaje,
            'Catalogo_Modas_Boom.pdf'
        );
    }
}
