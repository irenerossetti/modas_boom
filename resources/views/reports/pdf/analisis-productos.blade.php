<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Análisis de Productos</title>
    <style>
        @page { margin: 20px; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; line-height: 1.4; color: #333; }
        h1 { text-align: center; color: #e91e63; font-size: 18pt; margin-bottom: 10px; border-bottom: 3px solid #e91e63; padding-bottom: 10px; }
        .header-info { text-align: center; margin-bottom: 20px; color: #666; font-size: 8pt; }
        .info-box { background-color: #f5f5f5; padding: 10px; margin-bottom: 15px; border-left: 4px solid #e91e63; }
        .info-box p { margin: 3px 0; font-size: 9pt; }
        .info-box strong { color: #e91e63; }
        .stats-container { width: 100%; margin-bottom: 15px; }
        .stats-container table { width: 100%; border-collapse: collapse; }
        .stats-container td { width: 33.33%; text-align: center; padding: 10px; background-color: #f5f5f5; border-right: 2px solid white; }
        .stats-container td:last-child { border-right: none; }
        .stat-value { font-size: 16pt; font-weight: bold; color: #e91e63; display: block; }
        .stat-label { font-size: 8pt; color: #666; display: block; margin-top: 3px; }
        .section-title { background-color: #e91e63; color: white; padding: 8px 10px; margin: 15px 0 10px 0; font-size: 11pt; font-weight: bold; }
        .section-title.hueso { background-color: #9e9e9e; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 8pt; }
        table.data-table thead { background-color: #f5f5f5; }
        table.data-table th { padding: 6px 4px; text-align: left; font-weight: bold; border-bottom: 2px solid #e91e63; color: #333; font-size: 8pt; }
        table.data-table td { padding: 5px 4px; border-bottom: 1px solid #e0e0e0; }
        .rank { font-weight: bold; color: #e91e63; text-align: center; }
        .rank.hueso { color: #9e9e9e; }
        .producto-nombre { font-weight: bold; color: #333; font-size: 8pt; }
        .numero { text-align: right; }
        .badge { display: inline-block; padding: 2px 5px; font-size: 7pt; font-weight: bold; color: white; }
        .badge-success { background-color: #4caf50; }
        .badge-warning { background-color: #ff9800; }
        .badge-danger { background-color: #f44336; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e0e0e0; text-align: center; font-size: 7pt; color: #999; }
        .no-data { text-align: center; padding: 15px; color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>Análisis de Productos: Estrella y Hueso</h1>
    <div class="header-info">
        Reporte de Productos Más Vendidos (Estrella) y Menos Vendidos (Hueso)
    </div>

    <div class="info-box">
        <p><strong>Fecha de Generación:</strong> {{ $generated_at }}</p>
        @if($fecha_desde || $fecha_hasta)
            <p><strong>Período de Análisis:</strong> 
                @if($fecha_desde && $fecha_hasta)
                    {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}
                @elseif($fecha_desde)
                    Desde {{ \Carbon\Carbon::parse($fecha_desde)->format('d/m/Y') }}
                @else
                    Hasta {{ \Carbon\Carbon::parse($fecha_hasta)->format('d/m/Y') }}
                @endif
            </p>
        @else
            <p><strong>Período de Análisis:</strong> Todos los registros</p>
        @endif
    </div>

    <div class="stats-container">
        <table>
            <tr>
                <td>
                    <span class="stat-value">{{ $total_productos }}</span>
                    <span class="stat-label">Total Productos</span>
                </td>
                <td>
                    <span class="stat-value">{{ $productos_con_ventas }}</span>
                    <span class="stat-label">Con Ventas</span>
                </td>
                <td>
                    <span class="stat-value">{{ $productos_sin_ventas }}</span>
                    <span class="stat-label">Sin Ventas</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">TOP 10 PRODUCTOS ESTRELLA (Más Vendidos)</div>
    
    @if($productos_estrella->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">#</th>
                    <th>Producto</th>
                    <th style="width: 60px; text-align: right;">Unidades</th>
                    <th style="width: 50px; text-align: right;">Pedidos</th>
                    <th style="width: 70px; text-align: right;">Ingresos</th>
                    <th style="width: 55px; text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos_estrella as $index => $producto)
                    <tr>
                        <td class="rank">{{ $index + 1 }}</td>
                        <td>
                            <div class="producto-nombre">{{ $producto->nombre }}</div>
                            @if($producto->categoria)
                                <small style="color: #999; font-size: 7pt;">{{ $producto->categoria }}</small>
                            @endif
                        </td>
                        <td class="numero"><strong>{{ number_format($producto->total_vendido, 0) }}</strong></td>
                        <td class="numero">{{ $producto->num_pedidos }}</td>
                        <td class="numero">Bs. {{ number_format($producto->ingresos_totales, 2) }}</td>
                        <td style="text-align: center;">
                            @if($producto->total_vendido > 50)
                                <span class="badge badge-success">Excelente</span>
                            @elseif($producto->total_vendido > 20)
                                <span class="badge badge-warning">Bueno</span>
                            @else
                                <span class="badge badge-danger">Bajo</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No hay datos de productos estrella para mostrar</div>
    @endif

    <div class="section-title hueso">TOP 10 PRODUCTOS HUESO (Menos Vendidos)</div>
    
    @if($productos_hueso->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px;">#</th>
                    <th>Producto</th>
                    <th style="width: 60px; text-align: right;">Unidades</th>
                    <th style="width: 50px; text-align: right;">Pedidos</th>
                    <th style="width: 70px; text-align: right;">Ingresos</th>
                    <th style="width: 70px; text-align: center;">Recomendación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productos_hueso as $index => $producto)
                    <tr>
                        <td class="rank hueso">{{ $index + 1 }}</td>
                        <td>
                            <div class="producto-nombre">{{ $producto->nombre }}</div>
                            @if($producto->categoria)
                                <small style="color: #999; font-size: 7pt;">{{ $producto->categoria }}</small>
                            @endif
                        </td>
                        <td class="numero">
                            @if($producto->total_vendido == 0)
                                <span style="color: #f44336; font-weight: bold;">0</span>
                            @else
                                {{ number_format($producto->total_vendido, 0) }}
                            @endif
                        </td>
                        <td class="numero">{{ $producto->num_pedidos }}</td>
                        <td class="numero">
                            @if($producto->ingresos_totales == 0)
                                <span style="color: #f44336;">Bs. 0.00</span>
                            @else
                                Bs. {{ number_format($producto->ingresos_totales, 2) }}
                            @endif
                        </td>
                        <td style="text-align: center; font-size: 7pt;">
                            @if($producto->total_vendido == 0)
                                <span style="color: #f44336; font-weight: bold;">Revisar</span>
                            @else
                                <span style="color: #ff9800;">Promocionar</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">No hay datos de productos hueso para mostrar</div>
    @endif

    <div class="footer">
        <p>Modas Boom - Sistema de Gestión | Generado automáticamente</p>
        <p>Este reporte es confidencial y de uso interno exclusivo</p>
    </div>
</body>
</html>