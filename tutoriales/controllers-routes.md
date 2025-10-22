# Laravel: Controllers y Routes - Gestión de Rutas y Controladores

## Información del Taller

**Nivel:** Intermedio
**Requisitos previos:** Conocimientos básicos de PHP y MVC
**Duración estimada:** 3 horas

## Objetivos de Aprendizaje

Al finalizar este taller, los estudiantes serán capaces de:

- Entender el sistema de routing de Laravel
- Crear y gestionar rutas de diferentes tipos
- Desarrollar controladores tanto para web como API
- Implementar controladores inline y con clases
- Integrar controladores con modelos Eloquent
- Aplicar mejores prácticas en la organización de rutas y controladores
- Manejar parámetros de rutas y validación básica

## Introducción al Sistema de Routing

### ¿Qué son las Rutas en Laravel?

Las **rutas** en Laravel definen cómo la aplicación responde a las peticiones HTTP de los clientes. Actúan como el punto de entrada que conecta las URLs con la lógica de la aplicación.

#### Características principales:

- Mapean URLs a acciones específicas
- Soportan diferentes métodos HTTP (GET, POST, PUT, DELETE, etc.)
- Permiten parámetros dinámicos
- Incluyen middleware para autenticación y validación
- Pueden agruparse para mejor organización
- Soportan nombres para generar URLs

#### Archivos de Rutas en Laravel

Laravel organiza las rutas en diferentes archivos según su propósito:

```
routes/
├── api.php      # Rutas de API (prefijo /api)
├── web.php      # Rutas web tradicionales
├── console.php  # Comandos de consola
└── channels.php # Broadcasting
```

### Tipos Básicos de Rutas

#### 1. Rutas Básicas

```php
<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

// Ruta GET simple
Route::get('/', function () {
    return view('welcome');
});
```

#### 2. Rutas con Parámetros

```php
// Parámetro obligatorio
Route::get('/customers/{id}', function ($id) {
    return "Cliente ID: {$id}";
});

// Parámetro opcional
Route::get('/customers/{id?}', function ($id = null) {
    if ($id) {
        return "Cliente ID: {$id}";
    }
    return "Lista de todos los clientes";
});

// Múltiples parámetros
Route::get('/customers/{id}/reviews/{reviewId}', function ($id, $reviewId) {
    return "Review {$reviewId} del cliente {$id}";
});
```

#### 3. Restricciones en Parámetros

```php
// Solo números
Route::get('/product/{id}', function ($id) {
    return "Producto: {$id}";
})->where('id', '[0-9]+');

// Solo letras
Route::get('/categories/{slug}', function ($slug) {
    return "Categoría: {$slug}";
})->where('slug', '[a-zA-Z\-]+');

// Expresiones regulares múltiples
Route::get('/reseñas/{mes}/{año}', function ($mes, $año) {
    return "Reseñas de {$mes}/{$año}";
})->where(['año' => '[0-9]{4}', 'mes' => '[0-9]{2}']);
```

#### 4. Rutas Nombradas

```php
// Definir una ruta con nombre
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// Usar el nombre para generar URLs
$url = route('dashboard'); // Genera: http://ejemplo.com/dashboard

// En vistas Blade
<a href="{{ route('dashboard') }}">Dashboard</a>
```

## Controladores Inline (Funciones Lambda)

### ¿Cuándo usar Controladores Inline?

Los controladores inline son útiles para:
- Lógica muy simple
- Prototipos rápidos
- APIs simples con una sola acción
- Endpoints de prueba

### Ejemplos Básicos

```php
<?php
// routes/web.php

// Respuesta simple de texto
Route::get('/hello', function () {
    return 'Hola mundo desde Laravel';
});

// Respuesta JSON en API routes/api.php
Route::get('/api/status', function () {
    return response()->json([
        'status' => 'OK',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

// Vista simple
Route::get('/about', function () {
    return view('about', [
        'title' => 'Acerca de nosotros',
        'company' => 'Mi Empresa'
    ]);
});
```

### Controladores Inline con Lógica de Negocio

