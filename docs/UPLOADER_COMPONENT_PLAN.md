# Plan de Implementación - Componente Uploader

## 📋 Contexto del Proyecto

**Branch actual**: `uploader`  
**Patrón base**: Componentes UI en `/app/Services/UI/`  
**Framework**: Laravel + Alpine.js/Vanilla JS  
**Estado**: Planificación completa - Pendiente implementación

---

## 🎯 Decisiones de Diseño Acordadas

### 1. Estrategia de Upload
- ✅ **Upload inmediato a backend** (NO almacenar en memoria frontend)
- Archivos se suben inmediatamente a storage temporal
- Queue workers (ya configurados) procesan thumbnails en background
- Usuario confirma uploads para mover archivos a destino final
- Cronjob limpia archivos temporales > 24 horas

**Justificación**: Mejor manejo de archivos grandes, validación temprana en backend, feedback progresivo al usuario.

### 2. Tipos de Archivo Soportados
- **Imágenes**: `image/*` (jpg, png, gif, webp, etc.)
- **Audio**: `audio/*` (mp3, wav, ogg, etc.)
- **Video**: `video/*` (mp4, webm, mov, etc.)
- **Documentos**: `application/pdf`, Word (.doc, .docx), Excel (.xls, .xlsx)

### 3. Arquitectura de Attachments Polimórficos

#### Modelo `Attachment`
- Relación polimórfica: `attachable_type` + `attachable_id`
- Permite asociar archivos a **cualquier modelo** (User, Post, Comment, etc.)
- Sistema de **colecciones** para organizar attachments: `avatar`, `gallery`, `documents`, `media`
- Soporte para **thumbnails** y **conversiones** (diferentes tamaños)
- **Soft deletes** para recuperación

#### Trait `HasAttachments`
Cualquier modelo puede usar attachments agregando:
```php
use App\Models\Traits\HasAttachments;

class User extends Model {
    use HasAttachments;
}
```

Esto proporciona:
- `$user->attachments()` - Todos los attachments
- `$user->attachmentsIn('avatar')` - Attachments de una colección
- `$user->avatar()` - Avatar (primer attachment de colección 'avatar')
- `$user->addAttachmentFromTemp($tempId, 'collection')` - Crear desde temporal
- `$user->replaceAttachment($tempId, 'avatar')` - Reemplazar (útil para avatar único)

#### Caso de Uso Inicial
- **Usuario → Avatar**: Relación polimórfica `users` → `attachments` (collection: 'avatar')
- Fácilmente extensible a: Post → Gallery, Comment → Files, Product → Images, etc.

---

## 🗄️ Estructura de Base de Datos

### Tabla `attachments` (Principal - Polimórfica)

```sql
- id (bigint, PK)
- attachable_type (varchar) ← Modelo asociado (ej: App\Models\User)
- attachable_id (bigint) ← ID del modelo
- filename (varchar) ← Nombre original
- stored_filename (varchar) ← Nombre en storage (UUID)
- path (varchar) ← Ruta completa en storage
- disk (varchar) ← local, s3, etc.
- mime_type (varchar)
- size (integer) ← Bytes
- extension (varchar)
- type (varchar) ← image, audio, video, document
- metadata (json) ← Dimensiones, duración, etc.
- thumbnail_path (varchar, nullable)
- conversions (json, nullable) ← Diferentes tamaños generados
- collection (varchar) ← avatar, gallery, documents, media
- order (integer) ← Orden en colección
- description (text, nullable)
- alt_text (varchar, nullable) ← Para accesibilidad en imágenes
- created_at, updated_at
- deleted_at (soft delete)

Índices:
- (attachable_type, attachable_id)
- (collection)
```

### Tabla `temporary_uploads` (Uploads temporales)

```sql
- id (uuid, PK)
- session_id (varchar) ← ID de sesión PHP
- component_id (bigint) ← ID del componente UI
- original_filename (varchar)
- stored_filename (varchar)
- mime_type (varchar)
- extension (varchar)
- size (integer)
- path (varchar)
- disk (varchar)
- type (varchar)
- thumbnail_path (varchar, nullable)
- metadata (json, nullable)
- expires_at (timestamp) ← Para limpieza automática
- created_at, updated_at

Índices:
- (session_id)
- (expires_at)
```

---

## 🏗️ Componentes a Implementar

### Backend (Laravel)

#### 1. Modelos
- ✅ `app/Models/Attachment.php`
  - Relación polimórfica `morphTo('attachable')`
  - Accessors: `url`, `thumbnail_url`
  - Métodos: `isImage()`, `isVideo()`, `isAudio()`, `isDocument()`
  - Event: Al eliminar, borrar archivos del storage

