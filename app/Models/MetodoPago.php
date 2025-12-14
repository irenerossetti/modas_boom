<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetodoPago extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'icono',
        'color',
        'configuracion',
        'qr_image',
        'activo',
        'orden'
    ];

    protected $appends = ['qr_image_url'];

    protected $casts = [
        'configuracion' => 'array',
        'activo' => 'boolean'
    ];

    // Scope para métodos activos
    public function scopeActivos($query)
    {
        return $query->whereRaw('activo = true')->orderBy('orden');
    }

    // Accessor para obtener la URL completa de la imagen QR
    public function getQrImageUrlAttribute()
    {
        return $this->qr_image ? asset($this->qr_image) : null;
    }
}
