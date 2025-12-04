# Especificación Técnica del Protocolo USIM (UI Services Implementation Model)

**Versión:** 1.0
**Tipo de Documento:** Especificación de Protocolo de Interfaz
**Ámbito:** Arquitectura Orientada a Servicios / Server-Driven UI

## 1. Introducción
El presente documento define formalmente la estructura y semántica del protocolo de comunicación utilizado en el framework USIM. Este protocolo implementa un patrón de arquitectura de **Interfaz de Usuario Dirigida por el Servidor** (*Server-Driven UI*), donde la lógica de presentación, el estado de los componentes y la jerarquía visual son determinados por el backend y transmitidos al cliente mediante mensajes serializados en formato JSON.

El objetivo principal es desacoplar la definición de la vista del motor de renderizado, permitiendo la actualización dinámica de la interfaz sin requerir cambios en el código fuente del cliente.

## 2. Arquitectura de Comunicación
La comunicación se establece mediante el intercambio de mensajes asíncronos donde el servidor actúa como la fuente de la verdad para el estado de la UI.

### 2.1 Estructura de Datos
El cuerpo del mensaje (*Payload*) se estructura como un **Diccionario de Componentes**. Cada clave en este diccionario representa un identificador único de componente ($ID$), y su valor asociado describe las propiedades, tipo y ubicación de dicho componente.

El cliente debe operar bajo un modelo de **Renderizado Reactivo**:
1.  Mantiene un árbol de componentes en memoria (Virtual DOM o State Tree).
2.  Recibe "deltas" o cambios desde el servidor.
3.  Aplica los cambios al árbol local y repinta la interfaz.

## 3. Sintaxis Formal (EBNF)
Para garantizar la independencia de la implementación, se define la sintaxis del protocolo utilizando la **Forma de Backus-Naur Extendida (EBNF)**. Esta gramática describe las reglas de formación de mensajes válidos.

```ebnf
(* Estructura del Mensaje *)
ProtocolMessage ::= "{" [ ComponentEntryList ] "}"
ComponentEntryList ::= ComponentEntry { "," ComponentEntry }

(* Definición de una Entrada de Componente *)
ComponentEntry  ::= ComponentID ":" ComponentBody

(* Identificadores *)
ComponentID     ::= IdentifierString
IdentifierString ::= Letter { Letter | Digit | "_" }

(* Cuerpo y Atributos *)
ComponentBody   ::= "{" AttributeList "}"
AttributeList   ::= Attribute { "," Attribute }
Attribute       ::= TypeAttr | ParentAttr | GenericAttr

(* Atributos Reservados *)
TypeAttr        ::= '"type"' ":" '"' ComponentType '"'
ParentAttr      ::= '"parent"' ":" ParentValue

(* Valores de Atributos *)
ComponentType   ::= "container" | "button" | "input" | "dropdown" | "select" | CustomType
ParentValue     ::= '"' ComponentID '"' | '"' AnchorKeyword '"' | "null"
GenericAttr     ::= StringLiteral ":" ValueLiteral

(* Palabras Clave de Anclaje (Anchors) *)
AnchorKeyword   ::= "main" | "menu" | "modal"

(* Primitivas *)
CustomType      ::= StringLiteral
StringLiteral   ::= '"' { Character } '"'
ValueLiteral    ::= StringLiteral | Number | Boolean
```

## 4. Semántica Operacional

### 4.1 Ciclo de Vida del Componente
El cliente procesará el mensaje iterando sobre cada `ComponentID`. La operación a realizar se infiere de la existencia previa del componente en el registro del cliente ($C$) y de los atributos recibidos.

1.  **Instanciación ($ID \notin C$):**
    *   Requiere obligatoriamente los atributos `type` y `parent`.
    *   El cliente crea la instancia y la anexa al nodo especificado en `parent`.

2.  **Mutación ($ID \in C$):**
    *   Ocurre cuando se recibe un ID que ya existe en memoria.
    *   Se actualizan únicamente los atributos presentes en el mensaje (fusión de propiedades).
    *   No es necesario reenviar `type` o `parent` a menos que estos cambien.

3.  **Eliminación (Destrucción):**
    *   Se activa explícitamente cuando el atributo `parent` tiene el valor `null`.
    *   **Efecto en Cascada:** La eliminación de un nodo implica la eliminación recursiva de todos sus nodos descendientes en el árbol de la UI.

