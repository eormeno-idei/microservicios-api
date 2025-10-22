# Documentación del API - Microservicios Laravel

## Índice
- [Estructura de Respuestas](#estructura-de-respuestas)
- [Endpoints de Autenticación](#endpoints-de-autenticación)
- [Endpoints de Usuario](#endpoints-de-usuario)
- [Endpoints de Archivos](#endpoints-de-archivos)
- [Endpoints Administrativos](#endpoints-administrativos)
- [Cliente API Web](#cliente-api-web)
- [Cliente JavaScript](#cliente-javascript)
- [Manejo de Errores](#manejo-de-errores)
- [Ejemplos Prácticos](#ejemplos-prácticos)

## Estructura de Respuestas

Todos los endpoints de la API siguen una estructura JSON estandarizada para mantener consistencia y facilitar el manejo de respuestas en el cliente.

### Respuestas Exitosas

#### Elemento Individual
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Elemento",
        "attachments": [
            {
                "id": "file_123",
                "name": "document.pdf",
                "mime_type": "application/pdf",
                "size": 1048576,
                "url": "/api/files/file_123",
                "protected": true,
                "metadata": {
                    "display_type": "document",
                    "preview_url": "/api/files/file_123/preview",
                    "created_at": "2025-07-16T10:00:00Z"
                }
            }
        ],
        "type": "single"
    },
    "message": "Elemento obtenido exitosamente"
}
```

#### Lista de Elementos
```json
{
    "success": true,
    "data": {
        "items": [
            { /* elemento 1 */ },
            { /* elemento 2 */ }
        ],
        "count": 2,
        "type": "list"
    },
    "message": "Lista obtenida exitosamente"
}
```

#### Lista Paginada
```json
{
    "success": true,
    "data": {
        "items": [
            { /* elementos de la página actual */ }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 150,
            "total_pages": 10,
            "has_next": true,
            "has_previous": false,
            "next_page": 2,
            "previous_page": null,
            "first_page_url": "/api/users?page=1",
            "last_page_url": "/api/users?page=10",
            "next_page_url": "/api/users?page=2",
            "prev_page_url": null
        },
        "count": 15,
        "type": "paginated_list"
    },
    "message": "Página 1 obtenida exitosamente"
}
```

### Estructura de Archivos Adjuntos

Los elementos pueden contener archivos adjuntos con la siguiente estructura:

```json
{
    "id": "unique_file_id",
    "name": "filename.ext",
    "mime_type": "image/jpeg",
    "size": 245760,
    "url": "/api/files/unique_file_id",
    "protected": true,
    "metadata": {
        "width": 400,
        "height": 400,
        "display_type": "avatar|thumbnail|document|inline_image",
        "thumbnail_url": "/api/files/unique_file_id/thumbnail",
        "preview_url": "/api/files/unique_file_id/preview",
        "alt_text": "Descripción del archivo",
        "created_at": "2025-07-16T10:00:00Z"
    }
}
```

### Respuestas de Error

```json
{
    "success": false,
    "message": "Mensaje descriptivo del error",
    "errors": {
        // Objeto con errores de validación específicos (opcional)
    }
}
```

## Endpoints de Autenticación

### GET /api/ping
Verifica que la API esté funcionando correctamente.

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "status": "ok"
    },
    "message": "API is running correctly"
}
```

### POST /api/register
Registra un nuevo usuario en el sistema.

**Request Body:**
```json
{
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
}
```

**Respuesta Exitosa (201):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "email_verified_at": null,
            "created_at": "2025-07-16T10:00:00.000000Z",
            "updated_at": "2025-07-16T10:00:00.000000Z",
            "attachments": []
        },
        "token": "1|abcdef123456...",
        "token_type": "Bearer",
        "type": "single"
    },
    "message": "User registered successfully. Please check your email to verify your account."
}
```

### POST /api/login
Autentica un usuario existente.

**Request Body:**
```json
{
    "email": "string",
    "password": "string"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "email_verified_at": "2025-07-16T10:00:00.000000Z",
            "created_at": "2025-07-16T10:00:00.000000Z",
            "updated_at": "2025-07-16T10:00:00.000000Z",
            "attachments": [
                {
                    "id": "avatar_123",
                    "name": "profile.jpg",
                    "mime_type": "image/jpeg",
                    "size": 204800,
                    "url": "/api/files/avatar_123",
                    "protected": true,
                    "metadata": {
                        "width": 300,
                        "height": 300,
                        "display_type": "avatar",
                        "thumbnail_url": "/api/files/avatar_123/thumbnail",
                        "alt_text": "Avatar de Juan Pérez",
                        "created_at": "2025-07-16T10:00:00Z"
                    }
                }
            ]
        },
        "token": "1|abcdef123456...",
        "token_type": "Bearer",
        "type": "single"
    },
    "message": "Login successful"
}
```

### POST /api/logout
Cierra la sesión del usuario actual.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": null,
    "message": "Successfully logged out"
}
```

### POST /api/password/forgot
Solicita un enlace de restablecimiento de contraseña.

**Request Body:**
```json
{
    "email": "string"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": null,
    "message": "Password reset link sent to your email"
}
```

### POST /api/password/reset
Restablece la contraseña usando el token del email.

**Request Body:**
```json
{
    "token": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": null,
    "message": "Password reset successfully"
}
```

## Endpoints de Usuario

### GET /api/user
Obtiene la información del usuario autenticado.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Juan Pérez",
        "email": "juan@example.com",
        "email_verified_at": "2025-07-16T10:00:00.000000Z",
        "created_at": "2025-07-16T10:00:00.000000Z",
        "updated_at": "2025-07-16T10:00:00.000000Z",
        "attachments": [
            {
                "id": "avatar_123",
                "name": "profile.jpg",
                "mime_type": "image/jpeg",
                "size": 204800,
                "url": "/api/files/avatar_123",
                "protected": true,
                "metadata": {
                    "width": 300,
                    "height": 300,
                    "display_type": "avatar",
                    "thumbnail_url": "/api/files/avatar_123/thumbnail",
                    "alt_text": "Avatar de Juan Pérez",
                    "created_at": "2025-07-16T10:00:00Z"
                }
            }
        ],
        "type": "single"
    },
    "message": "User data retrieved successfully"
}
```

### PUT /api/user/profile
Actualiza el perfil del usuario autenticado.

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "string (opcional)",
    "email": "string (opcional)"
}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Juan Pérez Actualizado",
        "email": "juan.nuevo@example.com",
        "email_verified_at": null,
        "created_at": "2025-07-16T10:00:00.000000Z",
        "updated_at": "2025-07-16T10:30:00.000000Z",
        "attachments": [],
        "type": "single"
    },
    "message": "Profile updated successfully"
}
```

