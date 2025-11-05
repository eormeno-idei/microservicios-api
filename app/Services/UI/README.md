# UI Builder - Tree Architecture

Sistema de construcción de interfaces de usuario basado en el **Patrón Composite** que permite crear UIs como estructuras de árbol con manipulación dinámica.

## 🚀 Quick Start

```php
use App\Services\UI\UIBuilder;
use App\Services\UI\Enums\LayoutType;

// Crear un contenedor
$screen = UIBuilder::container('my_screen')
    ->slot('canvas')
    ->title('My Application');

// Agregar elementos
$screen->add(
    UIBuilder::button('submit')
        ->label('Submit')
        ->action('submit_form')
        ->style('primary')
);

$screen->add(
    UIBuilder::label('info')
        ->text('Fill out the form')
        ->style('info')
);

// Serializar a JSON
$json = $screen->build();
```

## 📦 Componentes Disponibles

### Container
```php
UIBuilder::container('id')
    ->slot('canvas')
    ->layout(LayoutType::VERTICAL) // or HORIZONTAL
    ->title('Title')
    ->add($element)
    ->build();
```

### Button
```php
UIBuilder::button('id')
    ->label('Click Me')
    ->action('my_action', ['param' => 'value'])
    ->icon('check')
    ->style('primary') // primary, success, danger, warning, default
    ->enabled(true)
    ->tooltip('Tooltip text')
    ->build();
```

### Label
```php
UIBuilder::label('id')
    ->text('Text content')
    ->style('default') // default, info, warning, error, success
    ->visible(true)
    ->build();
```

### Table
```php
UIBuilder::table('id')
    ->title('Table Title')
    ->addHeader('Column 1')
    ->addHeader('Column 2', 'col2', width: '200px')
    ->rows([
        ['Data 1', 'Data 2'],
        ['Data 3', 'Data 4']
    ])
    ->pagination(true)
    ->build();
```

## 🌳 Manipulación de Árbol

### Agregar Elementos
```php
$container = UIBuilder::container('parent')->getContainer();

// Agregar un elemento
$container->add(UIBuilder::button('btn1'));

// Agregar múltiples elementos
$container->addMany([
    UIBuilder::button('btn1'),
    UIBuilder::label('lbl1')
]);
```

### Remover Elementos
```php
// Remover (lanza excepción si no existe)
$container->remove('btn1:button');

// Remover (retorna true/false)
$removed = $container->tryRemove('btn1:button');
```

### Actualizar Elementos
```php
$container->update(
    'btn1:button',
    UIBuilder::button('btn1')->label('Updated Label')
);
```

### Buscar Elementos
```php
// Buscar recursivamente en todo el árbol
$element = $container->find('nested_button:button');

if ($element !== null) {
    // Hacer algo con el elemento
}

// Verificar si existe (solo hijos directos)
if ($container->has('btn1:button')) {
    // ...
}
```

### Otros Métodos
```php
// Obtener todos los hijos
$children = $container->getChildren();

// Contar hijos
$count = $container->count();

// Limpiar todos los hijos
$container->clear();
```

## 🏗️ Arquitectura

```
UIElement (interface)
├── UIComponent (abstract) - Elementos hoja
│   ├── ButtonBuilder
│   ├── LabelBuilder
│   └── TableBuilder
└── UIContainer (composite) - Contenedores
    └── Métodos: add, remove, update, find, etc.
```

## 📝 Ejemplos

### Ejemplo 1: UI Simple
```php
$ui = UIBuilder::container('simple')
    ->slot('canvas')
    ->title('Simple UI');

$ui->add(
    UIBuilder::button('submit')
        ->label('Submit')
        ->style('primary')
);

$ui->add(
    UIBuilder::label('info')
        ->text('Click to submit')
);

return $ui->build();
```

### Ejemplo 2: Contenedores Anidados
```php
$root = UIBuilder::container('root')->getContainer();

// Header
$header = new UIContainer('header');
$header->layout(LayoutType::HORIZONTAL);
$header->add(UIBuilder::label('logo')->text('Logo'));
$header->add(UIBuilder::label('title')->text('Title'));

// Content
$content = new UIContainer('content');
$content->add(UIBuilder::button('btn1')->label('Action 1'));
$content->add(UIBuilder::button('btn2')->label('Action 2'));

// Footer
$footer = new UIContainer('footer');
$footer->add(UIBuilder::label('copyright')->text('© 2025'));

// Ensamblar
$root->add($header);
$root->add($content);
$root->add($footer);

return $root->toJson();
```

### Ejemplo 3: Tabla con Acciones
```php
$table = UIBuilder::table('games')
    ->title('My Games')
    ->addHeader('Name')
    ->addHeader('Status')
    ->addHeader('Actions', 'actions', width: '200px');

$rows = [];
foreach ($games as $game) {
    $actions = UIBuilder::container("actions_{$game['id']}")
        ->layout(LayoutType::HORIZONTAL);
    
    $actions->add(
        UIBuilder::button("play_{$game['id']}")
            ->label('Play')
            ->icon('play')
            ->action('play_game', ['game_id' => $game['id']])
    );
    
    $actions->add(
        UIBuilder::button("delete_{$game['id']}")
            ->label('Delete')
            ->icon('trash')
            ->action('delete_game', ['game_id' => $game['id']])
    );
    
    $rows[] = [
        $game['name'],
        $game['status'],
        $actions->build()
    ];
}

$table->rows($rows);
```