### 4.2 Sistema de Anclajes (Anchors)
El atributo `parent` define la topología del árbol. Existen nodos raíz virtuales predefinidos por el framework, denominados **Anclajes**:

*   `main`: Contenedor principal de la vista (Screen).
*   `menu`: Contenedor para navegación lateral o persistente.
*   `modal`: Capa superior para diálogos emergentes (Z-index superior).

### 4.3 Reglas de Identificadores
*   **Determinismo:** El backend debe asegurar que, para un mismo estado lógico, se generen siempre los mismos `COMPONENT_ID`.
*   **Nomenclatura:** Los IDs definidos manualmente deben seguir la convención de nombres de variables (alfanuméricos, sin espacios).
*   **Unicidad:** El ID debe ser único dentro del contexto de la sesión de usuario actual.

### 4.4 Generación de Identificadores Determinísticos

En la práctica, la mayoría de los identificadores de componentes son generados automáticamente por el sistema, no definidos manualmente. El framework utiliza un **generador de IDs centralizado** (`UIIdGenerator`) que garantiza unicidad y determinismo sin depender de la base de datos o el estado de sesión.

#### 4.4.1 Mecanismo de Generación

El sistema combina dos estrategias para generar IDs numéricos únicos:

**1. Offsets por Contexto (Context-Based Offsets)**

Cada servicio o contexto de UI obtiene un "espacio de numeración" propio basado en su nombre de clase. Por ejemplo:
- `App\Services\UI\AuthService` podría obtener el offset `50000000`
- `App\Services\UI\DashboardService` podría obtener el offset `120000000`
- `App\Services\UI\FormsService` podría obtener el offset `230000000`

Estos offsets se calculan usando un hash CRC32 del nombre completo de la clase, escalado en múltiplos de 10,000. Esto significa que cada servicio tiene un "rango" de 10,000 IDs únicos disponibles (ejemplo: desde 50000000 hasta 50009999).

**2. IDs Locales Auto-incrementales o Determinísticos**

Dentro de cada contexto, se pueden generar IDs de dos formas:

- **Auto-incremental:** Para componentes dinámicos sin nombre específico (listas, tablas, elementos repetitivos), se usa un contador secuencial que se reinicia por request.
  ```
  Offset + AutoIncrement = ID Final
  50000000 + 1 = 50000001
  50000000 + 2 = 50000002
  ```

- **Basado en Nombre:** Para componentes con identificador semántico (botones principales, contenedores importantes), se genera un hash del nombre del componente.
  ```
  Offset + hash("submit_button") = 50006234
  Offset + hash("cancel_button") = 50001892
  ```

#### 4.4.2 Garantías del Sistema

- **Unicidad Global:** El offset por contexto evita colisiones entre servicios diferentes.
- **Determinismo:** Para un mismo nombre de componente en el mismo contexto, siempre se genera el mismo ID.
- **Trazabilidad:** Dado un ID numérico, es posible determinar qué servicio lo generó mediante reverse lookup del offset.
- **Sin Estado Persistente:** Los IDs se calculan en tiempo de ejecución sin consultar base de datos, lo que garantiza alto rendimiento.

#### 4.4.3 Ejemplo Conceptual

```php
// Componente con nombre explícito (minoría de casos)
UIIdGenerator::generateFromName('App\Services\UI\AuthService', 'login_button');
// Resultado: 50006234 (siempre el mismo)

// Componente dinámico sin nombre (mayoría de casos)
UIIdGenerator::generate('App\Services\UI\DashboardService');
// Primera llamada: 120000001
// Segunda llamada: 120000002
// Tercera llamada: 120000003
```

En la práctica, **la mayoría de los componentes** (elementos de listas, cards dinámicas, widgets generados en bucles) utilizan la generación auto-incremental sin nombre, ya que no requieren un identificador semántico estable. Solo los componentes críticos de la interfaz (botones de acción principales, contenedores raíz, modales importantes) utilizan IDs basados en nombre para facilitar el debugging y las referencias cruzadas.

## 5. Ejemplos de Implementación

A continuación se ilustra la aplicación de la sintaxis abstracta mediante objetos JSON concretos para los distintos estados del ciclo de vida. Los ejemplos utilizan **IDs numéricos generados automáticamente**, que representan el caso mayoritario en producción.

### 5.1 Renderizado Inicial (Instanciación)
El servidor envía la estructura de un formulario de Login. Nótese que solo el contenedor raíz tiene un ID nominal para facilitar referencias; el resto usa IDs generados automáticamente.

