<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de Pedido - Modas Boom</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #6366f1;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8fafc;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .estado-change {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #6366f1;
            text-align: center;
        }
        .estado-anterior {
            display: inline-block;
            background-color: #f3f4f6;
            color: #6b7280;
            padding: 8px 16px;
            border-radius: 20px;
            margin: 0 10px;
        }
        .estado-nuevo {
            display: inline-block;
            background-color: #dbeafe;
            color: #1d4ed8;
            padding: 8px 16px;
            border-radius: 20px;
            margin: 0 10px;
            font-weight: bold;
        }
        .arrow {
            font-size: 24px;
            color: #6366f1;
            margin: 0 10px;
        }
        .pedido-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #374151;
            color: white;
            border-radius: 8px;
        }
        .timeline {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .timeline-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 6px;
        }
        .timeline-item.completed {
            background-color: #f0fdf4;
            color: #166534;
        }
        .timeline-item.current {
            background-color: #dbeafe;
            color: #1d4ed8;
            font-weight: bold;
        }
        .timeline-item.pending {
            background-color: #f9fafb;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📋 Actualización de Pedido</h1>
        <p>Pedido #{{ $pedido->id_pedido }}</p>
    </div>

    <div class="content">
        <p>Estimado/a <strong>{{ $cliente->nombre }}</strong>,</p>
        
        <p>Le informamos que el estado de su pedido ha sido actualizado:</p>

        <div class="estado-change">
            <h3>🔄 Cambio de Estado</h3>
            <div style="margin: 20px 0;">
                <span class="estado-anterior">{{ $estadoAnterior }}</span>
                <span class="arrow">→</span>
                <span class="estado-nuevo">{{ $estadoNuevo }}</span>
            </div>
            <p><small>Actualizado el {{ now()->format('d/m/Y H:i') }}</small></p>
        </div>

        <div class="pedido-info">
            <h3>📋 Información del Pedido</h3>
            <p><strong>Número de pedido:</strong> #{{ $pedido->id_pedido }}</p>
            <p><strong>Fecha de pedido:</strong> {{ $pedido->created_at->format('d/m/Y') }}</p>
            <p><strong>Estado actual:</strong> <span style="color: #1d4ed8; font-weight: bold;">{{ $estadoNuevo }}</span></p>
            <p><strong>Total:</strong> ${{ number_format($pedido->total, 2) }}</p>
            @if($pedido->fecha_entrega_programada)
            <p><strong>Fecha estimada de entrega:</strong> {{ \Carbon\Carbon::parse($pedido->fecha_entrega_programada)->format('d/m/Y') }}</p>
            @endif
        </div>

        <div class="timeline">
            <h3>📊 Progreso del Pedido</h3>
            @php
                $estados = ['Pendiente', 'En proceso', 'Asignado', 'En producción', 'Terminado', 'Entregado'];
                $estadoActualIndex = array_search($estadoNuevo, $estados);
            @endphp
            
            @foreach($estados as $index => $estado)
                <div class="timeline-item 
                    @if($index < $estadoActualIndex) completed
                    @elseif($index == $estadoActualIndex) current
                    @else pending @endif">
                    
                    @if($index < $estadoActualIndex)
                        ✅
                    @elseif($index == $estadoActualIndex)
                        🔄
                    @else
                        ⏳
                    @endif
                    
                    <span style="margin-left: 10px;">{{ $estado }}</span>
                </div>
            @endforeach
        </div>

        @if($estadoNuevo == 'En proceso')
            <p>Su pedido está siendo procesado por nuestro equipo. Le mantendremos informado sobre el progreso.</p>
        @elseif($estadoNuevo == 'Asignado')
            <p>Su pedido ha sido asignado a nuestro equipo de producción y comenzará a ser trabajado pronto.</p>
        @elseif($estadoNuevo == 'En producción')
            <p>¡Excelente! Su pedido está actualmente en producción. Nuestro equipo está trabajando en él.</p>
        @endif

        <p>Si tiene alguna pregunta sobre su pedido, no dude en contactarnos. Estaremos encantados de ayudarle.</p>
        
        <p>¡Gracias por confiar en Modas Boom!</p>
    </div>

    <div class="footer">
        <h3>Modas Boom</h3>
        <p>Su tienda de confianza en moda y estilo</p>
        <p>📧 {{ config('mail.from.address') }} | 📱 WhatsApp disponible</p>
        <p><small>Le mantendremos informado sobre cualquier cambio en su pedido</small></p>
    </div>
</body>
</html>