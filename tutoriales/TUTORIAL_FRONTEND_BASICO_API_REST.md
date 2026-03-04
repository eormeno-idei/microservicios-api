# Tutorial: Frontend Básico con API REST en Laravel

## Introducción

Este tutorial cubre los fundamentos de comunicación entre un frontend vanilla (HTML, CSS, JavaScript) y una API REST construida con Laravel. Se enfoca específicamente en la integración JavaScript-Backend, asumiendo conocimientos previos de HTML, CSS y fundamentos de JavaScript.

> ℹ️ **Nota**
> Se le llama "vanilla" a JavaScript puro, sin frameworks o librerías adicionales como React, Vue o Angular.

## Objetivos

- Crear una ruta pública en Laravel que retorne datos en formato JSON
- Consumir la ruta desde JavaScript usando la Fetch API
- Renderizar la respuesta del servidor en el DOM
- Preparar la estructura para futura autenticación y autorización

## Parte 1: Configuración del Backend

### 1.1. Creación de la Ruta Pública

En Laravel, las rutas públicas de API se definen en `routes/api.php`. Crearemos una ruta simple que retorne un mensaje de bienvenida.

Agregar en `routes/api.php`:

```php
Route::get('/landing', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Bienvenido a la API de Microservicios',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});
```

### 1.2. Verificación de la Ruta

Iniciar el servidor de desarrollo:

```bash
php artisan serve
```

La ruta estará disponible en: `http://localhost:8000/api/landing`

Verificar en el navegador o con curl:

```bash
curl http://localhost:8000/api/landing
```

### 1.3. Configuración de CORS

Para permitir que el frontend consuma la API desde un origen diferente, Laravel incluye el middleware de CORS configurado por defecto. Verificar que en `config/cors.php` la configuración permita el origen del frontend.

Para desarrollo local, la configuración predeterminada es suficiente. Si se requiere ajustar:

```php
'paths' => ['api/*'],
'allowed_origins' => ['*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

> ℹ️ **Nota**
> A partir de Laravel 7.x, el soporte CORS está integrado por defecto. Pero es importante revisar la configuración para entornos de producción.
> Para más detalles, consultar la [documentación de Laravel sobre CORS](https://laravel.com/docs/routing#cors).

## Parte 2: Estructura del Frontend

### 2.1. Estructura de Archivos

Crear la siguiente estructura de archivos dentro de `public/`:

```
public/
├── frontend/
│   ├── index.html
│   ├── css/
│   │   └── styles.css
│   └── js/
│       └── app.js
```

### 2.2. Archivo HTML

Crear `public/frontend/index.html`:

```html
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Microservicios API - Frontend</title>
    <link rel="stylesheet" href="./frontend/css/styles.css">
</head>

<body>
    <div class="container">
        <header class="header">
            <h1 class="header__title">Microservicios API</h1>
            <nav class="header__nav">
                <button id="btnLogin" class="btn btn--secondary">Iniciar Sesión</button>
                <button id="btnLogout" class="btn btn--secondary" style="display: none;">Cerrar Sesión</button>
            </nav>
        </header>

        <main class="main">
            <section class="landing-section">
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Cargando...</p>
                </div>

                <div class="content" id="content" style="display: none;">
                    <h2 class="content__title">Estado de la API</h2>
                    <div class="card">
                        <p class="card__label">Mensaje:</p>
                        <p class="card__value" id="apiMessage"></p>
                    </div>
                    <div class="card">
                        <p class="card__label">Versión:</p>
                        <p class="card__value" id="apiVersion"></p>
                    </div>
                    <div class="card">
                        <p class="card__label">Timestamp:</p>
                        <p class="card__value" id="apiTimestamp"></p>
                    </div>
                </div>

                <div class="error" id="error" style="display: none;">
                    <h3 class="error__title">Error al conectar con la API</h3>
                    <p class="error__message" id="errorMessage"></p>
                    <button class="btn btn--primary" onclick="location.reload()">Reintentar</button>
                </div>
            </section>

            <!-- Sección para usuarios autenticados -->
            <section class="protected-section" id="protectedSection" style="display: none;">
                <div class="card card--highlight">
                    <h3 class="card__title">Panel de Usuario</h3>
                    <p class="card__description">Esta sección solo es visible para usuarios autenticados.</p>
                    <button class="btn btn--primary" id="btnUserAction">Acción de Usuario</button>
                </div>
            </section>

            <!-- Sección para administradores -->
            <section class="admin-section" id="adminSection" style="display: none;">
                <div class="card card--admin">
                    <h3 class="card__title">Panel de Administrador</h3>
                    <p class="card__description">Esta sección solo es visible para administradores.</p>
                    <button class="btn btn--danger" id="btnAdminAction">Acción de Administrador</button>
                </div>
            </section>
        </main>

        <footer class="footer">
            <p>&copy; 2025 Microservicios API. Todos los derechos reservados.</p>
        </footer>
    </div>

    <script src="./frontend/js/app.js"></script>
