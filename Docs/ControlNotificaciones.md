# Control de Notificaciones

Esta vista permite al administrador monitorear y controlar la conexión del servicio de notificaciones (p. ej. un servicio que actúe como puerta a WhatsApp) y realizar acciones como:

- Ver estado (/status)
- Ver la información del número/usuario (/me)
- Visualizar el QR actual (/qr) y solicitar su generación (/generate-qr)
- Eliminar la sesión (/delete-session)
- Listar chats (/chats) y ver mensajes por chat (/chats/{jid})
- Enviar mensajes de texto (/send)
- Enviar archivos (/send-file)
- Bloquear / desbloquear un contacto (/block)
- Rechazar una llamada entrante (/reject-call) - eliminada: el servicio de notificaciones (Baileys) bloquea las llamadas por defecto, por lo que no es necesario exponer esta ruta.

Requisitos
- Copia la variable de entorno en tu `.env` (puedes usar `NOTIFICATIONS_URL_BASE`):
  - `NOTIFICATIONS_URL_BASE=https://notificaciones.example.com/api`
  - `NOTIFICATIONS_API_KEY` (opcional): si tu servidor de notificaciones requiere una API key, configúrala en este env var.

Detalles de uso
- La vista (`Control de Notificaciones`) está disponible en la Barra Lateral para usuarios con rol Administrador.
- La UI usa la `NOTIFICATIONS_URL_BASE` del `.env` como base para construir las llamadas a la API.
  - Nota: el endpoint `/qr` puede devolver un string base64 plano o un objeto JSON con `{ "qr": "<base64>" }`. La UI ahora detecta ambos formatos y muestra el QR correctamente.
    - Para ver tu QR: abre la página `Control de Notificaciones` y mira la sección "QR" (imagen y botones). Después de generar o refrescar el QR, la imagen se mostrará debajo del título "QR". Si la imagen no aparece, vuelve a refrescar la página o usa el botón "Refrescar QR".
    - Si recibes un error 419 al hacer POST a `/admin/notificaciones/generate-qr`, recarga la página (token CSRF expirado). La UI incluye la cabecera `X-CSRF-TOKEN` y envía cookies por defecto, así que la recarga solucionará la mayoría de los casos.
- El envío de mensajes y archivos es realizado por la UI a la base URL; ten en cuenta CORS: si la API se encuentra en otro dominio, dicha API debe permitir solicitudes desde el dominio del sistema (o, alternativamente, puedes configurar un proxy en el servidor para reenviar las peticiones desde el backend para evitar CORS).

Configuración del servidor para notificaciones automáticas:
- NOTIFICATIONS_URL_BASE: URL base del servicio de notificaciones (Baileys/Twilio proxy) — obligatorio si quieres que el sistema envíe notificaciones automáticas desde el backend.
- NOTIFICATIONS_API_KEY: (opcional) llave para autenticar requests server-to-server con el servicio de notificaciones.
 - NOTIFICATIONS_ENABLED: true/false — Si `false`, las notificaciones backend se simulan (útil para entornos de testing o staging). Por defecto `true`.

Notas de seguridad y operacionales
- Las peticiones a `send`, `send-file`, `delete-session` u otras rutas que modifican el estado requieren que la API de notificaciones valide que quien realiza la petición esté autorizado. Normalmente el servicio espera algún token o autenticación — en caso necesario, añade un proxy server side para inyectar tokens y mantener secretos fuera del navegador.
- Para grandes volúmenes de mensajes o archivos, se recomienda implementar colas en el server de notificaciones para evitar tiempos de espera y procesamientos bloqueantes.

## Notificaciones automáticas (resumen)

El sistema envía automáticamente notificaciones por WhatsApp (vía el servicio de notificaciones configurado) al cliente en los siguientes eventos:

- Cuando se crea un pedido (tanto desde `clienteStore` como por `empleadoStore` o `store`): se envía una confirmación con el número de pedido, lista de productos (si aplica), total, y fecha de creación.
- Cuando cambia el estado del pedido: el cliente recibe actualizaciones de estado (Incluye: "En proceso", "Asignado", "En producción", "Terminado", "Entregado", "Cancelado").
- Cuando se registra avance de producción: el cliente recibe el porcentaje de avance y la etapa asociada.
- Cuando se programa o vuelve a programar la fecha de entrega: el cliente recibe la fecha programada o la reprogramación con el motivo.
- Cuando el administrador registra una devolución (p. ej. de pantalones): el cliente recibe la prenda, cantidad y motivo de devolución.

> Nota: el envío real de mensajes depende de la configuración del servicio (p. ej. Baileys o Twilio). En entorno de desarrollo las notificaciones se simulan y se registran en el log.
