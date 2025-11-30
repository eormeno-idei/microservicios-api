# 📚 Documentación del Proyecto

> **Última actualización:** 30 de noviembre de 2025

## 🎯 Índice General

### 🚀 Framework USIM (UI Services Implementation Model)

- **[USIM_ACADEMIC_REPORT.md](USIM_ACADEMIC_REPORT.md)** ⭐ **[PRINCIPAL]**
  - Documentación académica completa del framework USIM
  - Arquitectura, características distintivas y ventajas competitivas
  - Ejemplos completos de servicios reales
  - Comparativas con stack tradicional (Laravel + React)
  - Roadmap con Pest testing y Laravel Reverb
  - **Líneas:** 1,379 | **Tamaño:** 41K

- **[CONTAINER_ALIGNMENT_GUIDE.md](CONTAINER_ALIGNMENT_GUIDE.md)**
  - Guía específica de alineación de contenedores horizontales
  - Configuración de `alignContent` y `alignItems`
  - Ejemplos prácticos con diferentes layouts
  - **Líneas:** 193 | **Tamaño:** 5.2K

---

### 🌐 API REST

- **[API_COMPLETE_DOCUMENTATION.md](API_COMPLETE_DOCUMENTATION.md)**
  - Documentación completa de endpoints REST
  - Estructura de respuestas estandarizadas
  - Autenticación con Sanctum
  - Manejo de archivos y attachments
  - Ejemplos de uso con cliente JavaScript
  - **Líneas:** 1,083 | **Tamaño:** 30K

---

### 🗄️ Base de Datos

- **[DATABASE_SEEDERS_GUIDE.md](DATABASE_SEEDERS_GUIDE.md)**
  - Arquitectura del sistema de seeders
  - Uso de archivos JSON para datos de prueba
  - Relaciones 1:N y N:M
  - Sistema de gestión de contenidos (CMS)
  - **Líneas:** 473 | **Tamaño:** 11K

- **[DATABASE_QUERY_EXAMPLES.md](DATABASE_QUERY_EXAMPLES.md)**
  - Ejemplos prácticos de consultas Eloquent
  - Queries con relaciones (posts, channels, medias)
  - Consultas avanzadas con eager loading
  - **Líneas:** 553 | **Tamaño:** 13K

- **[SEEDERS_IMPLEMENTATION_SUMMARY.md](SEEDERS_IMPLEMENTATION_SUMMARY.md)**
  - Resumen de implementación de seeders
  - Estado y validación de datos
  - Comandos útiles
  - **Líneas:** 430 | **Tamaño:** 11K

- **[SEEDERS_FILES_INVENTORY.md](SEEDERS_FILES_INVENTORY.md)**
  - Inventario completo de archivos creados/modificados
  - Listado de modelos, migrations, seeders y factories
  - **Líneas:** 365 | **Tamaño:** 11K

---

### 📧 Sistema de Emails

- **[EMAIL_CUSTOMIZATION_GUIDE.md](EMAIL_CUSTOMIZATION_GUIDE.md)**
  - Tres métodos de personalización de emails
  - Personalización básica con CSS inline
  - Vistas Blade personalizadas
  - Notificaciones Mailable completas
  - Ejemplos de reset de contraseña y verificación
  - **Líneas:** 271 | **Tamaño:** 7.6K

---

### 📋 Sistema de Logs

- **[LOG_VIEWER.md](LOG_VIEWER.md)**
  - Sistema completo de visualización de logs
  - Interfaz web para consultar logs
  - Filtros por nivel, fecha y contenido
  - Configuración de canales de logging
  - **Líneas:** 278 | **Tamaño:** 6.6K

- **[LOG_VIEWER_DEMO.md](LOG_VIEWER_DEMO.md)**
  - Ejemplos prácticos para generar logs de prueba
  - Testing del sistema de logs
  - Casos de uso comunes
  - **Líneas:** 288 | **Tamaño:** 6.4K

---

### 🚀 Producción y Deployment