</body>

</html>
```

### 2.3. Archivo CSS

Crear `public/frontend/css/styles.css`:

```css
/* Reset y Variables */
:root {
    --primary-color: #2563eb;
    --secondary-color: #64748b;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --bg-color: #f8fafc;
    --surface-color: #ffffff;
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    background-color: var(--bg-color);
    color: var(--text-primary);
    line-height: 1.6;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Header */
.header {
    background-color: var(--surface-color);
    padding: 1.5rem 2rem;
    box-shadow: var(--shadow-sm);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.header__title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--primary-color);
}

.header__nav {
    display: flex;
    gap: 1rem;
}

/* Main Content */
.main {
    flex: 1;
    padding: 2rem;
}

/* Sections */
.welcome-section,
.protected-section,
.admin-section {
    margin-bottom: 2rem;
}

/* Loading State */
.loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid var(--border-color);
    border-top-color: var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading p {
    margin-top: 1rem;
    color: var(--text-secondary);
}

/* Content */
.content {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.content__title {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
}

/* Cards */
.card {
    background-color: var(--surface-color);
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 1rem;
    box-shadow: var(--shadow-md);
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.card__label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.card__value {
    font-size: 1.125rem;
    color: var(--text-primary);
    font-weight: 500;
}

.card__title {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--text-primary);
}

.card__description {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.card--highlight {
    border-left: 4px solid var(--success-color);
}

.card--admin {
    border-left: 4px solid var(--danger-color);
    background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);
}

/* Error State */
.error {
    text-align: center;
    padding: 3rem 2rem;
    animation: fadeIn 0.5s ease-in;
}

.error__title {
    font-size: 1.5rem;
    color: var(--danger-color);
    margin-bottom: 1rem;
}

.error__message {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

/* Buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-transform: none;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.btn:active {
    transform: translateY(0);
}

.btn--primary {
    background-color: var(--primary-color);
    color: white;
}

.btn--primary:hover {
    background-color: #1d4ed8;
}

.btn--secondary {
    background-color: var(--secondary-color);
    color: white;
}

.btn--secondary:hover {
    background-color: #475569;
}

.btn--danger {
    background-color: var(--danger-color);
    color: white;
}

.btn--danger:hover {
    background-color: #dc2626;
}

/* Footer */
.footer {
    background-color: var(--surface-color);
    padding: 2rem;
    text-align: center;
    color: var(--text-secondary);
    box-shadow: 0 -1px 2px 0 rgba(0, 0, 0, 0.05);
}

/* Responsive */
@media (max-width: 768px) {
    .header {
        flex-direction: column;
        text-align: center;
    }
    
    .header__nav {
        width: 100%;
        justify-content: center;
    }
    
    .main {
        padding: 1rem;
    }
    
    .card {
        padding: 1rem;
    }
}
```

## Parte 3: Integración JavaScript con la API

### 3.1. Conceptos Fundamentales

#### 3.1.1. Fetch API

La Fetch API es el estándar moderno para realizar peticiones HTTP desde JavaScript. Retorna Promesas, lo que permite manejar operaciones asíncronas de manera elegante.

Sintaxis básica:

```javascript
fetch(url, options)
    .then(response => response.json())
    .then(data => {
        // Procesar datos
    })
    .catch(error => {
        // Manejar errores
    });
```

#### 3.1.2. Async/Await

Sintaxis alternativa para trabajar con Promesas de manera más legible:

```javascript
async function fetchData() {
    try {
        const response = await fetch(url);
        const data = await response.json();
        // Procesar datos
    } catch (error) {
        // Manejar errores
    }
}
```

### 3.2. Implementación del Cliente JavaScript

Crear `public/frontend/js/app.js`:

```javascript
/**
 * Configuración de la aplicación
 */
const API_BASE_URL = 'http://localhost:8000/api';

/**
 * Referencias a elementos del DOM
 */
const elements = {
    loading: document.getElementById('loading'),
    content: document.getElementById('content'),
    error: document.getElementById('error'),
    errorMessage: document.getElementById('errorMessage'),
    apiMessage: document.getElementById('apiMessage'),
    apiVersion: document.getElementById('apiVersion'),
    apiTimestamp: document.getElementById('apiTimestamp'),
    protectedSection: document.getElementById('protectedSection'),
    adminSection: document.getElementById('adminSection'),
    btnLogin: document.getElementById('btnLogin'),
    btnLogout: document.getElementById('btnLogout')
};

/**
 * Estado de la aplicación
 */
const appState = {
    isAuthenticated: false,
    isAdmin: false,
    user: null
};

/**
 * Clase para manejar las peticiones a la API
 */
class ApiClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }

    /**
     * Realiza una petición GET a la API
     * @param {string} endpoint - Endpoint de la API
     * @returns {Promise<Object>} Respuesta de la API
     */
    async get(endpoint) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            // Verificar si la respuesta es exitosa
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error en la petición:', error);
            throw error;
        }
    }

    /**
     * Realiza una petición POST a la API
     * @param {string} endpoint - Endpoint de la API
     * @param {Object} body - Cuerpo de la petición
     * @returns {Promise<Object>} Respuesta de la API
     */
    async post(endpoint, body) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error en la petición:', error);
            throw error;
        }
    }
}

