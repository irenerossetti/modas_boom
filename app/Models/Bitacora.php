<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    protected $primaryKey = 'id_bitacora';
    public $timestamps = true;

    protected $fillable = [
        'id_usuario',
        'accion',
        'modulo',
        'descripcion',
        'datos_anteriores',
        'datos_nuevos',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con el modelo User
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }

    // Scopes para filtros comunes
    public function scopeByUsuario($query, $usuarioId)
    {
        return $query->where('id_usuario', $usuarioId);
    }

    public function scopeByAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopeByModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopeByFechas($query, $fechaDesde, $fechaHasta)
    {
        if ($fechaDesde) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }
        return $query;
    }

    // Método para obtener el nombre del usuario
    public function getNombreUsuarioAttribute()
    {
        return $this->usuario ? $this->usuario->nombre : 'Sistema';
    }

    // Método para obtener el avatar del usuario
    public function getAvatarUsuarioAttribute()
    {
        if ($this->usuario && $this->usuario->nombre) {
            return strtoupper(substr($this->usuario->nombre, 0, 1));
        }
        return 'S';
    }

    // Método para obtener el rol del usuario
    public function getRolUsuarioAttribute()
    {
        return $this->usuario && $this->usuario->rol ? $this->usuario->rol->nombre : 'Sistema';
    }
}