### POST /api/user/avatar
Sube o actualiza la imagen de perfil del usuario.

**Headers requeridos:**
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
```
avatar: File (imagen JPG, PNG, WebP máximo 2MB)
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "Juan Pérez",
            "email": "juan@example.com",
            "attachments": [
                {
                    "id": "avatar_456",
                    "name": "new_profile.jpg",
                    "mime_type": "image/jpeg",
                    "size": 187392,
                    "url": "/api/files/avatar_456",
                    "protected": true,
                    "metadata": {
                        "width": 400,
                        "height": 400,
                        "display_type": "avatar",
                        "thumbnail_url": "/api/files/avatar_456/thumbnail",
                        "alt_text": "Avatar de Juan Pérez",
                        "created_at": "2025-07-16T11:00:00Z"
                    }
                }
            ]
        },
        "type": "single"
    },
    "message": "Avatar uploaded successfully"
}
```

### DELETE /api/user/avatar
Elimina la imagen de perfil del usuario.

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "message": "Avatar deleted successfully",
        "type": "single"
    },
    "message": "Avatar deleted successfully"
}
```

## Endpoints de Archivos

### GET /api/files/{id}
Descarga o muestra un archivo por su ID.

**Parámetros de Query (opcionales):**
- `download=true`: Fuerza descarga del archivo
- `thumbnail=true`: Obtiene versión thumbnail (si está disponible)

**Headers requeridos (para archivos protegidos):**
```
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
- Content-Type: Según el tipo de archivo
- Content-Disposition: attachment; filename="nombre_archivo.ext" (si download=true)

