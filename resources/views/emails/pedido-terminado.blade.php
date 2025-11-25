<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Terminado - Modas Boom</title>
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
            background-color: #4f46e5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .pedido-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #10b981;
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
            color: #059669;
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
        .btn {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Su pedido está terminado!</h1>
        <p>Pedido #{{ $pedido->id_pedido }}</p>
    </div>

    <div class="content">
        <p>Estimado/a <strong>{{ $cliente->nombre }}</strong>,</p>
        
        <p>Nos complace informarle que su pedido ha sido <strong>terminado</strong> y está listo para ser entregado.</p>

        <div class="pedido-info">
            <h3>📋 Información del Pedido</h3>
            <p><strong>Número de pedido:</strong> #{{ $pedido->id_pedido }}</p>
            <p><strong>Fecha de pedido:</strong> {{ $pedido->created_at->format('d/m/Y') }}</p>
            <p><strong>Estado:</strong> <span style="color: #059669; font-weight: bold;">{{ $pedido->estado }}</span></p>
            @if($pedido->fecha_entrega_programada)
            <p><strong>Fecha de entrega programada:</strong> {{ \Carbon\Carbon::parse($pedido->fecha_entrega_programada)->format('d/m/Y') }}</p>
            @endif
        </div>

        <div class="productos">
            <h3>🛍️ Productos de su pedido</h3>
            @foreach($pedido->prendas as $prenda)
            <div class="producto-item">
                <div>
                    <strong>{{ $prenda->nombre }}</strong><br>
                    <small>Cantidad: {{ $prenda->pivot->cantidad }}</small>
                </div>
                <div>
                    ${{ number_format($prenda->pivot->precio_unitario * $prenda->pivot->cantidad, 2) }}
                </div>
            </div>
            @endforeach
            
            <div class="total">
                Total: ${{ number_format($pedido->total, 2) }}
            </div>
        </div>

        <p>Su pedido estará disponible para recoger en nuestras instalaciones o será enviado según las condiciones acordadas.</p>
        
        <p>Si tiene alguna pregunta o necesita más información, no dude en contactarnos.</p>

        <p>¡Gracias por confiar en Modas Boom!</p>
    </div>

    <div class="footer">
        <h3>Modas Boom</h3>
        <p>Su tienda de confianza en moda y estilo</p>
        <p>📧 {{ config('mail.from.address') }} | 📱 WhatsApp disponible</p>
    </div>
</body>
</html>