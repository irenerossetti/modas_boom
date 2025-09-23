<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
// Esta línea es correcta, le decimos que use la clase de autenticación de Laravel
use Illuminate\Foundation\Auth\User as Authenticatable; 
use Illuminate\Notifications\Notifiable;

// 1. El nombre de la clase debe ser "Usuario"
class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    // 2. Le decimos el nombre exacto de nuestra tabla
    protected $table = 'usuario';

    // 3. Le decimos el nombre exacto de nuestra clave primaria
    protected $primaryKey = 'id_usuario';

    /**
     * 4. Lista de columnas que se pueden llenar desde un formulario.
     * Deben coincidir con los nombres de tu tabla 'usuario'.
     */
    protected $fillable = [
        'id_rol',
        'nombre',
        'telefono',
        'direccion',
        'email',
        'password',
        'habilitado',
    ];

    /**
     * Los atributos que deben ocultarse para la serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}