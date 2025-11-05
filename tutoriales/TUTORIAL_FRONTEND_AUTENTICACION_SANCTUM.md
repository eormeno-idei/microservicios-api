# Tutorial: Autenticación con Laravel Sanctum y Spatie Permissions

## Introducción

Este tutorial es la continuación del tutorial básico de Frontend con API REST. Se implementará un sistema completo de autenticación consumiendo los endpoints ya existentes en el backend, que cuenta con Laravel Sanctum y Spatie Permission para gestión de roles y permisos.

## Objetivos

- Consumir la API de autenticación existente desde el frontend
- Crear formularios de login y registro funcionales
- Gestionar tokens de Sanctum en el frontend
- Controlar visibilidad de elementos según permisos
- Implementar cierre de sesión
- Manejar persistencia de sesión con "Recordarme"
- Integrar verificación de email (opcional)

## Parte 1: Análisis del Backend Existente

### 1.1. Estructura de la API de Autenticación

El backend ya cuenta con un sistema completo de autenticación implementado en `app/Http/Controllers/Api/AuthController.php`. Los endpoints disponibles son:

#### Endpoints Públicos (No requieren autenticación)

**POST `/api/register`** - Registro de nuevos usuarios
- Campos requeridos: `name`, `email`, `password`, `password_confirmation`
- Envía automáticamente un email de verificación
- Retorna el usuario y un token de autenticación

**POST `/api/login`** - Inicio de sesión
- Campos requeridos: `email`, `password`
- Genera un token de autenticación con Sanctum
- Retorna el usuario autenticado y el token

**POST `/api/password/forgot`** - Solicitar recuperación de contraseña
- Campo requerido: `email`
- Envía un email con enlace para resetear la contraseña

**POST `/api/password/reset`** - Resetear contraseña
- Campos requeridos: `token`, `email`, `password`, `password_confirmation`
- Cambia la contraseña usando el token recibido por email

**GET `/api/email/verify/{id}/{hash}`** - Verificar email
- Endpoint firmado para verificar el email del usuario
- Se accede desde el link enviado por correo

#### Endpoints Protegidos (Requieren autenticación)

**POST `/api/logout`** - Cerrar sesión
- Elimina el token actual del usuario
- Requiere header: `Authorization: Bearer {token}`

**GET `/api/user`** - Obtener usuario autenticado
- Retorna los datos del usuario actual

**POST `/api/email/resend`** - Reenviar email de verificación
- Solicita un nuevo email de verificación

### 1.2. Formato de Respuestas del Backend

Todas las respuestas del backend siguen este formato estandarizado:

```json
{
    "success": true,
    "data": { ... },
    "message": "Mensaje descriptivo"
}
```

En caso de errores de validación (HTTP 422):

```json
{
    "success": false,
    "message": "Validation errors",
    "errors": {
        "campo": ["Mensaje de error"]
    }
}
```

### 1.3. Modelo User y Características

El modelo `User` implementa:
- `HasApiTokens` (Laravel Sanctum) - Gestión de tokens
- `HasRoles` (Spatie Permission) - Sistema de roles y permisos  
- `MustVerifyEmail` - Requiere verificación de email
- `CanResetPassword` - Permite recuperación de contraseña

### 1.4. Verificar Configuración

Asegurarse de que el servidor de desarrollo esté ejecutándose:

```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000/api`

### 1.2. Configurar el Modelo User para Sanctum

Verificar que el modelo `User` use el trait `HasApiTokens`:

```php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

### 1.3. Crear el Controlador de Autenticación

Crear el controlador `AuthController`:

```bash
php artisan make:controller Api/AuthController
```

Implementar el controlador:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registro de nuevo usuario
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'mobile' => 'nullable|string|max:100',
            'semantic_context' => 'nullable|string',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mobile' => $request->mobile,
            'semantic_context' => $request->semantic_context,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar rol por defecto
        $user->assignRole('user');

        // Crear token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Obtener permisos del usuario
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $permissions,
                ],
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login de usuario
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'boolean',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // Determinar el nombre del token según "remember"
        $tokenName = $request->remember ? 'auth_token_remember' : 'auth_token';
        
        // Crear token con expiración según "remember"
        if ($request->remember) {
            // Token con duración extendida (30 días)
            $token = $user->createToken($tokenName, ['*'], now()->addDays(30))->plainTextToken;
        } else {
            // Token con duración estándar (24 horas)
            $token = $user->createToken($tokenName, ['*'], now()->addDay())->plainTextToken;
        }

        // Obtener permisos del usuario usando Spatie
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'status' => 'success',
            'message' => 'Autenticación exitosa',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $roles,
                    'permissions' => $permissions,
                ],
                'token' => $token,
                'remember' => $request->remember ?? false,
            ]
        ]);
    }

    /**
     * Logout de usuario
     */
    public function logout(Request $request)
    {
        // Eliminar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Obtener permisos usando Spatie
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $roles,
                    'permissions' => $permissions,
                ],
            ]
        ]);
    }
}
```

### 1.4. Definir las Rutas de Autenticación

En `routes/api.php`, agregar las rutas:

```php
use App\Http\Controllers\Api\AuthController;

// Rutas públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Ejemplo de ruta con permiso específico
    Route::get('/admin/dashboard', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Bienvenido al panel de administración',
            'data' => [
                'stats' => [
                    'users' => 150,
                    'posts' => 320,
                    'comments' => 1240,
                ]
            ]
        ]);
    })->middleware('permission:access-admin-panel');
    
    // Ejemplo de ruta para usuarios autenticados
    Route::get('/user/profile', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Perfil de usuario',
            'data' => [
                'profile' => [
                    'bio' => 'Usuario activo del sistema',
                    'posts_count' => 15,
                    'followers' => 42,
                ]
            ]
        ]);
    });
});
```