/**
 * Instancia del cliente API
 */
const apiClient = new ApiClient(API_BASE_URL);

/**
 * Clase para manejar la interfaz de usuario
 */
class UIManager {
    /**
     * Muestra el estado de carga
     */
    showLoading() {
        elements.loading.style.display = 'flex';
        elements.content.style.display = 'none';
        elements.error.style.display = 'none';
    }

    /**
     * Muestra el contenido principal
     */
    showContent() {
        elements.loading.style.display = 'none';
        elements.content.style.display = 'block';
        elements.error.style.display = 'none';
    }

    /**
     * Muestra un mensaje de error
     * @param {string} message - Mensaje de error
     */
    showError(message) {
        elements.loading.style.display = 'none';
        elements.content.style.display = 'none';
        elements.error.style.display = 'block';
        elements.errorMessage.textContent = message;
    }

    /**
     * Actualiza el contenido con los datos de la API
     * @param {Object} data - Datos de la API
     */
    updateContent(data) {
        elements.apiMessage.textContent = data.message;
        elements.apiVersion.textContent = data.version;
        elements.apiTimestamp.textContent = this.formatTimestamp(data.timestamp);
    }

    /**
     * Formatea un timestamp para mostrar
     * @param {string} timestamp - Timestamp ISO
     * @returns {string} Timestamp formateado
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    /**
     * Actualiza la visibilidad de secciones según el estado de autenticación
     */
    updateAuthUI() {
        // Mostrar/ocultar botones de autenticación
        if (appState.isAuthenticated) {
            elements.btnLogin.style.display = 'none';
            elements.btnLogout.style.display = 'block';
            elements.protectedSection.style.display = 'block';
        } else {
            elements.btnLogin.style.display = 'block';
            elements.btnLogout.style.display = 'none';
            elements.protectedSection.style.display = 'none';
        }

        // Mostrar/ocultar sección de administrador
        if (appState.isAuthenticated && appState.isAdmin) {
            elements.adminSection.style.display = 'block';
        } else {
            elements.adminSection.style.display = 'none';
        }
    }
}

