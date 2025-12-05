<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvanceProduccion extends Model
{
    use HasFactory;

    protected $table = 'avance_produccion';

    protected $fillable = [
        'id_pedido',
        'etapa',
        'porcentaje_avance',
        'descripcion',
        'observaciones',
        'registrado_por',
        'user_id_operario',
        'costo_mano_obra'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con Pedido
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    /**
     * Relación con Usuario que registró
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }

    /**
     * Relación con Usuario operario que realizó el trabajo físico
     */
    public function operario()
    {
        return $this->belongsTo(User::class, 'user_id_operario', 'id_usuario');
    }

    /**
     * Etapas disponibles
     */
    public static function getEtapasDisponibles()
    {
        return [
            'Corte' => 'Corte',
            'Confección' => 'Confección',
            'Acabado' => 'Acabado',
            'Control de Calidad' => 'Control de Calidad'
        ];
    }

    /**
     * Scope para filtrar por pedido
     */
    public function scopeByPedido($query, $pedidoId)
    {
        if ($pedidoId) {
            return $query->where('id_pedido', $pedidoId);
        }
        return $query;
    }

    /**
     * Scope para filtrar por etapa
     */
    public function scopeByEtapa($query, $etapa)
    {
        if ($etapa) {
            return $query->where('etapa', $etapa);
        }
        return $query;
    }
}