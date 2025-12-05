<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Producción</title>
    <style>
        @page {
            margin: 20px;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #1f2937;
            font-size: 16pt;
            margin-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .header-info {
            text-align: center;
            margin-bottom: 20px;
            color: #6b7280;
            font-size: 8pt;
        }
        .summary-box {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
        }
        .summary-title {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 8px;
            color: #374151;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #374151;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-size: 8pt;
            font-weight: bold;
        }
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 8pt;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .operario-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .operario-header {
            background-color: #dbeafe;
            padding: 8px;
            margin-bottom: 8px;
            border-left: 3px solid #3b82f6;
        }
        .operario-name {
            font-size: 10pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .operario-stats {
            font-size: 8pt;
            color: #374151;
        }
        .stat-row {
            margin: 2px 0;
        }
        .stat-label {
            color: #6b7280;
            display: inline-block;
            width: 120px;
        }
        .stat-value {
            font-weight: bold;
            color: #1f2937;
        }
        .total-pagar {
            color: #059669;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 5px;
        }
        .summary-label {
            font-size: 8pt;
            color: #6b7280;
        }
        .summary-value {
            font-size: 12pt;
            font-weight: bold;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <h1>Reporte de Producción - Pago a Destajo</h1>
    
    <div class="header-info">
        <strong>Fecha de Generación:</strong> {{ now()->format('d/m/Y H:i') }}<br>
        <strong>Generado por:</strong> {{ auth()->user()->nombre }}
    </div>

    <!-- Resumen General -->
    <div class="summary-box">
        <div class="summary-title">Resumen General</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Avances</div>
                <div class="summary-value">{{ $totales['total_avances'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Prendas</div>
                <div class="summary-value">{{ number_format($totales['total_prendas']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total a Pagar</div>
                <div class="summary-value" style="color: #059669;">Bs. {{ number_format($totales['total_a_pagar'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Operarios</div>
                <div class="summary-value">{{ $estadisticas->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Detalle por Operario -->
    <div class="summary-title">Detalle por Operario</div>
    
    @foreach($estadisticas as $stat)
        <div class="operario-section">
            <div class="operario-header">
                <div class="operario-name">Operario: {{ $stat['operario']->nombre }}</div>
                <div class="operario-stats">
                    <div class="stat-row">
                        <span class="stat-label">Avances:</span>
                        <span class="stat-value">{{ $stat['total_avances'] }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Prendas Procesadas:</span>
                        <span class="stat-value">{{ number_format($stat['total_prendas_procesadas']) }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Promedio por Avance:</span>
                        <span class="stat-value">Bs. {{ number_format($stat['promedio_por_avance'], 2) }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Total a Pagar:</span>
                        <span class="stat-value total-pagar">Bs. {{ number_format($stat['total_a_pagar'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Detalle de Avances -->
    <div class="summary-title" style="margin-top: 20px;">Detalle de Todos los Avances</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Fecha</th>
                <th style="width: 20%;">Operario</th>
                <th style="width: 10%;">Pedido</th>
                <th style="width: 20%;">Etapa</th>
                <th style="width: 10%;">Avance</th>
                <th style="width: 15%;">Costo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($avances as $avance)
                <tr>
                    <td>{{ $avance->created_at->format('d/m/Y') }}</td>
                    <td>{{ $avance->operario->nombre ?? 'N/A' }}</td>
                    <td>#{{ $avance->id_pedido }}</td>
                    <td>{{ $avance->etapa }}</td>
                    <td>{{ $avance->porcentaje_avance }}%</td>
                    <td class="total-pagar">Bs. {{ number_format($avance->costo_mano_obra, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Modas Boom - Sistema de Gestión de Producción<br>
        Este documento es confidencial y solo debe ser usado para fines internos
    </div>
</body>
</html>