- **[PRODUCTION_UPLOAD_FIX.md](PRODUCTION_UPLOAD_FIX.md)**
  - Solución al error 413 en uploads de producción
  - Configuración de PHP-FPM (`upload_max_filesize`, `post_max_size`)
  - Configuración de Nginx (`client_max_body_size`)
  - Comandos de verificación y troubleshooting
  - **Líneas:** 283 | **Tamaño:** 6.2K

---

### 🎨 Componentes Técnicos

- **[TECHNICAL_COMPONENTS_README.md](TECHNICAL_COMPONENTS_README.md)**
  - Sistema de CSS modular
  - Configuración de temas (variables CSS)
  - Renderizador de Markdown
  - Personalización de estilos
  - Arquitectura de archivos CSS
  - **Líneas:** 479 | **Tamaño:** 13K

---

## 📖 Guía de Lectura Recomendada

### Para Nuevos Desarrolladores:
1. **Inicio:** [USIM_ACADEMIC_REPORT.md](USIM_ACADEMIC_REPORT.md) - Entender el framework
2. **Ejemplos:** Revisar ejemplos de ButtonDemoService, ProfileService y ModalDemoService en el report
3. **API REST:** [API_COMPLETE_DOCUMENTATION.md](API_COMPLETE_DOCUMENTATION.md) - Endpoints disponibles
4. **Base de Datos:** [DATABASE_SEEDERS_GUIDE.md](DATABASE_SEEDERS_GUIDE.md) - Estructura de datos

### Para Desarrollo de UI:
1. [USIM_ACADEMIC_REPORT.md](USIM_ACADEMIC_REPORT.md) - Framework completo
2. [CONTAINER_ALIGNMENT_GUIDE.md](CONTAINER_ALIGNMENT_GUIDE.md) - Layouts específicos
3. [TECHNICAL_COMPONENTS_README.md](TECHNICAL_COMPONENTS_README.md) - CSS y estilos

### Para DevOps/Deployment:
1. [PRODUCTION_UPLOAD_FIX.md](PRODUCTION_UPLOAD_FIX.md) - Configuración de uploads
2. [LOG_VIEWER.md](LOG_VIEWER.md) - Monitoreo y debugging

### Para Testing:
1. [DATABASE_QUERY_EXAMPLES.md](DATABASE_QUERY_EXAMPLES.md) - Consultas de ejemplo
2. [LOG_VIEWER_DEMO.md](LOG_VIEWER_DEMO.md) - Generar logs de prueba

---

## 📊 Resumen Estadístico

| Categoría | Documentos | Tamaño Total |
|-----------|------------|--------------|
| **Framework USIM** | 2 | 46K |
| **API REST** | 1 | 30K |
| **Base de Datos** | 4 | 46K |
| **Emails** | 1 | 7.6K |
| **Logs** | 2 | 13K |
| **Producción** | 1 | 6.2K |
| **Componentes** | 1 | 13K |
| **TOTAL** | **12** | **~162K** |

---

## 🗑️ Documentos Eliminados (Obsoletos)

Los siguientes documentos fueron removidos por estar desactualizados o duplicados:

- ❌ `UI_FRAMEWORK_GUIDE.md` - API antigua (reemplazado por USIM_ACADEMIC_REPORT.md)
- ❌ `UPLOADER_COMPONENT_PLAN.md` - Plan ya implementado
- ❌ `pasos.md` - Notas temporales
- ❌ `IMPLEMENTATION_COMPLETE_SUMMARY.md` - Información dispersa y redundante
- ❌ `FILE_UPLOAD_EXAMPLES.md` - Ejemplos de API legacy sin USIM

---

## 🤝 Contribución

Al crear nueva documentación:
- Usar Markdown con sintaxis clara
- Incluir ejemplos de código completos
- Mantener estructura consistente (título, introducción, ejemplos, resumen)
- Agregar entrada en este README.md

---

**Preparado por:** Equipo de Desarrollo IDEI  
**Última revisión:** 30 de noviembre de 2025  
**Versión del Framework:** USIM 1.0