```json
{
  "50001234": {
    "type": "container",
    "parent": "main",
    "style": "flex_column",
    "padding": "20"
  },
  "50001235": {
    "type": "input",
    "parent": "50001234",
    "placeholder": "Correo electrónico",
    "inputType": "email"
  },
  "50001236": {
    "type": "input",
    "parent": "50001234",
    "placeholder": "Contraseña",
    "inputType": "password"
  },
  "50006789": {
    "type": "button",
    "parent": "50001234",
    "text": "Iniciar Sesión",
    "action": "auth_login",
    "variant": "primary"
  },
  "50001237": {
    "type": "button",
    "parent": "50001234",
    "text": "¿Olvidaste tu contraseña?",
    "action": "auth_recover",
    "variant": "link"
  }
}
```

**Nota:** El ID `50006789` corresponde al botón principal generado con `generateFromName('submit_button')` para mantener estabilidad. Los demás componentes (`50001235`, `50001236`, `50001237`) usan auto-incremento, ya que son elementos internos sin necesidad de referencia explícita.

### 5.2 Actualización de Estado (Mutación)
El usuario presiona el botón de login. El servidor responde con un delta que actualiza solo los componentes afectados.

```json
{
  "50006789": {
    "text": "Validando...",
    "disabled": true,
    "loading": true
  },
  "50001235": {
    "enabled": false
  },
  "50001236": {
    "enabled": false
  }
}
```

### 5.3 Renderizado de Lista Dinámica
El servidor responde con una lista de notificaciones. Cada item de la lista usa IDs auto-generados únicos.

```json
{
  "120004001": {
    "type": "container",
    "parent": "main",
    "orientation": "vertical"
  },
  "120004002": {
    "type": "card",
    "parent": "120004001",
    "title": "Nueva solicitud",
    "subtitle": "Hace 5 minutos",
    "icon": "bell"
  },
  "120004003": {
    "type": "card",
    "parent": "120004001",
    "title": "Documento aprobado",
    "subtitle": "Hace 2 horas",
    "icon": "check"
  },
  "120004004": {
    "type": "card",
    "parent": "120004001",
    "title": "Recordatorio",
    "subtitle": "Hace 1 día",
    "icon": "clock"
  }
}
```

**Nota:** Cada card recibe un ID secuencial generado automáticamente. No hay necesidad de IDs semánticos porque estos elementos son transitorios y se regeneran en cada request.

### 5.4 Limpieza de Interfaz (Eliminación)
Tras un login exitoso, el servidor ordena destruir el formulario completo. Al eliminar el contenedor padre, todos los hijos son eliminados en cascada.

```json
{
  "50001234": {
    "parent": null
  }
}
```

**Nota:** Solo se necesita eliminar el contenedor raíz (`50001234`). Los componentes `50001235`, `50001236`, `50006789` y `50001237` se destruyen automáticamente por la regla de cascada del árbol DOM.

## 6. Comunicación Frontend → Backend (Event Payload)

Cuando el usuario interactúa con un componente UI (click, change, input, etc.), el frontend envía una solicitud HTTP POST al backend con información sobre el evento. Esta sección describe la estructura del payload de eventos.

### 6.1 Estructura del Request

**Endpoint:** `POST /api/ui-event`

**Headers requeridos:**
```http
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: {token}
X-Requested-With: XMLHttpRequest
X-USIM-Storage: {encrypted_storage}
```

**Body (JSON):**
```json
{
  "component_id": 50006789,
  "event": "click",
  "action": "submit_form",
  "parameters": {
    "email": "user@example.com",
    "password": "********",
    "remember_me": true
  }
}
```

### 6.2 Descripción de Campos

#### 6.2.1 `component_id` (integer, required)
Identificador numérico único del componente que generó el evento. Este ID permite al backend:
1. Determinar qué servicio debe procesar el evento (mediante reverse lookup del offset)
2. Identificar el componente específico que emitió la acción

**Ejemplo:** `50006789` → Servicio: `App\Services\UI\AuthService`

#### 6.2.2 `event` (string, required)
Tipo de evento del DOM que activó la acción. Valores comunes:
- `"click"` - Click en botones, enlaces, cards clickeables
- `"change"` - Cambio en selects, checkboxes, radio buttons
- `"input"` - Escritura en inputs (con debounce)
- `"submit"` - Envío de formularios
- `"keypress"` - Tecla presionada (ej: Enter en inputs)