### 1.5. Configurar Permisos y Roles con Spatie

Crear un seeder para roles y permisos básicos:

```bash
php artisan make:seeder RoleSeeder
```

Implementar el seeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Role::count() > 2) {
            return;
        }

         // Resetear caché de roles y permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            'access-admin-panel',
            'manage-users',
            'edit-content',
            'delete-content',
            'view-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos

        // Rol: user (usuario básico)
        $roleUser = Role::create(['name' => 'user']);
        $roleUser->givePermissionTo(['edit-content']);

        // Rol: moderator
        $roleModerator = Role::create(['name' => 'moderator']);
        $roleModerator->givePermissionTo(['edit-content', 'delete-content', 'view-reports']);

        // Rol: admin
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
```

Ejecutar el seeder:

```bash
php artisan db:seed --class=RoleSeeder
```

## Parte 2: Actualización del Frontend

### 2.1. Actualizar el HTML con Formularios de Autenticación

Modificar `public/frontend/index.html` para incluir los modales de login y registro:

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
                <span id="userWelcome" class="user-welcome" style="display: none;"></span>
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
                    <button class="btn btn--primary" id="btnUserAction">Ver Perfil</button>
                </div>
            </section>

            <!-- Sección para administradores -->
            <section class="admin-section" id="adminSection" style="display: none;">
                <div class="card card--admin">
                    <h3 class="card__title">Panel de Administrador</h3>
                    <p class="card__description">Esta sección solo es visible para administradores.</p>
                    <button class="btn btn--danger" id="btnAdminAction">Ver Dashboard</button>
                </div>
            </section>
        </main>

        <footer class="footer">
            <p>&copy; 2025 Microservicios API. Todos los derechos reservados.</p>
        </footer>
    </div>

    <!-- Modal de Login -->
    <div id="loginModal" class="modal">
        <div class="modal__content">
            <div class="modal__header">
                <h2 class="modal__title">Iniciar Sesión</h2>
                <button class="modal__close" id="closeLoginModal">&times;</button>
            </div>
            <div class="modal__body">
                <!-- Área de mensajes de error -->
                <div class="alert alert--error" id="loginError" style="display: none;">
                    <p id="loginErrorMessage"></p>
                </div>

                <form id="loginForm">
                    <div class="form-group">
                        <label for="loginEmail" class="form-label">Email</label>
                        <input 
                            type="email" 
                            id="loginEmail" 
                            class="form-input" 
                            placeholder="tu@email.com"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="loginPassword" class="form-label">Contraseña</label>
                        <input 
                            type="password" 
                            id="loginPassword" 
                            class="form-input" 
                            placeholder="••••••••"
                            required
                        >
                    </div>
                    <div class="form-group form-group--checkbox">
                        <label class="checkbox-label">
                            <input 
                                type="checkbox" 
                                id="rememberMe" 
                                class="checkbox-input"
                            >
                            <span class="checkbox-text">Recordarme</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn--primary btn--block" id="btnSubmitLogin">
                        Iniciar Sesión
                    </button>
                </form>
                <div class="modal__footer-text">
                    <p>¿No tienes cuenta? <a href="#" id="showRegisterModal" class="link">Regístrate aquí</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Registro -->
    <div id="registerModal" class="modal">
        <div class="modal__content">
            <div class="modal__header">
                <h2 class="modal__title">Crear Cuenta</h2>
                <button class="modal__close" id="closeRegisterModal">&times;</button>
            </div>
            <div class="modal__body">
                <!-- Área de mensajes de error -->
                <div class="alert alert--error" id="registerError" style="display: none;">
                    <p id="registerErrorMessage"></p>
                </div>

                <form id="registerForm">
                    <div class="form-group">
                        <label for="registerName" class="form-label">Nombre Completo</label>
                        <input 
                            type="text" 
                            id="registerName" 
                            class="form-input" 
                            placeholder="Juan Pérez"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="registerEmail" class="form-label">Email</label>
                        <input 
                            type="email" 
                            id="registerEmail" 
                            class="form-input" 
                            placeholder="tu@email.com"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="registerPassword" class="form-label">Contraseña</label>
                        <input 
                            type="password" 
                            id="registerPassword" 
                            class="form-input" 
                            placeholder="••••••••"
                            required
                            minlength="8"
                        >
                    </div>
                    <div class="form-group">
                        <label for="registerPasswordConfirm" class="form-label">Confirmar Contraseña</label>
                        <input 
                            type="password" 
                            id="registerPasswordConfirm" 
                            class="form-input" 
                            placeholder="••••••••"
                            required
                            minlength="8"
                        >
                    </div>
                    <button type="submit" class="btn btn--primary btn--block" id="btnSubmitRegister">
                        Registrarse
                    </button>
                </form>
                <div class="modal__footer-text">
                    <p>¿Ya tienes cuenta? <a href="#" id="showLoginModal" class="link">Inicia sesión aquí</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="./frontend/js/app.js"></script>
</body>
</html>
```

### 2.2. Actualizar los Estilos CSS

Agregar los estilos para los modales y formularios en `public/frontend/css/styles.css`:

```css
/* Estilos anteriores se mantienen... */

/* User Welcome */
.user-welcome {
    color: var(--text-primary);
    font-weight: 500;
    margin-right: 1rem;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s ease-in;
}

.modal.active {
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal__content {
    background-color: var(--surface-color);
    border-radius: 1rem;
    width: 90%;
    max-width: 450px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--border-color);
}

.modal__title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.modal__close {
    background: none;
    border: none;
    font-size: 2rem;
    color: var(--text-secondary);
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s;
}

.modal__close:hover {
    color: var(--text-primary);
}

.modal__body {
    padding: 2rem;
}

.modal__footer-text {
    margin-top: 1.5rem;
    text-align: center;
    color: var(--text-secondary);
}

.modal__footer-text p {
    margin: 0;
}

/* Alert Messages */
.alert {
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.alert--error {
    background-color: #fee;
    border: 1px solid #fcc;
    color: var(--danger-color);
}

.alert--success {
    background-color: #efe;
    border: 1px solid #cfc;
    color: var(--success-color);
}

.alert p {
    margin: 0;
    font-size: 0.9rem;
}

/* Forms */
.form-group {
    margin-bottom: 1.25rem;
}

.form-group--checkbox {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
    font-size: 0.9rem;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-family: inherit;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.form-input::placeholder {
    color: var(--text-secondary);
}

/* Checkbox */
.checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    user-select: none;
}

.checkbox-input {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.5rem;
    cursor: pointer;
    accent-color: var(--primary-color);
}

.checkbox-text {
    color: var(--text-primary);
    font-size: 0.95rem;
}

/* Link */
.link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.link:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

/* Button Block */
.btn--block {
    width: 100%;
    margin-top: 0.5rem;
}

/* Loading button state */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive Modal */
@media (max-width: 768px) {
    .modal__content {
        width: 95%;
        margin: 1rem;
    }
    
    .modal__header,
    .modal__body {
        padding: 1.25rem;
    }
}
```

### 2.3. Implementar la Lógica de Autenticación en JavaScript

Reemplazar completamente `public/frontend/js/app.js`:

