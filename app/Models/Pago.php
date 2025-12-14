<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pago';

    protected $fillable = [
        'id_pedido', 'id_cliente', 'monto', 'metodo', 'referencia', 'fecha_pago', 'registrado_por', 'anulado', 'anulado_por', 'anulado_motivo', 'recibo_path', 'notas'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'anulado' => 'boolean',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id_usuario');
    }

    /**
     * Scope to filter by `anulado` using driver-safe SQL.
     * Usage: Pago::anulado(false)->get();
     */
    public function scopeAnulado($query, $value)
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            $literal = $value ? 'true' : 'false';
            return $query->whereRaw('"anulado" = ' . $literal);
        }
        return $query->where('anulado', $value);
    }
}
