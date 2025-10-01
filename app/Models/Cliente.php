<?php

// en app/Models/Cliente.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    
    protected $table = 'cliente';
    protected $primaryKey = 'id_cliente';
    public $timestamps = false;
    
    // Columnas que se pueden asignar masivamente
    protected $fillable = [
        'id_usuario',
        'nro_documento',
        'habilitado',
    ];

    // Un Cliente está asociado a un Usuario
    public function usuario()
    {
        // Apuntamos al modelo User y no a Usuario
        return $this->belongsTo(User::class, 'id_usuario');
    }
}