## Endpoints Administrativos

### GET /api/users
Obtiene una lista paginada de usuarios (solo administradores).

**Headers requeridos:**
```
Authorization: Bearer {token}
```

**Parámetros de Query (opcionales):**
- `page`: Número de página (default: 1)
- `per_page`: Elementos por página (default: 15, máximo: 100)
- `search`: Término de búsqueda en nombre o email

**Respuesta Exitosa (200):**
```json
{
    "success": true,
    "data": {
        "items": [
            {
                "id": 1,
                "name": "Juan Pérez",
                "email": "juan@example.com",
                "email_verified_at": "2025-07-16T10:00:00.000000Z",
                "created_at": "2025-07-16T10:00:00.000000Z",
                "attachments": [
                    {
                        "id": "avatar_123",
                        "name": "profile.jpg",
                        "mime_type": "image/jpeg",
                        "size": 204800,
                        "url": "/api/files/avatar_123",
                        "protected": true,
                        "metadata": {
                            "display_type": "avatar",
                            "thumbnail_url": "/api/files/avatar_123/thumbnail"
                        }
                    }
                ]
            }
        ],
        "pagination": {
            "current_page": 1,
            "per_page": 15,
            "total": 150,
            "total_pages": 10,
            "has_next": true,
            "has_previous": false,
            "next_page": 2,
            "previous_page": null,
            "first_page_url": "/api/users?page=1",
            "last_page_url": "/api/users?page=10",
            "next_page_url": "/api/users?page=2",
            "prev_page_url": null
        },
        "count": 15,
        "type": "paginated_list"
    },
    "message": "Users retrieved successfully"
}
```

## Cliente API Web

### 🚀 Características del Cliente Web

- **Interfaz moderna y responsiva** con diseño gradiente
- **Soporte completo para métodos HTTP**: GET, POST, PUT, DELETE, PATCH
- **Editor de headers personalizados** con validación JSON
- **Editor de cuerpo de petición** con syntax highlighting
- **Respuesta formateada** con syntax highlighting JSON
- **Renderizado automático de archivos adjuntos**
- **Validación en tiempo real** de formularios
- **Estados de carga** y feedback visual
- **Función de copiado** al portapapeles

### Acceso y Uso

**URL de acceso:** `http://127.0.0.1:8000/api-client`

#### Campos disponibles

1. **Método HTTP**: Selecciona entre GET, POST, PUT, DELETE, PATCH
2. **URL**: Ingresa la URL completa del endpoint a probar
3. **Headers**: JSON con headers personalizados (opcional)
4. **Request Body**: JSON con el cuerpo de la petición (para POST, PUT, PATCH)

#### Ejemplos de uso básico

**Ejemplo GET básico:**
```
Método: GET
URL: http://tu-dominio/api/ping
Headers: {"Accept": "application/json"}
Body: (vacío)
```

**Ejemplo POST con datos:**
```
Método: POST
URL: http://tu-dominio/api/register
Headers: {
  "Content-Type": "application/json",
  "Accept": "application/json"
}
Body: {
  "name": "Juan Pérez",
  "email": "juan@ejemplo.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Ejemplo con autenticación:**
```
Método: GET
URL: http://tu-dominio/api/user
Headers: {
  "Accept": "application/json",
  "Authorization": "Bearer tu-token-de-acceso"
}
```

### Renderizado de Archivos Adjuntos

El cliente web detecta automáticamente archivos adjuntos en las respuestas y los renderiza según su tipo:

- **Imágenes**: Preview con thumbnail, modal de imagen completa
- **PDFs**: Icono de documento, botón de vista previa
- **Archivos genéricos**: Icono y descarga directa

## Cliente JavaScript

### Clase ApiClient

```javascript
/**
 * Cliente para realizar peticiones a la API
 */
class ApiClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        this.token = localStorage.getItem('auth_token');
    }

    // Configurar headers con autenticación
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        return headers;
    }

    // Configurar headers para FormData (sin Content-Type)
    getFormHeaders() {
        const headers = {
            'Accept': 'application/json'
        };

        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }

        return headers;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        
        const response = await fetch(url, {
            ...options,
            headers: options.headers || this.getHeaders()
        });

        return await this.handleApiResponse(response);
    }

    // Métodos de autenticación
    async register(userData) {
        const result = await this.request('/api/register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
        
        // Guardar token automáticamente
        if (result.data.token) {
            this.setToken(result.data.token);
        }
        
        return result;
    }

    async login(credentials) {
        const result = await this.request('/api/login', {
            method: 'POST',
            body: JSON.stringify(credentials)
        });
        
        // Guardar token automáticamente
        if (result.data.token) {
            this.setToken(result.data.token);
        }
        
        return result;
    }

    async logout() {
        const result = await this.request('/api/logout', {
            method: 'POST'
        });
        
        // Limpiar token local
        this.clearToken();
        
        return result;
    }

    // Métodos de usuario
    async getUser() {
        return await this.request('/api/user');
    }

    async updateProfile(profileData) {
        return await this.request('/api/user/profile', {
            method: 'PUT',
            body: JSON.stringify(profileData)
        });
    }

    async uploadAvatar(file) {
        const formData = new FormData();
        formData.append('avatar', file);
        
        return await this.request('/api/user/avatar', {
            method: 'POST',
            headers: this.getFormHeaders(),
            body: formData
        });
    }

    async deleteAvatar() {
        return await this.request('/api/user/avatar', {
            method: 'DELETE'
        });
    }

    // Métodos administrativos
    async getUsers(page = 1, perPage = 15, search = '') {
        const params = new URLSearchParams({
            page,
            per_page: perPage,
            ...(search && { search })
        });
        
        return await this.request(`/api/users?${params}`);
    }

    // Método para descargar archivos
    async getFile(fileId, options = {}) {
        const params = new URLSearchParams(options);
        const url = `${this.baseUrl}/api/files/${fileId}${params.toString() ? '?' + params.toString() : ''}`;
        
        const response = await fetch(url, {
            headers: this.getHeaders()
        });
        
        if (!response.ok) {
            throw new Error(`Error downloading file: ${response.statusText}`);
        }
        
        return response;
    }

    // Gestión de tokens
    setToken(token) {
        this.token = token;
        localStorage.setItem('auth_token', token);
    }

    clearToken() {
        this.token = null;
        localStorage.removeItem('auth_token');
    }

    // Manejo de respuestas
    async handleApiResponse(response) {
        const data = await response.json();
        
        if (!data.success) {
            const error = new Error(data.message);
            error.status = response.status;
            error.errors = data.errors;
            throw error;
        }
        
        // Procesar archivos adjuntos si existen
        if (data.data) {
            this.processAttachments(data.data);
        }
        
        return data;
    }

    // Procesar archivos adjuntos
    processAttachments(dataObj) {
        // Para elemento único
        if (dataObj.attachments) {
            dataObj.attachments = dataObj.attachments.map(this.processAttachment.bind(this));
        }
        
        // Para usuario en respuesta de autenticación
        if (dataObj.user && dataObj.user.attachments) {
            dataObj.user.attachments = dataObj.user.attachments.map(this.processAttachment.bind(this));
        }
        
        // Para listas
        if (dataObj.items && Array.isArray(dataObj.items)) {
            dataObj.items.forEach(item => {
                if (item.attachments) {
                    item.attachments = item.attachments.map(this.processAttachment.bind(this));
                }
            });
        }
    }

    processAttachment(attachment) {
        // Agregar URL completa si es relativa
        if (attachment.url && !attachment.url.startsWith('http')) {
            attachment.url = `${this.baseUrl}${attachment.url}`;
        }
        
        // Procesar URLs de thumbnail y preview
        if (attachment.metadata) {
            if (attachment.metadata.thumbnail_url && !attachment.metadata.thumbnail_url.startsWith('http')) {
                attachment.metadata.thumbnail_url = `${this.baseUrl}${attachment.metadata.thumbnail_url}`;
            }
            if (attachment.metadata.preview_url && !attachment.metadata.preview_url.startsWith('http')) {
                attachment.metadata.preview_url = `${this.baseUrl}${attachment.metadata.preview_url}`;
            }
        }
        
        return attachment;
    }
}
```

### Funciones Auxiliares

```javascript
// Validación avanzada de archivos
async function validateFileAdvanced(file, type = 'general') {
    const FILE_VALIDATION_RULES = {
        avatar: {
            maxSize: 2 * 1024 * 1024, // 2MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/webp'],
            maxWidth: 1000,
            maxHeight: 1000
        },
        document: {
            maxSize: 10 * 1024 * 1024, // 10MB
            allowedTypes: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        },
        image: {
            maxSize: 5 * 1024 * 1024, // 5MB
            allowedTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            maxWidth: 2000,
            maxHeight: 2000
        }
    };

    const rules = FILE_VALIDATION_RULES[type] || FILE_VALIDATION_RULES.image;
    const errors = [];
    
    // Validar tamaño
    if (file.size > rules.maxSize) {
        errors.push(`El archivo es demasiado grande. Máximo: ${formatFileSize(rules.maxSize)}`);
    }
    
    // Validar tipo MIME
    if (!rules.allowedTypes.includes(file.type)) {
        errors.push(`Tipo de archivo no permitido. Permitidos: ${rules.allowedTypes.join(', ')}`);
    }
    
    return { isValid: errors.length === 0, errors };
}

