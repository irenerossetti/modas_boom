<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'nombre', 'contacto', 'telefono', 'email'
    ];

    public function compras()
    {
        return $this->hasMany(CompraInsumo::class, 'proveedor_id', 'id');
    }
}
