@php
  $old = [
    'id_cliente'     => old('id_cliente', isset($pedido) ? $pedido->id_cliente : ''),
    'fecha_pedido'   => old('fecha_pedido', isset($pedido) ? optional($pedido->fecha_pedido)->format('Y-m-d') : now()->format('Y-m-d')),
    'fecha_entrega'  => old('fecha_entrega', isset($pedido) ? optional($pedido->fecha_entrega)->format('Y-m-d') : ''),
    'metodo_pago'    => old('metodo_pago', isset($pedido) ? $pedido->metodo_pago : ''),
    'observaciones'  => old('observaciones', isset($pedido) ? $pedido->observaciones : ''),
  ];

  $initialItems = collect(
    old('items', isset($pedido)
      ? $pedido->detalles->map(fn($d) => [
          'id_producto'     => $d->id_producto,
          'cantidad'        => (float) $d->cantidad,
          'precio_unitario' => (float) $d->precio_unitario,
        ])->values()->all()
      : [])
  );

  if ($initialItems->isEmpty()) {
    $initialItems = collect([['id_producto' => '', 'cantidad' => 1, 'precio_unitario' => 0]]);
  }

  $alpineProducts = $productos->map(fn($p) => [
    'id'     => $p->id_producto,
    'nombre' => $p->nombre,
    'precio' => (float) ($p->precio_unitario ?? 0),
  ])->values();

  $alpineProductsJson = $alpineProducts->toJson(JSON_UNESCAPED_UNICODE);
  $initialItemsJson   = $initialItems->toJson(JSON_UNESCAPED_UNICODE);
@endphp
