<?php

namespace App\Http\Controllers;

use App\Models\SolicitudReembolso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudReembolsoController extends Controller
{
    /**
     * Mostrar lista de solicitudes de reembolso
     */
    public function index(Request $request)
    {
        $query = SolicitudReembolso::with(['pago', 'pedido.cliente', 'solicitadoPor', 'procesadoPor']);
        
        // Filtros
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }
        
        if ($request->metodo_reembolso) {
            $query->where('metodo_reembolso', $request->metodo_reembolso);
        }
        
        $solicitudes = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.solicitudes-reembolso.index', compact('solicitudes'));
    }
    
    /**
     * Mostrar detalles de una solicitud
     */
    public function show($id)
    {
        $solicitud = SolicitudReembolso::with(['pago', 'pedido.cliente', 'solicitadoPor', 'procesadoPor'])->findOrFail($id);
        
        return view('admin.solicitudes-reembolso.show', compact('solicitud'));
    }
    
    /**
     * Marcar solicitud como procesada
     */
    public function marcarProcesada(Request $request, $id)
    {
        $request->validate([
            'notas_procesamiento' => 'nullable|string|max:1000'
        ]);
        
        $solicitud = SolicitudReembolso::findOrFail($id);
        
        if ($solicitud->estado !== 'pendiente') {
            return redirect()->back()->with('error', 'Esta solicitud ya fue procesada.');
        }
        
        $solicitud->marcarComoProcesado(auth()->id(), $request->notas_procesamiento);
        
        return redirect()->back()->with('success', 'Solicitud marcada como procesada exitosamente.');
    }

    /**
     * Marcar reembolso como completado desde la vista de pedido
     */
    public function marcarCompletado($id)
    {
        // Debug básico
        \Log::info('=== INICIO marcarCompletado ===');
        \Log::info('ID recibido: ' . $id);
        \Log::info('Usuario autenticado: ' . (auth()->check() ? auth()->id() : 'NO'));
        \Log::info('Rol del usuario: ' . (auth()->check() ? auth()->user()->id_rol : 'NO'));
        
        try {
            // Buscar el reembolso
            $solicitud = SolicitudReembolso::find($id);
            if (!$solicitud) {
                \Log::error('Reembolso no encontrado con ID: ' . $id);
                return redirect()->back()->with('error', 'Reembolso no encontrado.');
            }
            
            \Log::info('Reembolso encontrado - Estado actual: ' . $solicitud->estado);
            \Log::info('Datos del reembolso: ' . json_encode($solicitud->toArray()));
            
            // Verificar permisos
            if (!auth()->check() || auth()->user()->id_rol !== 1) {
                \Log::warning('Usuario sin permisos - ID: ' . (auth()->check() ? auth()->id() : 'NO_AUTH') . ' - Rol: ' . (auth()->check() ? auth()->user()->id_rol : 'NO_ROL'));
                return redirect()->back()->with('error', 'No tiene permisos para realizar esta acción.');
            }
            
            // Verificar estado
            if ($solicitud->estado !== 'pendiente') {
                \Log::warning('Reembolso no está pendiente - Estado actual: ' . $solicitud->estado);
                return redirect()->back()->with('error', 'Este reembolso ya fue procesado. Estado: ' . $solicitud->estado);
            }
            
            // Intentar actualizar usando SQL directo para debug
            \Log::info('Intentando actualizar con SQL directo...');
            
            $affected = \DB::table('solicitudes_reembolso')
                ->where('id', $id)
                ->update([
                    'estado' => 'procesado',
                    'procesado_por' => auth()->id(),
                    'fecha_procesado' => now(),
                    'notas_procesamiento' => 'Completado manualmente desde vista de pedido',
                    'updated_at' => now()
                ]);
            
            \Log::info('Filas afectadas por SQL directo: ' . $affected);

            // También anular el pago asociado
            if ($affected > 0) {
                // Obtener el ID del pago
                $pagoId = $solicitud->pago_id;
                
                // Anular el pago
                \DB::update(
                    'UPDATE "pago" SET "anulado" = true, "anulado_por" = ?, "anulado_motivo" = ?, "updated_at" = ? WHERE "id" = ?',
                    [auth()->id(), 'Reembolso completado (Solicitud ID: ' . $id . ')', now(), $pagoId]
                );
                
                \Log::info('Pago asociado anulado - ID: ' . $pagoId);
            }
            
            // Verificar el cambio
            $solicitudActualizada = SolicitudReembolso::find($id);
            \Log::info('Estado después de SQL directo: ' . $solicitudActualizada->estado);
            
            if ($solicitudActualizada->estado === 'procesado') {
                \Log::info('¡Actualización exitosa!');
                return redirect()->back()->with('success', 'Reembolso marcado como completado exitosamente.');
            } else {
                \Log::error('La actualización no funcionó - Estado sigue siendo: ' . $solicitudActualizada->estado);
                return redirect()->back()->with('error', 'Error: No se pudo actualizar el estado del reembolso.');
            }
            
        } catch (\Exception $e) {
            \Log::error('ERROR COMPLETO: ' . $e->getMessage());
            \Log::error('TRACE: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        } finally {
            \Log::info('=== FIN marcarCompletado ===');
        }
    }
    /**
     * Cambiar estado de una solicitud de reembolso
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,procesado,rechazado',
            'notas' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $solicitud = SolicitudReembolso::findOrFail($id);
            $estadoAnterior = $solicitud->estado;
            $nuevoEstado = $request->estado;
            
            // Si no hay cambio, retornar
            if ($estadoAnterior === $nuevoEstado) {
                return redirect()->back()->with('info', 'El estado ya es ' . $nuevoEstado);
            }

            // Actualizar solicitud
            $updateData = [
                'estado' => $nuevoEstado,
                'updated_at' => now()
            ];

            if ($nuevoEstado === 'procesado') {
                $updateData['procesado_por'] = auth()->id();
                $updateData['fecha_procesado'] = now();
                $updateData['notas_procesamiento'] = $request->notas ?? 'Procesado manualmente';
            } elseif ($nuevoEstado === 'rechazado') {
                $updateData['procesado_por'] = auth()->id();
                $updateData['fecha_procesado'] = now(); // También guardamos fecha de rechazo
                $updateData['notas_procesamiento'] = $request->notas ?? 'Rechazado manualmente';
            } else {
                // Si vuelve a pendiente
                $updateData['procesado_por'] = null;
                $updateData['fecha_procesado'] = null;
            }

            $solicitud->update($updateData);

            // Manejar el estado del pago asociado
            $pago = $solicitud->pago;
            if ($pago) {
                if ($nuevoEstado === 'procesado') {
                    // Si se procesa, ANULAR el pago
                    if (!$pago->anulado) {
                        DB::table('pago')->where('id', $pago->id)->update([
                            'anulado' => true,
                            'anulado_por' => auth()->id(),
                            'anulado_motivo' => 'Reembolso procesado (ID: ' . $solicitud->id . ')',
                            'updated_at' => now()
                        ]);
                    }
                } else {
                    // Si pasa a pendiente o rechazado, y estaba anulado POR REEMBOLSO, restaurarlo
                    // Verificamos si la razón de anulación contiene "Reembolso" para no restaurar pagos anulados por otras razones
                    if ($pago->anulado && str_contains($pago->anulado_motivo, 'Reembolso')) {
                        DB::table('pago')->where('id', $pago->id)->update([
                            'anulado' => false,
                            'anulado_por' => null,
                            'anulado_motivo' => null,
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Estado del reembolso actualizado a ' . strtoupper($nuevoEstado));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error cambiando estado de reembolso: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error actualizando el estado: ' . $e->getMessage());
        }
    }
}