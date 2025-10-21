<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $fillable = [
        'id_cliente',
        'fecha_pedido',
        'fecha_entrega',
        'metodo_pago',
        'total_pedido',
        'observaciones',
        'estado',
    ];

    protected $casts = [
        'fecha_pedido'  => 'date',
        'fecha_entrega' => 'date',
        'total_pedido'  => 'decimal:2',
    ];
    // Aquí puedes definir relaciones, por ejemplo, con el Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }
    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }

}