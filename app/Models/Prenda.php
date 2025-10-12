<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prenda extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'categoria',
        'imagen',
        'colores',
        'tallas',
        'activo',
        'stock'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'colores' => 'array',
        'tallas' => 'array',
        'activo' => 'boolean',
        'stock' => 'integer'
    ];

    /**
     * Scope para prendas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeByCategoria($query, $categoria)
    {
        if ($categoria) {
            return $query->where('categoria', $categoria);
        }
        return $query;
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeBuscar($query, $busqueda)
    {
        if ($busqueda) {
            return $query->where('nombre', 'like', '%' . $busqueda . '%')
                        ->orWhere('descripcion', 'like', '%' . $busqueda . '%');
        }
        return $query;
    }

    /**
     * Obtener la URL completa de la imagen
     */
    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset($this->imagen) : asset('images/default-prenda.jpg');
    }

    /**
     * Formatear el precio como moneda
     */
    public function getPrecioFormateadoAttribute()
    {
        return 'Bs. ' . number_format($this->precio, 2);
    }

    /**
     * Relación con los Pedidos (muchos a muchos)
     */
    public function pedidos(): BelongsToMany
    {
        return $this->belongsToMany(Pedido::class, 'pedido_prenda', 'prenda_id', 'pedido_id')
                    ->withPivot(['cantidad', 'precio_unitario', 'talla', 'color', 'observaciones'])
                    ->withTimestamps();
    }

    /**
     * Verificar si hay stock disponible
     */
    public function tieneStock($cantidad = 1): bool
    {
        return $this->stock >= $cantidad;
    }

    /**
     * Descontar stock
     */
    public function descontarStock($cantidad): bool
    {
        if ($this->tieneStock($cantidad)) {
            $this->decrement('stock', $cantidad);
            return true;
        }
        return false;
    }

    /**
     * Restaurar stock (para cancelaciones)
     */
    public function restaurarStock($cantidad): void
    {
        $this->increment('stock', $cantidad);
    }

    /**
     * Obtener categorías disponibles
     */
    public static function getCategorias()
    {
        return self::select('categoria')
            ->distinct()
            ->where('activo', true)
            ->orderBy('categoria')
            ->pluck('categoria');
    }
}