```php
// Ejemplo: API simple de productos
Route::get('/api/products', function () {
    $products = [
        ['id' => 1, 'name' => 'Laptop', 'price' => 999.99],
        ['id' => 2, 'name' => 'Mouse', 'price' => 25.99],
        ['id' => 3, 'name' => 'Teclado', 'price' => 89.99]
    ];
    
    return response()->json([
        'data' => $products,
        'count' => count($products)
    ]);
});

// Endpoint con parámetros y validación básica
Route::get('/calc/{operation}/{num1}/{num2}', function ($operation, $num1, $num2) {
    $result = 0;
    switch ($operation) {
        case 'add':
            $result = $num1 + $num2;
            break;
        case 'sub':
            $result = $num1 - $num2;
            break;
        case 'mul':
            $result = $num1 * $num2;
            break;
        case 'div':
            $result = $num2 != 0 ? $num1 / $num2 : 'Error: Zero division';
            break;
        default:
            return response()->json(['error' => 'Invalid operator'], 400);
    }
    return response()->json([
        'operation' => $operation,
        'num1' => $num1,
        'num2' => $num2,
        'result' => $result
    ]);
});
```

### Limitaciones de los Controladores Inline

```php
// Esto se vuelve difícil de mantener:
Route::post('/stat', function () {
    $validated = request()->validate([
        'numbers' => 'required|array|min:2',
        'numbers.*' => 'numeric',
        'operation' => 'required|in:sum,mean'
    ],
    [
        'numbers.required' => 'Debe proporcionar al menos 2 números.',
        'numbers.array' => 'Los números deben ser un array.',
        'numbers.min' => 'Debe proporcionar al menos 2 números.',
        'numbers.*.numeric' => 'Todos los elementos del array deben ser numéricos.',
        'operation.required' => 'La operación es obligatoria.',
        'operation.in' => 'La operación debe ser: sum o mean.'
    ]);

    $numbers = $validated['numbers'];
    $operation = $validated['operation'];
    $result = null;
    switch ($operation) {
        case 'sum':
            $result = array_sum($numbers);
            break;
        case 'mean':
            $result = array_sum($numbers) / count($numbers);
            break;
    }
    return response()->json(['result' => $result]);
});


// El json del post podría ser:
{
    "numbers": [1, 2, 3, 4, 5],
    "operation": "mean"
}

// En este punto es mejor usar un Controller
```

## Controladores con Clases

### Creando Controladores

```bash
# Crear un controlador básico
php artisan make:controller HolaController

# Crear controlador con métodos REST
php artisan make:controller ProductController --resource

# Crear controlador para API
php artisan make:controller Api/ProductController --api

# Crear controlador con modelo asociado
php artisan make:controller ProductController --resource --model=Product
```

### Estructura Básica de un Controlador

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HolaController extends Controller
{
    public function saludar()
    {
        return view("hola-mundo");
    }
}
```

### Asociando Rutas con Controladores

```php
<?php
// routes/web.php

use App\Http\Controllers\HolaController;

// Método específico
Route::get('/', [HolaController::class, 'saludar']);
```

## Controladores Resource (RESTful)

### Concepto de Resource Controllers

Los Resource Controllers implementan las operaciones CRUD siguiendo las convenciones REST:

| Verbo HTTP | URI                | Acción  | Nombre de Ruta | Propósito |
|------------|--------------------|---------|----|-----------|
| GET        | /products          | index   | products.index | Listar todos |
| GET        | /products/create   | create  | products.create | Mostrar formulario de creación |
| POST       | /products          | store   | products.store | Guardar nuevo |
| GET        | /products/{id}     | show    | products.show | Mostrar específico |
| GET        | /products/{id}/edit| edit    | products.edit | Mostrar formulario de edición |
| PUT/PATCH  | /products/{id}     | update  | products.update | Actualizar |
| DELETE     | /products/{id}     | destroy | products.destroy | Eliminar |

### Creando un Resource Controller

```php
<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Listar todos los productos
        $products = [
            ['id' => 1, 'name' => 'Laptop', 'price' => 999.99],
            ['id' => 2, 'name' => 'Mouse', 'price' => 25.99],
            ['id' => 3, 'name' => 'Teclado', 'price' => 89.99]
        ];
        
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0'
        ]);
        
        // Aquí guardarías en la base de datos
        // Product::create($request->validated());
        
        return redirect()->route('products.index')
                        ->with('success', 'Producto creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Buscar producto específico
        $product = ['id' => $id, 'name' => 'Producto ' . $id, 'price' => 99.99];
        
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = ['id' => $id, 'name' => 'Producto ' . $id, 'price' => 99.99];
        
        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0'
        ]);
        
        // Aquí actualizarías en la base de datos
        // $product = Product::findOrFail($id);
        // $product->update($request->validated());
        
        return redirect()->route('products.show', $id)
                        ->with('success', 'Producto actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Aquí eliminarías de la base de datos
        // Product::findOrFail($id)->delete();
        
        return redirect()->route('products.index')
                        ->with('success', 'Producto eliminado correctamente');
    }
}
```

### Registrando Resource Routes

```php
<?php
// routes/web.php

