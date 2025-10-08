# Documentación Técnica - Componentes del Sistema

## Índice de Componentes Técnicos
- [Sistema de CSS](#sistema-de-css)
- [Renderizador de Markdown](#renderizador-de-markdown)
- [Configuración de Temas](#configuración-de-temas)
- [Personalización Avanzada](#personalización-avanzada)

---

## Sistema de CSS

### Arquitectura de Estilos

Los estilos del proyecto están organizados en múltiples archivos para facilitar el mantenimiento y la personalización:

```
public/css/
├── api-client-variables.css          # Variables CSS y configuración
├── api-client.css                    # Estilos principales del cliente
├── api-client-responsive.css         # Media queries y responsividad  
└── documentation-dark-theme.css      # Tema unificado para documentación
```

#### 1. **api-client-variables.css**
**Variables CSS y configuración de temas**
- Define todas las variables CSS para colores, espaciado, tipografía
- Incluye configuración para tema oscuro automático
- Clases de utilidad para métodos HTTP y estados
- Variables para fácil personalización

```css
:root {
    --primary-color: #00d4aa;
    --success-color: #27ae60;
    --error-color: #e74c3c;
    --warning-color: #f39c12;
    --bg-darker: #0f1419;
    --text-light: #ffffff;
}
```

#### 2. **api-client.css**
**Estilos principales**
- Estilos base del componente API Client
- Layout principal con CSS Grid
- Estilos de formularios y botones
- Paneles de request y response
- Syntax highlighting para JSON
- Animaciones y transiciones

#### 3. **api-client-responsive.css**
**Estilos responsivos**
- Media queries para diferentes tamaños de pantalla
- Adaptaciones para tablets (768px)
- Adaptaciones para móviles (480px)
- Adaptaciones para pantallas muy pequeñas (320px)

#### 4. **documentation-dark-theme.css**
**Tema oscuro unificado**
- Tema oscuro unificado con las mismas variables del API Client
- Estilos específicos para vistas de documentación
- Consistencia visual completa entre API Client y documentación
- Syntax highlighting para Markdown
- Estilos para páginas de error
- Responsive design adaptado

### Personalización de Colores

#### Cambiar Paleta Principal
```css
/* En api-client-variables.css o documentation-dark-theme.css */
:root {
    --primary-color: #tu-color-primario;
    --success-color: #tu-color-exito;
    --error-color: #tu-color-error;
    --warning-color: #tu-color-advertencia;
}
```

#### Cambiar Fuentes
```css
:root {
    --font-family-primary: 'Tu-Fuente', -apple-system, sans-serif;
    --font-family-mono: 'Tu-Fuente-Mono', 'Fira Code', monospace;
    --font-size-base: 14px;
    --line-height-base: 1.6;
}
```

#### Cambiar Espaciado
```css
:root {
    --spacing-xs: 4px;
    --spacing-sm: 8px;
    --spacing-md: 16px;
    --spacing-lg: 24px;
    --spacing-xl: 32px;
}
```

---

## Renderizador de Markdown

- **Marked.js v11.1.1**: Renderizador robusto y probado
- **Highlight.js v11.9.0**: Syntax highlighting profesional
- Soporte completo para GitHub Flavored Markdown (GFM)
- Mejor performance y mantenibilidad
- Integración perfecta con el tema oscuro

### Librerías Utilizadas

#### Marked.js
- **Versión**: 11.1.1
- **CDN**: `https://cdn.jsdelivr.net/npm/marked@11.1.1/marked.min.js`
- **Características**:
  - Parsing rápido y eficiente
  - Soporte completo para CommonMark
  - GitHub Flavored Markdown (GFM)
  - Extensible con plugins
  - Renderer personalizable

#### Highlight.js
- **Versión**: 11.9.0
- **CDN JS**: `https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js`
- **CDN CSS**: `https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css`
- **Características**:
  - Soporte para 190+ lenguajes de programación
  - Detección automática de lenguaje
  - Tema oscuro integrado
  - Performance optimizada

### Configuración Técnica

#### Configuración de Marked.js
```javascript
marked.setOptions({
    highlight: function(code, lang) {
        if (lang && hljs.getLanguage(lang)) {
            return hljs.highlight(code, { language: lang }).value;
        }
        return hljs.highlightAuto(code).value;
    },
    langPrefix: 'hljs language-',
    breaks: true,              // Saltos de línea automáticos
    gfm: true,                 // GitHub Flavored Markdown
    headerIds: true,           // IDs automáticos para headers
    mangle: false,             // No modificar IDs
    sanitize: false            // Permitir HTML (controlado)
});
```

#### Renderer Personalizado
```javascript
const renderer = new marked.Renderer();

// Enlaces externos se abren en nueva pestaña
renderer.link = function(href, title, text) {
    const isExternal = href.startsWith('http') && !href.includes(window.location.hostname);
    const target = isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
    const titleAttr = title ? ` title="${title}"` : '';
    return `<a href="${href}"${titleAttr}${target}>${text}</a>`;
};

// Tablas responsive
renderer.table = function(header, body) {
    return `<div class="table-responsive"><table class="markdown-table">
        <thead>${header}</thead>
        <tbody>${body}</tbody>
    </table></div>`;
};

// Código inline con clase personalizada
renderer.codespan = function(text) {
    return `<code class="inline-code">${text}</code>`;
};

// Blockquotes con estilo mejorado
renderer.blockquote = function(quote) {
    return `<blockquote class="markdown-blockquote">${quote}</blockquote>`;
};
```

### Integración con Tema Oscuro

#### CSS Personalizado para Markdown
```css
/* Código inline */
.inline-code {
    background: var(--bg-card);
    color: var(--json-string-color);
    border: 1px solid var(--border-color);
    padding: 2px 6px;
    border-radius: 4px;
    font-family: var(--font-family-mono);
    font-size: 0.9em;
}

/* Bloques de código con Highlight.js */
.hljs {
    background: var(--bg-darker) !important;
    color: var(--text-light) !important;
    border-radius: 8px;
    padding: 16px;
    overflow-x: auto;
}

/* Blockquotes */
.markdown-blockquote {
    border-left: 4px solid var(--primary-color);
    background: var(--bg-card);
    padding: 16px 20px;
    margin: 16px 0;
    border-radius: 0 8px 8px 0;
}

/* Tablas responsive */
.table-responsive {
    overflow-x: auto;
    margin: 16px 0;
}

.markdown-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--bg-card);
    border-radius: 8px;
    overflow: hidden;
}

.markdown-table th,
.markdown-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.markdown-table th {
    background: var(--bg-darker);
    font-weight: 600;
    color: var(--primary-color);
}
```

---

## Configuración de Temas

### Tema Oscuro Unificado

El sistema cuenta con un tema oscuro completamente unificado entre:
- **API Client** (archivos `api-client-*.css`)
- **Documentación** (`documentation-dark-theme.css`)

#### Variables CSS Compartidas
```css
:root {
    /* Colores principales */
    --primary-color: #00d4aa;
    --secondary-color: #1e3a8a;
    --accent-color: #f59e0b;
    
    /* Colores de estado */
    --success-color: #10b981;
    --error-color: #ef4444;
    --warning-color: #f59e0b;
    --info-color: #3b82f6;
    
    /* Colores de fondo */
    --bg-primary: #1a1a1a;
    --bg-secondary: #2d2d2d;
    --bg-card: #1e1e1e;
    --bg-darker: #0f1419;
    
    /* Colores de texto */
    --text-light: #ffffff;
    --text-muted: #9ca3af;
    --text-primary: #f3f4f6;
    
    /* Colores específicos para JSON */
    --json-key-color: #79c0ff;
    --json-string-color: #98d982;
    --json-number-color: #d2a8ff;
    --json-boolean-color: #ffa657;
    
    /* Bordes y sombras */
    --border-color: #374151;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
```

#### Archivos que Usan el Tema Unificado
- `resources/views/api-client.blade.php` → API Client principal
- `resources/views/documentation/markdown.blade.php` → Documentación Markdown
- `resources/views/documentation/not-found.blade.php` → Páginas de error 404

### Características del Tema Unificado
- Misma paleta de colores en toda la aplicación
- Variables CSS compartidas
- Consistencia visual completa
- Syntax highlighting unificado para JSON y Markdown
- Experiencia de usuario coherente

---

## Personalización Avanzada

### Cambiar Esquema de Colores Completo

#### Tema Azul Profesional
```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #1e40af;
    --accent-color: #3b82f6;
    --success-color: #059669;
    --error-color: #dc2626;
    --warning-color: #d97706;
}
```

#### Tema Verde Tecnológico
```css
:root {
    --primary-color: #10b981;
    --secondary-color: #047857;
    --accent-color: #34d399;
    --success-color: #059669;
    --error-color: #dc2626;
    --warning-color: #f59e0b;
}
```

#### Tema Púrpura Moderno
```css
:root {
    --primary-color: #8b5cf6;
    --secondary-color: #7c3aed;
    --accent-color: #a78bfa;
    --success-color: #10b981;
    --error-color: #ef4444;
    --warning-color: #f59e0b;
}
```

### Personalizar Syntax Highlighting

#### Para JSON (API Client)
```css
.json-key { color: var(--json-key-color); }
.json-string { color: var(--json-string-color); }
.json-number { color: var(--json-number-color); }
.json-boolean { color: var(--json-boolean-color); }
.json-null { color: var(--text-muted); }
```

#### Para Código (Markdown)
```css
/* Personalizar colores de Highlight.js */
.hljs-keyword { color: #ff7b72; }
.hljs-string { color: #a5d6ff; }
.hljs-number { color: #79c0ff; }
.hljs-comment { color: #8b949e; }
.hljs-function { color: #d2a8ff; }
```

### Configuración Responsive Personalizada

#### Breakpoints Personalizados
```css
/* Variables de breakpoints */
:root {
    --breakpoint-sm: 576px;
    --breakpoint-md: 768px;
    --breakpoint-lg: 992px;
    --breakpoint-xl: 1200px;
    --breakpoint-xxl: 1400px;
}

/* Media queries personalizadas */
@media (max-width: 768px) {
    .api-client-container {
        grid-template-columns: 1fr;
        grid-template-rows: auto auto;
    }
    
    .request-panel, .response-panel {
        min-height: 400px;
    }
}

@media (max-width: 480px) {
    .method-select {
        font-size: 14px;
        padding: 8px;
    }
    
    .execute-button {
        width: 100%;
        margin-top: 12px;
    }
}
```

### Animaciones y Transiciones Personalizadas

#### Configurar Velocidad de Animaciones
```css
:root {
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
    --transition-slow: 0.5s ease;
    --animation-duration: 0.3s;
}

/* Animaciones personalizadas */
.fade-in {
    animation: fadeIn var(--animation-duration) var(--transition-normal);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.slide-up {
    animation: slideUp var(--animation-duration) var(--transition-normal);
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
```

---

## Optimizaciones de Performance

### CSS
- **Variables CSS**: Reducen redundancia y mejoran mantenimiento
- **Arquitectura modular**: Carga solo los estilos necesarios
- **Media queries eficientes**: Optimizadas para diferentes dispositivos
- **Animaciones con GPU**: Uso de `transform` y `opacity`

### JavaScript (Markdown)
- **CDN optimizado**: Carga desde JSDelivr y Cloudflare
- **Carga diferida**: Scripts se cargan solo cuando se necesitan
- **Cache del navegador**: Headers optimizados para cache
- **Detección de lenguaje**: Highlight.js optimizado para performance

### Recomendaciones Adicionales
1. **Minificación**: Usar herramientas de build para producción
2. **Compresión**: Habilitar gzip/brotli en el servidor
3. **Cache**: Configurar headers de cache para assets estáticos
4. **Lazy loading**: Cargar componentes solo cuando son visibles

---

## Recursos y Referencias

### Documentación Oficial
- [Marked.js Documentation](https://marked.js.org/)
- [Highlight.js Documentation](https://highlightjs.org/)
- [CSS Variables MDN](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)
- [CSS Grid MDN](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Grid_Layout)

### Herramientas Útiles
- [CSS Variables Generator](https://cssvar.com/)
- [Color Palette Generator](https://coolors.co/)
- [Highlight.js Demo](https://highlightjs.org/static/demo/)
- [Markdown Test File](MARKDOWN_TEST.md)

---

**Nota**: Esta documentación técnica se actualiza junto con las mejoras del sistema. Para cambios específicos, consultar el historial de commits o el [índice completo de documentación](DOCUMENTATION_INDEX.md).
