<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;

    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false; // La tabla no tiene created_at/updated_at

    protected $fillable = [
        'nombre',
        'descripcion',
        'habilitado',
    ];

    // Un Rol puede tener muchos Usuarios
    public function usuarios()
    {
        return $this->hasMany(User::class, 'id_rol');
    }
}