<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudReembolso extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_reembolso';

    protected $fillable = [
        'pago_id',
        'pedido_id', 
        'tipo_reembolso',
        'motivo_detallado',
        'beneficiario_nombre',
        'beneficiario_ci',
        'beneficiario_telefono',
        'beneficiario_email',
        'metodo_reembolso',
        'banco',
        'numero_cuenta',
        'monto',
        'estado',
        'solicitado_por',
        'procesado_por',
        'fecha_procesado',
        'notas_procesamiento'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_procesado' => 'datetime',
    ];

    // Relaciones
    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id', 'id_pedido');
    }

    public function solicitadoPor()
    {
        return $this->belongsTo(User::class, 'solicitado_por', 'id_usuario');
    }

    public function procesadoPor()
    {
        return $this->belongsTo(User::class, 'procesado_por', 'id_usuario');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeProcesados($query)
    {
        return $query->where('estado', 'procesado');
    }

    // Métodos de utilidad
    public function marcarComoProcesado($procesadoPor, $notas = null)
    {
        $this->update([
            'estado' => 'procesado',
            'procesado_por' => $procesadoPor,
            'fecha_procesado' => now(),
            'notas_procesamiento' => $notas
        ]);
    }

    public function getTipoReembolsoFormateadoAttribute()
    {
        $tipos = [
            'error_sistema' => 'Error del Sistema',
            'pedido_cancelado' => 'Pedido Cancelado',
            'solicitud_cliente' => 'Solicitud del Cliente'
        ];
        
        return $tipos[$this->tipo_reembolso] ?? $this->tipo_reembolso;
    }

    public function getEstadoColorAttribute()
    {
        $colores = [
            'pendiente' => 'bg-yellow-100 text-yellow-800',
            'procesado' => 'bg-green-100 text-green-800',
            'rechazado' => 'bg-red-100 text-red-800'
        ];
        
        return $colores[$this->estado] ?? 'bg-gray-100 text-gray-800';
    }
}