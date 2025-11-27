<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraInsumo extends Model
{
    use HasFactory;

    protected $table = 'compras_insumos';

    protected $fillable = [
        'proveedor_id', 'descripcion', 'monto', 'fecha_compra', 'registrado_por', 'tela_id', 'cantidad'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_compra' => 'datetime'
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function tela()
    {
        return $this->belongsTo(Tela::class, 'tela_id', 'id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }
}
