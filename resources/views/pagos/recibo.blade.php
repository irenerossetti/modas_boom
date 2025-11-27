<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recibo de Pago #{{ $pago->id }}</title>
    <style>
        /* Incluir la fuente DejaVu embebida para DomPDF */
        @font-face {
            font-family: 'DejaVu Sans';
            font-style: normal;
            font-weight: 400;
            /* Prefer project's storage fonts if present for config/dompdf font_dir; otherwise fallback to vendor dompdf fonts.
               Use normalized paths for Windows */
            @php
                $storageDejaVu = str_replace('\\', '/', storage_path('fonts/DejaVuSans.ttf'));
                $vendorDejaVu = str_replace('\\', '/', base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf'));
                $fontPath = file_exists($storageDejaVu) ? $storageDejaVu : $vendorDejaVu;
            @endphp
            src: url('{{ $fontPath }}') format('truetype');
        }
        body { font-family: 'DejaVu Sans', DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { margin: 0 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #ddd; }
    </style>
    </head>
<body>
    <div class="header">
        <h2>Recibo de Pago - Modas Boom</h2>
        <p>Recibo #{{ $pago->id }} — Pedido #{{ $pago->id_pedido }}</p>
    </div>
    <div class="content">
        <h3>Datos del cliente</h3>
        <p>{{ $pago->pedido->cliente->nombre }} {{ $pago->pedido->cliente->apellido }}</p>
        <p>Email: {{ $pago->pedido->cliente->email }}</p>
        <hr>
        <h3>Detalle del pago</h3>
        <table>
            <tr><th>Fecha</th><td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td></tr>
            <tr><th>Monto</th><td>Bs. {{ number_format($pago->monto, 2) }}</td></tr>
            <tr><th>Método</th><td>{{ $pago->metodo }}</td></tr>
            <tr><th>Referencia</th><td>{{ $pago->referencia }}</td></tr>
            <tr><th>Registrado por</th><td>{{ $pago->registradoPor->nombre ?? 'Sistema' }}</td></tr>
        </table>
        <hr>
        <p>Gracias por su pago. Este recibo digital es válido como comprobante.</p>
    </div>
    <div class="footer">Modas Boom — {{ now()->format('Y') }}</div>
</body>
</html>
