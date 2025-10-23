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
}