**Nota:** Este campo es principalmente informativo; el backend toma decisiones basándose en `action`, no en `event`.

#### 6.2.3 `action` (string, required)
Nombre de la acción a ejecutar en el backend, en formato **snake_case**. El backend convierte automáticamente este nombre al método correspondiente.

**Convención de naming:**
```
Frontend (action)    →    Backend (método)
─────────────────────────────────────────
"submit_form"        →    onSubmitForm()
"delete_user"        →    onDeleteUser()
"open_settings"      →    onOpenSettings()
"update_profile"     →    onUpdateProfile()
```

#### 6.2.4 `parameters` (object, optional)
Objeto con datos adicionales del evento. Puede incluir:

**a) Valores de formularios (auto-recolectados):**

Los botones recolectan automáticamente valores de todos los inputs en su contexto (mismo contenedor o modal):

```json
{
  "email": "user@example.com",
  "password": "secretpass",
  "remember_me": true,
  "age": 25
}
```

**b) Parámetros explícitos (definidos en el componente):**

```json
{
  "user_id": 123,
  "resource_type": "document",
  "delete_permanent": false
}
```

**c) Parámetros internos del sistema:**

- `_caller_service_id`: ID del servicio que abrió un modal (para callbacks)

```json
{
  "_caller_service_id": 120000001,
  "confirmed": true
}
```

**Prioridad:** Los parámetros explícitos sobrescriben los valores auto-recolectados si hay conflicto.

### 6.3 Header `X-USIM-Storage`

Este header transporta variables de estado persistente encriptadas que el backend necesita mantener entre requests. El contenido es opaco para el cliente (string encriptado).

**Flujo:**
1. Backend genera variables `store_*` y las serializa/encripta
2. Frontend las almacena en `localStorage` bajo la clave `'usim'`
3. Frontend las reenvía en cada request mediante el header `X-USIM-Storage`
4. Backend las desencripta e inyecta en las propiedades `protected store_*` del servicio

**Ejemplo de variables de estado en el backend:**
```php
protected int $store_user_id;
protected string $store_session_token;
protected array $store_selected_filters;
```

### 6.4 Ejemplo Completo de Request

**Escenario:** Usuario hace click en "Eliminar Usuario" en una tabla de administración.

```http
POST /api/ui-event HTTP/1.1
Host: example.com
Content-Type: application/json
X-CSRF-TOKEN: abc123token
X-USIM-Storage: eyJpdiI6Ik...encrypted_data...

{
  "component_id": 230004521,
  "event": "click",
  "action": "delete_user",
  "parameters": {
    "user_id": 42,
    "confirm": true
  }
}
```

**Procesamiento en el backend:**

1. **Resolución del servicio:**
   ```php
   UIIdGenerator::getContextFromId(230004521)
   // → "App\Services\UI\UserManagementService"
   ```

2. **Conversión de acción a método:**
   ```php
   "delete_user" → "onDeleteUser"
   ```

3. **Invocación del método:**
   ```php
   $service = app('App\Services\UI\UserManagementService');
   $service->initializeEventContext($decryptedStorage);
   $service->onDeleteUser(['user_id' => 42, 'confirm' => true]);
   $service->finalizeEventContext();
   ```

4. **Respuesta al frontend:**
   ```json
   {
     "toast": {
       "message": "Usuario eliminado correctamente",
       "type": "success"
     },
     "230004520": {
       "parent": null
     }
   }
   ```

### 6.5 Validación del Request

El backend valida automáticamente:
- `component_id`: Debe ser un entero válido
- `event`: Debe ser una cadena no vacía
- `action`: Debe ser una cadena no vacía
- `parameters`: Debe ser un objeto (si está presente)

**Respuestas de error:**

**404 - Servicio no encontrado:**
```json
{
  "error": "Service not found for this component"
}
```

**404 - Acción no implementada:**
```json
{
  "error": "Action 'invalid_action' not implemented"
}
```

**500 - Error interno:**
```json
{
  "error": "Internal server error",
  "message": "Detailed error (solo en modo debug)"
}
```

## 7. Acciones del Sistema (System Actions)

Además de la manipulación de componentes UI, el protocolo contempla un conjunto de **acciones del sistema** que el backend puede enviar al frontend para controlar aspectos de la aplicación que trascienden el árbol de componentes. Estas acciones se incluyen en el mismo mensaje JSON de respuesta, pero operan a nivel de aplicación en lugar de nivel de componente.