/**
 * Instancia del gestor de UI
 */
const uiManager = new UIManager();

/**
 * Controlador principal de la aplicación
 */
class AppController {
    /**
     * Inicializa la aplicación
     */
    async init() {
        console.log('Inicializando aplicación...');
        
        // Registrar event listeners
        this.registerEventListeners();
        
        // Cargar datos iniciales
        await this.loadLandingData();
        
        // Actualizar UI según estado de autenticación
        uiManager.updateAuthUI();
    }

    /**
     * Registra los event listeners de la aplicación
     */
    registerEventListeners() {
        // Botón de login (placeholder para siguiente tutorial)
        elements.btnLogin.addEventListener('click', () => {
            console.log('Login button clicked - Implementación en próximo tutorial');
            alert('La funcionalidad de autenticación se implementará en el siguiente tutorial');
        });

        // Botón de logout (placeholder para siguiente tutorial)
        elements.btnLogout.addEventListener('click', () => {
            console.log('Logout button clicked - Implementación en próximo tutorial');
            alert('La funcionalidad de cierre de sesión se implementará en el siguiente tutorial');
        });

        // Botón de acción de usuario (placeholder)
        const btnUserAction = document.getElementById('btnUserAction');
        if (btnUserAction) {
            btnUserAction.addEventListener('click', () => {
                console.log('User action button clicked');
                alert('Esta funcionalidad requiere autenticación');
            });
        }

        // Botón de acción de administrador (placeholder)
        const btnAdminAction = document.getElementById('btnAdminAction');
        if (btnAdminAction) {
            btnAdminAction.addEventListener('click', () => {
                console.log('Admin action button clicked');
                alert('Esta funcionalidad requiere privilegios de administrador');
            });
        }
    }

    /**
     * Carga los datos de bienvenida desde la API
     */
    async loadLandingData() {
        try {
            // Mostrar estado de carga
            uiManager.showLoading();

            // Realizar petición a la API
            const data = await apiClient.get('/landing');

            // Verificar que la respuesta sea exitosa
            if (data.status === 'success') {
                uiManager.updateContent(data);
                uiManager.showContent();
            } else {
                throw new Error('La respuesta de la API no fue exitosa');
            }

        } catch (error) {
            console.error('Error al cargar datos:', error);
            uiManager.showError(
                'No se pudo conectar con la API. Verifica que el servidor esté ejecutándose.'
            );
        }
    }
}

/**
 * Punto de entrada de la aplicación
 */
document.addEventListener('DOMContentLoaded', () => {
    const app = new AppController();
    app.init();
});
```

## Parte 4: Análisis del Flujo de Datos

### 4.1. Ciclo de Vida de una Petición

1. **Inicialización**: El evento `DOMContentLoaded` se dispara cuando el DOM está completamente cargado
2. **Instanciación**: Se crea una instancia de `AppController` y se llama a `init()`
3. **Petición HTTP**: `apiClient.get('/landing')` realiza una petición GET al endpoint
4. **Procesamiento**: Laravel procesa la petición y retorna una respuesta JSON
5. **Respuesta**: La respuesta viaja de vuelta al cliente
6. **Renderizado**: `UIManager` actualiza el DOM con los datos recibidos

### 4.2. Manejo de Estados

La aplicación maneja tres estados principales:

- **Loading**: Estado inicial mientras se cargan los datos
- **Success**: Datos cargados correctamente y mostrados al usuario
- **Error**: Ocurrió un problema y se muestra un mensaje de error

### 4.3. Arquitectura del Código JavaScript

El código está organizado en clases con responsabilidades específicas:

- **ApiClient**: Maneja toda la comunicación con el backend
- **UIManager**: Gestiona las actualizaciones del DOM y la presentación
- **AppController**: Coordina la lógica de negocio y el flujo de la aplicación

Esta separación de responsabilidades facilita el mantenimiento y la escalabilidad del código.

## Parte 5: Pruebas y Verificación

### 5.1. Verificar el Servidor

Asegurarse de que el servidor Laravel esté ejecutándose:

```bash
php artisan serve
```

### 5.2. Acceder a la Aplicación

Abrir en el navegador:

```
http://localhost:8000/frontend/index.html
```

### 5.3. Consola del Navegador

Abrir las herramientas de desarrollador (F12) y verificar:

- **Console**: No debe haber errores JavaScript
- **Network**: Debe aparecer la petición a `/api/landing` con status 200
- **Response**: Verificar la estructura JSON de la respuesta

### 5.4. Comportamiento Esperado

1. Al cargar la página, debe aparecer un spinner de carga
2. Después de ~1 segundo, debe mostrarse el contenido con los datos de la API
3. Los valores mostrados deben corresponder a la respuesta del endpoint `/landing`
4. Las secciones de usuario autenticado y administrador NO deben ser visibles

## Parte 6: Conceptos Clave del Flujo de Comunicación

### 6.1. Promesas y Programación Asíncrona

JavaScript es single-threaded, pero las peticiones HTTP son operaciones asíncronas. Las Promesas permiten manejar este comportamiento sin bloquear la ejecución del código.

```javascript
// Sin await - retorna una Promesa
const promise = fetch('/api/landing');