use App\Http\Controllers\ProductController;

// Registra todas las rutas REST automáticamente
Route::resource('products', ProductController::class);

// Solo ciertas acciones
Route::resource('products', ProductController::class)
    ->only(['index', 'show', 'create', 'store']);

// Excluir ciertas acciones
Route::resource('products', ProductController::class)
    ->except(['destroy']);

// Múltiples resources
Route::resources([
    'products' => ProductController::class,
    'categories' => CategoryController::class,
]);
```

## Controladores API

### Diferencias entre Controladores Web y API

| Aspecto | Controladores Web | Controladores API |
|---------|-------------------|-------------------|
| Respuesta | HTML (vistas) | JSON/XML |
| Estado | Sesiones/cookies | Stateless |
| Autenticación | Sessions | Tokens |
| Validación | Redirect back | HTTP status codes |
| Rutas | web.php | api.php |

### Creando un API Controller

```php
<?php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $products = [
            ['id' => 1, 'name' => 'Laptop', 'price' => 999.99, 'stock' => 5],
            ['id' => 2, 'name' => 'Mouse', 'price' => 25.99, 'stock' => 20],
            ['id' => 3, 'name' => 'Teclado', 'price' => 89.99, 'stock' => 15]
        ];

        return response()->json([
            'success' => true,
            'data' => $products,
            'meta' => [
                'total' => count($products),
                'page' => 1,
                'per_page' => 10
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0'
        ]);

        // Simular creación de producto
        $product = [
            'id' => rand(4, 100),
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'stock' => $request->input('stock'),
            'created_at' => now()->toISOString()
        ];

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'data' => $product
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        // Simular búsqueda de producto
        if (!is_numeric($id) || $id < 1) {
            return response()->json([
                'success' => false,
                'message' => 'ID de producto no válido'
            ], 400);
        }

        $product = [
            'id' => (int) $id,
            'name' => 'Producto ' . $id,
            'price' => 99.99,
            'stock' => 10,
            'description' => 'Descripción del producto ' . $id
        ];

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0'
        ]);

        // Simular actualización
        $product = [
            'id' => (int) $id,
            'name' => $request->input('name', 'Producto ' . $id),
            'price' => $request->input('price', 99.99),
            'stock' => $request->input('stock', 10),
            'updated_at' => now()->toISOString()
        ];

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente',
            'data' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        // Verificar que el producto existe (simulado)
        if (!is_numeric($id) || $id < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente'
        ]);
    }
}
```

### Registrando Rutas API

```php
<?php
// routes/api.php

use App\Http\Controllers\Api\ProductController;

// Resource API (excluye create y edit que son para formularios)
Route::apiResource('products', ProductController::class);

// Rutas adicionales de API
Route::prefix('v1')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::get('products/{id}/stock', [ProductController::class, 'checkStock']);
});