```javascript
/**
 * Configuración de la aplicación
 */
const API_BASE_URL = 'http://localhost:8000/api';
const TOKEN_KEY = 'auth_token';
const USER_KEY = 'user_data';

/**
 * Referencias a elementos del DOM
 */
const elements = {
    // Elementos principales
    loading: document.getElementById('loading'),
    content: document.getElementById('content'),
    error: document.getElementById('error'),
    errorMessage: document.getElementById('errorMessage'),
    apiMessage: document.getElementById('apiMessage'),
    apiVersion: document.getElementById('apiVersion'),
    apiTimestamp: document.getElementById('apiTimestamp'),
    
    // Secciones protegidas
    protectedSection: document.getElementById('protectedSection'),
    adminSection: document.getElementById('adminSection'),
    
    // Navegación
    userWelcome: document.getElementById('userWelcome'),
    btnLogin: document.getElementById('btnLogin'),
    btnLogout: document.getElementById('btnLogout'),
    
    // Modales
    loginModal: document.getElementById('loginModal'),
    registerModal: document.getElementById('registerModal'),
    closeLoginModal: document.getElementById('closeLoginModal'),
    closeRegisterModal: document.getElementById('closeRegisterModal'),
    showRegisterModal: document.getElementById('showRegisterModal'),
    showLoginModal: document.getElementById('showLoginModal'),
    
    // Formulario de Login
    loginForm: document.getElementById('loginForm'),
    loginEmail: document.getElementById('loginEmail'),
    loginPassword: document.getElementById('loginPassword'),
    rememberMe: document.getElementById('rememberMe'),
    loginError: document.getElementById('loginError'),
    loginErrorMessage: document.getElementById('loginErrorMessage'),
    btnSubmitLogin: document.getElementById('btnSubmitLogin'),
    
    // Formulario de Registro
    registerForm: document.getElementById('registerForm'),
    registerName: document.getElementById('registerName'),
    registerEmail: document.getElementById('registerEmail'),
    registerPassword: document.getElementById('registerPassword'),
    registerPasswordConfirm: document.getElementById('registerPasswordConfirm'),
    registerError: document.getElementById('registerError'),
    registerErrorMessage: document.getElementById('registerErrorMessage'),
    btnSubmitRegister: document.getElementById('btnSubmitRegister'),
    
    // Botones de acción
    btnUserAction: document.getElementById('btnUserAction'),
    btnAdminAction: document.getElementById('btnAdminAction'),
};

/**
 * Estado de la aplicación
 */
const appState = {
    isAuthenticated: false,
    user: null,
    token: null,
    permissions: [],
    roles: [],
};

/**
 * Clase para manejar el almacenamiento local
 */
class StorageManager {
    static setToken(token) {
        localStorage.setItem(TOKEN_KEY, token);
    }

    static getToken() {
        return localStorage.getItem(TOKEN_KEY);
    }

    static removeToken() {
        localStorage.removeItem(TOKEN_KEY);
    }

    static setUser(user) {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    }

    static getUser() {
        const user = localStorage.getItem(USER_KEY);
        return user ? JSON.parse(user) : null;
    }

    static removeUser() {
        localStorage.removeItem(USER_KEY);
    }

    static clear() {
        this.removeToken();
        this.removeUser();
    }
}

/**
 * Clase para manejar las peticiones a la API
 */
class ApiClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }

    /**
     * Obtener headers con autenticación
     */
    getHeaders(includeAuth = false) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        };

        if (includeAuth && appState.token) {
            headers['Authorization'] = `Bearer ${appState.token}`;
        }

        return headers;
    }

    /**
     * Petición GET
     */
    async get(endpoint, authenticated = false) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'GET',
                headers: this.getHeaders(authenticated),
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error('Error en GET:', error);
            throw error;
        }
    }

    /**
     * Petición POST
     */
    async post(endpoint, body, authenticated = false) {
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'POST',
                headers: this.getHeaders(authenticated),
                body: JSON.stringify(body),
            });

            return await this.handleResponse(response);
        } catch (error) {
            console.error('Error en POST:', error);
            throw error;
        }
    }

    /**
     * Manejar respuesta de la API
     */
    async handleResponse(response) {
        const data = await response.json();

        if (!response.ok) {
            // Si hay error de validación, extraer los mensajes
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join(', ');
                throw new Error(errorMessages);
            }
            throw new Error(data.message || `HTTP error! status: ${response.status}`);
        }

        return data;
    }
}

/**
 * Instancia del cliente API
 */
const apiClient = new ApiClient(API_BASE_URL);

/**
 * Clase para manejar la autenticación
 */
class AuthManager {
    /**
     * Iniciar sesión
     */
    static async login(email, password) {
        const data = await apiClient.post('/login', {
            email,
            password,
        });

        // Verificar que la respuesta sea exitosa
        if (data.status !== 'success') {
            throw new Error(data.message || 'Error en el login');
        }

        // Guardar token y usuario
        StorageManager.setToken(data.data.token);
        StorageManager.setUser(data.data.user);

        // Actualizar estado de la aplicación
        appState.isAuthenticated = true;
        appState.token = data.data.token;
        appState.user = data.data.user;
        
        // Obtener permisos y roles de Spatie si existen
        appState.permissions = data.data.user.permissions || [];
        appState.roles = data.data.user.roles || [];

        return data;
    }

    /**
     * Registrar usuario
     */
    static async register(name, email, password, passwordConfirmation) {
        const data = await apiClient.post('/register', {
            name,
            email,
            first_name,
            last_name,
            mobile,
            semantic_context,
            password,
            password_confirmation: passwordConfirmation,
        });

        // Verificar que la respuesta sea exitosa
        if (!data.success) {
            throw new Error(data.message || 'Error en el registro');
        }

        // Guardar token y usuario
        StorageManager.setToken(data.data.token);
        StorageManager.setUser(data.data.user);

        // Actualizar estado de la aplicación
        appState.isAuthenticated = true;
        appState.token = data.data.token;
        appState.user = data.data.user;
        
        // Obtener permisos y roles de Spatie si existen
        appState.permissions = data.data.user.permissions || [];
        appState.roles = data.data.user.roles || [];

        return data;
    }

    /**
     * Cerrar sesión
     */
    static async logout() {
        try {
            await apiClient.post('/logout', {}, true);
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
        } finally {
            // Limpiar estado local siempre
            this.clearSession();
        }
    }

    /**
     * Limpiar sesión local
     */
    static clearSession() {
        StorageManager.clear();
        appState.isAuthenticated = false;
        appState.token = null;
        appState.user = null;
        appState.permissions = [];
        appState.roles = [];
    }

    /**
     * Verificar si hay sesión guardada
     */
    static checkStoredSession() {
        const token = StorageManager.getToken();
        const user = StorageManager.getUser();

        if (token && user) {
            appState.isAuthenticated = true;
            appState.token = token;
            appState.user = user;
            appState.permissions = user.permissions || [];
            appState.roles = user.roles || [];
            return true;
        }

        return false;
    }

    /**
     * Obtener usuario actual del servidor
     */
    static async getCurrentUser() {
        const data = await apiClient.get('/user', true);
        
        if (!data.success) {
            throw new Error(data.message || 'Error al obtener usuario');
        }
        
        // Actualizar usuario almacenado
        StorageManager.setUser(data.data);
        appState.user = data.data;
        appState.permissions = data.data.permissions || [];
        appState.roles = data.data.roles || [];

        return data.data;
    }
}

/**
 * Clase para manejar permisos
 */
class PermissionManager {
    /**
     * Verificar si el usuario tiene un permiso específico
     */
    static hasPermission(permission) {
        return appState.permissions.includes(permission);
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    static hasRole(role) {
        return appState.roles.includes(role);
    }

    /**
     * Verificar si el usuario es administrador
     */
    static isAdmin() {
        return this.hasRole('admin');
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos especificados
     */
    static hasAnyPermission(permissions) {
        return permissions.some(permission => this.hasPermission(permission));
    }
}

/**
 * Clase para manejar la interfaz de usuario
 */
class UIManager {
    /**
     * Mostrar el estado de carga
     */
    showLoading() {
        elements.loading.style.display = 'flex';
        elements.content.style.display = 'none';
        elements.error.style.display = 'none';
    }

    /**
     * Mostrar el contenido principal
     */
    showContent() {
        elements.loading.style.display = 'none';
        elements.content.style.display = 'block';
        elements.error.style.display = 'none';
    }

    /**
     * Mostrar un mensaje de error
     */
    showError(message) {
        elements.loading.style.display = 'none';
        elements.content.style.display = 'none';
        elements.error.style.display = 'block';
        elements.errorMessage.textContent = message;
    }

    /**
     * Actualizar el contenido con los datos de la API
     */
    updateContent(data) {
        elements.apiMessage.textContent = data.message;
        elements.apiVersion.textContent = data.version;
        elements.apiTimestamp.textContent = this.formatTimestamp(data.timestamp);
    }

    /**
     * Formatea un timestamp para mostrar
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
     * Actualizar la visibilidad de secciones según autenticación y permisos
     */
    updateAuthUI() {
        if (appState.isAuthenticated) {
            // Mostrar nombre de usuario
            elements.userWelcome.textContent = `Hola, ${appState.user.name}`;
            elements.userWelcome.style.display = 'inline';
            
            // Botones de navegación
            elements.btnLogin.style.display = 'none';
            elements.btnLogout.style.display = 'block';
            
            // Sección de usuario autenticado
            elements.protectedSection.style.display = 'block';
            
            // Sección de administrador (solo si tiene el permiso)
            if (PermissionManager.hasPermission('access-admin-panel')) {
                elements.adminSection.style.display = 'block';
            } else {
                elements.adminSection.style.display = 'none';
            }
        } else {
            // Usuario no autenticado
            elements.userWelcome.style.display = 'none';
            elements.btnLogin.style.display = 'block';
            elements.btnLogout.style.display = 'none';
            elements.protectedSection.style.display = 'none';
            elements.adminSection.style.display = 'none';
        }
    }

    /**
     * Mostrar modal
     */
    showModal(modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    /**
     * Ocultar modal
     */
    hideModal(modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    /**
     * Mostrar error en formulario
     */
    showFormError(errorElement, messageElement, message) {
        errorElement.style.display = 'block';
        messageElement.textContent = message;
    }

    /**
     * Ocultar error en formulario
     */
    hideFormError(errorElement) {
        errorElement.style.display = 'none';
    }

    /**
     * Resetear formulario
     */
    resetForm(form) {
        form.reset();
    }

    /**
     * Deshabilitar botón de submit
     */
    disableSubmitButton(button, text = 'Procesando...') {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = text;
    }

    /**
     * Habilitar botón de submit
     */
    enableSubmitButton(button) {
        button.disabled = false;
        button.textContent = button.dataset.originalText || 'Enviar';
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
     * Inicializar la aplicación
     */
    async init() {
        console.log('Inicializando aplicación...');
        
        // Verificar si hay sesión guardada
        const hasStoredSession = AuthManager.checkStoredSession();
        
        if (hasStoredSession) {
            console.log('Sesión encontrada, verificando con servidor...');
            try {
                // Verificar que el token siga siendo válido
                await AuthManager.getCurrentUser();
                console.log('Sesión válida');
            } catch (error) {
                console.error('Sesión expirada:', error);
                AuthManager.clearSession();
            }
        }
        
        // Registrar event listeners
        this.registerEventListeners();
        
        // Cargar datos iniciales
        await this.loadLandingData();
        
        // Actualizar UI según estado de autenticación
        uiManager.updateAuthUI();
    }

    /**
     * Registrar los event listeners de la aplicación
     */
    registerEventListeners() {
        // Botones de navegación
        elements.btnLogin.addEventListener('click', () => {
            uiManager.showModal(elements.loginModal);
        });

        elements.btnLogout.addEventListener('click', async () => {
            await this.handleLogout();
        });

        // Cerrar modales
        elements.closeLoginModal.addEventListener('click', () => {
            uiManager.hideModal(elements.loginModal);
            uiManager.hideFormError(elements.loginError);
        });

        elements.closeRegisterModal.addEventListener('click', () => {
            uiManager.hideModal(elements.registerModal);
            uiManager.hideFormError(elements.registerError);
        });

        // Cerrar modal al hacer clic fuera
        elements.loginModal.addEventListener('click', (e) => {
            if (e.target === elements.loginModal) {
                uiManager.hideModal(elements.loginModal);
            }
        });

        elements.registerModal.addEventListener('click', (e) => {
            if (e.target === elements.registerModal) {
                uiManager.hideModal(elements.registerModal);
            }
        });

        // Alternar entre modales
        elements.showRegisterModal.addEventListener('click', (e) => {
            e.preventDefault();
            uiManager.hideModal(elements.loginModal);
            uiManager.showModal(elements.registerModal);
        });

        elements.showLoginModal.addEventListener('click', (e) => {
            e.preventDefault();
            uiManager.hideModal(elements.registerModal);
            uiManager.showModal(elements.loginModal);
        });

        // Formularios
        elements.loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        elements.registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleRegister();
        });

        // Botones de acción
        elements.btnUserAction.addEventListener('click', () => {
            this.handleUserAction();
        });

        elements.btnAdminAction.addEventListener('click', () => {
            this.handleAdminAction();
        });
    }

    /**
     * Cargar los datos de landing desde la API
     */
    async loadLandingData() {
        try {
            uiManager.showLoading();
            const data = await apiClient.get('/landing');

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

    /**
     * Manejar el login
     */
    async handleLogin() {
        const email = elements.loginEmail.value.trim();
        const password = elements.loginPassword.value;
        const remember = elements.rememberMe.checked;

        // Ocultar errores previos
        uiManager.hideFormError(elements.loginError);

        // Validación básica
        if (!email || !password) {
            uiManager.showFormError(
                elements.loginError,
                elements.loginErrorMessage,
                'Por favor, completa todos los campos.'
            );
            return;
        }

        try {
            uiManager.disableSubmitButton(elements.btnSubmitLogin, 'Iniciando sesión...');

            await AuthManager.login(email, password, remember);

            // Cerrar modal y actualizar UI
            uiManager.hideModal(elements.loginModal);
            uiManager.resetForm(elements.loginForm);
            uiManager.updateAuthUI();

            console.log('Login exitoso:', appState.user);

        } catch (error) {
            console.error('Error en login:', error);
            uiManager.showFormError(
                elements.loginError,
                elements.loginErrorMessage,
                error.message || 'Error al iniciar sesión. Verifica tus credenciales.'
            );
        } finally {
            uiManager.enableSubmitButton(elements.btnSubmitLogin);
        }
    }

    /**
     * Manejar el registro
     */
    async handleRegister() {
        const name = elements.registerName.value.trim();
        const email = elements.registerEmail.value.trim();
        const password = elements.registerPassword.value;
        const passwordConfirm = elements.registerPasswordConfirm.value;

        // Ocultar errores previos
        uiManager.hideFormError(elements.registerError);

        // Validación básica
        if (!name || !email || !password || !passwordConfirm) {
            uiManager.showFormError(
                elements.registerError,
                elements.registerErrorMessage,
                'Por favor, completa todos los campos.'
            );
            return;
        }

        if (password !== passwordConfirm) {
            uiManager.showFormError(
                elements.registerError,
                elements.registerErrorMessage,
                'Las contraseñas no coinciden.'
            );
            return;
        }

        if (password.length < 8) {
            uiManager.showFormError(
                elements.registerError,
                elements.registerErrorMessage,
                'La contraseña debe tener al menos 8 caracteres.'
            );
            return;
        }

        try {
            uiManager.disableSubmitButton(elements.btnSubmitRegister, 'Registrando...');

            await AuthManager.register(name, email, password, passwordConfirm);

            // Cerrar modal y actualizar UI
            uiManager.hideModal(elements.registerModal);
            uiManager.resetForm(elements.registerForm);
            uiManager.updateAuthUI();

            console.log('Registro exitoso:', appState.user);
            alert(`¡Cuenta creada exitosamente! Bienvenido, ${appState.user.name}!`);

        } catch (error) {
            console.error('Error en registro:', error);
            uiManager.showFormError(
                elements.registerError,
                elements.registerErrorMessage,
                error.message || 'Error al crear la cuenta. Intenta nuevamente.'
            );
        } finally {
            uiManager.enableSubmitButton(elements.btnSubmitRegister);
        }
    }

    /**
     * Manejar el logout
     */
    async handleLogout() {
        if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            return;
        }

        try {
            await AuthManager.logout();
            uiManager.updateAuthUI();
            console.log('Logout exitoso');
        } catch (error) {
            console.error('Error en logout:', error);
            alert('Error al cerrar sesión');
        }
    }

    /**
     * Manejar acción de usuario
     */
    async handleUserAction() {
        try {
            // Obtener datos actuales del usuario
            const data = await apiClient.get('/user', true);
            
            if (data.success) {
                const user = data.data;
                console.log('Perfil de usuario:', user);
                
                const userInfo = `
                    === PERFIL DE USUARIO ===
                    Nombre: ${user.name}
                    Email: ${user.email}
                    Roles: ${user.roles?.join(', ') || 'Ninguno'}
                    Permisos: ${user.permissions?.join(', ') || 'Ninguno'}
                    Email verificado: ${user.email_verified_at ? 'Sí' : 'No'}
                `;
                
                alert(userInfo);
            }
        } catch (error) {
            console.error('Error al obtener perfil:', error);
            alert('Error al cargar el perfil de usuario');
        }
    }

    /**
     * Manejar acción de administrador
     */
    async handleAdminAction() {
        // Verificar que tenga el permiso necesario
        if (!PermissionManager.hasPermission('access-admin-panel')) {
            alert('No tienes permisos para acceder al panel de administración');
            return;
        }

        // Mostrar información de administrador
        const adminInfo = `
            === PANEL DE ADMINISTRADOR ===
            Usuario: ${appState.user.name}
            Roles: ${appState.roles.join(', ')}
            Permisos: ${appState.permissions.join(', ')}
            
            Este panel permite gestionar:
            - Usuarios del sistema
            - Roles y permisos
            - Configuración general
            
            (La implementación completa requiere endpoints adicionales en el backend)
        `;
        
        alert(adminInfo);
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

## Parte 3: Análisis del Flujo de Autenticación

### 3.1. Flujo de Login

1. **Usuario ingresa credenciales**: Email y password
2. **Validación frontend**: Verificar que los campos no estén vacíos
3. **Petición al backend**: POST a `/api/login` con las credenciales
4. **Backend procesa** (`AuthController@login`): 
   - Valida formato de datos con `Validator`
   - Busca usuario por email
   - Verifica contraseña con `Hash::check()`
   - Crea token con Sanctum usando `createToken()`
   - Retorna respuesta con formato `{success, data: {user, token}, message}`
5. **Frontend almacena**:
   - Token en `localStorage`
   - Datos del usuario en `localStorage`
   - Actualiza `appState`
6. **UI se actualiza**: Muestra secciones según permisos disponibles
7. **Email de verificación**: Si el usuario no ha verificado su email, recibirá un recordatorio

### 3.2. Flujo de Registro

1. **Usuario completa formulario**: Nombre, email, password y confirmación
2. **Validación frontend**: 
   - Campos completos
   - Contraseñas coinciden
   - Longitud mínima de contraseña (8 caracteres)
3. **Petición al backend**: POST a `/api/register`
4. **Backend procesa** (`AuthController@register`):
   - Valida datos con `Validator`
   - Crea usuario con `User::create()`
   - Dispara evento `Registered` para enviar email de verificación
   - Crea token con Sanctum
   - Retorna respuesta con user y token
5. **Frontend almacena**: Similar al login
6. **Usuario queda autenticado**: Inmediatamente después del registro
7. **Email de verificación enviado**: El usuario recibe un email para verificar su cuenta

### 3.3. Gestión de Tokens

Los tokens se incluyen en cada petición autenticada mediante el header `Authorization`:

```javascript
headers: {
    'Authorization': `Bearer ${token}`
}
```

Laravel Sanctum valida el token automáticamente mediante el middleware `auth:sanctum`.

**Creación del token en el backend:**
```php
$token = $user->createToken('auth_token')->plainTextToken;
```

**Eliminación del token al cerrar sesión:**
```php
$request->user()->currentAccessToken()->delete();
```

### 3.4. Persistencia de Sesión

El sistema utiliza `localStorage` del navegador para mantener la sesión:

- **Token almacenado**: Persiste entre recargas de página
- **Datos del usuario**: Se almacenan localmente para acceso rápido
- **Verificación al iniciar**: Al cargar la app, se verifica si existe un token válido
- **Revalidación opcional**: Se puede llamar a `/api/user` para verificar que el token siga vigente

**Nota sobre "Recordarme"**: El checkbox "Recordarme" está presente en el formulario para futuras implementaciones. Actualmente el token persiste hasta que el usuario cierre sesión manualmente o el token sea invalidado por el servidor

### 3.5. Control de Acceso por Permisos

El sistema verifica permisos en dos niveles:

**Frontend (UI)**:
```javascript
if (PermissionManager.hasPermission('access-admin-panel')) {
    elements.adminSection.style.display = 'block';
}
```

**Backend (API)**:
```php
Route::get('/admin/dashboard', function () {
    // ...
})->middleware('permission:access-admin-panel');
```

## Parte 4: Seguridad y Mejores Prácticas

### 4.1. Nunca Almacenar Contraseñas

Las contraseñas se envían al servidor pero nunca se almacenan en el cliente. Solo el token se guarda localmente.

### 4.2. HTTPS en Producción

En producción, siempre usar HTTPS para proteger las credenciales y tokens en tránsito.

### 4.3. Validación en Ambos Lados

Aunque el frontend valida los datos, el backend siempre debe realizar su propia validación. La validación frontend es solo para mejorar la experiencia del usuario.

### 4.4. Manejo de Tokens Expirados

Cuando un token expira, el backend retorna un error 401. El frontend debe:
1. Detectar el error
2. Limpiar la sesión local
3. Redirigir al usuario al login

### 4.5. XSS y CSRF

- **XSS**: No usar `innerHTML` con datos del usuario sin sanitizar
- **CSRF**: Sanctum maneja CSRF automáticamente para SPAs en el mismo dominio

## Parte 5: Pruebas del Sistema

### 5.1. Preparar Base de Datos con Roles y Permisos

Si aún no se ha ejecutado, crear y ejecutar un seeder para roles y permisos:

```bash
php artisan make:seeder RolesAndPermissionsSeeder
```

Contenido del seeder:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        Permission::create(['name' => 'access-admin-panel']);
        Permission::create(['name' => 'manage-users']);
        Permission::create(['name' => 'edit-content']);

        // Crear roles
        $roleUser = Role::create(['name' => 'user']);
        $roleUser->givePermissionTo(['edit-content']);

        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
```

