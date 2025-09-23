<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    protected $table = 'rol';
    protected $primaryKey = 'id_rol';
    public $timestamps = false; // Tu tabla rol no parece tener timestamps

    // Un Rol puede tener muchos Usuarios
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'id_rol');
    }
}