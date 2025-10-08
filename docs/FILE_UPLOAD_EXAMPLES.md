# Ejemplos de Uso - API Client con Archivos

## Casos de Uso Comunes

### 1. **Subir Documentos Simples**

**Endpoint:** `POST http://localhost:8000/api/test-files`

**Proceso:**
1. Seleccionar método POST
2. Ingresar URL: `http://localhost:8000/api/test-files`
3. Hacer clic en "Seleccionar Archivos"
4. Elegir archivos (PDF, Word, etc.)
5. Ejecutar petición

**Respuesta esperada:**
```json
{
    "success": true,
    "message": "Archivos subidos exitosamente",
    "data": {
        "files": [...],
        "total_files": 2
    }
}
```

### 2. **Subir con Datos Adicionales**

**Request Body (JSON):**
```json
{
    "project_id": "12345",
    "category": "contracts",
    "urgent": true,
    "metadata": {
        "client": "Empresa ABC",
        "department": "Legal"
    }
}
```

**Archivos:** Contratos en PDF
**Descripción:** "Contratos para revisión legal"

### 3. **Galería de Imágenes**

**Request Body:**
```json
{
    "album_name": "Evento 2025",
    "event_date": "2025-07-16",
    "photographer": "Juan Pérez"
}
```

**Archivos:** JPG, PNG
**Descripción:** "Fotos del evento anual"

### 4. **Respaldo de Datos**

**Request Body:**
```json
{
    "backup_type": "database",
    "scheduled": false,
    "retention_days": 30
}
```

**Archivos:** .sql, .zip
**Descripción:** "Respaldo manual de base de datos"

## Ejemplos de Testing

### Test 1: Archivo Individual
- **Archivo:** `test.txt` (crear un archivo de texto simple)
- **Contenido:** "Este es un archivo de prueba"
- **Esperado:** Upload exitoso

### Test 2: Múltiples Archivos
- **Archivos:** 
  - `imagen.jpg` (cualquier imagen)
  - `documento.pdf` (cualquier PDF)
  - `datos.json` (archivo JSON)
- **Esperado:** Los 3 archivos subidos

### Test 3: Archivo Grande
- **Archivo:** Crear un archivo > 10MB
- **Esperado:** Error de validación

### Test 4: Muchos Archivos
- **Archivos:** Seleccionar más de 10 archivos
- **Esperado:** Error de límite de archivos

## Headers Personalizados Útiles

### Para APIs con Autenticación
```json
{
    "Authorization": "Bearer tu-token-aqui",
    "X-API-Key": "tu-api-key"
}
```

### Para Tracking
```json
{
    "X-Request-ID": "req-12345",
    "X-User-Agent": "API-Client-Test"
}
```

## Endpoints de Gestión

### Listar Archivos Subidos
**GET** `http://localhost:8000/api/files`

**Headers:**
```json
{
    "Authorization": "Bearer your-token"
}
```

### Descargar Archivo
**GET** `http://localhost:8000/api/files/download/nombre-archivo.ext`

### Eliminar Archivo
**DELETE** `http://localhost:8000/api/files/nombre-archivo.ext`

## Integración con Otras APIs

### Ejemplo: Envío a API Externa
```json
{
    "api_endpoint": "https://external-api.com/upload",
    "forward_files": true,
    "callback_url": "https://mi-app.com/callback"
}
```

### Ejemplo: Procesamiento de Archivos
```json
{
    "process_type": "resize_images",
    "resize_width": 800,
    "resize_height": 600,
    "quality": 85
}
```

## Casos de Error Comunes

### Error 422: Validación
```json
{
    "success": false,
    "message": "Error de validación",
    "errors": {
        "files.0": ["El archivo es demasiado grande"]
    }
}
```

### Error 413: Payload Too Large
- Archivo muy grande (>10MB)
- Demasiados archivos

### Error 500: Error de Servidor
- Problemas de permisos en storage
- Espacio insuficiente en disco

## Scripts de Prueba

### Crear Archivo de Texto para Testing
```bash
# Windows PowerShell
"Este es un archivo de prueba con contenido de ejemplo para testing de la API" | Out-File -FilePath "test.txt" -Encoding UTF8
```

### Crear Archivo JSON de Ejemplo
```bash
# Windows PowerShell
'{"nombre": "archivo de prueba", "fecha": "2025-07-16", "version": "1.0"}' | Out-File -FilePath "data.json" -Encoding UTF8
```

### Crear Archivo Binario de Prueba
```bash
# Crear archivo de ~1MB
fsutil file createnew archivo_prueba.bin 1048576
```

## Monitoreo y Logs

### Logs de Laravel
```bash
tail -f storage/logs/laravel.log
```

### Verificar Storage
```bash
ls -la storage/app/public/uploads/
```

### Verificar Enlace Simbólico
```bash
ls -la public/storage
```

## Configuración Avanzada

### Aumentar Límites en PHP
```ini
# php.ini
upload_max_filesize = 20M
post_max_size = 25M
max_file_uploads = 20
```

### Variables de Entorno Laravel
```env
# .env
FILESYSTEM_DISK=public
```

## Mejores Prácticas

1. **Siempre validar archivos en el cliente**
2. **Usar descripciones descriptivas**
3. **Mantener nombres de archivo únicos**
4. **Implementar retry logic para uploads grandes**
5. **Validar tipos MIME en el servidor**
6. **Implementar limpieza de archivos antiguos**
7. **Usar progress indicators para UX**

## Extensiones Futuras

- Integración con AWS S3
- Compresión de imágenes automática
- Vista previa de archivos
- Organización por carpetas
- Versioning de archivos
- Sincronización con servicios cloud