// Formatear tamaño de archivo
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Descargar archivo con nombre correcto
async function downloadFile(fileId, fileName) {
    try {
        const response = await api.getFile(fileId, { download: true });
        const blob = await response.blob();
        
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    } catch (error) {
        console.error('Error downloading file:', error);
        alert('Error al descargar el archivo');
    }
}
```

## Manejo de Errores

### Códigos de Estado HTTP

- **200 OK**: Operación exitosa
- **201 Created**: Recurso creado exitosamente
- **400 Bad Request**: Solicitud inválida
- **401 Unauthorized**: Credenciales incorrectas
- **403 Forbidden**: Sin permisos para acceder al recurso
- **404 Not Found**: Recurso no encontrado
- **413 Payload Too Large**: Archivo demasiado grande
- **422 Unprocessable Entity**: Errores de validación

### Estructura de Errores

Todos los errores siguen la estructura estandarizada con `success: false`. Los errores de validación incluyen un objeto `errors` con detalles específicos de cada campo.

**Ejemplo de error de validación:**
```json
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password confirmation does not match."]
    }
}
```

## Ejemplos Prácticos

### Registro de Usuario con Avatar

```javascript
const api = new ApiClient('http://localhost:8000');

// Registrar usuario
try {
    const registrationResult = await api.register({
        name: 'María García',
        email: 'maria@example.com',
        password: 'secreto123',
        password_confirmation: 'secreto123'
    });
    
    console.log('Usuario registrado:', registrationResult.data.user);
    console.log('Token obtenido:', registrationResult.data.token);
    
    // Subir avatar después del registro
    const avatarFile = document.getElementById('avatar-input').files[0];
    if (avatarFile) {
        const validation = await validateFileAdvanced(avatarFile, 'avatar');
        
        if (validation.isValid) {
            const avatarResult = await api.uploadAvatar(avatarFile);
            console.log('Avatar subido:', avatarResult.data.user.attachments[0]);
        } else {
            console.error('Archivo no válido:', validation.errors);
        }
    }
    
} catch (error) {
    console.error('Error en el proceso:', error.message);
    if (error.errors) {
        Object.keys(error.errors).forEach(field => {
            console.error(`${field}: ${error.errors[field].join(', ')}`);
        });
    }
}
```

### Lista Paginada con Búsqueda

```javascript
// Función para cargar usuarios con paginación
async function loadUsers(page = 1, search = '') {
    try {
        const result = await api.getUsers(page, 15, search);
        
        console.log(`Página ${page} de ${result.data.pagination.total_pages}`);
        console.log(`Mostrando ${result.data.count} de ${result.data.pagination.total} usuarios`);
        
        // Renderizar usuarios
        const usersContainer = document.getElementById('users-container');
        usersContainer.innerHTML = result.data.items.map(user => `
            <div class="user-card">
                <div class="user-info">
                    <h3>${user.name}</h3>
                    <p>${user.email}</p>
                    <span class="verification-status ${user.email_verified_at ? 'verified' : 'unverified'}">
                        ${user.email_verified_at ? 'Verificado' : 'Sin verificar'}
                    </span>
                </div>
                ${user.attachments && user.attachments.length > 0 ? `
                    <div class="user-avatar">
                        <img src="${user.attachments[0].metadata.thumbnail_url}" 
                             alt="${user.attachments[0].metadata.alt_text}"
                             style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                    </div>
                ` : `
                    <div class="user-avatar-placeholder">
                        <span>${user.name.charAt(0).toUpperCase()}</span>
                    </div>
                `}
            </div>
        `).join('');
        
        // Renderizar controles de paginación
        renderPaginationControls(result.data.pagination);
        
    } catch (error) {
        console.error('Error cargando usuarios:', error.message);
    }
}
```

### Reset de Contraseña

```javascript
// Solicitar reset de contraseña
async function requestPasswordReset(email) {
    try {
        const response = await fetch('/api/password/forgot', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ email })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Se ha enviado un enlace de restablecimiento a tu email');
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al solicitar el restablecimiento');
    }
}

