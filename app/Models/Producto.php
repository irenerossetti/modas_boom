<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    protected $fillable = ['id_tipoProducto','nombre','descripcion','precio_unitario','habilitado'];
    public $timestamps = true;

    public function tipo()
    {
        return $this->belongsTo(TipoProducto::class, 'id_tipoProducto', 'id_tipoProducto');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_producto', 'id_producto');
    }
}
