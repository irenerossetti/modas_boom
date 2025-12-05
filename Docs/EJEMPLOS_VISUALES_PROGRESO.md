# 🎨 Ejemplos Visuales - Componente de Progreso

## Vista Previa de Diseños

### 1. Dashboard del Cliente - Card Grande

```blade
<div class="p-4 bg-gradient-to-br from-gray-50 to-white rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
    <!-- Header del pedido -->
    <div class="flex items-center justify-between mb-3">
        <div>
            <p class="font-bold text-gray-800 text-lg">Pedido #1234</p>
            <p class="text-sm text-gray-500">04/12/2025</p>
        </div>
        <div class="text-right">
            <p class="text-lg font-bold text-gray-800">Bs. 450</p>
            <a href="#" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                Ver detalles →
            </a>
        </div>
    </div>
    
    <!-- Barra de progreso -->
    <x-pedido-progress :estado="'En producción'" />
</div>
```

**Resultado Visual:**
```
┌─────────────────────────────────────────────────────────┐
│  Pedido #1234                           Bs. 450         │
│  04/12/2025                             Ver detalles →  │
│                                                         │
│  ●━━━━●━━━━●━━━━○━━━━○                                │
│  En proceso  Asignado  En producción  Terminado  Entregado │
└─────────────────────────────────────────────────────────┘
```

---

### 2. Lista de Pedidos - Versión Compacta

```blade
<div class="space-y-3">
    <div class="flex items-center justify-between p-3 bg-white rounded-lg border hover:shadow-md transition-shadow">
        <div class="flex-1 mr-4">
            <p class="font-semibold text-gray-800">Pedido #1234</p>
            <x-pedido-progress-compact :estado="'Asignado'" />
        </div>
        <div class="text-right">
            <p class="font-bold text-gray-800">Bs. 450</p>
            <a href="#" class="text-xs text-blue-600">Ver →</a>
        </div>
    </div>
</div>
```

**Resultado Visual:**
```
┌────────────────────────────────────────────┐
│  Pedido #1234                   Bs. 450   │
│  ▓▓▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░░░  2/5       │
│  Asignado                       Ver →     │
└────────────────────────────────────────────┘
```

---

### 3. Tabla Responsive

```blade
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedido</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progreso</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">#1234</div>
                    <div class="text-sm text-gray-500">04/12/2025</div>
                </td>
                <td class="px-6 py-4">
                    <x-pedido-progress-compact :estado="'Terminado'" />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-bold text-gray-900">Bs. 450</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <a href="#" class="text-blue-600 hover:text-blue-900">Ver</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
```

---

### 4. Modal de Detalle

```blade
<div class="bg-white rounded-2xl shadow-2xl p-8 max-w-2xl mx-auto">
    <div class="text-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Pedido #1234</h2>
        <p class="text-gray-500">Creado el 04/12/2025</p>
    </div>
    
    <!-- Progreso destacado -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
            Estado de tu Pedido
        </h3>
        <x-pedido-progress :estado="'En producción'" />
    </div>
    
    <!-- Información adicional -->
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Total</p>
            <p class="text-2xl font-bold text-gray-800">Bs. 450</p>
        </div>
        <div class="text-center p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Entrega Estimada</p>
            <p class="text-lg font-semibold text-gray-800">15/12/2025</p>
        </div>
    </div>
    
    <div class="text-center">
        <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg">
            Ver Detalles Completos
        </button>
    </div>
</div>
```

---

### 5. Notificación/Toast

```blade
<div class="fixed bottom-4 right-4 bg-white rounded-lg shadow-2xl p-4 max-w-sm border-l-4 border-green-500 animate-slide-in">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-gray-900">
                ¡Tu pedido avanzó!
            </p>
            <p class="text-sm text-gray-500 mb-2">
                Pedido #1234 ahora está en producción
            </p>
            <x-pedido-progress-compact :estado="'En producción'" />
        </div>
    </div>
</div>
```

---

### 6. Timeline Vertical (Alternativa)

