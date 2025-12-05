# 🔌 Circuit Breaker Pattern - Servicio de Notificaciones

## 📋 Resumen

El `NotificationProxyController` implementa un patrón **Circuit Breaker** para manejar fallos del servicio externo de notificaciones sin afectar el flujo principal de la aplicación.

## 🎯 Objetivo

Permitir que operaciones críticas (crear pedidos, registrar pagos, etc.) continúen funcionando **incluso cuando el servicio de WhatsApp/notificaciones esté caído**.

## ⚙️ Configuración

### Timeouts Implementados
- **Connection Timeout**: 2 segundos
- **Request Timeout**: 2 segundos
- **Retry**: 1 intento adicional con 100ms de delay
- **Tiempo máximo total**: ~2.1 segundos

### Variables de Entorno
```env
NOTIFICATIONS_URL_BASE=http://localhost:3000
```

## 🛡️ Comportamiento ante Fallos

### Cuando el servicio está caído:

1. **No lanza excepciones** al código que lo llama
2. **Registra el error** en los logs de Laravel
3. **Retorna respuesta JSON** con estructura predecible:

```json
{
  "success": false,
  "error": "notifications_unavailable",
  "message": "Servicio de notificaciones no disponible temporalmente, pero el proceso continuó",
  "code": 503,
  "_debug": {
    "method": "POST",
    "url": "http://localhost:3000/send",
    "reason": "Connection timeout or refused",
    "timestamp": "2025-12-04T10:30:00Z"
  }
}
```

4. **El flujo principal continúa** sin interrupciones

## 📝 Tipos de Errores Manejados

| Error | Causa | Respuesta |
|-------|-------|-----------|
| `ConnectionException` | Servicio no responde, timeout | Fail silently (503) |
| `RequestException` | Error en la petición HTTP | Fail silently (503) |
| HTTP 4xx/5xx | Servicio responde con error | Fail silently (503) |
| `Throwable` | Cualquier otro error inesperado | Fail silently (503) |

## 🔍 Logging

Todos los errores se registran en `storage/logs/laravel.log`:

```php
// Ejemplo de log
[2025-12-04 10:30:00] local.ERROR: NotificationProxyController [POST]: Connection failed to http://localhost:3000/send
{
  "error": "Connection timeout",
  "endpoint": "/send",
  "payload_keys": ["to", "message"]
}
```

## 💡 Uso en Controladores

### ❌ Antes (sin Circuit Breaker)
```php
// Si el servicio falla, toda la operación falla
$pedido = Pedido::create($data);
$this->whatsAppService->enviarNotificacion($cliente->telefono, $mensaje);
// ⚠️ Si WhatsApp falla aquí, el pedido se crea pero el usuario ve error 500
```

### ✅ Ahora (con Circuit Breaker)
```php
// El pedido se crea siempre, la notificación es "best effort"
$pedido = Pedido::create($data);

$resultado = $this->whatsAppService->enviarNotificacion($cliente->telefono, $mensaje);

if (!$resultado['success']) {
    // Opcional: mostrar mensaje al usuario
    session()->flash('warning', 'Pedido creado, pero no se pudo enviar notificación WhatsApp');
}
// ✅ El flujo continúa normalmente
```

## 🧪 Testing

Ejecutar tests del Circuit Breaker:

```bash
php artisan test tests/Feature/NotificationProxyFailSilentlyTest.php
```

### Casos de Prueba Cubiertos:
- ✅ Servicio no disponible (503)
- ✅ Connection timeout
- ✅ Servicio disponible (200)
- ✅ Timeout de 2 segundos se respeta

## 📊 Monitoreo

### Verificar logs de fallos:
```bash
# Windows
type storage\logs\laravel.log | findstr "NotificationProxyController"

# Linux/Mac
tail -f storage/logs/laravel.log | grep "NotificationProxyController"
```

### Métricas recomendadas:
- Tasa de fallos del servicio de notificaciones
- Tiempo promedio de respuesta
- Número de timeouts por día

## 🔧 Troubleshooting

### El servicio siempre falla
1. Verificar que `NOTIFICATIONS_URL_BASE` esté configurado
2. Verificar que el servicio externo esté corriendo
3. Probar manualmente: `curl http://localhost:3000/status`

### Timeouts muy frecuentes
1. Considerar aumentar timeout a 3-5 segundos (solo si es necesario)
2. Verificar latencia de red
3. Revisar logs del servicio externo

### Logs no aparecen
1. Verificar permisos de `storage/logs/`
2. Verificar configuración de `config/logging.php`
3. Ejecutar: `php artisan cache:clear`

## 🚀 Mejoras Futuras

- [ ] Implementar cache de estado del servicio (evitar intentos si está caído)
- [ ] Cola de reintentos para notificaciones fallidas
- [ ] Dashboard de métricas de disponibilidad
- [ ] Alertas automáticas cuando tasa de fallos > 50%

## 📚 Referencias

- [Circuit Breaker Pattern - Martin Fowler](https://martinfowler.com/bliki/CircuitBreaker.html)
- [Laravel HTTP Client - Timeout](https://laravel.com/docs/11.x/http-client#timeout)
- [Resilient Systems Design](https://aws.amazon.com/builders-library/timeouts-retries-and-backoff-with-jitter/)

---

**Última actualización**: 4 de diciembre de 2025  
**Responsable**: DevOps Team  
**Versión**: 1.0
