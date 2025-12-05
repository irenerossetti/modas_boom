# 📊 Componente de Progreso de Pedidos

## Descripción

Componentes Blade para visualizar el estado de progreso de los pedidos de forma intuitiva y atractiva.

## Estados Soportados

1. **En proceso** - Pedido recibido y en espera
2. **Asignado** - Pedido asignado a un operario
3. **En producción** - Pedido en fabricación
4. **Terminado** - Pedido completado
5. **Entregado** - Pedido entregado al cliente

---

## 🎨 Componente Principal: `pedido-progress`

### Uso Básico

```blade
<x-pedido-progress :estado="$pedido->estado" />
```

### Características

- ✅ Barra de progreso horizontal con 5 pasos
- ✅ Iconos SVG para cada etapa
- ✅ Animaciones suaves con Tailwind
- ✅ Responsive (versión desktop y mobile)
- ✅ Resalta el paso actual con ring y escala
- ✅ Pasos completados en verde

### Ejemplo en Dashboard

```blade
<div class="bg-white p-4 rounded-xl shadow-md">
    <div class="flex justify-between mb-3">
        <div>
            <h3 class="font-bold">Pedido #{{ $pedido->id_pedido }}</h3>
            <p class="text-sm text-gray-500">{{ $pedido->created_at->format('d/m/Y') }}</p>
        </div>
        <div class="text-right">
            <p class="font-bold">Bs. {{ number_format($pedido->total, 0) }}</p>
        </div>
    </div>
    
    <x-pedido-progress :estado="$pedido->estado" />
</div>
```

---

## 📏 Componente Compacto: `pedido-progress-compact`

### Uso Básico

```blade
<x-pedido-progress-compact :estado="$pedido->estado" />
```

### Características

- ✅ Barra de progreso simple con porcentaje
- ✅ Indicador numérico (ej: 3/5)
- ✅ Texto del estado con color
- ✅ Ideal para tablas y espacios reducidos

### Ejemplo en Tabla

```blade
<table class="min-w-full">
    <thead>
        <tr>
            <th>Pedido</th>
            <th>Fecha</th>
            <th>Total</th>
            <th>Progreso</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pedidos as $pedido)
        <tr>
            <td>#{{ $pedido->id_pedido }}</td>
            <td>{{ $pedido->created_at->format('d/m/Y') }}</td>
            <td>Bs. {{ number_format($pedido->total, 0) }}</td>
            <td>
                <x-pedido-progress-compact :estado="$pedido->estado" />
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

---

## 🎯 Ejemplos de Uso

### 1. Dashboard del Cliente

```blade
@foreach($pedidos_recientes as $pedido)
    <div class="p-4 bg-gray-50 rounded-xl">
        <div class="flex justify-between mb-3">
            <div>
                <p class="font-bold">Pedido #{{ $pedido->id_pedido }}</p>
                <p class="text-sm text-gray-500">{{ $pedido->created_at->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="font-bold">Bs. {{ number_format($pedido->total, 0) }}</p>
            </div>
        </div>
        
        <x-pedido-progress :estado="$pedido->estado" />
    </div>
@endforeach
```

### 2. Lista de Pedidos (Versión Compacta)

```blade
<div class="space-y-2">
    @foreach($pedidos as $pedido)
        <div class="flex items-center justify-between p-3 bg-white rounded-lg border">
            <div class="flex-1">
                <p class="font-semibold">Pedido #{{ $pedido->id_pedido }}</p>
                <x-pedido-progress-compact :estado="$pedido->estado" />
            </div>
            <div class="ml-4">
                <p class="font-bold">Bs. {{ number_format($pedido->total, 0) }}</p>
            </div>
        </div>
    @endforeach
</div>
```

### 3. Detalle de Pedido

```blade
<div class="bg-white p-6 rounded-xl shadow-lg">
    <h2 class="text-2xl font-bold mb-4">Estado del Pedido</h2>
    
    <x-pedido-progress :estado="$pedido->estado" />
    
    <div class="mt-6 text-center">
        <p class="text-gray-600">
            Tu pedido está actualmente en: 
            <span class="font-bold text-green-600">{{ $pedido->estado }}</span>
        </p>
    </div>
</div>
```

### 4. Card de Pedido con Hover

```blade
<div class="bg-white p-4 rounded-xl shadow-md hover:shadow-xl transition-shadow cursor-pointer">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h3 class="font-bold text-lg">Pedido #{{ $pedido->id_pedido }}</h3>
            <p class="text-sm text-gray-500">{{ $pedido->created_at->diffForHumans() }}</p>
        </div>
        <div class="text-right">
            <p class="text-xl font-bold text-gray-800">
                Bs. {{ number_format($pedido->total, 0) }}
            </p>
        </div>
    </div>
    
    <x-pedido-progress :estado="$pedido->estado" />
    
    <div class="mt-3 text-right">
        <a href="{{ route('pedidos.show', $pedido->id_pedido) }}" 
           class="text-sm text-blue-600 hover:text-blue-800 font-medium">
            Ver detalles →
        </a>
    </div>
</div>
```

---

## 🎨 Personalización

### Cambiar Colores

Edita el archivo `resources/views/components/pedido-progress.blade.php`:

```blade
<!-- Cambiar color de pasos completados -->
<div class="... {{ $isCompleted ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-400' }}">

<!-- Cambiar color del ring del paso actual -->
<div class="... {{ $isCurrent ? 'ring-4 ring-blue-200' : '' }}">
```

### Agregar Tooltips

```blade
<div class="... relative group">
    {!! $step['icono'] !!}
    
    <!-- Tooltip -->
    <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap">
        {{ $step['nombre'] }}
    </div>
</div>
```

---

## 📱 Responsive

El componente principal incluye dos versiones:

- **Desktop** (`hidden sm:block`): Muestra todos los pasos con nombres
- **Mobile** (`block sm:hidden`): Muestra iconos compactos con estado debajo

---

## 🚀 Mejoras Futuras

- [ ] Agregar fechas estimadas para cada paso
- [ ] Mostrar tiempo transcurrido en cada etapa
- [ ] Animación de pulso en el paso actual
- [ ] Notificaciones cuando cambia el estado
- [ ] Integración con WebSockets para actualización en tiempo real

---

## 📝 Notas

- Los componentes usan Tailwind CSS
- Las animaciones son suaves gracias a `transition-all duration-300`
- Los iconos son SVG de Heroicons
- Compatible con Laravel 10+

---

**Última actualización**: 4 de diciembre de 2025  
**Autor**: Equipo de Desarrollo  
**Versión**: 1.0
