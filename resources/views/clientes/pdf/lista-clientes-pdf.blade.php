<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; }
        th { background: #f3f4f6; }
        h1 { font-size: 18px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h1>Lista de Clientes</h1>
    @if(isset($warning))
        <p style="color: red; font-size: 14px;">{{ $warning }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre Completo</th>
                <th>CI/NIT</th>
                <th>Email</th>
                <th>Teléfono</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $index => $cliente)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $cliente->nombre }} {{ $cliente->apellido }}</td>
                    <td>{{ $cliente->ci_nit }}</td>
                    <td>{{ $cliente->email ?? 'N/A' }}</td>
                    <td>{{ $cliente->telefono ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>