```blade
<div class="space-y-4">
    @php
        $estados = [
            ['nombre' => 'En proceso', 'completado' => true, 'fecha' => '01/12/2025 10:30'],
            ['nombre' => 'Asignado', 'completado' => true, 'fecha' => '02/12/2025 14:15'],
            ['nombre' => 'En producción', 'completado' => true, 'fecha' => '03/12/2025 09:00'],
            ['nombre' => 'Terminado', 'completado' => false, 'fecha' => null],
            ['nombre' => 'Entregado', 'completado' => false, 'fecha' => null],
        ];
    @endphp
    
    @foreach($estados as $index => $estado)
        <div class="flex items-start">
            <!-- Línea vertical -->
            @if(!$loop->last)
                <div class="absolute left-5 top-10 h-16 w-0.5 {{ $estado['completado'] ? 'bg-green-500' : 'bg-gray-200' }}"></div>
            @endif
            
            <!-- Icono -->
            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center z-10
                {{ $estado['completado'] ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                @if($estado['completado'])
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @else
                    <span class="text-sm font-bold">{{ $index + 1 }}</span>
                @endif
            </div>
            
            <!-- Contenido -->
            <div class="ml-4 flex-1">
                <h4 class="font-semibold {{ $estado['completado'] ? 'text-gray-800' : 'text-gray-400' }}">
                    {{ $estado['nombre'] }}
                </h4>
                @if($estado['fecha'])
                    <p class="text-sm text-gray-500">{{ $estado['fecha'] }}</p>
                @else
                    <p class="text-sm text-gray-400">Pendiente</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
```

---

### 7. Card con Animación de Pulso

```blade
<div class="bg-white rounded-xl shadow-lg p-6 relative overflow-hidden">
    <!-- Efecto de brillo animado -->
    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-20 animate-shimmer"></div>
    
    <div class="relative z-10">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-xl font-bold text-gray-800">Pedido #1234</h3>
                <p class="text-sm text-gray-500">En proceso activo</p>
            </div>
            <div class="relative">
                <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-75"></div>
                <div class="relative bg-green-500 text-white rounded-full w-12 h-12 flex items-center justify-center font-bold">
                    3/5
                </div>
            </div>
        </div>
        
        <x-pedido-progress :estado="'En producción'" />
        
        <div class="mt-4 text-center">
            <p class="text-sm text-gray-600">
                Estimado de entrega: <span class="font-semibold">5 días</span>
            </p>
        </div>
    </div>
</div>

<style>
@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}
.animate-shimmer {
    animation: shimmer 2s infinite;
}
</style>
```

---

### 8. Grid de Pedidos (Dashboard Admin)

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($pedidos as $pedido)
        <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-xl transition-shadow">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <h4 class="font-bold text-gray-800">#{{ $pedido->id_pedido }}</h4>
                    <p class="text-xs text-gray-500">{{ $pedido->cliente->nombre }}</p>
                </div>
                <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded">
                    Bs. {{ number_format($pedido->total, 0) }}
                </span>
            </div>
            
            <x-pedido-progress-compact :estado="$pedido->estado" />
            
            <div class="mt-3 flex justify-between items-center">
                <span class="text-xs text-gray-500">
                    {{ $pedido->created_at->diffForHumans() }}
                </span>
                <a href="#" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Ver →
                </a>
            </div>
        </div>
    @endforeach
</div>
```

---

## 🎨 Paleta de Colores Usada

- **Verde (Completado)**: `bg-green-500`, `text-green-600`
- **Amarillo (En proceso)**: `bg-yellow-100`, `text-yellow-800`
- **Azul (Asignado)**: `bg-blue-100`, `text-blue-800`
- **Púrpura (En producción)**: `bg-purple-100`, `text-purple-800`
- **Gris (Pendiente)**: `bg-gray-200`, `text-gray-400`

---

## 📱 Breakpoints Responsive

- **Mobile**: `< 640px` - Iconos compactos
- **Tablet**: `640px - 1024px` - Barra completa
- **Desktop**: `> 1024px` - Barra completa con nombres

---

## ✨ Animaciones Incluidas

1. **Transiciones suaves**: `transition-all duration-300`
2. **Hover effects**: `hover:shadow-lg`
3. **Ring en paso actual**: `ring-4 ring-green-200`
4. **Escala en paso actual**: `scale-110`
5. **Gradientes**: `bg-gradient-to-r from-green-400 to-green-600`

---

**Última actualización**: 4 de diciembre de 2025