- ✅ `app/Models/TemporaryUpload.php`
  - Modelo para uploads temporales
  - Método: `toAttachment()` para convertir a Attachment

- ✅ `app/Models/Traits/HasAttachments.php`
  - Trait para agregar a cualquier modelo
  - Relaciones: `attachments()`, `attachmentsIn()`, `avatar()`
  - Métodos: `addAttachmentFromTemp()`, `replaceAttachment()`

#### 2. Servicios
- ✅ `app/Services/UI/Components/UploaderBuilder.php`
  - Extiende `UIComponent`
  - Config: allowed_types, max_size, max_files, collection, etc.
  - Callbacks: onUpload, onValidate, onError, onDelete
  - Shortcuts: `avatar()`, `gallery()`, `documents()`, `media()`

- ✅ `app/Services/Upload/UploadService.php`
  - `detectFileType()` - Detectar tipo por MIME
  - `extractMetadata()` - Extraer metadata según tipo
  - `extractImageMetadata()` - Dimensiones de imagen
  - `extractVideoMetadata()` - Info de video (requiere FFmpeg)
  - `extractAudioMetadata()` - Info de audio (requiere getID3)

- ✅ `app/Services/Screens/UploaderDemoService.php`
  - Demo de uso del componente
  - Ejemplos: avatar, gallery, documents

#### 3. Controllers
- ✅ `app/Http/Controllers/UploadController.php`
  - `uploadTemporary()` - POST /api/upload/temporary
  - `deleteTemporary()` - DELETE /api/upload/temporary/{id}

#### 4. Jobs (Queue)
- ✅ `app/Jobs/GenerateThumbnailJob.php`
  - Genera thumbnails para imágenes
  - Genera preview frames para videos
  - Se dispara automáticamente al subir archivo

- ✅ `app/Jobs/CleanTemporaryUploadsJob.php`
  - Elimina archivos temporales > 24 horas
  - Ejecutar con cron: `schedule->job(CleanTemporaryUploadsJob::class)->daily()`

#### 5. Migraciones
- ✅ `database/migrations/YYYY_MM_DD_create_attachments_table.php`
- ✅ `database/migrations/YYYY_MM_DD_create_temporary_uploads_table.php`

#### 6. Rutas
```php
// routes/api.php
Route::post('/upload/temporary', [UploadController::class, 'uploadTemporary']);
Route::delete('/upload/temporary/{id}', [UploadController::class, 'deleteTemporary']);
```

#### 7. Actualizar Modelo User
```php
use App\Models\Traits\HasAttachments;

class User extends Authenticatable {
    use HasAttachments;
    
    public function getAvatarUrlAttribute(): ?string {
        return $this->avatar?->url ?? '/images/default-avatar.png';
    }
}
```

---

### Frontend (JavaScript + CSS)

#### 1. JavaScript
- ✅ `public/js/ui-renderer.js`
  - Agregar clase `UploaderComponent extends UIComponent`
  - Métodos:
    - `render()` - Crear HTML del componente
    - `createDropZone()` - Zona drag & drop
    - `createFileList()` - Lista de archivos subidos
    - `createFileInput()` - Input oculto
    - `handleFiles()` - Procesar archivos seleccionados/arrastrados
    - `uploadFile()` - XHR upload con progress tracking
    - `validateFile()` - Validar tipo, tamaño, cantidad
    - `addUploadedFile()` - Agregar a lista visual
    - `removeFile()` - Eliminar archivo (DELETE request)
    - `showProgress()` - Actualizar barra de progreso

#### 2. CSS
- ✅ `public/css/ui-components.css`
  - Estilos para:
    - `.ui-uploader-group` - Contenedor principal
    - `.ui-uploader-dropzone` - Zona de drop (con estados hover/dragging)
    - `.ui-uploader-file-list` - Lista de archivos
    - `.ui-uploader-file-item` - Item individual
    - `.ui-uploader-preview` - Preview/thumbnail
    - `.ui-uploader-progress` - Barra de progreso
    - `.ui-uploader-progress-bar` - Barra interior
    - `.ui-uploader-error` - Estado de error
    - `.ui-uploader-actions` - Botones de acción

---

## 🔄 Flujo de Trabajo Completo

### Upload Flow

