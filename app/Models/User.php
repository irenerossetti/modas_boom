<?php

// en app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // --- INICIO DE LOS AJUSTES ---

    protected $table = 'usuario';
    protected $primaryKey = 'id_usuario';
    public $timestamps = true; // La tabla tiene created_at y updated_at

    // ATRIBUTOS DE LA TABLA 'usuario'
    protected $fillable = [
        'id_rol',
        'nombre',
        'telefono',
        'direccion',
        'email',
        'password', // Laravel manejará el hash automáticamente
        'habilitado',
    ];

    // Ocultar el password en las respuestas JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Mapeo para que Laravel sepa cómo manejar ciertos datos
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'habilitado' => 'boolean',
        'fecha_registro' => 'datetime',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id_usuario';
    }

    // Relación con el modelo Rol
    public function rol(): BelongsTo
    {
        // Apunta al modelo Rol y usa la llave foránea 'id_rol'
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    // --- FIN DE LOS AJUSTES ---
}