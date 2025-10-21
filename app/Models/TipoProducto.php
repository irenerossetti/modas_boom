<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProducto extends Model
{
    protected $table = 'tipo_producto';
    protected $primaryKey = 'id_tipoProducto';
    protected $fillable = ['nombre','descripcion','dificultad_produccion','habilitado'];
    public $timestamps = true;

    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_tipoProducto', 'id_tipoProducto');
    }
}