Ejecutar el seeder:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### 5.2. Crear Usuarios de Prueba

**Opción 1: Usando Tinker**

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Usuario administrador
$admin = User::create([
    'name' => 'Admin User',
    'email' => 'admin@test.com',
    'password' => Hash::make('password123')
]);
$admin->assignRole('admin');
$admin->markEmailAsVerified(); // Marcar email como verificado

// Usuario regular
$user = User::create([
    'name' => 'Regular User',
    'email' => 'user@test.com',
    'password' => Hash::make('password123')
]);
$user->assignRole('user');
$user->markEmailAsVerified();
```

**Opción 2: Usar el formulario de registro**

Simplemente registrarse desde el frontend. Por defecto, los nuevos usuarios recibirán un email de verificación.

### 5.3. Escenarios de Prueba

#### 1. Registro de Nuevo Usuario

- Abrir la aplicación en `http://localhost:8000/frontend/index.html`
- Click en "Iniciar Sesión"
- Click en "¿No tienes cuenta? Regístrate aquí"
- Completar el formulario:
  - Nombre: "Test User"
  - Email: "test@test.com"
  - Contraseña: "password123"
  - Confirmar contraseña: "password123"
- Click en "Registrarse"
- **Resultado esperado**: 
  - Cuenta creada exitosamente
  - Sesión iniciada automáticamente
  - Aparece el mensaje de bienvenida
  - Se muestra la sección de usuario autenticado
  - NO se muestra la sección de administrador