```
1. Usuario arrastra/selecciona archivos
   ↓
2. JavaScript: Validación cliente (tipo, tamaño, cantidad)
   ↓
3. JavaScript: XHR POST /api/upload/temporary (con progress)
   ↓
4. Backend: Validar archivo
   ↓
5. Backend: Guardar en storage/app/temp/uploads/{session_id}/
   ↓
6. Backend: Crear TemporaryUpload en BD
   ↓
7. Backend: Dispatch GenerateThumbnailJob (queue)
   ↓
8. Backend: Retornar JSON { temp_id, filename, size, preview_url, ... }
   ↓
9. Frontend: Mostrar preview + metadata en lista
   ↓
10. Usuario: Click "Confirm" o botón similar
    ↓
11. Frontend: Ejecutar callback onUpload con temp_ids
    ↓
12. Backend: Ejecutar handler onUpload del componente
    ↓
13. Backend: Convertir TemporaryUpload → Attachment
    ↓
14. Backend: Asociar a modelo (ej: $user->replaceAttachment())
    ↓
15. Backend: Eliminar TemporaryUpload
    ↓
16. Cronjob: Limpiar temporales > 24h (CleanTemporaryUploadsJob)
```

### Delete Flow

```
1. Usuario: Click botón eliminar en archivo
   ↓
2. Frontend: DELETE /api/upload/temporary/{id}
   ↓
3. Backend: Verificar sesión
   ↓
4. Backend: Eliminar archivo del storage
   ↓
5. Backend: Eliminar TemporaryUpload de BD
   ↓
6. Frontend: Remover de lista visual
```

---

## 💻 Ejemplos de Uso

### Avatar de Usuario (Upload único)

```php
// UploaderDemoService.php
protected function buildBaseUI(UIContainer $container, ...$params): void
{
    $container->add(
        UIBuilder::uploader('avatar_uploader')
            ->avatar()  // Shortcut: solo imágenes, max 1 archivo, 2MB
            ->label('Profile Picture')
            ->onUpload(function($tempIds) {
                $user = auth()->user();
                $user->replaceAttachment($tempIds[0], 'avatar');
            })
    );
}
```

### Galería Multimedia (Upload múltiple)

```php
protected function buildBaseUI(UIContainer $container, ...$params): void
{
    $container->add(
        UIBuilder::uploader('gallery_uploader')
            ->gallery()  // Shortcut: imágenes/videos, max 10 archivos
            ->label('Upload Photos & Videos')
            ->onUpload(function($tempIds) use ($post) {
                foreach ($tempIds as $tempId) {
                    $post->addAttachmentFromTemp($tempId, 'gallery');
                }
            })
    );
}
```

### Documentos PDF/Word

```php
protected function buildBaseUI(UIContainer $container, ...$params): void
{
    $container->add(
        UIBuilder::uploader('docs_uploader')
            ->documents()  // Shortcut: PDF, Word, Excel
            ->label('Upload Documents')
            ->maxFiles(5)
            ->maxSize(20)  // 20MB
            ->onUpload(function($tempIds) use ($project) {
                foreach ($tempIds as $tempId) {
                    $project->addAttachmentFromTemp($tempId, 'documents');
                }
            })
    );
}
```

### Upload Personalizado

```php
protected function buildBaseUI(UIContainer $container, ...$params): void
{
    $container->add(
        UIBuilder::uploader('custom_uploader')
            ->allowedTypes(['audio/*', 'video/*'])
            ->maxSize(50)  // 50MB
            ->maxFiles(3)
            ->collection('media')
            ->onValidate(function($file) {
                // Validación custom
                if ($file->size > 30 * 1024 * 1024) {
                    return ['error' => 'Audio files must be under 30MB'];
                }
            })
            ->onUpload(function($tempIds) use ($podcast) {
                foreach ($tempIds as $tempId) {
                    $podcast->addAttachmentFromTemp($tempId, 'episodes');
                }
            })
            ->onError(function($error) {
                // Log error
            })
    );
}
```

---

## 📦 Configuración Necesaria

### 1. Storage Configuration

```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'visibility' => 'private',
    ],
    
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

### 2. Queue Configuration
Ya configurado. Queue workers corriendo:
- **Dev local**: `php artisan queue:work`
- **Producción**: Supervisor

### 3. Scheduled Jobs (Cron)

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Limpiar uploads temporales diariamente
    $schedule->job(new CleanTemporaryUploadsJob)->daily();
}
```

### 4. UIBuilder Registration

```php
// app/Services/UI/UIBuilder.php
public static function uploader(?string $name = null): UploaderBuilder
{
    return new UploaderBuilder($name);
}
```

---

## ✅ Checklist de Implementación

### Migraciones
- [ ] Crear `create_attachments_table.php`
- [ ] Crear `create_temporary_uploads_table.php`
- [ ] Ejecutar `php artisan migrate`

