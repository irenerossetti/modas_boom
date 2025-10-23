<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pedido extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     */
    protected $table = 'pedido';

    /**
     * La clave primaria de la tabla.
     */
    protected $primaryKey = 'id_pedido';

    /**
     * Indica si el modelo debe tener timestamps (created_at y updated_at).
     */
    public $timestamps = true;

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'id_cliente',
        'estado',
        'total'
    ];

    /**
     * Casting de atributos
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id_pedido';
    }

    /**
     * Estados disponibles para pedidos
     */
    public static function getEstadosDisponibles(): array
    {
        return [
            'En proceso' => 'En proceso',
            'Asignado' => 'Asignado',
            'En producción' => 'En producción',
            'Terminado' => 'Terminado',
            'Entregado' => 'Entregado',
            'Cancelado' => 'Cancelado'
        ];
    }

    /**
     * Relación con el Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    /**
     * Relación con las Prendas (muchos a muchos)
     */
    public function prendas(): BelongsToMany
    {
        return $this->belongsToMany(Prenda::class, 'pedido_prenda', 'pedido_id', 'prenda_id')
                    ->withPivot(['cantidad', 'precio_unitario', 'talla', 'color', 'observaciones'])
                    ->withTimestamps();
    }

    /**
     * Obtener el color CSS para el estado actual
     */
    public function getEstadoColorAttribute(): string
    {
        $colores = [
            'En proceso' => 'bg-blue-100 text-blue-800',
            'Asignado' => 'bg-yellow-100 text-yellow-800',
            'En producción' => 'bg-orange-100 text-orange-800',
            'Terminado' => 'bg-green-100 text-green-800',
            'Entregado' => 'bg-purple-100 text-purple-800',
            'Cancelado' => 'bg-red-100 text-red-800'
        ];
        
        return $colores[$this->estado] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Obtener el icono para el estado actual
     */
    public function getEstadoIconoAttribute(): string
    {
        $iconos = [
            'En proceso' => 'fas fa-clock',
            'Asignado' => 'fas fa-user-check',
            'En producción' => 'fas fa-cogs',
            'Terminado' => 'fas fa-check-circle',
            'Entregado' => 'fas fa-shipping-fast',
            'Cancelado' => 'fas fa-times-circle'
        ];
        
        return $iconos[$this->estado] ?? 'fas fa-question-circle';
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeByEstado($query, $estado)
    {
        if ($estado) {
            return $query->where('estado', $estado);
        }
        return $query;
    }

    /**
     * Scope para filtrar por cliente
     */
    public function scopeByCliente($query, $clienteId)
    {
        if ($clienteId) {
            return $query->where('id_cliente', $clienteId);
        }
        return $query;
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeByFechas($query, $fechaDesde, $fechaHasta)
    {
        if ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }
        return $query;
    }

    /**
     * Scope para buscar por número de pedido o cliente
     */
    public function scopeBuscar($query, $busqueda)
    {
        if ($busqueda) {
            return $query->where(function ($q) use ($busqueda) {
                $q->where('id_pedido', 'like', '%' . $busqueda . '%')
                  ->orWhereHas('cliente', function ($clienteQuery) use ($busqueda) {
                      $clienteQuery->where('nombre', 'like', '%' . $busqueda . '%')
                                   ->orWhere('apellido', 'like', '%' . $busqueda . '%')
                                   ->orWhere('ci_nit', 'like', '%' . $busqueda . '%');
                  });
            });
        }
        return $query;
    }

    /**
     * Verificar si el pedido puede ser editado
     */
    public function puedeSerEditado(): bool
    {
        return !in_array($this->estado, ['Entregado', 'Cancelado']);
    }

    /**
     * Verificar si el pedido puede ser cancelado
     */
    public function puedeSerCancelado(): bool
    {
        return !in_array($this->estado, ['Entregado', 'Cancelado']);
    }

    /**
     * Verificar si el pedido puede ser asignado
     */
    public function puedeSerAsignado(): bool
    {
        return in_array($this->estado, ['En proceso', 'Asignado']);
    }

    /**
     * Obtener el nombre completo del cliente
     */
    public function getNombreCompletoClienteAttribute(): string
    {
        return $this->cliente ? 
            $this->cliente->nombre . ' ' . $this->cliente->apellido : 
            'Cliente no encontrado';
    }

    /**
     * Formatear el total como moneda
     */
    public function getTotalFormateadoAttribute(): string
    {
        return $this->total ? 'Bs. ' . number_format($this->total, 2) : 'No especificado';
    }
}