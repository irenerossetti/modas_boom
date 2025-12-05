@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-boom-text-dark">
                <i class="fas fa-calendar-alt mr-2"></i>
                Calendario de Entregas
            </h1>
            <p class="text-sm text-gray-600 mt-1">Visualiza las fechas de entrega programadas de los pedidos</p>
        </div>
        <a href="{{ route('pedidos.index') }}" 
            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
            <i class="fas fa-list mr-2"></i> Ver Lista
        </a>
    </div>

    <!-- Leyenda de colores -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Leyenda de Estados:</h3>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center">
                <div class="w-4 h-4 rounded" style="background-color: #3b82f6;"></div>
                <span class="ml-2 text-sm text-gray-600">En proceso</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded" style="background-color: #eab308;"></div>
                <span class="ml-2 text-sm text-gray-600">Asignado</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded" style="background-color: #f97316;"></div>
                <span class="ml-2 text-sm text-gray-600">En producción</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded" style="background-color: #22c55e;"></div>
                <span class="ml-2 text-sm text-gray-600">Terminado</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 rounded" style="background-color: #a855f7;"></div>
                <span class="ml-2 text-sm text-gray-600">Entregado</span>
            </div>
        </div>
    </div>

    <!-- Calendario -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div id="calendar"></div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/es.global.min.js'></script>

<!-- Tippy.js para tooltips -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/dist/tippy.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            list: 'Lista'
        },
        height: 'auto',
        events: '{{ route('pedidos.calendar-json') }}',
        eventClick: function(info) {
            // Prevenir navegación por defecto
            info.jsEvent.preventDefault();
            
            // Abrir en nueva pestaña
            if (info.event.url) {
                window.open(info.event.url, '_blank');
            }
        },
        eventDidMount: function(info) {
            // Crear tooltip con información del pedido
            tippy(info.el, {
                content: `
                    <div class="text-left">
                        <div class="font-bold mb-2">${info.event.title}</div>
                        <div class="text-sm">
                            <div><strong>Estado:</strong> ${info.event.extendedProps.estado}</div>
                            <div><strong>Total:</strong> ${info.event.extendedProps.total}</div>
                            <div><strong>Cliente:</strong> ${info.event.extendedProps.cliente}</div>
                        </div>
                        <div class="text-xs mt-2 text-gray-400">Click para ver detalles</div>
                    </div>
                `,
                allowHTML: true,
                theme: 'light',
                placement: 'top',
                arrow: true,
                animation: 'scale',
            });
        },
        eventMouseEnter: function(info) {
            info.el.style.cursor = 'pointer';
            info.el.style.transform = 'scale(1.05)';
            info.el.style.transition = 'transform 0.2s';
        },
        eventMouseLeave: function(info) {
            info.el.style.transform = 'scale(1)';
        },
        // Personalización de estilos
        eventClassNames: function(arg) {
            return ['shadow-md', 'rounded', 'px-2', 'py-1'];
        },
        dayCellClassNames: function(arg) {
            return ['hover:bg-gray-50'];
        }
    });
    
    calendar.render();
});
</script>

<style>
    /* Estilos personalizados para el calendario */
    #calendar {
        font-family: inherit;
    }
    
    .fc-event {
        border: none !important;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .fc-daygrid-event {
        white-space: normal !important;
        align-items: flex-start !important;
    }
    
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 700 !important;
        color: #1f2937;
    }
    
    .fc-button {
        background-color: #4f46e5 !important;
        border-color: #4f46e5 !important;
        text-transform: capitalize !important;
    }
    
    .fc-button:hover {
        background-color: #4338ca !important;
        border-color: #4338ca !important;
    }
    
    .fc-button-active {
        background-color: #3730a3 !important;
        border-color: #3730a3 !important;
    }
    
    .fc-day-today {
        background-color: #fef3c7 !important;
    }
    
    /* Tooltip personalizado */
    .tippy-box[data-theme~='light'] {
        background-color: white;
        color: #1f2937;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem;
    }
    
    .tippy-box[data-theme~='light'][data-placement^='top'] > .tippy-arrow::before {
        border-top-color: white;
    }
</style>
@endsection
