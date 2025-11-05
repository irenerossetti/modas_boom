<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido - Modas Boom</title>
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
            background-color: #3b82f6;
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
        .pedido-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .productos {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .producto-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            color: #3b82f6;
            text-align: right;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background-color: #374151;
            color: white;
            border-radius: 8px;
        }
        .status-info {
            background-color: #dbeafe;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .next-steps {
            background-color: #f0f9ff;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Pedido Confirmado!</h1>
        <p>Gracias por su compra</p>
    </div>

    <div class="content">
        <p>Estimado/a <strong>{{ $cliente->nombre }}</strong>,</p>
        
        <p>Hemos recibido su pedido correctamente y está siendo procesado. A continuación encontrará los detalles de su compra:</p>

        <div class="pedido-info">
            <h3>📋 Información del Pedido</h3>
            <p><strong>Número de pedido:</strong> #{{ $pedido->id_pedido }}</p>
            <p><strong>Fecha de pedido:</strong> {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Estado actual:</strong> <span style="color: #3b82f6; font-weight: bold;">{{ $pedido->estado }}</span></p>
            @if($pedido->fecha_entrega_programada)
            <p><strong>Fecha estimada de entrega:</strong> {{ \Carbon\Carbon::parse($pedido->fecha_entrega_programada)->format('d/m/Y') }}</p>
            @endif
        </div>

        <div class="productos">
            <h3>🛍️ Resumen de su pedido</h3>
            @foreach($pedido->prendas as $prenda)
            <div class="producto-item">
                <div>
                    <strong>{{ $prenda->nombre }}</strong><br>
                    <small>Cantidad: {{ $prenda->pivot->cantidad }} | Precio unitario: ${{ number_format($prenda->pivot->precio_unitario, 2) }}</small>
                </div>
                <div>
                    ${{ number_format($prenda->pivot->precio_unitario * $prenda->pivot->cantidad, 2) }}
                </div>
            </div>
            @endforeach
            
            <div class="total">
                Total a pagar: ${{ number_format($pedido->total, 2) }}
            </div>
        </div>

        <div class="status-info">
            <h3>📊 Estado del Pedido</h3>
            <p>Su pedido se encuentra actualmente en estado: <strong>{{ $pedido->estado }}</strong></p>
            <p>Le mantendremos informado sobre cualquier cambio en el estado de su pedido a través de este correo electrónico.</p>
        </div>

        <div class="next-steps">
            <h3>🔄 Próximos Pasos</h3>
            <ul>
                <li>✅ Pedido recibido y confirmado</li>
                <li>⏳ Procesamiento y preparación</li>
                <li>🏭 Producción (si aplica)</li>
                <li>📦 Preparación para entrega</li>
                <li>🚚 Entrega o notificación para recoger</li>
            </ul>
        </div>

        <p>Si tiene alguna pregunta sobre su pedido, no dude en contactarnos. Estaremos encantados de ayudarle.</p>
        
        <p>¡Gracias por confiar en Modas Boom!</p>
    </div>

    <div class="footer">
        <h3>Modas Boom</h3>
        <p>Su tienda de confianza en moda y estilo</p>
        <p>📧 {{ config('mail.from.address') }} | 📱 WhatsApp disponible</p>
        <p><small>Mantenga este correo como comprobante de su pedido</small></p>
    </div>
</body>
</html>