// Con middleware de autenticación
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('products', ProductController::class);
});
```

## Integración con Modelos Eloquent

### Revisando los Modelos Existentes

Primero, veamos los modelos que tienes en tu proyecto:

```php
// app/Models/Category.php - Modelo básico con relaciones
// app/Models/Customer.php - Clientes con campos personales
// app/Models/Product.php - Productos con categorías
// app/Models/Review.php - Reseñas de productos
// app/Models/User.php - Usuarios del sistema
```

### Controlador con Eloquent: CategoryController

```php
<?php
// app/Http/Controllers/CategoryController.php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categories = Category::select('id', 'name', 'slug', 'color', 'is_active')
                             ->orderBy('name')
                             ->get();
        
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean'
        ]);
        
        $category = Category::create($validated);
        
        return redirect()->route('categories.show', $category)
                        ->with('success', 'Categoría creada correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): View
    {
        // Model binding automático - Laravel busca por ID
        // También podemos cargar relaciones
        $category->loadCount('products');
        
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean'
        ]);
        
        $category->update($validated);
        
        return redirect()->route('categories.show', $category)
                        ->with('success', 'Categoría actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Verificar si tiene productos asociados
        if ($category->products()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría con productos asociados');
        }
        
        $category->delete();
        
        return redirect()->route('categories.index')
                        ->with('success', 'Categoría eliminada correctamente');
    }
}
```

### Controlador API con Eloquent: CustomerApiController

```php
<?php
// app/Http/Controllers/Api/CustomerController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();
        
        // Filtros opcionales
        if ($request->has('is_premium')) {
            $query->where('is_premium', $request->boolean('is_premium'));
        }
        
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Paginación
        $perPage = $request->input('per_page', 15);
        $customers = $query->select('id', 'first_name', 'last_name', 'email', 'is_premium', 'created_at')
                          ->orderBy('created_at', 'desc')
                          ->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total()
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'is_premium' => 'boolean'
        ]);
        
        $customer = Customer::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Cliente creado correctamente',
            'data' => $customer
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer): JsonResponse
    {
        // Cargar relaciones si es necesario
        $customer->load(['reviews' => function ($query) {
            $query->with('product:id,name')->latest()->limit(5);
        }]);
        
        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'is_premium' => 'boolean'
        ]);
        
        $customer->update($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado correctamente',
            'data' => $customer->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        // Verificar si tiene reseñas
        if ($customer->reviews()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un cliente con reseñas asociadas'
            ], 400);
        }
        
        $customer->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado correctamente'
        ]);
    }
    
    /**
     * Get customer's reviews
     */
    public function reviews(Customer $customer): JsonResponse
    {
        $reviews = $customer->reviews()
                          ->with('product:id,name,price')
                          ->orderBy('reviewed_at', 'desc')
                          ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'total' => $reviews->total()
            ]
        ]);
    }
}
```

### Controlador Complejo: ProductController con Relaciones

```php
<?php
// app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Product::with('category:id,name,color');
        
        // Filtro por categoría
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }
        
        // Filtro por precio
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Filtro por stock
        if ($request->has('in_stock')) {
            $query->where('stock', '>', 0);
        }
        
        // Ordenamiento
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        
        if (in_array($sortBy, ['name', 'price', 'stock', 'created_at'])) {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $products = $query->paginate(12)->withQueryString();
        
        // Datos para filtros
        $categories = Category::select('id', 'name')
                             ->where('is_active', true)
                             ->orderBy('name')
                             ->get();
        
        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categories = Category::select('id', 'name')
                             ->where('is_active', true)
                             ->orderBy('name')
                             ->get();
        
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'weight' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'category_id' => 'required|exists:categories,id'
        ]);
        
        $product = Product::create($validated);
        
        return redirect()->route('products.show', $product)
                        ->with('success', 'Producto creado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        // Cargar relaciones necesarias
        $product->load([
            'category:id,name,color',
            'reviews' => function ($query) {
                $query->with('customer:id,first_name,last_name')
                      ->orderBy('reviewed_at', 'desc')
                      ->limit(5);
            }
        ]);
        
        // Calcular estadísticas de reseñas
        $reviewsStats = [
            'total' => $product->reviews()->count(),
            'average' => round($product->reviews()->avg('rating'), 1),
            'distribution' => $product->reviews()
                                   ->selectRaw('rating, COUNT(*) as count')
                                   ->groupBy('rating')
                                   ->orderBy('rating', 'desc')
                                   ->pluck('count', 'rating')
                                   ->toArray()
        ];
        
        // Productos relacionados (misma categoría)
        $relatedProducts = Product::where('category_id', $product->category_id)
                                 ->where('id', '!=', $product->id)
                                 ->where('is_active', true)
                                 ->inRandomOrder()
                                 ->limit(4)
                                 ->get();
        
        return view('products.show', compact('product', 'reviewsStats', 'relatedProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        $categories = Category::select('id', 'name')
                             ->where('is_active', true)
                             ->orderBy('name')
                             ->get();
        
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'weight' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'category_id' => 'required|exists:categories,id'
        ]);
        
        $product->update($validated);
        
        return redirect()->route('products.show', $product)
                        ->with('success', 'Producto actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Verificar si tiene reseñas
        if ($product->reviews()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un producto con reseñas');
        }
        
        $product->delete();
        
        return redirect()->route('products.index')
                        ->with('success', 'Producto eliminado correctamente');
    }
}
```

## Agrupación y Organización de Rutas

### Agrupación Básica

```php
<?php
// routes/web.php