### Modelos
- [ ] Crear `Attachment.php`
- [ ] Crear `TemporaryUpload.php`
- [ ] Crear `Traits/HasAttachments.php`
- [ ] Actualizar `User.php` para usar trait

### Backend - Servicios y Controllers
- [ ] Crear `UploaderBuilder.php`
- [ ] Crear `UploadService.php`
- [ ] Crear `UploadController.php`
- [ ] Agregar rutas en `routes/api.php`

### Backend - Jobs
- [ ] Crear `GenerateThumbnailJob.php`
- [ ] Crear `CleanTemporaryUploadsJob.php`
- [ ] Configurar scheduled job en `Kernel.php`

### Backend - Demo
- [ ] Crear `UploaderDemoService.php`
- [ ] Agregar ruta de demo en `routes/web.php`

### Frontend
- [ ] Agregar `UploaderComponent` en `ui-renderer.js`
- [ ] Agregar estilos en `ui-components.css`
- [ ] Probar drag & drop
- [ ] Probar upload con progress bar
- [ ] Probar validaciones

### Testing
- [ ] Probar upload de imagen → thumbnail generado
- [ ] Probar upload de video → preview generado
- [ ] Probar upload de audio
- [ ] Probar upload de PDF
- [ ] Probar validaciones de tipo y tamaño
- [ ] Probar límite de archivos
- [ ] Probar eliminación de archivo temporal
- [ ] Probar confirmación y creación de Attachment
- [ ] Probar asociación polimórfica con User (avatar)
- [ ] Probar cronjob de limpieza

### Documentación
- [ ] Actualizar README con ejemplos de uso
- [ ] Documentar en `docs/TECHNICAL_COMPONENTS_README.md`

---

## 🚀 Cómo Continuar Mañana

### Opción 1: Comando Directo

Abre VSCode en el trabajo y di a Copilot:

```
Lee el archivo #file:UPLOADER_COMPONENT_PLAN.md e implementa el componente uploader 
completo siguiendo el plan definido. Empieza por las migraciones y modelos.
```

### Opción 2: Paso a Paso

Si prefieres ir paso por paso:

```
Lee #file:UPLOADER_COMPONENT_PLAN.md

Luego implementa solo:
1. Las migraciones (attachments y temporary_uploads)
2. Los modelos (Attachment, TemporaryUpload, HasAttachments trait)
3. Actualiza el modelo User
```

Una vez eso funcione, continúa con:

```
Ahora implementa:
1. UploaderBuilder.php
2. UploadService.php
3. UploadController.php
4. Las rutas API
```

Y así sucesivamente.

### Opción 3: Implementación Completa Automática

```
Lee #file:UPLOADER_COMPONENT_PLAN.md e implementa TODO el componente uploader 
de una sola vez, siguiendo exactamente el plan. Crea todos los archivos necesarios.
```

---

## 🔍 Verificación Post-Implementación

Una vez implementado, verificar:

1. **Migraciones ejecutadas**:
   ```bash
   php artisan migrate:status
   ```

2. **Rutas registradas**:
   ```bash
   php artisan route:list | grep upload
   ```

3. **Queue worker corriendo**:
   ```bash
   php artisan queue:work --tries=3
   ```

4. **Storage configurado**:
   ```bash
   php artisan storage:link
   ```

5. **Demo funcionando**:
   - Abrir navegador: `/demo/uploader` (o la ruta definida)
   - Probar drag & drop
   - Verificar progress bar
   - Verificar preview
   - Verificar confirmación

---

## 📝 Notas Importantes

- **No usar `file_put_contents`** directamente, usar `Storage` facade de Laravel
- **Validar MIME type en backend** además del cliente (seguridad)
- **Generar nombres únicos** con UUID para evitar colisiones
- **Limpiar temporales** regularmente para no llenar storage
- **CSRF token** requerido en todos los requests AJAX
- **Session ID** para aislar uploads entre usuarios
- **Soft deletes** en Attachment para recuperación

---

## 🎨 Extensiones Futuras (No implementar ahora)

- [ ] Crop de imágenes antes de subir
- [ ] Editor de imágenes (filtros, rotación)
- [ ] Compresión automática de imágenes
- [ ] Procesamiento de video (conversión a diferentes formatos)
- [ ] Integración con S3/CloudStorage
- [ ] OCR para PDFs
- [ ] Transcripción de audio
- [ ] Metadata EXIF de fotos
- [ ] Watermarks automáticos

---

**Última actualización**: 27 de noviembre de 2025  
**Estado**: ✅ Plan completo - Listo para implementar  
**Próximo paso**: Crear migraciones y modelos
