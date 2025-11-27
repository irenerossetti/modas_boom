<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevolucionPrenda extends Model
{
    use HasFactory;

    protected $table = 'devolucion_prenda';

    protected $fillable = [
        'id_pedido',
        'id_prenda',
        'cantidad',
        'motivo',
        'registrado_por'
    ];

    protected $casts = [
        'cantidad' => 'integer'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function prenda()
    {
        return $this->belongsTo(Prenda::class, 'id_prenda', 'id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }
}
