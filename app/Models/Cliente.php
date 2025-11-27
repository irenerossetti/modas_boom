<?php

// en app/Models/Cliente.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    
    protected $table = 'clientes';
    
    // Columnas que se pueden asignar masivamente
    protected $fillable = [
        'id_usuario',
        'nombre',
        'apellido',
        'ci_nit',
        'telefono',
        'email',
        'direccion',
    ];

    // Un Cliente está asociado a un Usuario
    public function usuario()
    {
        // Apuntamos al modelo User y no a Usuario
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    // Un Cliente puede tener muchos Pedidos
    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_cliente', 'id');
    }

    /**
     * Compute the current debt of the client.
     * Deuda = sum(pedidos.total) - sum(pagos.monto where anulado = false)
     */
    public function deudaActual(): float
    {
        $totalPedidos = $this->pedidos()->sum('total') ?? 0;
        // Use Pago::anulado(false) scope which is driver-aware for boolean values (Postgres vs SQLite)
        $totalPagado = \App\Models\Pago::where('id_cliente', $this->id)->anulado(false)->sum('monto') ?? 0;
        return round($totalPedidos - $totalPagado, 2);
    }
}