// Con await - espera el resultado
const response = await fetch('/api/landing');
```

### 6.2. Manejo de Errores

Es fundamental manejar errores en dos niveles:

1. **Errores de red**: Conexión rechazada, timeout, etc.
2. **Errores HTTP**: Status codes 4xx, 5xx

```javascript
try {
    const response = await fetch(url);
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }
    const data = await response.json();
} catch (error) {
    // Manejar ambos tipos de errores
}
```

### 6.3. Headers HTTP

Los headers informan al servidor sobre el tipo de contenido que se envía y se espera:

```javascript
headers: {
    'Content-Type': 'application/json',  // Enviamos JSON
    'Accept': 'application/json'          // Esperamos JSON
}
```

### 6.4. Manipulación del DOM

Las actualizaciones del DOM deben realizarse de manera eficiente:

```javascript
// Cachear referencias a elementos
const element = document.getElementById('myElement');

// Actualizar contenido
element.textContent = 'Nuevo texto';

// Alternar visibilidad
element.style.display = 'block';
```

## Parte 7: Extensiones y Mejoras Futuras

### 7.1. Preparación para Autenticación

El código incluye placeholders para funcionalidad de autenticación:

- Botones de login/logout
- Objeto `appState` para mantener información de sesión
- Secciones protegidas en el HTML
- Método `updateAuthUI()` para actualizar la interfaz

### 7.2. Gestión de Tokens

En el siguiente tutorial se implementará:

- Almacenamiento de tokens JWT en `localStorage`
- Inclusión de tokens en headers de peticiones autenticadas
- Renovación automática de tokens
- Manejo de expiración de sesión

### 7.3. Autorización por Roles

Se implementará lógica para:

- Verificar el rol del usuario autenticado
- Mostrar/ocultar elementos según permisos
- Validar permisos antes de ejecutar acciones

## Conclusión

Este tutorial ha cubierto los fundamentos de comunicación entre un frontend vanilla y una API REST en Laravel. Se ha establecido:

- Una arquitectura modular y escalable en JavaScript
- Patrones de comunicación asíncrona con el backend
- Manejo apropiado de estados y errores
- Una base sólida para implementar autenticación y autorización

El siguiente tutorial extenderá esta aplicación agregando autenticación completa con Sanctum, permitiendo que los usuarios se registren, inicien sesión, y accedan a recursos protegidos según sus roles.

## Referencias Adicionales

- [MDN - Fetch API](https://developer.mozilla.org/es/docs/Web/API/Fetch_API)
- [MDN - Async/Await](https://developer.mozilla.org/es/docs/Web/JavaScript/Reference/Statements/async_function)
- [Laravel - API Resources](https://laravel.com/docs/eloquent-resources)
- [Laravel - CORS](https://laravel.com/docs/routing#cors)