// Resetear contraseña con token
async function resetPassword(token, email, password, passwordConfirmation) {
    try {
        const response = await fetch('/api/password/reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                token,
                email,
                password,
                password_confirmation: passwordConfirmation
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Contraseña restablecida exitosamente');
            // Redirigir al login
            window.location.href = '/login';
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al restablecer la contraseña');
    }
}
```

## Notas Importantes de Implementación

### Autenticación
- La API utiliza Laravel Sanctum para la autenticación mediante tokens
- Los tokens no expiran automáticamente (configuración de Laravel Sanctum)
- Incluye el token en el header Authorization: `Bearer {tu_token_aquí}`

### Manejo de Archivos
- **Tipos permitidos para avatares**: JPG, JPEG, PNG, WebP
- **Tamaño máximo**: 2MB por archivo
- **Archivos protegidos**: Requieren autenticación para acceder
- **Thumbnails**: Se generan automáticamente para imágenes
- **URLs de archivos**: Son relativas al dominio de la API

### Validaciones
- Las validaciones de email siguen el estándar RFC 5322
- Las contraseñas deben tener al menos 8 caracteres
- El registro de usuarios requiere confirmación de contraseña
- Los archivos se almacenan de forma segura y se acceden mediante IDs únicos

### Seguridad
- Las imágenes de perfil se redimensionan automáticamente
- Los archivos eliminados se marcan como eliminados pero no se borran físicamente inmediatamente
- Todos los endpoints devuelven JSON con la estructura estandarizada
- Validación CSRF incluida en el cliente web

---

**Desarrollado para Laravel 12** - Una API moderna y completa con gestión de archivos multimedia.