// Agrupación por prefijo
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    });
    Route::resource('products', AdminProductController::class);
    Route::resource('categories', AdminCategoryController::class);
});

// Agrupación por middleware
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('profile', ProfileController::class)->only(['show', 'edit', 'update']);
});

// Agrupación por namespace
Route::namespace('Admin')->prefix('admin')->group(function () {
    // Los controladores estarán en App\Http\Controllers\Admin\
    Route::resource('customers', 'CustomerController');
});
```

### Agrupación Avanzada con Múltiples Parámetros

```php
<?php
// routes/web.php

// Agrupación compleja
Route::prefix('dashboard')
     ->middleware(['auth', 'verified'])
     ->name('dashboard.')
     ->group(function () {
         Route::get('/', [DashboardController::class, 'index'])->name('home');
         Route::resource('products', ProductController::class);
         Route::resource('customers', CustomerController::class);
         
         // Sub-agrupación para administradores
         Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
             Route::resource('customers', CustomerController::class);
             Route::resource('categories', CategoryController::class);
         });
     });

// routes/api.php
Route::prefix('v1')->middleware('api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    });
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('products', Api\ProductController::class);
        Route::apiResource('customers', Api\CustomerController::class);
        Route::apiResource('categories', Api\CategoryController::class);
        
        // Rutas anidadas
        Route::get('customers/{customer}/reviews', [Api\CustomerController::class, 'reviews']);
        Route::apiResource('products.reviews', Api\ProductReviewController::class)->shallow();
    });
});
```

## Manejo de Errores y Validación

### Validación en Controladores

```php
<?php
// app/Http/Controllers/ReviewController.php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'customer_id' => 'required|exists:customers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'is_verified_purchase' => 'boolean'
        ]);
        
        // Verificar que el cliente no haya reseñado ya este producto
        $existingReview = Review::where('product_id', $validated['product_id'])
                               ->where('customer_id', $validated['customer_id'])
                               ->first();
        
        if ($existingReview) {
            return back()->withErrors(['review' => 'Ya has reseñado este producto']);
        }
        
        $validated['reviewed_at'] = now();
        $review = Review::create($validated);
        
        return redirect()->route('products.show', $validated['product_id'])
                        ->with('success', 'Reseña agregada correctamente');
    }
    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        // Solo el propietario puede editar
        if ($review->customer_id !== auth()->id()) {
            abort(403, 'No tienes permiso para editar esta reseña');
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);
        
        $review->update($validated);
        
        return back()->with('success', 'Reseña actualizada correctamente');
    }
}
```

### Manejo de Errores en API Controllers

```php
<?php
// app/Http/Controllers/Api/ReviewController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'customer_id' => 'required|exists:customers,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'is_verified_purchase' => 'boolean'
            ]);
            
            // Verificar duplicados
            $existingReview = Review::where('product_id', $validated['product_id'])
                                   ->where('customer_id', $validated['customer_id'])
                                   ->first();
            
            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una reseña para este producto y cliente'
                ], 400);
            }
            
            $validated['reviewed_at'] = now();
            $review = Review::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Reseña creada correctamente',
                'data' => $review->load(['product:id,name', 'customer:id,first_name,last_name'])
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}
```

## Mejores Prácticas

### Organización de Controladores

```
app/Http/Controllers/
├── Api/                    # Controladores de API
│   ├── V1/                # Versionado de API
│   │   ├── ProductController.php
│   │   └── CustomerController.php
│   └── AuthController.php
├── Admin/                  # Controladores de administración
│   ├── DashboardController.php
│   ├── CustomerController.php
│   └── SettingsController.php
├── Auth/                   # Autenticación personalizada
│   ├── LoginController.php
│   └── RegisterController.php
├── ProductController.php   # Controladores principales
├── CustomerController.php
└── HomeController.php
```

### Principios de Desarrollo

#### 1. Single Responsibility Principle

```php
// Malo: Controlador que hace demasiado
class ProductController extends Controller
{
    public function store(Request $request)
    {
        // Validación
        $validated = $request->validate([...]);
        
        // Lógica de negocio compleja
        if ($validated['price'] > 1000) {
            // Enviar email al manager
            Mail::to('manager@empresa.com')->send(new ExpensiveProductAlert($validated));
        }
        
        // Procesar imagen
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products');
            $validated['image_url'] = Storage::url($path);
        }
        
