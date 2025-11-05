# Guía Completa del Framework de UI

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Arquitectura del Framework](#arquitectura-del-framework)
3. [Conceptos Fundamentales](#conceptos-fundamentales)
4. [Componentes del Sistema](#componentes-del-sistema)
5. [Guía de Uso](#guía-de-uso)
6. [Ejemplos Prácticos](#ejemplos-prácticos)
7. [Características Avanzadas](#características-avanzadas)
8. [Mejores Prácticas](#mejores-prácticas)
9. [Troubleshooting](#troubleshooting)

---

## Introducción

### ¿Qué es este Framework?

Este es un **framework de UI reactivo backend-driven** que permite construir interfaces de usuario dinámicas donde:

- ✅ **El backend (PHP/Laravel) controla completamente la estructura y lógica de la UI**
- ✅ **El frontend (JavaScript) es un renderizador agnóstico que interpreta instrucciones**
- ✅ **Las actualizaciones son automáticas y optimizadas** (solo se envían cambios, no toda la UI)
- ✅ **Los componentes son reutilizables y type-safe** (con inyección automática)
- ✅ **El estado persiste entre requests** (cacheo inteligente)

### ¿Cuándo usar este Framework?

**Ideal para:**
- Aplicaciones altamente dinámicas con lógica compleja en el backend
- Dashboards y paneles de administración
- Interfaces que requieren actualizaciones en tiempo real
- Aplicaciones donde la seguridad es crítica (lógica en backend)
- Prototipos rápidos sin escribir JavaScript custom

**No ideal para:**
- Sitios web estáticos o de contenido
- Aplicaciones que requieren animaciones complejas
- SPAs con navegación compleja del lado del cliente

---

## Arquitectura del Framework

### Diagrama de Capas

```
┌────────────────────────────────────────────────────────────────┐
│                         FRONTEND                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐    │
│  │ demo.blade   │  │ ui-renderer  │  │ ui-components    │    │
│  │    .php      │  │     .js      │  │     .css         │    │
│  │              │  │              │  │                  │    │
│  │ Contenedores │  │ Motor de     │  │ Estilos de       │    │
│  │ HTML base    │  │ renderizado  │  │ componentes      │    │
│  └──────────────┘  └──────────────┘  └──────────────────┘    │
└────────────────────────────────────────────────────────────────┘
                            ↕ JSON/HTTP
┌────────────────────────────────────────────────────────────────┐
│                      CONTROLADORES                             │
│  ┌──────────────────┐        ┌──────────────────────┐         │
│  │ UIDemoController │        │ UIEventController    │         │
│  │                  │        │                      │         │
│  │ Ruta → Servicio  │        │ Evento → Handler     │         │
│  │ Servicio → JSON  │        │ Diff → Respuesta     │         │
│  └──────────────────┘        └──────────────────────┘         │
└────────────────────────────────────────────────────────────────┘
                            ↕
┌────────────────────────────────────────────────────────────────┐
│                    SERVICIOS Y BUILDERS                        │
│  ┌──────────────────────────────────────────────────────┐     │
│  │              AbstractUIService                       │     │
│  │  • Gestión de estado (cache)                        │     │
│  │  • Lifecycle de eventos                             │     │
│  │  • Diff automático                                  │     │
│  │  • Inyección de componentes                         │     │
│  └──────────────────────────────────────────────────────┘     │
│                            ↓                                   │
│  ┌──────────────────────────────────────────────────────┐     │
│  │              UIBuilder (Factory)                     │     │
│  │  button() • label() • input() • select()            │     │
│  │  checkbox() • table() • card() • container()        │     │
│  └──────────────────────────────────────────────────────┘     │
│                            ↓                                   │
│  ┌──────────────────────────────────────────────────────┐     │
│  │         Componentes (ButtonBuilder, etc.)           │     │
│  │  • Configuración fluida                             │     │
│  │  • Serialización a JSON                             │     │
│  │  • IDs determinísticos                              │     │
│  └──────────────────────────────────────────────────────┘     │
└────────────────────────────────────────────────────────────────┘
                            ↕
┌────────────────────────────────────────────────────────────────┐
│                   SOPORTE Y UTILIDADES                         │
│  ┌──────────────┐  ┌───────────┐  ┌──────────────────┐       │
│  │UIIdGenerator │  │ UIDiffer  │  │ UIStateManager   │       │
│  │              │  │           │  │                  │       │
│  │IDs únicos    │  │Detección  │  │Cache de estado   │       │
│  │por contexto  │  │de cambios │  │UI entre requests │       │
│  └──────────────┘  └───────────┘  └──────────────────┘       │
└────────────────────────────────────────────────────────────────┘
```

### Flujo de Datos

#### **1. Carga Inicial**
```
Usuario → /demo-ui
    ↓
web.php → demo.blade.php (con parámetro 'demo-ui')
    ↓
JavaScript ejecuta: fetch('/api/demo-ui')
    ↓
UIDemoController → DemoUiService.getUI()
    ↓
buildBaseUI() genera estructura de componentes
    ↓
UIContainer.toJson() serializa a JSON
    ↓
JSON → Frontend
    ↓
UIRenderer crea instancias de componentes
    ↓
Componentes se montan en el DOM
```

#### **2. Evento de Usuario**
```
Usuario hace click en botón
    ↓
ButtonComponent.sendEventToBackend('click', 'submit_form', {...})
    ↓
POST /api/ui-event {component_id, action, parameters}
    ↓
UIEventController.handleEvent()
    ↓
UIIdGenerator.getContextFromId() → Resuelve servicio
    ↓
service.initializeEventContext() → Captura estado anterior
    ↓
service.onSubmitForm(parameters) → Ejecuta lógica y modifica UI
    ↓
service.finalizeEventContext() → Calcula diff (solo cambios)
    ↓
JSON con cambios → Frontend
    ↓
UIRenderer.handleUIUpdate() → Aplica cambios al DOM
```

---

## Conceptos Fundamentales

### 1. Componentes

**Definición:** Bloques reutilizables de UI con configuración y comportamiento.

**Tipos disponibles:**
- `button` - Botones interactivos
- `label` - Etiquetas de texto
- `input` - Campos de entrada
- `select` - Listas desplegables
- `checkbox` - Casillas de verificación
- `table` - Tablas con paginación
- `card` - Tarjetas visuales
- `container` - Contenedores para agrupar componentes
- `menu_dropdown` - Menús desplegables

**Anatomía de un componente:**
```php
UIBuilder::button('btn_submit')  // Nombre (opcional pero recomendado)
    ->label('Guardar')           // Texto visible
    ->action('save_data')        // Acción a ejecutar
    ->style('primary')           // Estilo visual
    ->icon('check')              // Icono
    ->enabled(true)              // Estado habilitado
    ->tooltip('Guardar datos')   // Tooltip
```

### 2. IDs Determinísticos

**Problema:** Necesitamos identificar componentes de forma única y estable.

**Solución:** Sistema de IDs basado en **offset por servicio + ID local**

```php
// Cada servicio tiene un offset único (múltiplo de 10,000)
DemoUiService    → offset = 12340000
InputDemoService → offset = 78560000

// Componentes SIN nombre → auto-increment
$btn1 = UIBuilder::button();  // ID: 12340001
$btn2 = UIBuilder::button();  // ID: 12340002

// Componentes CON nombre → hash determinístico
$btn = UIBuilder::button('btn_submit');  // ID siempre: 12345678 (estable)
$lbl = UIBuilder::label('lbl_status');   // ID siempre: 12349876 (estable)
```

**Ventajas:**
- ✅ **Únicos globalmente** (no hay colisiones entre servicios)
- ✅ **Estables entre requests** (componentes con nombre tienen ID fijo)
- ✅ **Trazables** (desde un ID podemos saber qué servicio lo generó)

### 3. Inyección de Componentes

**Feature:** Los componentes con nombre se inyectan automáticamente como propiedades protegidas.

```php
class MyService extends AbstractUIService
{
    // Declarar componentes como propiedades protegidas
    protected LabelBuilder $lbl_status;
    protected ButtonBuilder $btn_submit;
    protected InputBuilder $txt_name;
    
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')->parent('main');
        
        // Crear componentes CON NOMBRE
        $container->add(
            UIBuilder::label('lbl_status')
                ->text('Esperando...')
        );
        
        $container->add(
            UIBuilder::input('txt_name')
                ->placeholder('Nombre')
        );
        
        $container->add(
            UIBuilder::button('btn_submit')
                ->label('Enviar')
                ->action('submit')
        );
        
        return $container;
    }
    
    public function onSubmit(array $params): void
    {
        // ¡Ahora puedes usar las propiedades directamente!
        $this->lbl_status
            ->text('Procesando...')
            ->style('warning');
        
        // Acceder a valores de inputs desde params
        $name = $params['txt_name'] ?? '';
        
        // Actualizar después de procesar
        $this->lbl_status
            ->text("¡Hola, {$name}!")
            ->style('success');
    }
}
```

**Requisitos:**
1. El componente debe tener un **nombre** (`UIBuilder::label('nombre')`)
2. La propiedad debe ser **protected** y tener el **mismo nombre**
3. La propiedad debe tener **type hint** del tipo correcto
4. Si el componente no existe y la propiedad no es nullable, se lanza excepción

### 4. Sistema de Cache

**Funcionamiento:**
- La UI generada se almacena en cache (Laravel Cache)
- Duración por defecto: Variable de entorno `UI_CACHE_TTL`
- Se invalida automáticamente después de eventos que modifican la UI
- Reseteo manual: Agregar `/reset` a la URL o llamar `clearStoredUI()`

```php
// Cache automático
$ui = $service->getUI();  // Primera vez: genera y cachea
$ui = $service->getUI();  // Segunda vez: lee desde cache

// Invalidar cache manualmente
$service->clearStoredUI();

// Resetear desde URL
/demo-ui/reset
```

### 5. Diff Automático

**Concepto:** Solo enviamos al frontend los componentes que cambiaron.

```php
// Estado ANTES del evento
{
    "123": {"type": "label", "text": "Valor inicial", "style": "default"},
    "124": {"type": "button", "label": "Guardar", "enabled": true}
}

// Usuario hace click → Ejecuta onSave()
$this->lbl_status->text('Guardado!')->style('success');

// Estado DESPUÉS del evento
{
    "123": {"type": "label", "text": "Guardado!", "style": "success"},
    "124": {"type": "button", "label": "Guardar", "enabled": true}
}

// DIFF enviado al frontend (solo cambios)
{
    "123": {
        "_id": 123,
        "type": "label",
        "text": "Guardado!",
        "style": "success"
    }
    // "124" no se envía porque no cambió
}
```

---

## Componentes del Sistema

### Rutas (`routes/web.php`)

```php
// Vista principal del demo
Route::get('/{demo}/{reset?}', function (string $demo, bool $reset = false) {
    return view('demo', [
        'demo' => $demo,
        'reset' => $reset
    ]);
})->where('demo', 'demo-ui|input-demo|table-demo|...')
  ->name('demo');

// API que retorna JSON de la UI
Route::get('/api/{demo}/{reset?}', [UIDemoController::class, 'show'])
    ->name('api.demo');

// Endpoint para eventos
Route::post('/api/ui-event', [UIEventController::class, 'handleEvent'])
    ->name('ui.event');
```

**Agregar nuevo demo:**
1. Agregar nombre en el `where()` (formato kebab-case)
2. Crear servicio en `app/Services/Screens/` (formato PascalCase + Service)
3. Registrar en `config/ui-services.php`

### Vista Base (`resources/views/demo.blade.php`)

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Demo - {{ $demo }}</title>
    <link rel="stylesheet" href="{{ asset('css/ui-components.css') }}">
</head>
<body>
    <header id="top-menu-bar">
        <div id="menu"></div>
    </header>
    
    <main id="main"></main>
    
    <div id="modal-overlay" class="modal-overlay hidden">
        <div id="modal" class="modal-container"></div>
    </div>
    
    <script>
        window.DEMO_NAME = '{{ $demo }}';
        window.RESET_DEMO = {{ $reset ? 'true' : 'false' }};
        window.MENU_SERVICE = 'demo-menu';
    </script>
    <script src="{{ asset('js/ui-renderer.js') }}"></script>
</body>
</html>
```

**Contenedores importantes:**
- `#menu` - Menú de navegación superior
- `#main` - Área principal de contenido
- `#modal` - Área para modales/diálogos

### Controladores

#### `UIDemoController`

**Responsabilidad:** Resolver nombre de ruta → servicio → JSON

```php
public function show(string $demo, bool $reset = false): JsonResponse
{
    // 'input-demo' → 'InputDemoService'
    $serviceName = Str::studly($demo) . 'Service';
    $serviceClass = "App\\Services\\Screens\\{$serviceName}";
    
    if (!class_exists($serviceClass)) {
        return response()->json(['error' => 'Service not found'], 404);
    }
    
    $service = app($serviceClass);
    
    if ($reset) {
        $service->clearStoredUI();
    }
    
    return response()->json($service->getUI());
}
```

#### `UIEventController`

**Responsabilidad:** Enrutar eventos a métodos de servicio

```php
public function handleEvent(Request $request): JsonResponse
{
    $validated = $request->validate([
        'component_id' => 'required|integer',
        'event' => 'required|string',
        'action' => 'required|string',
        'parameters' => 'array',
    ]);
    
    // 1. Resolver servicio desde component_id
    $serviceClass = UIIdGenerator::getContextFromId($validated['component_id']);
    $service = app($serviceClass);
    
    // 2. Convertir acción a método: 'submit_form' → 'onSubmitForm'
    $method = $this->actionToMethodName($validated['action']);
    
    // 3. Lifecycle de evento
    $service->initializeEventContext();
    $service->$method($validated['parameters']);
    $result = $service->finalizeEventContext();
    
    return response()->json($result);
}
```

### Servicios de UI

#### Clase Base: `AbstractUIService`

```php
abstract class AbstractUIService
{
    protected UIContainer $container;
    protected ?array $oldUI = null;
    protected ?array $newUI = null;
    
    // DEBE implementarse en servicios hijos
    abstract protected function buildBaseUI(...$params): UIContainer;
    
    // Lifecycle de eventos
    public function initializeEventContext(): void;
    public function finalizeEventContext(): array;
    
    // API pública
    public function getUI(...$params): array;
    public function clearStoredUI(): void;
}
```

#### Implementación de Servicio

```php
namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Enums\LayoutType;

class MyDemoService extends AbstractUIService
{
    // Componentes inyectados automáticamente
    protected LabelBuilder $lbl_message;
    protected InputBuilder $txt_input;
    protected ButtonBuilder $btn_action;
    
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Mi Demo');
        
        // Agregar componentes
        $container->add(
            UIBuilder::label('lbl_message')
                ->text('Mensaje inicial')
                ->style('info')
        );
        
        $container->add(
            UIBuilder::input('txt_input')
                ->label('Entrada')
                ->placeholder('Escribe algo...')
        );
        
        $container->add(
            UIBuilder::button('btn_action')
                ->label('Procesar')
                ->action('process_input')
                ->style('primary')
        );
        
        return $container;
    }
    
    // Event handlers
    public function onProcessInput(array $params): void
    {
        $input = $params['txt_input'] ?? '';
        
        if (empty($input)) {
            $this->lbl_message
                ->text('Por favor ingresa un valor')
                ->style('warning');
            return;
        }
        
        $this->lbl_message
            ->text("Procesado: {$input}")
            ->style('success');
    }
}
```

### Factory: `UIBuilder`

```php
class UIBuilder
{
    public static function button(?string $name = null): ButtonBuilder;
    public static function label(?string $name = null): LabelBuilder;
    public static function input(?string $name = null): InputBuilder;
    public static function select(?string $name = null): SelectBuilder;
    public static function checkbox(?string $name = null): CheckboxBuilder;
    public static function table(?string $name = null, int $rows, int $cols): TableBuilder;
    public static function card(?string $name = null): CardBuilder;
    public static function container(?string $name = null): UIContainer;
    public static function menuDropdown(string $name): MenuDropdownBuilder;
}
```

### Componentes Individuales

Cada componente tiene su propio builder con métodos fluidos:

```php
// ButtonBuilder
UIBuilder::button('btn_save')
    ->label(string $text)
    ->action(string $action, array $params = [])
    ->style('primary'|'success'|'danger'|'warning'|'info')
    ->variant('filled'|'outlined'|'text')
    ->icon(string $icon)
    ->enabled(bool $enabled)
    ->tooltip(string $tooltip);

// LabelBuilder
UIBuilder::label('lbl_status')
    ->text(string $text)
    ->style('default'|'info'|'success'|'warning'|'error'|'heading'|'h1-h6')
    ->textAlign('left'|'center'|'right'|'justify');

// InputBuilder
UIBuilder::input('txt_name')
    ->label(string $label)
    ->type('text'|'email'|'password'|'number'|'date'|...)
    ->placeholder(string $placeholder)
    ->value(string $value)
    ->required(bool $required)
    ->disabled(bool $disabled)
    ->readonly(bool $readonly)
    ->maxlength(int $maxlength);

// SelectBuilder
UIBuilder::select('sel_country')
    ->label(string $label)
    ->options(array $options)  // ['value' => 'label', ...]
    ->placeholder(string $placeholder)
    ->value(string $selected)
    ->required(bool $required)
    ->disabled(bool $disabled)
    ->onChange(string $action);

// TableBuilder
UIBuilder::table('tbl_users', rows: 0, cols: 0)
    ->title(string $title)
    ->addHeader(string $text, ?string $name, ?string $align, ?int $width)
    ->rows(array $data)
    ->pagination(bool $enabled, int $perPage, int $total);

// CardBuilder
UIBuilder::card('card_user')
    ->title(string $title)
    ->subtitle(string $subtitle)
    ->description(string $description)
    ->image(string $url)
    ->actions(array $buttons)
    ->style('default'|'outlined'|'elevated'|'flat'|'gradient')
    ->theme('primary'|'success'|'danger'|...)
    ->clickable(bool $clickable, ?string $action);
```

---

## Guía de Uso

### Paso 1: Crear un Nuevo Servicio

```php
// app/Services/Screens/TaskManagerService.php
<?php

namespace App\Services\Screens;

use App\Services\UI\UIBuilder;
use App\Services\UI\AbstractUIService;
use App\Services\UI\Components\UIContainer;
use App\Services\UI\Components\LabelBuilder;
use App\Services\UI\Components\InputBuilder;
use App\Services\UI\Components\ButtonBuilder;
use App\Services\UI\Enums\LayoutType;

class TaskManagerService extends AbstractUIService
{
    // Componentes inyectados
    protected LabelBuilder $lbl_status;
    protected InputBuilder $txt_task;
    protected UIContainer $task_list;
    
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->layout(LayoutType::VERTICAL)
            ->title('Gestor de Tareas');
        
        // Status label
        $container->add(
            UIBuilder::label('lbl_status')
                ->text('Listo para agregar tareas')
                ->style('info')
        );
        
        // Input para nueva tarea
        $container->add(
            UIBuilder::input('txt_task')
                ->label('Nueva Tarea')
                ->placeholder('Escribe una tarea...')
                ->required(true)
        );
        
        // Botón agregar
        $container->add(
            UIBuilder::button('btn_add')
                ->label('Agregar Tarea')
                ->action('add_task')
                ->style('primary')
                ->icon('➕')
        );
        
        // Separador
        $container->add(
            UIBuilder::label()
                ->text('═══════════════════════════')
                ->style('default')
        );
        
        // Contenedor de tareas
        $taskList = UIBuilder::container('task_list')
            ->layout(LayoutType::VERTICAL)
            ->title('Mis Tareas');
        
        $container->add($taskList);
        
        return $container;
    }
    
    public function onAddTask(array $params): void
    {
        $taskText = $params['txt_task'] ?? '';
        
        if (empty($taskText)) {
            $this->lbl_status
                ->text('⚠️ Por favor ingresa una tarea')
                ->style('warning');
            return;
        }
        
        // Agregar nueva tarea al contenedor
        $taskId = 'task_' . time();
        
        $taskContainer = UIBuilder::container($taskId)
            ->layout(LayoutType::HORIZONTAL);
        
        $taskContainer->add(
            UIBuilder::label()
                ->text("📋 {$taskText}")
                ->style('default')
        );
        
        $taskContainer->add(
            UIBuilder::button("btn_delete_{$taskId}")
                ->label('🗑️')
                ->action('delete_task', ['task_id' => $taskId])
                ->style('danger')
        );
        
        $this->task_list->add($taskContainer);
        
        // Actualizar status
        $this->lbl_status
            ->text('✅ Tarea agregada exitosamente')
            ->style('success');
        
        // Limpiar input
        $this->txt_task->value('');
    }
    
    public function onDeleteTask(array $params): void
    {
        $taskId = $params['task_id'] ?? null;
        
        if ($taskId && $this->task_list->tryRemove($taskId)) {
            $this->lbl_status
                ->text('🗑️ Tarea eliminada')
                ->style('info');
        } else {
            $this->lbl_status
                ->text('❌ Error al eliminar tarea')
                ->style('error');
        }
    }
}
```

### Paso 2: Registrar el Servicio

```php
// config/ui-services.php
return [
    \App\Services\Screens\DemoUiService::class,
    \App\Services\Screens\InputDemoService::class,
    // ... otros servicios
    \App\Services\Screens\TaskManagerService::class,  // ← Agregar aquí
];
```

### Paso 3: Agregar Ruta

```php
// routes/web.php
Route::get('/{demo}/{reset?}', function (string $demo, bool $reset = false) {
    return view('demo', ['demo' => $demo, 'reset' => $reset]);
})->where('demo', 'demo-ui|input-demo|task-manager')  // ← Agregar 'task-manager'
  ->name('demo');
```

### Paso 4: Acceder al Demo

```
http://localhost/task-manager
```

---

## Ejemplos Prácticos

### Ejemplo 1: Formulario con Validación

```php
class FormValidationService extends AbstractUIService
{
    protected LabelBuilder $lbl_error;
    protected InputBuilder $txt_email;
    protected InputBuilder $txt_password;
    
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->title('Login');
        
        // Error message (inicialmente oculto)
        $container->add(
            UIBuilder::label('lbl_error')
                ->text('')
                ->style('error')
                ->visible(false)
        );
        
        $container->add(
            UIBuilder::input('txt_email')
                ->label('Email')
                ->type('email')
                ->placeholder('usuario@ejemplo.com')
                ->required(true)
        );
        
        $container->add(
            UIBuilder::input('txt_password')
                ->label('Contraseña')
                ->type('password')
                ->required(true)
        );
        
        $container->add(
            UIBuilder::button('btn_login')
                ->label('Iniciar Sesión')
                ->action('login')
                ->style('primary')
        );
        
        return $container;
    }
    
    public function onLogin(array $params): void
    {
        $email = $params['txt_email'] ?? '';
        $password = $params['txt_password'] ?? '';
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->lbl_error
                ->text('❌ Email inválido')
                ->visible(true);
            return;
        }
        
        // Validar contraseña
        if (strlen($password) < 6) {
            $this->lbl_error
                ->text('❌ La contraseña debe tener al menos 6 caracteres')
                ->visible(true);
            return;
        }
        
        // Simular login exitoso
        $this->lbl_error
            ->text('✅ ¡Login exitoso!')
            ->style('success')
            ->visible(true);
        
        // Limpiar campos
        $this->txt_email->value('');
        $this->txt_password->value('');
    }
}
```

### Ejemplo 2: Tabla con Paginación

```php
class UserListService extends AbstractUIService
{
    protected TableBuilder $tbl_users;
    
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->title('Lista de Usuarios');
        
        // Crear tabla
        $table = UIBuilder::table('tbl_users')
            ->title('Usuarios Registrados')
            ->addHeader('ID', 'id', align: 'center', width: 80)
            ->addHeader('Nombre', 'name')
            ->addHeader('Email', 'email')
            ->addHeader('Estado', 'status', align: 'center', width: 120)
            ->addHeader('Acciones', 'actions', align: 'center', width: 200);
        
        // Obtener datos (simulado)
        $users = $this->getUsersFromDatabase();
        
        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                $user['id'],
                $user['name'],
                $user['email'],
                $this->getStatusLabel($user['status']),
                $this->getActionButtons($user['id']),
            ];
        }
        
        $table->rows($rows)
              ->pagination(true, perPage: 10, total: count($users));
        
        $container->add($table);
        
        return $container;
    }
    
    private function getUsersFromDatabase(): array
    {
        // Simulación - reemplazar con consulta real
        return [
            ['id' => 1, 'name' => 'Juan Pérez', 'email' => 'juan@test.com', 'status' => 'active'],
            ['id' => 2, 'name' => 'María García', 'email' => 'maria@test.com', 'status' => 'inactive'],
            // ... más usuarios
        ];
    }
    
    private function getStatusLabel(string $status): LabelBuilder
    {
        $style = $status === 'active' ? 'success' : 'warning';
        $text = $status === 'active' ? '✅ Activo' : '⏸️ Inactivo';
        
        return UIBuilder::label()
            ->text($text)
            ->style($style);
    }
    
    private function getActionButtons(int $userId): UIContainer
    {
        $actions = UIBuilder::container()
            ->layout(LayoutType::HORIZONTAL);
        
        $actions->add(
            UIBuilder::button()
                ->label('Editar')
                ->action('edit_user', ['user_id' => $userId])
                ->style('primary')
                ->icon('✏️')
        );
        
        $actions->add(
            UIBuilder::button()
                ->label('Eliminar')
                ->action('delete_user', ['user_id' => $userId])
                ->style('danger')
                ->icon('🗑️')
        );
        
        return $actions;
    }
    
    public function onEditUser(array $params): void
    {
        $userId = $params['user_id'] ?? null;
        // Implementar lógica de edición
    }
    
    public function onDeleteUser(array $params): void
    {
        $userId = $params['user_id'] ?? null;
        // Implementar lógica de eliminación
    }
    
    public function onChangePage(array $params): void
    {
        $page = $params['page'] ?? 1;
        
        // Obtener datos de la página
        $users = $this->getUsersFromDatabase($page);
        
        // Actualizar tabla con nuevos datos
        // El framework automáticamente detectará los cambios
    }
}
```

### Ejemplo 3: Contador Interactivo

```php
class CounterService extends AbstractUIService
{
    protected LabelBuilder $lbl_counter;
    protected ButtonBuilder $btn_increment;
    protected ButtonBuilder $btn_decrement;
    
    private function getCounterValue(): int
    {
        return Cache::get('counter_value', 0);
    }
    
    private function setCounterValue(int $value): void
    {
        Cache::put('counter_value', $value, now()->addHours(24));
    }
    
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->title('Contador');
        
        $counterContainer = UIBuilder::container('counter_display')
            ->layout(LayoutType::HORIZONTAL);
        
        $counterContainer->add(
            UIBuilder::button('btn_decrement')
                ->label('➖')
                ->action('decrement')
                ->style('danger')
        );
        
        $value = $this->getCounterValue();
        $counterContainer->add(
            UIBuilder::label('lbl_counter')
                ->text((string) $value)
                ->style($this->getCounterStyle($value))
        );
        
        $counterContainer->add(
            UIBuilder::button('btn_increment')
                ->label('➕')
                ->action('increment')
                ->style('success')
        );
        
        $container->add($counterContainer);
        
        // Botón reset
        $container->add(
            UIBuilder::button('btn_reset')
                ->label('Resetear')
                ->action('reset')
                ->style('warning')
        );
        
        return $container;
    }
    
    private function getCounterStyle(int $value): string
    {
        if ($value > 10) return 'success';
        if ($value < 0) return 'danger';
        return 'primary';
    }
    
    public function onIncrement(array $params): void
    {
        $newValue = $this->getCounterValue() + 1;
        $this->setCounterValue($newValue);
        
        $this->lbl_counter
            ->text((string) $newValue)
            ->style($this->getCounterStyle($newValue));
    }
    
    public function onDecrement(array $params): void
    {
        $newValue = $this->getCounterValue() - 1;
        $this->setCounterValue($newValue);
        
        $this->lbl_counter
            ->text((string) $newValue)
            ->style($this->getCounterStyle($newValue));
    }
    
    public function onReset(array $params): void
    {
        $this->setCounterValue(0);
        
        $this->lbl_counter
            ->text('0')
            ->style('primary');
    }
}
```

### Ejemplo 4: Select Dinámico con Cascada

```php
class CascadeSelectService extends AbstractUIService
{
    protected SelectBuilder $sel_country;
    protected SelectBuilder $sel_state;
    protected SelectBuilder $sel_city;
    protected LabelBuilder $lbl_selection;
    
    protected function buildBaseUI(...$params): UIContainer
    {
        $container = UIBuilder::container('main')
            ->parent('main')
            ->title('Selección en Cascada');
        
        // Country select
        $container->add(
            UIBuilder::select('sel_country')
                ->label('País')
                ->options([
                    'mx' => 'México',
                    'ar' => 'Argentina',
                    'es' => 'España',
                ])
                ->placeholder('Selecciona un país')
                ->onChange('country_changed')
        );
        
        // State select (inicialmente deshabilitado)
        $container->add(
            UIBuilder::select('sel_state')
                ->label('Estado/Provincia')
                ->placeholder('Primero selecciona un país')
                ->disabled(true)
        );
        
        // City select (inicialmente deshabilitado)
        $container->add(
            UIBuilder::select('sel_city')
                ->label('Ciudad')
                ->placeholder('Primero selecciona un estado')
                ->disabled(true)
        );
        
        // Label para mostrar selección
        $container->add(
            UIBuilder::label('lbl_selection')
                ->text('Selecciona ubicación...')
                ->style('info')
        );
        
        return $container;
    }
    
    public function onCountryChanged(array $params): void
    {
        $country = $params['value'] ?? null;
        
        if (!$country) {
            $this->sel_state->disabled(true)->options([]);
            $this->sel_city->disabled(true)->options([]);
            return;
        }
        
        // Obtener estados del país seleccionado
        $states = $this->getStatesByCountry($country);
        
        $this->sel_state
            ->options($states)
            ->disabled(false)
            ->placeholder('Selecciona un estado')
            ->value('');  // Reset value
        
        $this->sel_city
            ->disabled(true)
            ->options([])
            ->value('');
        
        $this->lbl_selection
            ->text("País seleccionado: {$country}")
            ->style('info');
    }
    
    public function onStateChanged(array $params): void
    {
        $state = $params['value'] ?? null;
        
        if (!$state) {
            $this->sel_city->disabled(true)->options([]);
            return;
        }
        
        // Obtener ciudades del estado seleccionado
        $cities = $this->getCitiesByState($state);
        
        $this->sel_city
            ->options($cities)
            ->disabled(false)
            ->placeholder('Selecciona una ciudad')
            ->value('');
        
        $this->lbl_selection
            ->text("Estado seleccionado: {$state}")
            ->style('info');
    }
    
    public function onCityChanged(array $params): void
    {
        $city = $params['value'] ?? null;
        
        if (!$city) return;
        
        $this->lbl_selection
            ->text("✅ Ubicación completa seleccionada: {$city}")
            ->style('success');
    }
    
    private function getStatesByCountry(string $country): array
    {
        $data = [
            'mx' => ['jal' => 'Jalisco', 'cdmx' => 'CDMX', 'nl' => 'Nuevo León'],
            'ar' => ['ba' => 'Buenos Aires', 'cor' => 'Córdoba'],
            'es' => ['mad' => 'Madrid', 'bcn' => 'Barcelona'],
        ];
        
        return $data[$country] ?? [];
    }
    
    private function getCitiesByState(string $state): array
    {
        $data = [
            'jal' => ['gdl' => 'Guadalajara', 'tlq' => 'Tlaquepaque'],
            'cdmx' => ['mig' => 'Miguel Hidalgo', 'coy' => 'Coyoacán'],
            // ... más datos
        ];
        
        return $data[$state] ?? [];
    }
}
```

---

## Características Avanzadas

### 1. Modales

**Abrir modal desde evento:**

```php
public function onOpenModal(array $params): void
{
    // Crear contenedor para modal
    $modalContainer = UIBuilder::container('modal_content')
        ->parent('modal')  // ← Importante: parent='modal'
        ->title('Confirmar Acción');
    
    $modalContainer->add(
        UIBuilder::label()
            ->text('¿Estás seguro de continuar?')
            ->style('warning')
    );
    
    $buttons = UIBuilder::container()
        ->layout(LayoutType::HORIZONTAL);
    
    $buttons->add(
        UIBuilder::button()
            ->label('Confirmar')
            ->action('confirm_action')
            ->style('primary')
    );
    
    $buttons->add(
        UIBuilder::button()
            ->label('Cancelar')
            ->action('close_modal')
            ->style('danger')
    );
    
    $modalContainer->add($buttons);
    
    // El modal se abre automáticamente cuando hay componentes con parent='modal'
}

public function onCloseModal(array $params): void
{
    // Retornar acción especial para cerrar modal
    return ['action' => 'close_modal'];
}
```

### 2. Menús Desplegables

```php
$menu = UIBuilder::menuDropdown('main_menu')
    ->parent('menu')
    ->label('☰ Menú')
    ->icon('menu')
    ->position('bottom-left');

$menu->addItem('home', 'Inicio', 'home', action: 'goto_home');
$menu->addItem('profile', 'Perfil', 'user', action: 'goto_profile');
$menu->addSeparator();
$menu->addItem('logout', 'Cerrar Sesión', 'logout', action: 'logout', style: 'danger');

// Submenús
$menu->addSubmenu('settings', 'Configuración', 'settings', [
    ['id' => 'general', 'label' => 'General', 'action' => 'settings_general'],
    ['id' => 'security', 'label' => 'Seguridad', 'action' => 'settings_security'],
]);

$container->add($menu);
```

### 3. Cards

```php
$card = UIBuilder::card('user_card')
    ->title('Juan Pérez')
    ->subtitle('Desarrollador Senior')
    ->description('Especialista en PHP y Laravel con 10 años de experiencia')
    ->image('https://example.com/avatar.jpg')
    ->style('elevated')
    ->theme('primary')
    ->clickable(true, action: 'view_profile', parameters: ['user_id' => 123]);

// Agregar acciones al card
$card->addAction('edit', 'Editar', 'primary');
$card->addAction('delete', 'Eliminar', 'danger');

$container->add($card);
```

### 4. Layouts Complejos

```php
protected function buildBaseUI(...$params): UIContainer
{
    $main = UIBuilder::container('main')
        ->parent('main')
        ->layout(LayoutType::VERTICAL);
    
    // Header horizontal
    $header = UIBuilder::container('header')
        ->layout(LayoutType::HORIZONTAL)
        ->gap(20);
    
    $header->add(UIBuilder::label()->text('Logo')->style('h2'));
    $header->add(UIBuilder::label()->text('Mi App')->style('h1'));
    
    $main->add($header);
    
    // Content con 2 columnas
    $content = UIBuilder::container('content')
        ->layout(LayoutType::HORIZONTAL)
        ->gap(20);
    
    // Sidebar
    $sidebar = UIBuilder::container('sidebar')
        ->layout(LayoutType::VERTICAL)
        ->width('250px')
        ->backgroundColor('#f5f5f5')
        ->padding(15);
    
    $sidebar->add(UIBuilder::button()->label('Opción 1')->action('opt1'));
    $sidebar->add(UIBuilder::button()->label('Opción 2')->action('opt2'));
    
    // Main content
    $mainContent = UIBuilder::container('main_content')
        ->layout(LayoutType::VERTICAL)
        ->flexGrow(1)
        ->padding(20);
    
    $mainContent->add(UIBuilder::label()->text('Contenido principal')->style('h2'));
    
    $content->add($sidebar);
    $content->add($mainContent);
    $main->add($content);
    
    // Footer
    $footer = UIBuilder::container('footer')
        ->layout(LayoutType::HORIZONTAL)
        ->justifyContent('center')
        ->backgroundColor('#333')
        ->padding(10);
    
    $footer->add(UIBuilder::label()->text('© 2025 Mi App')->style('default'));
    $main->add($footer);
    
    return $main;
}
```

### 5. Manipulación Dinámica de Contenedores

```php
public function onAddElement(array $params): void
{
    // Obtener contenedor dinámico
    $container = $this->container->findByName('dynamic_list');
    
    if ($container) {
        // Agregar elemento
        $container->add(
            UIBuilder::label('new_item_' . time())
                ->text('Nuevo elemento agregado')
                ->style('success')
        );
    }
}

public function onRemoveElement(array $params): void
{
    $elementId = $params['element_id'] ?? null;
    
    if ($elementId) {
        // Intentar remover (no lanza excepción si no existe)
        $container = $this->container->findByName('dynamic_list');
        $container->tryRemove($elementId);
    }
}

public function onClearAll(array $params): void
{
    // Limpiar todos los elementos del contenedor
    $container = $this->container->findByName('dynamic_list');
    $container->clear();
}
```

---

## Mejores Prácticas

### 1. Nomenclatura

**✅ Buenas prácticas:**
```php
// Nombres descriptivos para componentes
UIBuilder::label('lbl_user_status')
UIBuilder::button('btn_save_form')
UIBuilder::input('txt_email_address')
UIBuilder::select('sel_country_code')

// Acciones en snake_case
->action('save_user')
->action('delete_record')
->action('update_profile')

// Handlers en onPascalCase
public function onSaveUser(array $params): void
public function onDeleteRecord(array $params): void
```

**❌ Evitar:**
```php
// Nombres genéricos sin contexto
UIBuilder::label('lbl1')
UIBuilder::button('button')

// Acciones ambiguas
->action('do_something')
->action('action1')
```

### 2. Organización de Código

```php
class MyService extends AbstractUIService
{
    // 1. Propiedades de componentes inyectados
    protected LabelBuilder $lbl_status;
    protected InputBuilder $txt_input;
    
    // 2. Método buildBaseUI
    protected function buildBaseUI(...$params): UIContainer
    {
        // Usar métodos privados para modularizar
        $container = UIBuilder::container('main')->parent('main');
        
        $this->buildHeader($container);
        $this->buildForm($container);
        $this->buildFooter($container);
        
        return $container;
    }
    
    // 3. Métodos de construcción privados
    private function buildHeader(UIContainer $container): void
    {
        // ...
    }
    
    private function buildForm(UIContainer $container): void
    {
        // ...
    }
    
    // 4. Event handlers públicos
    public function onSubmit(array $params): void
    {
        // ...
    }
    
    // 5. Métodos auxiliares privados
    private function validateInput(string $input): bool
    {
        // ...
    }
}
```

### 3. Manejo de Estado

**✅ Usar Cache para estado persistente:**
```php
private function getUserData(): array
{
    return Cache::remember('user_data', 3600, function () {
        return DB::table('users')->where('id', Auth::id())->first();
    });
}

private function updateUserData(array $data): void
{
    Cache::forget('user_data');
    DB::table('users')->where('id', Auth::id())->update($data);
}
```

**✅ Usar Session para estado temporal:**
```php
private function getFormData(): array
{
    return session('form_data', []);
}

private function setFormData(array $data): void
{
    session(['form_data' => $data]);
}
```

### 4. Validación de Entrada

```php
public function onSubmitForm(array $params): void
{
    // Validar entrada
    $email = $params['txt_email'] ?? '';
    $password = $params['txt_password'] ?? '';
    
    $errors = [];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido';
    }
    
    if (strlen($password) < 8) {
        $errors[] = 'La contraseña debe tener al menos 8 caracteres';
    }
    
    if (!empty($errors)) {
        $this->lbl_error
            ->text('❌ ' . implode('. ', $errors))
            ->style('error')
            ->visible(true);
        return;
    }
    
    // Procesar formulario...
}
```

### 5. Reutilización de Componentes

```php
// Crear método factory para componentes repetitivos
private function createTaskRow(string $id, string $text, bool $completed): UIContainer
{
    $row = UIBuilder::container("task_row_{$id}")
        ->layout(LayoutType::HORIZONTAL);
    
    $row->add(
        UIBuilder::checkbox("chk_{$id}")
            ->label($text)
            ->checked($completed)
            ->onChange('toggle_task', ['task_id' => $id])
    );
    
    $row->add(
        UIBuilder::button("btn_delete_{$id}")
            ->label('🗑️')
            ->action('delete_task', ['task_id' => $id])
            ->style('danger')
    );
    
    return $row;
}

// Usar en buildBaseUI
protected function buildBaseUI(...$params): UIContainer
{
    $container = UIBuilder::container('main')->parent('main');
    
    $tasks = $this->getTasks();
    foreach ($tasks as $task) {
        $container->add(
            $this->createTaskRow($task['id'], $task['text'], $task['completed'])
        );
    }
    
    return $container;
}
```

### 6. Testing

```php
// tests/Feature/Services/MyServiceTest.php
use Tests\TestCase;
use App\Services\Screens\MyService;

class MyServiceTest extends TestCase
{
    protected MyService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MyService();
    }
    
    public function test_build_base_ui_returns_container()
    {
        $ui = $this->service->getUI();
        
        $this->assertIsArray($ui);
        $this->assertArrayHasKey('type', reset($ui));
        $this->assertEquals('container', reset($ui)['type']);
    }
    
    public function test_event_handler_modifies_ui()
    {
        // Inicializar UI
        $this->service->getUI();
        
        // Simular evento
        $this->service->initializeEventContext();
        $this->service->onSubmit(['txt_input' => 'test']);
        $changes = $this->service->finalizeEventContext();
        
        $this->assertNotEmpty($changes);
    }
}
```

---

## Troubleshooting

### Problema: Componente no se inyecta automáticamente

**Síntoma:**
```
RuntimeException: Component 'lbl_status' not found in UI container
```

**Soluciones:**
1. ✅ Verificar que el nombre de la propiedad coincida con el nombre del componente:
   ```php
   protected LabelBuilder $lbl_status;  // ← Propiedad
   UIBuilder::label('lbl_status')        // ← Componente (mismo nombre)
   ```

2. ✅ Verificar que el componente se agregue al contenedor:
   ```php
   $container->add(UIBuilder::label('lbl_status'));
   ```

3. ✅ Si el componente es opcional, hacer la propiedad nullable:
   ```php
   protected ?LabelBuilder $lbl_status;
   ```

### Problema: Los cambios no se reflejan en el frontend

**Síntoma:** Modifico un componente en el handler pero no veo cambios.

**Soluciones:**
1. ✅ Verificar que el método handler sea público:
   ```php
   public function onMyAction(array $params): void  // ← Debe ser public
   ```

2. ✅ Verificar que no estés retornando un array vacío:
   ```php
   public function onMyAction(array $params): void
   {
       // No retornar nada, o retornar void
       $this->lbl_status->text('Actualizado');
   }
   ```

3. ✅ Verificar que la acción en el botón coincida con el handler:
   ```php
   ->action('my_action')  // ← snake_case
   public function onMyAction(...)  // ← onPascalCase
   ```

4. ✅ Limpiar cache de UI:
   ```
   http://localhost/demo-ui/reset
   ```

### Problema: Error 404 al enviar evento

**Síntoma:**
```
POST /api/ui-event → 404 Not Found
```

**Soluciones:**
1. ✅ Verificar que el servicio esté registrado en `config/ui-services.php`
2. ✅ Verificar que el componente tenga un nombre (no anónimo)
3. ✅ Verificar que el `component_id` se esté enviando correctamente

### Problema: Tabla no renderiza correctamente

**Síntoma:** Las columnas no se alinean o el contenido se corta.

**Soluciones:**
1. ✅ Especificar anchos para columnas con botones:
   ```php
   ->addHeader('Acciones', 'actions', width: 200)
   ```

2. ✅ Verificar que el número de celdas coincida con el número de headers:
   ```php
   // 3 headers
   ->addHeader('ID')->addHeader('Name')->addHeader('Actions')
   
   // 3 celdas por fila
   ->rows([
       [1, 'Juan', $button],  // ✅ Correcto
       [2, 'María']           // ❌ Faltan celdas
   ])
   ```

### Problema: Modal no se abre

**Síntoma:** El modal no aparece al ejecutar acción.

**Soluciones:**
1. ✅ Verificar que el contenedor tenga `parent('modal')`:
   ```php
   UIBuilder::container('modal_content')
       ->parent('modal')  // ← Importante
   ```

2. ✅ Verificar que el div modal exista en `demo.blade.php`:
   ```html
   <div id="modal-overlay" class="modal-overlay hidden">
       <div id="modal" class="modal-container"></div>
   </div>
   ```

### Problema: Inputs no envían sus valores

**Síntoma:** En el handler, `$params['txt_input']` es null.

**Soluciones:**
1. ✅ Verificar que el input tenga un `name`:
   ```php
   UIBuilder::input('txt_email')  // ← El nombre también es el 'name'
       ->name('txt_email')        // (Opcional, se establece automáticamente)
   ```

2. ✅ Verificar que el botón no esté dentro de un formulario HTML nativo

3. ✅ Verificar que el input esté en el mismo contenedor que el botón (o en un ancestro común)

---

## Conclusión

Este framework proporciona una forma poderosa y type-safe de construir interfaces de usuario dinámicas donde:

✅ **El backend tiene control total** sobre la estructura y lógica de la UI
✅ **Las actualizaciones son automáticas** y optimizadas
✅ **El código es limpio y mantenible** gracias a la inyección de componentes
✅ **La separación de responsabilidades es clara** (backend = lógica, frontend = presentación)
✅ **El estado persiste entre requests** gracias al sistema de cache

Para más información sobre componentes específicos, consulta:
- [UI Builder - Tree Architecture](UI/README.md)
- [Ejemplos de Seeders](DATABASE_SEEDERS_GUIDE.md)
- [Documentación de API](API_COMPLETE_DOCUMENTATION.md)

---

**Versión:** 1.0  
**Fecha:** Noviembre 2025  
**Autor:** Framework UI Team
