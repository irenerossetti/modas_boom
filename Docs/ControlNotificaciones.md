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

Detalles de uso
- La vista (`Control de Notificaciones`) está disponible en la Barra Lateral para usuarios con rol Administrador.
- La UI usa la `NOTIFICATIONS_URL_BASE` del `.env` como base para construir las llamadas a la API.
  - Nota: el endpoint `/qr` puede devolver un string base64 plano o un objeto JSON con `{ "qr": "<base64>" }`. La UI ahora detecta ambos formatos y muestra el QR correctamente.
    - Para ver tu QR: abre la página `Control de Notificaciones` y mira la sección "QR" (imagen y botones). Después de generar o refrescar el QR, la imagen se mostrará debajo del título "QR". Si la imagen no aparece, vuelve a refrescar la página o usa el botón "Refrescar QR".
    - Si recibes un error 419 al hacer POST a `/admin/notificaciones/generate-qr`, recarga la página (token CSRF expirado). La UI incluye la cabecera `X-CSRF-TOKEN` y envía cookies por defecto, así que la recarga solucionará la mayoría de los casos.
- El envío de mensajes y archivos es realizado por la UI a la base URL; ten en cuenta CORS: si la API se encuentra en otro dominio, dicha API debe permitir solicitudes desde el dominio del sistema (o, alternativamente, puedes configurar un proxy en el servidor para reenviar las peticiones desde el backend para evitar CORS).

Notas de seguridad y operacionales
- Las peticiones a `send`, `send-file`, `delete-session` u otras rutas que modifican el estado requieren que la API de notificaciones valide que quien realiza la petición esté autorizado. Normalmente el servicio espera algún token o autenticación — en caso necesario, añade un proxy server side para inyectar tokens y mantener secretos fuera del navegador.
- Para grandes volúmenes de mensajes o archivos, se recomienda implementar colas en el server de notificaciones para evitar tiempos de espera y procesamientos bloqueantes.
