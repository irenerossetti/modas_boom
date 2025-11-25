<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Entregado - Modas Boom</title>
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
            background-color: #059669;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f0fdf4;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .pedido-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #059669;
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
        .success-icon {
            font-size: 48px;
            color: #059669;
            text-align: center;
            margin: 20px 0;
        }
        .feedback-section {
            background-color: #fef3c7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Pedido Entregado Exitosamente!</h1>
        <p>Pedido #{{ $pedido->id_pedido }}</p>
    </div>

    <div class="content">
        <div class="success-icon">✅</div>
        
        <p>Estimado/a <strong>{{ $cliente->nombre }}</strong>,</p>
        
        <p>¡Excelente noticia! Su pedido ha sido <strong>entregado exitosamente</strong>.</p>

        <div class="pedido-info">
            <h3>📋 Información del Pedido</h3>
            <p><strong>Número de pedido:</strong> #{{ $pedido->id_pedido }}</p>
            <p><strong>Fecha de pedido:</strong> {{ $pedido->created_at->format('d/m/Y') }}</p>
            <p><strong>Fecha de entrega:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            <p><strong>Estado:</strong> <span style="color: #059669; font-weight: bold;">{{ $pedido->estado }}</span></p>
        </div>

        <div class="productos">
            <h3>🛍️ Productos entregados</h3>
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

        <div class="feedback-section">
            <h3>💭 Su opinión es importante</h3>
            <p>Esperamos que esté completamente satisfecho/a con su compra. Su feedback nos ayuda a mejorar nuestros productos y servicios.</p>
            <p>Si tiene algún comentario o sugerencia, no dude en contactarnos.</p>
        </div>

        <p>Gracias por elegir Modas Boom. ¡Esperamos verle pronto para su próxima compra!</p>
    </div>

    <div class="footer">
        <h3>Modas Boom</h3>
        <p>Su tienda de confianza en moda y estilo</p>
        <p>📧 {{ config('mail.from.address') }} | 📱 WhatsApp disponible</p>
        <p><small>¿Necesita ayuda? Contáctenos en cualquier momento</small></p>
    </div>
</body>
</html>