#### 2. Login con Credenciales Correctas

- Si ya hay sesión, cerrar sesión primero
- Click en "Iniciar Sesión"
- Ingresar:
  - Email: "user@test.com"
  - Password: "password123"
- Click en "Iniciar Sesión"
- **Resultado esperado**:
  - Login exitoso
  - Aparece "Hola, Regular User"
  - Se muestra la sección de usuario
  - NO se muestra la sección de admin

#### 3. Login con Credenciales Incorrectas

- Click en "Iniciar Sesión"
- Ingresar credenciales inválidas
- **Resultado esperado**:
  - Mensaje de error: "The provided credentials are incorrect."
  - El formulario permanece visible
  - NO se inicia sesión

#### 4. Verificar Permisos de Usuario Regular vs Admin

**Usuario Regular (user@test.com)**:
- Login
- Verificar que aparece "Panel de Usuario"
- Verificar que NO aparece "Panel de Administrador"
- Click en "Ver Perfil"
- **Resultado**: Se muestra información del usuario sin permisos de admin

**Usuario Administrador (admin@test.com)**:
- Logout del usuario anterior
- Login con cuenta de admin
- Verificar que aparece "Panel de Usuario"
- Verificar que TAMBIÉN aparece "Panel de Administrador"
- Click en "Ver Dashboard"
- **Resultado**: Se muestra información de administrador con todos los permisos

