<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresupuestoProduccion extends Model
{
    protected $table = 'presupuestos_produccion';

    protected $fillable = [
        'tipo_prenda',
        'tipo_tela',
        'descripcion',
        'costo_tela',
        'costo_cierre',
        'costo_boton',
        'costo_bolsa',
        'costo_hilo',
        'costo_etiqueta_cinta',
        'costo_etiqueta_carton',
        'costo_tallerista',
        'costo_planchado',
        'costo_ayudante',
        'costo_cortador',
        'total_materiales',
        'total_mano_obra',
        'costo_total',
        'id_usuario_registro',
        'id_pedido',
        'estado'
    ];

    protected $casts = [
        'costo_tela' => 'decimal:2',
        'costo_cierre' => 'decimal:2',
        'costo_boton' => 'decimal:2',
        'costo_bolsa' => 'decimal:2',
        'costo_hilo' => 'decimal:2',
        'costo_etiqueta_cinta' => 'decimal:2',
        'costo_etiqueta_carton' => 'decimal:2',
        'costo_tallerista' => 'decimal:2',
        'costo_planchado' => 'decimal:2',
        'costo_ayudante' => 'decimal:2',
        'costo_cortador' => 'decimal:2',
        'total_materiales' => 'decimal:2',
        'total_mano_obra' => 'decimal:2',
        'costo_total' => 'decimal:2',
    ];

    /**
     * Relación con el usuario que registró el presupuesto
     */
    public function usuarioRegistro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_registro', 'id_usuario');
    }

    /**
     * Relación con el pedido (opcional)
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Calcular total de materiales
     */
    public function calcularTotalMateriales(): float
    {
        return $this->costo_tela + $this->costo_cierre + $this->costo_boton + 
               $this->costo_bolsa + $this->costo_hilo + $this->costo_etiqueta_cinta + 
               $this->costo_etiqueta_carton;
    }

    /**
     * Calcular total de mano de obra
     */
    public function calcularTotalManoObra(): float
    {
        return $this->costo_tallerista + $this->costo_planchado + 
               $this->costo_ayudante + $this->costo_cortador;
    }

    /**
     * Calcular costo total
     */
    public function calcularCostoTotal(): float
    {
        return $this->calcularTotalMateriales() + $this->calcularTotalManoObra();
    }

    /**
     * Actualizar totales automáticamente
     */
    public function actualizarTotales(): void
    {
        $this->total_materiales = $this->calcularTotalMateriales();
        $this->total_mano_obra = $this->calcularTotalManoObra();
        $this->costo_total = $this->calcularCostoTotal();
    }

    /**
     * Scope para presupuestos por estado
     */
    public function scopeByEstado($query, $estado)
    {
        if ($estado) {
            return $query->where('estado', $estado);
        }
        return $query;
    }

    /**
     * Scope para presupuestos por tipo de prenda
     */
    public function scopeByTipoPrenda($query, $tipoPrenda)
    {
        if ($tipoPrenda) {
            return $query->where('tipo_prenda', 'ILIKE', '%' . $tipoPrenda . '%');
        }
        return $query;
    }

    /**
     * Accessor para formato de moneda
     */
    public function getCostoTotalFormateadoAttribute(): string
    {
        return 'Bs. ' . number_format($this->costo_total, 2);
    }

    /**
     * Accessor para formato de materiales
     */
    public function getTotalMaterialesFormateadoAttribute(): string
    {
        return 'Bs. ' . number_format($this->total_materiales, 2);
    }

    /**
     * Accessor para formato de mano de obra
     */
    public function getTotalManoObraFormateadoAttribute(): string
    {
        return 'Bs. ' . number_format($this->total_mano_obra, 2);
    }

    /**
     * Verificar si el presupuesto puede ser modificado
     */
    public function puedeSerModificado(): bool
    {
        return $this->estado === 'Borrador';
    }

    /**
     * Verificar si el presupuesto puede ser utilizado
     */
    public function puedeSerUtilizado(): bool
    {
        return in_array($this->estado, ['Aprobado', 'Utilizado']);
    }
}
