<!doctype html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Recibo Consolidado - {{ $cliente->nombre }} {{ $cliente->apellido }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { margin: 0 20px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-row td { font-weight: bold; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Recibo Consolidado - Modas Boom</h2>
        <p>Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</p>
        @if(isset($periodo) && $periodo)
            <p><strong>Periodo:</strong> {{ $periodo }}</p>
        @endif
    </div>
    <div class="content">
        <h3>Datos del Cliente</h3>
        <p><strong>Cliente:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
        <p><strong>Email:</strong> {{ $cliente->email }}</p>
        <p><strong>CI/NIT:</strong> {{ $cliente->ci_nit }}</p>
        <hr>
        <h3>Detalle de Pagos</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Recibo #</th>
                    <th>Pedido #</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th style="text-align: right;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $pago)
                    <tr>
                        <td>{{ $pago->fecha_pago->format('d/m/Y H:i') }}</td>
                        <td>{{ $pago->id }}</td>
                        <td>{{ $pago->id_pedido }}</td>
                        <td>{{ $pago->metodo }}</td>
                        <td>{{ $pago->referencia }}</td>
                        <td style="text-align: right;">Bs. {{ number_format($pago->monto, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" style="text-align: right;">TOTAL PAGADO:</td>
                    <td style="text-align: right;">Bs. {{ number_format($pagos->sum('monto'), 2) }}</td>
                </tr>
            </tbody>
        </table>
        <p style="margin-top: 20px;">Este documento consolida los pagos realizados en el periodo seleccionado.</p>
    </div>
    <div class="footer">Modas Boom — {{ now()->format('Y') }}</div>
</body>
</html>