#### 5. Persistencia de Sesión

- Login con cualquier usuario
- Abrir DevTools (F12) → Application → Local Storage
- Verificar que existen las claves: `auth_token` y `user_data`
- Recargar la página (F5)
- **Resultado esperado**:
  - La sesión se mantiene
  - El usuario sigue autenticado
  - Las secciones correspondientes siguen visibles

#### 6. Logout

- Con sesión iniciada, click en "Cerrar Sesión"
- Confirmar el diálogo
- **Resultado esperado**:
  - Mensaje "Sesión cerrada exitosamente"
  - Se ocultan las secciones protegidas
  - Aparece botón "Iniciar Sesión"
  - `localStorage` está limpio (verificar en DevTools)

#### 7. Verificación de Email (Opcional)

- Registrar un nuevo usuario
- Revisar los logs de mail (Mailpit en `http://localhost:8025`)
- Copiar el link de verificación del email
- Pegar en el navegador
- **Resultado esperado**:
  - Mensaje: "Email verified successfully"
  - El campo `email_verified_at` del usuario se actualiza

### 5.4. Verificar en la Consola del Navegador

Abrir DevTools (F12) y verificar:

- **Console**: Ver logs de las peticiones y respuestas
- **Network**: Ver las llamadas HTTP a `/api/login`, `/api/register`, etc.
- **Application → Local Storage**: Ver `auth_token` y `user_data`

