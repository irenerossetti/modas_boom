<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservacionCalidad extends Model
{
    use HasFactory;

    protected $table = 'observaciones_calidad';

    protected $fillable = [
        'id_pedido',
        'tipo_observacion',
        'area_afectada',
        'descripcion',
        'prioridad',
        'estado',
        'accion_correctiva',
        'registrado_por',
        'corregido_por',
        'fecha_correccion'
    ];

    protected $casts = [
        'fecha_correccion' => 'datetime',
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
     * Relación con Usuario que corrigió
     */
    public function corregidoPor()
    {
        return $this->belongsTo(User::class, 'corregido_por', 'id_usuario');
    }

    /**
     * Tipos de observación disponibles
     */
    public static function getTiposObservacion()
    {
        return [
            'Defecto' => 'Defecto',
            'Mejora' => 'Mejora',
            'Aprobado' => 'Aprobado',
            'Rechazado' => 'Rechazado'
        ];
    }

    /**
     * Prioridades disponibles
     */
    public static function getPrioridades()
    {
        return [
            'Baja' => 'Baja',
            'Media' => 'Media',
            'Alta' => 'Alta',
            'Crítica' => 'Crítica'
        ];
    }

    /**
     * Estados disponibles
     */
    public static function getEstados()
    {
        return [
            'Pendiente' => 'Pendiente',
            'En corrección' => 'En corrección',
            'Corregido' => 'Corregido',
            'Cerrado' => 'Cerrado'
        ];
    }
}