### 7.1 Catálogo de Acciones

#### 7.1.1 `close_modal`
Cierra el modal activo en el frontend.

```json
{
  "action": "close_modal"
}
```

**Uso típico:** Después de que el usuario completa una acción en un diálogo modal (guardar, cancelar, confirmar), el servidor envía esta acción para cerrar el modal automáticamente.

**Ejemplo integrado:**
```json
{
  "action": "close_modal",
  "50001234": {
    "text": "Guardado exitosamente"
  }
}
```

#### 7.1.2 `redirect`
Ordena al frontend realizar una redirección a una URL específica.

```json
{
  "redirect": "/dashboard"
}
```

**Uso típico:** Después de un login exitoso, registro completado, o cuando se requiere navegar a otra vista de la aplicación.

**Ejemplo con URL absoluta:**
```json
{
  "redirect": "https://external-service.com/callback"
}
```

**Nota:** Si el backend envía `redirect: null`, el frontend utilizará la URL previa guardada (patrón "intended redirect" de Laravel).

#### 7.1.3 `toast`
Muestra una notificación tipo "toast" (mensaje temporal flotante) al usuario.

```json
{
  "toast": {
    "message": "Datos guardados correctamente",
    "type": "success",
    "duration": 5000,
    "position": "top-right"
  }
}
```

**Parámetros:**
- `message` (string): Texto del mensaje
- `type` (string): Tipo de notificación - `"info"`, `"success"`, `"warning"`, `"error"`
- `duration` (int): Duración en milisegundos (por defecto: 5000)
- `position` (string): Posición en pantalla - `"top-right"`, `"top-left"`, `"bottom-right"`, `"bottom-left"`, `"top-center"`, `"bottom-center"`
- `open_effect` (string): Efecto de apertura - `"fade"`, `"slide"`, `"zoom"`
- `show_effect` (string): Efecto de visualización - `"bounce"`, `"pulse"`, `"shake"`
- `close_effect` (string): Efecto de cierre - `"fade"`, `"slide"`

**Uso típico:** Confirmar acciones del usuario sin interrumpir el flujo de trabajo (guardados, eliminaciones, validaciones exitosas).

**Ejemplo de error:**
```json
{
  "toast": {
    "message": "No se pudo conectar con el servidor",
    "type": "error",
    "duration": 8000,
    "position": "top-center",
    "show_effect": "shake"
  }
}
```

#### 7.1.4 `update_modal`
Actualiza dinámicamente el contenido de un modal ya abierto sin cerrarlo.

```json
{
  "update_modal": {
    "120005001": {
      "type": "label",
      "parent": "modal",
      "text": "Procesando paso 2 de 3..."
    }
  }
}
```

**Uso típico:** Para wizards multi-paso, barras de progreso, o cuando el usuario debe permanecer en el mismo modal mientras cambia el contenido (por ejemplo, validación de formularios en tiempo real).

**Ejemplo de wizard:**
```json
{
  "update_modal": {
    "modal_title": {
      "text": "Paso 2: Información de contacto"
    },
    "modal_content": {
      "parent": null
    },
    "new_modal_content": {
      "type": "container",
      "parent": "modal",
      "orientation": "vertical"
    }
  }
}
```

### 7.2 Composición de Respuestas

El servidor puede combinar múltiples acciones y cambios de componentes en una sola respuesta. El frontend procesa las acciones y los cambios de componentes en el orden apropiado.

**Ejemplo de flujo completo:**
```json
{
  "toast": {
    "message": "Sesión iniciada correctamente",
    "type": "success",
    "duration": 3000
  },
  "redirect": "/dashboard",
  "50001234": {
    "parent": null
  }
}
```

Este ejemplo:
1. Muestra un toast de éxito
2. Elimina el formulario de login
3. Redirige al dashboard

### 7.3 Orden de Procesamiento

El frontend debe procesar las acciones en el siguiente orden para garantizar una experiencia de usuario coherente:

1. **Actualización de componentes:** Todos los cambios en el árbol de componentes (creación, modificación, eliminación)
2. **Toast:** Mostrar notificaciones
3. **Update Modal:** Actualizar contenido de modales
4. **Close Modal:** Cerrar modales
5. **Redirect:** Redirecciones (siempre al final)

**Justificación:** Las redirecciones deben ser la última acción porque invalidan todo el estado de la aplicación actual. Los toasts deben mostrarse antes de cerrar modales para que el usuario pueda verlos.
