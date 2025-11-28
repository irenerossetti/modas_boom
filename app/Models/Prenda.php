<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

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
     * Boot model to normalize 'activo' for Postgres during saving.
     */
    protected static function booted()
    {
        static::saving(function ($model) {
            try {
                if (DB::connection()->getDriverName() === 'pgsql' && array_key_exists('activo', $model->attributes)) {
                    // Use textual boolean literal to avoid driver binding booleans as integers
                    $model->attributes['activo'] = $model->attributes['activo'] ? 'true' : 'false';
                }
            } catch (\Exception $e) {
                // If DB not available (e.g., in some test setups), keep original value
            }
        });
    }

    /**
     * Mutator to normalize the 'activo' attribute before DB operations.
     * Postgres rejects integer literals compared to boolean columns ("activo" = 1).
     * Ensure we always store as text 'true'/'false' so it's castable to boolean in SQL.
     */
    public function setActivoAttribute($value)
    {
        // Normalize to actual boolean so DB stores booleans (or numeric 0/1 for Sqlite).
        if (is_bool($value)) {
            $this->attributes['activo'] = $value;
            return;
        }

        if (is_int($value)) {
            $this->attributes['activo'] = $value === 1;
            return;
        }

        // For strings / other values, use PHP filter_var to parse boolean-like strings
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $this->attributes['activo'] = (bool)$bool;
    }

    /**
     * Scope para prendas activas
     */
    public function scopeActivas($query)
    {
        // Use explicit boolean literal for Postgres compatibility
        return $query->whereRaw('"activo" = true');
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
        // Forzar comparación con literal booleano en SQL para Postgres
        // evita errores cuando el driver o parámetros usarían enteros (activo = 1)
        return self::select('categoria')
            ->distinct()
            ->whereRaw('"activo" = true')
            ->orderBy('categoria')
            ->pluck('categoria');
    }
}