        // Actualizar inventario
        $inventory = Inventory::where('product_sku', $validated['sku'])->first();
        $inventory->decrement('quantity', $validated['stock']);
        
        // Crear producto
        $product = Product::create($validated);
        
        return redirect()->route('products.index');
    }
}

// Mejor: Usar servicios y form requests
class ProductController extends Controller
{
    protected $productService;
    
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    
    public function store(StoreProductRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());
        
        return redirect()->route('products.show', $product)
                        ->with('success', 'Producto creado correctamente');
    }
}
```

#### 2. Consistencia en Respuestas

```php
// Para controladores web
class BaseController extends Controller
{
    protected function successResponse(string $message, $data = null)
    {
        return back()->with('success', $message)->with('data', $data);
    }
    
    protected function errorResponse(string $message, $errors = null)
    {
        return back()->withErrors(['error' => $message])->withInput();
    }
}

// Para controladores API
class BaseApiController extends Controller
{
    protected function successResponse($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }
    
    protected function errorResponse(string $message, $errors = null, int $code = 400)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];
        
        if ($errors) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $code);
    }
}
```

#### 3. Model Binding Personalizado

```php
// En RouteServiceProvider.php
public function boot()
{
    // Buscar productos por slug en lugar de ID
    Route::bind('product', function ($value) {
        return Product::where('slug', $value)->firstOrFail();
    });
    
    // Buscar categorías activas únicamente
    Route::bind('activeCategory', function ($value) {
        return Category::where('slug', $value)
                      ->where('is_active', true)
                      ->firstOrFail();
    });
}

// En las rutas
Route::get('/products/{product:slug}', [ProductController::class, 'show']);
Route::get('/categories/{activeCategory:slug}/products', [CategoryController::class, 'products']);
```

## Ejercicios Prácticos

### Ejercicio 1: Controlador Básico de Comentarios

**Objetivo**: Crear un sistema simple de comentarios usando controladores inline.

**Tareas**:
1. Crear rutas inline para listar, crear y eliminar comentarios
2. Usar arrays estáticos para simular datos
3. Implementar validación básica

**Esqueleto**:

```php
// routes/web.php

// Listar comentarios
Route::get('/comments', function () {
    $comments = [
        ['id' => 1, 'author' => 'Juan', 'content' => 'Excelente artículo', 'created_at' => '2025-01-15'],
        ['id' => 2, 'author' => 'María', 'content' => 'Muy útil la información', 'created_at' => '2025-01-14'],
    ];
    
    // Tu implementación aquí
});

// Crear comentario
Route::post('/comments', function (Request $request) {
    // Validar datos
    // Simular guardado
    // Retornar respuesta
});

// Eliminar comentario
Route::delete('/comments/{id}', function ($id) {
    // Validar que existe
    // Simular eliminación
    // Retornar respuesta
});
```

### Ejercicio 2: Resource Controller Completo

**Objetivo**: Crear un controlador resource para gestionar categorías.

**Tareas**:
1. Generar el controlador con artisan
2. Implementar todos los métodos CRUD
3. Integrar con el modelo Category existente
4. Agregar validaciones apropiadas

**Comandos iniciales**:

```bash
php artisan make:controller CategoryManagementController --resource --model=Category
```

### Ejercicio 3: API Controller con Filtros

**Objetivo**: Crear una API para productos con capacidades de filtrado avanzado.

**Tareas**:
1. Crear controlador API para productos
2. Implementar filtros por precio, categoría, stock
3. Agregar búsqueda por texto
4. Implementar paginación y ordenamiento
5. Manejar errores apropiadamente

**Esqueleto**:

```php
// app/Http/Controllers/Api/ProductSearchController.php

class ProductSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = Product::with('category:id,name');
        
        // Filtro por texto (nombre o descripción)
        if ($request->has('search')) {
            // Tu implementación
        }
        
        // Filtro por rango de precios
        if ($request->has('min_price') || $request->has('max_price')) {
            // Tu implementación
        }
        
        // Filtro por categoría
        if ($request->has('category_id')) {
            // Tu implementación
        }
        
        // Filtro por disponibilidad
        if ($request->has('in_stock')) {
            // Tu implementación
        }
        
        // Ordenamiento
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        
        // Tu implementación de ordenamiento
        
        // Paginación
        $products = $query->paginate($request->input('per_page', 15));
        
        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                // Información de paginación
            ]
        ]);
    }
}
```

### Ejercicio 4: Controlador con Relaciones Complejas

**Objetivo**: Crear un controlador que maneje reseñas de productos con estadísticas.

**Tareas**:
1. Crear controlador para gestionar reseñas
2. Implementar método para obtener estadísticas de producto
3. Crear endpoint para reseñas por cliente
4. Agregar validación de duplicados

**Funcionalidades esperadas**:
- Crear reseña (validar que no exista duplicada)
- Obtener reseñas de un producto con paginación
- Obtener estadísticas de reseñas por producto
- Obtener historial de reseñas de un cliente

### Ejercicio 5: Rutas Agrupadas y Middleware

**Objetivo**: Organizar las rutas del proyecto con agrupaciones lógicas.

**Tareas**:
1. Crear agrupaciones para diferentes tipos de clientes
2. Implementar middleware de autenticación simulado
3. Organizar rutas API con versionado
4. Crear rutas anidadas para recursos relacionados

**Estructura esperada**:

```php
// Rutas públicas
Route::get('/', [HomeController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);

// Rutas de cliente autenticado
Route::middleware('auth')->group(function () {
    // Dashboard y perfil
});

// Rutas de administrador
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Gestión de productos, categorías, clientes
});

// API v1
Route::prefix('api/v1')->group(function () {
    // Rutas públicas de API
    // Rutas protegidas de API
});
```

## Comandos Útiles para Desarrollo

### Comandos Artisan para Controladores

```bash
# Crear controlador básico
php artisan make:controller ExampleController

# Controlador con métodos resource
php artisan make:controller ProductController --resource

# Controlador API (sin create/edit)
php artisan make:controller Api/ProductController --api

# Controlador con modelo asociado
php artisan make:controller ProductController --resource --model=Product

# Controlador invocable (solo método __invoke)
php artisan make:controller ProcessPaymentController --invokable

# Ver todas las rutas registradas
php artisan route:list

# Ver rutas filtradas
php artisan route:list --name=product
php artisan route:list --method=GET

# Limpiar cache de rutas
php artisan route:clear
php artisan route:cache
```

### Debugging y Testing

```bash
# Ver información de una ruta específica
php artisan route:show products.index

# Generar URLs desde línea de comandos
php artisan tinker
> route('products.show', 1)
> url('/products/1')

# Probar controladores en Tinker
> $controller = new App\Http\Controllers\ProductController();
> $request = request();
> $controller->index($request);
```

## Resumen del Taller

### Conceptos Clave Aprendidos:

1. **Sistema de Routing**:
   - Rutas básicas con diferentes métodos HTTP
   - Parámetros obligatorios y opcionales
   - Restricciones y validaciones de parámetros
   - Rutas nombradas para mejor mantenibilidad

2. **Controladores Inline**:
   - Funciones lambda para lógica simple
   - Casos de uso apropiados y limitaciones
   - Cuándo migrar a controladores de clase

3. **Controladores de Clase**:
   - Estructura y organización
   - Resource controllers para operaciones CRUD
   - Diferencias entre controladores web y API
   - Integración con modelos Eloquent

4. **Mejores Prácticas**:
   - Organización del código
   - Manejo de errores y validación
   - Respuestas consistentes
   - Principio de responsabilidad única

5. **Funcionalidades Avanzadas**:
   - Agrupación de rutas
   - Model binding automático
   - Middleware y autenticación
   - Relaciones complejas con Eloquent

### Próximos Pasos:

- Implementar middleware personalizado
- Crear form requests para validación avanzada
- Desarrollar servicios para lógica de negocio compleja
- Implementar cache y optimización de consultas
- Agregar testing automatizado para controladores

### Recursos Adicionales:

- [Documentación oficial de Routing](https://laravel.com/docs/routing)
- [Documentación de Controllers](https://laravel.com/docs/controllers)
- [Guía de RESTful APIs](https://laravel.com/docs/eloquent-resources)
- [Mejores prácticas de Laravel](https://github.com/alexeymezenin/laravel-best-practices)
