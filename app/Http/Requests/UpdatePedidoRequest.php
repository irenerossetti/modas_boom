<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePedidoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id_cliente'               => ['required','integer','exists:clientes,id'],
            'fecha_entrega'            => ['nullable','date','after_or_equal:fecha_pedido'],
            'metodo_pago'              => ['nullable','string','max:50'],
            'observaciones'            => ['nullable','string'],

            'items'                    => ['required','array','min:1'],
            'items.*.id_producto'      => ['required','integer','exists:producto,id_producto'],
            'items.*.cantidad'         => ['required','numeric','min:0.01'],
            'items.*.precio_unitario'  => ['required','numeric','min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                 => 'Debes mantener al menos un ítem.',
            'items.*.id_producto.required'   => 'Producto requerido.',
            'items.*.id_producto.exists'     => 'Producto inválido.',
            'items.*.cantidad.required'      => 'Cantidad requerida.',
            'items.*.cantidad.min'           => 'La cantidad debe ser mayor a 0.',
            'items.*.precio_unitario.required'=> 'Precio requerido.',
            'items.*.precio_unitario.min'    => 'El precio no puede ser negativo.',
        ];
    }
}