### Ejemplo 4: Modificación Dinámica
```php
$container = UIBuilder::container('dynamic')->getContainer();

// Agregar elementos
$container->add(UIBuilder::button('btn1')->label('Button 1'));
$container->add(UIBuilder::button('btn2')->label('Button 2'));

// Remover elemento
$container->remove('btn2:button');

// Actualizar elemento
$container->update(
    'btn1:button',
    UIBuilder::button('btn1')->label('Updated')->enabled(false)
);

// Buscar y modificar
$found = $container->find('btn1:button');
if ($found) {
    // Hacer algo con el elemento encontrado
}
```

## 🎯 Formato de IDs

Todos los elementos tienen IDs simples (sin tipo concatenado).
El tipo se especifica como un atributo separado en la configuración.

Ejemplos de IDs:
- `submit`
- `info`
- `games_table`
- `header`

El tipo está disponible en el atributo `type` de la configuración.

## 📤 Serialización

El método `toJson()` serializa recursivamente toda la estructura:

```php
$json = $container->toJson();

// Resultado:
[
    'container_id' => [
        'type' => 'container',
        'visible' => true,
        'layout' => 'vertical',
        'elements' => [
            'button_id' => [
                'type' => 'button',
                ...
            ],
            'label_id' => [
                'type' => 'label',
                ...
            ],
            'nested' => [
                'type' => 'container',
                'elements' => [
                    // Elementos anidados
                ]
            ]
        ]
    ]
]
```

## ✅ Testing

```php
use App\Services\UI\Components\UIContainer;
use App\Services\UI\UIBuilder;

test('can manipulate UI tree', function () {
    $container = new UIContainer('test');
    
    // Add
    $container->add(UIBuilder::button('btn1'));
    expect($container->count())->toBe(1);
    
    // Remove
    $container->remove('btn1:button');
    expect($container->count())->toBe(0);
    
    // Find
    $container->add(UIBuilder::button('btn2'));
    $found = $container->find('btn2:button');
    expect($found)->not->toBeNull();
});
```

## 📚 Documentación Completa

- [Arquitectura Completa](../../docs/ui-builder-tree-architecture.md)
- [Diagrama Visual](../../docs/ui-builder-architecture-diagram.md)
- [Resumen de Implementación](../../docs/IMPLEMENTATION_SUMMARY.md)
- [Ejemplos Prácticos](../../docs/examples/ui-builder-tree-example.php)

## 🔄 Migración desde Versión Antigua

### Antes (Array Concatenation)
```php
private function buildUI(): array
{
    $elements = [];
    $elements += UIBuilder::button('btn1')->build();
    $elements += UIBuilder::label('lbl1')->build();
    return $elements;
}
```

### Ahora (Tree Structure)
```php
private function buildUI($container): void
{
    $container->add(UIBuilder::button('btn1'));
    $container->add(UIBuilder::label('lbl1'));
}

// Uso:
$container = UIBuilder::container('ui');
$this->buildUI($container);
return $container->build();
```

## ⚡ Mejores Prácticas

1. **Usar `getContainer()`** cuando necesites manipulación avanzada:
   ```php
   $container = UIBuilder::container('id')->getContainer();
   ```

2. **No llamar `.build()` en elementos intermedios**:
   ```php
   // ❌ Mal
   $container->add(UIBuilder::button('btn')->build());
   
   // ✅ Bien
   $container->add(UIBuilder::button('btn'));
   ```

3. **Usar búsqueda recursiva** para elementos anidados:
   ```php
   $element = $root->find('deep_element:button');
   ```

4. **Aprovechar el encadenamiento fluido**:
   ```php
   $container
       ->add($element1)
       ->add($element2)
       ->add($element3);
   ```

## 🐛 Troubleshooting

**Problema**: Excepción "Element already exists"
```php
// Causa: Intentar agregar elemento con ID duplicado
$container->add(UIBuilder::button('btn1'));
$container->add(UIBuilder::button('btn1')); // ❌ Error

// Solución: Usar tryRemove o IDs únicos
$container->tryRemove('btn1:button');
$container->add(UIBuilder::button('btn1')); // ✅ Ok
```

**Problema**: Elemento no encontrado con `find()`
```php
// Usa el ID simple, sin el tipo
$found = $container->find('btn1'); // ✅ Correcto
$found = $container->find('btn1:button'); // ❌ Ya no se usa este formato
```

## 📞 Soporte

Para preguntas o problemas, consulta:
- Tests: `tests/Unit/Services/UI/UIContainerTest.php`
- Ejemplos: `docs/examples/ui-builder-tree-example.php`
- Documentación: `docs/ui-builder-tree-architecture.md`

---

**Versión**: 2.0 (Tree Architecture)  
**Última actualización**: Octubre 2025
