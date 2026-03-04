# Guía de Alineación de Contenedores Horizontales

## Resumen
Esta guía explica cómo configurar contenedores horizontales con diferentes estilos de alineación en el USIM Framework.

## Enums Disponibles

### `JustifyContent` (Alineación en eje principal/horizontal)
```php
use App\Services\UI\Enums\JustifyContent;

JustifyContent::START           // Items al inicio (izquierda) - DEFAULT
JustifyContent::END             // Items al final (derecha)
JustifyContent::CENTER          // Items centrados
JustifyContent::SPACE_BETWEEN   // Primer item a la izquierda, último a la derecha
JustifyContent::SPACE_AROUND    // Espacio igual alrededor de cada item
JustifyContent::SPACE_EVENLY    // Espacio completamente uniforme
```

### `AlignItems` (Alineación en eje transversal/vertical)
```php
use App\Services\UI\Enums\AlignItems;

AlignItems::START      // Items arriba
AlignItems::END        // Items abajo
AlignItems::CENTER     // Items centrados verticalmente
AlignItems::BASELINE   // Items alineados por su línea base de texto
AlignItems::STRETCH    // Items se estiran para llenar el contenedor - DEFAULT
```

## Ejemplos de Uso

### 1. Items juntos al inicio (por defecto)
```php
$container = UIBuilder::container('my_container')
    ->layout(LayoutType::HORIZONTAL);
    // No se especifica justifyContent, usa START por defecto
```

**Resultado visual:**
```
[Item1] [Item2] ..................
```

---

### 2. Primer item a la izquierda, último a la derecha (JUSTIFICADO)
**Perfecto para: Headers, Navigation bars, Toolbars**

```php
$container = UIBuilder::container('menu_header')
    ->layout(LayoutType::HORIZONTAL)
    ->justifyContent(JustifyContent::SPACE_BETWEEN)
    ->alignItems(AlignItems::CENTER);
```

**Resultado visual:**
```
[Item1] .................. [Item2]
```

---

### 3. Ambos items centrados con gap
**Perfecto para: Botones de acción, Controles centrados**

```php
$container = UIBuilder::container('centered_controls')
    ->layout(LayoutType::HORIZONTAL)
    ->justifyContent(JustifyContent::CENTER)
    ->alignItems(AlignItems::CENTER)
    ->gap('20px');
```

**Resultado visual:**
```
.......... [Item1] [GAP] [Item2] ..........
```

---

### 4. Items con espacio uniforme entre ellos
**Perfecto para: Cards horizontales, Galería de items**

```php
$container = UIBuilder::container('gallery')
    ->layout(LayoutType::HORIZONTAL)
    ->justifyContent(JustifyContent::SPACE_EVENLY)
    ->alignItems(AlignItems::CENTER);
```

**Resultado visual:**
```
.. [Item1] .... [Item2] .... [Item3] ..
```

---

## Ejemplo Completo: Menu con User Info

```php
use App\Services\UI\Enums\JustifyContent;
use App\Services\UI\Enums\AlignItems;

protected function buildBaseUI(...$params): UIContainer
{
    $menu_placeholder = UIBuilder::container('_menu_placeholder')
        ->parent('menu')
        ->layout(LayoutType::HORIZONTAL)
        ->justifyContent(JustifyContent::SPACE_BETWEEN)  // Menú izq, User der
        ->alignItems(AlignItems::CENTER)                 // Centrar verticalmente
        ->gap('20px')                                     // 20px entre items
        ->padding(0)
        ->shadow(0)
        ->borderRadius(0);

    $menu_placeholder
        ->add($this->buildMenu())      // Menú principal (izquierda)
        ->add($this->buildUserMenu()); // Menú usuario (derecha)

    return $menu_placeholder;
}
```

---

## Propiedades Adicionales de Spacing

### Gap (Espacio entre items)
```php
->gap('20px')           // Gap uniforme horizontal y vertical
->rowGap('10px')        // Gap solo entre filas
->columnGap('15px')     // Gap solo entre columnas
```

### Padding (Espacio interno del contenedor)
```php
->padding('10px')                                    // Padding uniforme
->padding('10px 20px')                               // Vertical | Horizontal
->padding('10px 20px 15px 25px')                     // Top Right Bottom Left
->paddingTop('10px')->paddingRight('20px')...etc     // Individual
```

---

## Casos de Uso Comunes

| Layout Deseado | justifyContent | alignItems | gap |
|----------------|----------------|------------|-----|
| **Header: Logo izq, User der** | `SPACE_BETWEEN` | `CENTER` | - |
| **Botones centrados** | `CENTER` | `CENTER` | `10px` |
| **Toolbar horizontal** | `START` | `CENTER` | `8px` |
| **Footer: Links espaciados** | `SPACE_EVENLY` | `CENTER` | - |
| **Tabs horizontales** | `START` | `STRETCH` | `0` |
| **Card row con gaps** | `START` | `START` | `20px` |

---

## Compatibilidad

✅ **Todos los navegadores modernos** soportan Flexbox
✅ **Responsive**: Funciona automáticamente en móviles
✅ **Backend-driven**: La configuración se define en PHP, se aplica en CSS

---

## Renderizado CSS

El framework automáticamente convierte la configuración backend en CSS:

```php
->justifyContent(JustifyContent::SPACE_BETWEEN)
->alignItems(AlignItems::CENTER)
->gap('20px')
```

Se renderiza como:
```css
.ui-container {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}
```

---

## Referencias

- MDN Web Docs: [Flexbox](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Flexible_Box_Layout)
- CSS Tricks: [Complete Guide to Flexbox](https://css-tricks.com/snippets/css/a-guide-to-flexbox/)
