## Recibos PDF - Solución de problemas de fuentes y caracteres extraños

Si los recibos PDF se muestran con símbolos extraños en lugar de texto legible, pero al copiar/pegar el texto en un editor aparece correctamente, lo más probable es que el visualizador esté sustituyendo o no tenga la fuente embebida.

Pasos recomendados para asegurar que DomPDF embeba la fuente DejaVu y el PDF se vea correctamente en los visores:

1. Ejecutar el comando para copiar las fuentes de DomPDF al directorio de fuentes del proyecto:

```bash
php artisan dompdf:publish-fonts
```

2. Limpiar caches (opcional pero recomendado):

```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

3. Verificar que `config/dompdf.php` contenga las siguientes opciones:

- `'font_dir' => storage_path('fonts')`
- `'font_cache' => storage_path('fonts')`
- `'default_font' => 'DejaVu Sans'`
- `'enable_font_subsetting' => true`

4. Verificar que `resources/views/pagos/recibo.blade.php` haga referencia a la fuente DejaVu (ahora se usa automáticamente desde `storage/fonts`)

5. Reiniciar servidor de desarrollo o puerto (si aplica):

```bash
php artisan serve
```

6. Emitir un recibo de prueba desde el panel o ruta: `GET /pagos/recibo/{id}` y abrirlo en su visor de PDF preferido (Adobe Reader, evince, Chrome). Si aún se ven símbolos extraños, pruebe con Adobe Reader, que suele respetar mejor las embebidas.

7. Si usas un entorno local, puedes generar el recibo inline (en lugar de descargarlo) con la ruta de debug para verificar que el navegador muestra correctamente la fuente:

```bash
GET /debug/pagos/{id}/recibo/stream
```

Accede a esta ruta mientras estás autenticado como administrador para renderizar el PDF en el navegador en lugar de descargarlo.

Si después de estos pasos el problema persiste:

- Asegúrese de que la fuente DejaVu exista en `storage/fonts/DejaVuSans.ttf`.
- Si su cliente PDF no muestra bien la fuente, revise si la fuente embebida se ve bien abriendo el PDF en Adobe Reader y comprobando propiedades del documento (File->Properties->Fonts)
- Por último, si necesita soporte adicional en un entorno de CI o producción con Postgres, intente replicar el entorno local exacto (fonts y `config/dompdf.php`) en ese entorno.
