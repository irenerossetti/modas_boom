<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tela extends Model
{
    use HasFactory;

    protected $table = 'telas';

    protected $fillable = [
        'nombre', 'descripcion', 'stock', 'unidad', 'stock_minimo', 'activo'
    ];

    protected $casts = [
        'stock' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
        'activo' => 'boolean'
    ];

    public function consumir($cantidad)
    {
        // reduce stock by cantidad and return true if success
        if ($this->stock >= $cantidad) {
            $this->decrement('stock', $cantidad);
            return true;
        }
        return false;
    }

    public function reponer($cantidad)
    {
        $this->increment('stock', $cantidad);
    }

    public function isLowStock(): bool
    {
        return (float)$this->stock <= (float)$this->stock_minimo;
    }
}
