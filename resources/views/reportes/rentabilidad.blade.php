<!doctype html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Rentabilidad</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .content { margin: 0 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 6px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #3b82f6; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 20px; }
        .btn:hover { background-color: #2563eb; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Rentabilidad por Producto</h2>
        <p>Fecha: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    
    <div class="content">
        @if(!request()->has('download'))
            <div style="text-align: right;">
                <a href="{{ route('reportes.rentabilidad', ['download' => 'pdf']) }}" class="btn">Descargar PDF</a>
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-right">Precio Venta</th>
                    <th class="text-right">Costo Prod.</th>
                    <th class="text-right">Margen (Bs.)</th>
                    <th class="text-right">Margen (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                    <tr>
                        <td>{{ $item['nombre'] }}</td>
                        <td>{{ $item['categoria'] }}</td>
                        <td class="text-right">{{ number_format($item['precio'], 2) }}</td>
                        <td class="text-right">{{ number_format($item['costo'], 2) }}</td>
                        <td class="text-right">{{ number_format($item['margen'], 2) }}</td>
                        <td class="text-right">{{ number_format($item['margen_porcentaje'], 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
