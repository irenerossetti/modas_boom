<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte del Sistema - MODA BOOM</title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 24px;
            font-weight: bold;
        }
        .header h2 {
            margin: 0 0 10px 0;
            color: #34495e;
            font-size: 16px;
            font-weight: normal;
        }
        .header p {
            margin: 3px 0;
            color: #7f8c8d;
            font-size: 11px;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #34495e;
            color: white;
            padding: 10px 15px;
            margin: 0 0 10px 0;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 15px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #2c3e50;
        }
        td {
            border: 1px solid #dee2e6;
            padding: 6px 5px;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px;
        }
        .summary-stats {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            width: 25%;
        }
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            display: block;
        }
        .stat-label {
            font-size: 9px;
            color: #6c757d;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MODA BOOM</h1>
        <h2>Reporte General del Sistema</h2>
        <p><strong>Generado:</strong> {{ \Carbon\Carbon::parse($generated_at)->format('d/m/Y H:i') }}</p>
        @if($desde || $hasta)
            <p><strong>Periodo:</strong> 
                @if($desde && $hasta)
                    {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
                @elseif($desde)
                    Desde {{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}
                @else
                    Hasta {{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}
                @endif
            </p>
        @else
            <p><strong>Periodo:</strong> Todos los registros</p>
        @endif
    </div>

    <!-- Resumen Estadístico -->
    <div class="info-box">
        <div class="summary-stats">
            <div class="stat-item">
                <span class="stat-value">{{ $productos->count() }}</span>
                <div class="stat-label">Productos</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $telas->count() }}</span>
                <div class="stat-label">Telas</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $ventas->count() }}</span>
                <div class="stat-label">Ventas</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ $compras->count() }}</span>
                <div class="stat-label">Compras</div>
            </div>
        </div>
    </div>

    <!-- Sección Productos -->
    <div class="section">
        <h3 class="section-title">PRODUCTOS</h3>
        @if($productos->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 30%;">Nombre</th>
                        <th style="width: 40%;">Descripcion</th>
                        <th style="width: 12%;">Precio (Bs.)</th>
                        <th style="width: 10%;">Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $producto)
                        <tr>
                            <td class="text-center">{{ $producto->id }}</td>
                            <td>{{ $producto->nombre }}</td>
                            <td>{{ $producto->descripcion ?? 'Sin descripcion' }}</td>
                            <td class="text-right">
                                @if($producto->precio)
                                    {{ number_format($producto->precio, 2) }}
                                @else
                                    <span style="color: #6c757d;">No definido</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $producto->stock ?? 0 }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No hay productos registrados</div>
        @endif
    </div>

    <!-- Sección Telas -->
    <div class="section">
        <h3 class="section-title">INVENTARIO DE TELAS</h3>
        @if($telas->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 35%;">Nombre</th>
                        <th style="width: 15%;">Stock Actual</th>
                        <th style="width: 12%;">Unidad</th>
                        <th style="width: 15%;">Stock Minimo</th>
                        <th style="width: 15%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($telas as $tela)
                        <tr>
                            <td class="text-center">{{ $tela->id }}</td>
                            <td>{{ $tela->nombre }}</td>
                            <td class="text-center">{{ number_format($tela->stock, 2) }}</td>
                            <td class="text-center">{{ $tela->unidad }}</td>
                            <td class="text-center">{{ number_format($tela->stock_minimo, 2) }}</td>
                            <td class="text-center">
                                @if($tela->stock <= $tela->stock_minimo)
                                    <span style="color: #dc3545; font-weight: bold;">BAJO</span>
                                @else
                                    <span style="color: #28a745; font-weight: bold;">OK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="no-data">No hay telas registradas en el inventario</div>
        @endif
    </div>

    <!-- Sección Ventas -->
    <div class="section page-break">
        <h3 class="section-title">VENTAS (PEDIDOS)</h3>
        @if($ventas->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">Pedido #</th>
                        <th style="width: 30%;">Cliente</th>
                        <th style="width: 15%;">Total (Bs.)</th>
                        <th style="width: 15%;">Estado</th>
                        <th style="width: 15%;">Fecha Creacion</th>
                        <th style="width: 15%;">Ultima Actualizacion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ventas as $venta)
                        <tr>
                            <td class="text-center"><strong>{{ $venta->id_pedido ?? $venta->id }}</strong></td>
                            <td>{{ $venta->cliente->nombre ?? 'Cliente no disponible' }} {{ $venta->cliente->apellido ?? '' }}</td>
                            <td class="text-right">
                                @if($venta->total)
                                    {{ number_format($venta->total, 2) }}
                                @else
                                    <span style="color: #6c757d;">Por definir</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span style="
                                    @if($venta->estado == 'Entregado') color: #28a745;
                                    @elseif($venta->estado == 'Cancelado') color: #dc3545;
                                    @elseif($venta->estado == 'En produccion') color: #007bff;
                                    @else color: #ffc107; @endif
                                    font-weight: bold;">
                                    {{ $venta->estado ?? 'En proceso' }}
                                </span>
                            </td>
                            <td class="text-center">{{ $venta->created_at->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $venta->updated_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Resumen de ventas -->
            <div style="margin-top: 15px; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                <strong>Resumen de Ventas:</strong><br>
                Total de pedidos: {{ $ventas->count() }}<br>
                Valor total: Bs. {{ number_format($ventas->where('total', '>', 0)->sum('total'), 2) }}
            </div>
        @else
            <div class="no-data">No hay ventas registradas en el periodo seleccionado</div>
        @endif
    </div>

    <!-- Sección Compras -->
    <div class="section">
        <h3 class="section-title">COMPRAS DE INSUMOS</h3>
        @if($compras->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 20%;">Proveedor</th>
                        <th style="width: 25%;">Descripcion</th>
                        <th style="width: 12%;">Monto (Bs.)</th>
                        <th style="width: 12%;">Fecha</th>
                        <th style="width: 15%;">Tela</th>
                        <th style="width: 8%;">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($compras as $compra)
                        <tr>
                            <td class="text-center">{{ $compra->id }}</td>
                            <td>{{ $compra->proveedor->nombre ?? 'Proveedor no disponible' }}</td>
                            <td>{{ $compra->descripcion ?? 'Sin descripcion' }}</td>
                            <td class="text-right">{{ number_format($compra->monto, 2) }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($compra->fecha_compra)->format('d/m/Y') }}</td>
                            <td>{{ $compra->tela->nombre ?? 'No especificada' }}</td>
                            <td class="text-center">{{ $compra->cantidad ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Resumen de compras -->
            <div style="margin-top: 15px; background-color: #f8f9fa; padding: 10px; border-radius: 5px;">
                <strong>Resumen de Compras:</strong><br>
                Total de compras: {{ $compras->count() }}<br>
                Monto total: Bs. {{ number_format($compras->sum('monto'), 2) }}
            </div>
        @else
            <div class="no-data">No hay compras registradas en el periodo seleccionado</div>
        @endif
    </div>

    <div class="footer">
        <p><strong>MODA BOOM</strong> - Sistema de Gestion Empresarial</p>
        <p>Reporte generado automaticamente el {{ now()->format('d/m/Y H:i') }}</p>
        <p>Total de secciones incluidas: 
            {{ ($productos->count() > 0 ? 1 : 0) + ($telas->count() > 0 ? 1 : 0) + ($ventas->count() > 0 ? 1 : 0) + ($compras->count() > 0 ? 1 : 0) }} de 4
        </p>
    </div>
</body>
</html>
