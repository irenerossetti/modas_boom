@extends('layouts.app')
@section('content')
<div class="py-8 px-6">
    <h1>Reporte del Sistema</h1>
    <p>Generado: {{ $generated_at }}</p>
    <p>Desde: {{ $desde ?? 'No especificado' }} Hasta: {{ $hasta ?? 'No especificado' }}</p>
    <hr>
    <h2>Productos</h2>
    <table style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripcion</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->nombre }}</td>
                    <td>{{ $p->descripcion ?? '' }}</td>
                    <td>{{ $p->precio ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <h2>Telas (Inventario)</h2>
    <table style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Stock</th>
                <th>Unidad</th>
                <th>Stock Minimo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($telas as $t)
                <tr>
                    <td>{{ $t->id }}</td>
                    <td>{{ $t->nombre }}</td>
                    <td>{{ $t->stock }}</td>
                    <td>{{ $t->unidad }}</td>
                    <td>{{ $t->stock_minimo }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>
    <h2>Ventas</h2>
    <table style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $v)
                <tr>
                    <td>{{ $v->id_pedido ?? $v->id }}</td>
                    <td>{{ $v->cliente->nombre ?? 'N/A' }}</td>
                    <td>{{ $v->total ?? 0 }}</td>
                    <td>{{ $v->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>
    <h2>Compras</h2>
    <table style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Proveedor</th>
                <th>Descripcion</th>
                <th>Monto</th>
                <th>Fecha</th>
                <th>Tela</th>
                <th>Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($compras as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td>{{ $c->proveedor->nombre ?? 'N/A' }}</td>
                    <td>{{ $c->descripcion }}</td>
                    <td>{{ $c->monto }}</td>
                    <td>{{ $c->fecha_compra }}</td>
                    <td>{{ $c->tela->nombre ?? '' }}</td>
                    <td>{{ $c->cantidad ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
