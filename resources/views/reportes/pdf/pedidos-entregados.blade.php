<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Pedidos Entregados</title>
    <style>
        @page {
            margin: 2cm;
            size: A4;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
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
            font-size: 18px;
            font-weight: normal;
        }
        .header p {
            margin: 3px 0;
            color: #7f8c8d;
            font-size: 12px;
        }
        .stats-container {
            margin-bottom: 25px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #e9ecef;
        }
        .stats {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 12px 8px;
            border-right: 1px solid #dee2e6;
            vertical-align: top;
        }
        .stat-item:last-child {
            border-right: none;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }
        .table-container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th {
            background-color: #34495e;
            color: white;
            padding: 10px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            border: 1px solid #dee2e6;
            padding: 8px 6px;
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
        .stars {
            color: #f39c12;
            font-size: 12px;
        }
        .rating-info {
            font-size: 8px;
            color: #6c757d;
            margin-top: 2px;
        }
        .no-rating {
            color: #adb5bd;
            font-style: italic;
            font-size: 9px;
        }
        .comment-cell {
            max-width: 120px;
            word-wrap: break-word;
            font-size: 9px;
            line-height: 1.3;
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
        <h2>Reporte de Pedidos Entregados</h2>
        <p>Período: {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</p>
        <p>Generado el: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="stats-container">
        <div class="stats">
            <div class="stat-item">
                <span class="stat-value">{{ number_format($estadisticas['total_entregados']) }}</span>
                <div class="stat-label">Total Entregados</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">{{ number_format($estadisticas['con_calificacion']) }}</span>
                <div class="stat-label">Con Calificación</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">
                    @if(isset($estadisticas['promedio_calificacion']) && $estadisticas['promedio_calificacion'])
                        {{ number_format($estadisticas['promedio_calificacion'], 1) }}/5
                    @else
                        N/A
                    @endif
                </span>
                <div class="stat-label">Promedio Calificación</div>
            </div>
            <div class="stat-item">
                <span class="stat-value">
                    {{ $estadisticas['total_entregados'] > 0 ? number_format(($estadisticas['con_calificacion'] / $estadisticas['total_entregados']) * 100, 1) : 0 }}%
                </span>
                <div class="stat-label">% Calificados</div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Pedido #</th>
                    <th style="width: 20%;">Cliente</th>
                    <th style="width: 12%;">CI/NIT</th>
                    <th style="width: 10%;">Total (Bs.)</th>
                    <th style="width: 10%;">F. Creación</th>
                    <th style="width: 10%;">F. Entrega</th>
                    <th style="width: 12%;">Calificación</th>
                    <th style="width: 18%;">Comentario</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedidos as $pedido)
                    <tr>
                        <td class="text-center"><strong>{{ $pedido->id_pedido }}</strong></td>
                        <td>{{ $pedido->cliente->nombre }} {{ $pedido->cliente->apellido }}</td>
                        <td class="text-center">{{ $pedido->cliente->ci_nit }}</td>
                        <td class="text-right">
                            @if($pedido->total)
                                {{ number_format($pedido->total, 2) }}
                            @else
                                <span style="color: #6c757d;">Por definir</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $pedido->created_at->format('d/m/Y') }}</td>
                        <td class="text-center">{{ $pedido->updated_at->format('d/m/Y') }}</td>
                        <td class="text-center">
                            @if($pedido->yaFueCalificado())
                                <div class="stars">
                                    @for($i = 1; $i <= $pedido->calificacion; $i++)★@endfor
                                    @for($i = $pedido->calificacion + 1; $i <= 5; $i++)☆@endfor
                                </div>
                                <div style="font-weight: bold; margin: 2px 0;">{{ $pedido->calificacion }}/5</div>
                                <div class="rating-info">{{ $pedido->fecha_calificacion->format('d/m/Y') }}</div>
                            @else
                                <span class="no-rating">Sin calificar</span>
                            @endif
                        </td>
                        <td class="comment-cell">
                            @if($pedido->comentario_calificacion)
                                "{{ Str::limit($pedido->comentario_calificacion, 80) }}"
                            @else
                                <span style="color: #adb5bd;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 20px; color: #6c757d; font-style: italic;">
                            No se encontraron pedidos entregados en el período seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p><strong>MODA BOOM</strong> - Sistema de Gestión de Pedidos</p>
        <p>Reporte generado automáticamente el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</p>
        @if($pedidos->count() > 0)
            <p>Total de registros: {{ $pedidos->count() }} pedido{{ $pedidos->count() != 1 ? 's' : '' }}</p>
        @endif
    </div>
</body>
</html>