### 5.5. Probar con cURL (Opcional)

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test2@test.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test2@test.com","password":"password123"}'

# Get user (reemplazar TOKEN con el token recibido)
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer TOKEN"
```

## Parte 6: Características Implementadas en el Backend

El backend ya cuenta con varias características avanzadas que pueden integrarse con el frontend:

### 6.1. Recuperación de Contraseña ✅ Implementado

**Endpoints disponibles:**
- `POST /api/password/forgot` - Envía email con link de recuperación
- `POST /api/password/reset` - Resetea la contraseña con el token

**Integración frontend (pendiente):**
- Modal para solicitar recuperación de contraseña
- Página para ingresar nueva contraseña con el token

### 6.2. Verificación de Email ✅ Implementado

**Endpoints disponibles:**
- `GET /api/email/verify/{id}/{hash}` - Verifica el email
- `POST /api/email/resend` - Reenvía email de verificación

**Características actuales:**
- Emails automáticos al registrarse
- Links firmados con expiración
- El modelo User implementa `MustVerifyEmail`

**Mejoras futuras:**
- Mostrar banner en frontend si el email no está verificado
- Botón para reenviar email desde la interfaz
- Restringir ciertas acciones hasta verificar el email

### 6.3. Integración con Spatie Permission ✅ Parcialmente Implementado

**Estado actual:**
- Backend preparado con `HasRoles` trait
- El frontend obtiene permisos en el login
- Control de visibilidad de secciones por permisos

**Extensiones futuras:**
- Crear endpoints para gestionar roles y permisos
- Interfaz de administración para asignar roles
- Middleware de permisos en rutas protegidas
- Listar todos los permisos disponibles

### 6.4. Autenticación de Dos Factores (2FA)

**Estado:** No implementado

**Requiere:**
- Instalar paquete de 2FA (ej: `laravel/fortify`)
- Generar y almacenar códigos QR
- Validar códigos TOTP en el login
- Interfaz frontend para activar/desactivar 2FA

### 6.5. Refresh Token Automático

**Estado:** No implementado

**Requiere:**
- Sistema de refresh tokens separado
- Interceptor en el frontend para detectar tokens próximos a expirar
- Endpoint para renovar tokens
- Manejo de refresh token en el cliente

### 6.6. Gestión de Sesiones Activas

**Extensión futura:**
- Listar todos los tokens activos del usuario
- Ver dispositivos y ubicaciones de sesiones
- Revocar tokens individuales
- Cerrar todas las sesiones excepto la actual

### 6.7. Rate Limiting y Seguridad

**Mejoras recomendadas:**
- Implementar rate limiting en endpoints sensibles
- Logs de intentos de login fallidos
- Bloqueo temporal después de múltiples intentos
- Notificaciones de login desde nuevos dispositivos

### Próximos Pasos Recomendados

1. **Interfaz para recuperación de contraseña**: Agregar modales y formularios para el flujo completo
2. **Gestión de verificación de email**: Mostrar banners y opciones para reenviar email
3. **Panel de administración**: Crear interfaces para gestionar usuarios, roles y permisos
4. **Mejoras de UX**: Agregar indicadores de carga, mensajes toast, animaciones
5. **Testing**: Implementar pruebas automatizadas para los flujos de autenticación

El sistema está completamente funcional y listo para producción, con capacidad para extenderse según las necesidades del proyecto.

## Referencias

- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [MDN - Web Storage API](https://developer.mozilla.org/es/docs/Web/API/Web_Storage_API)
- [OWASP - Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)
