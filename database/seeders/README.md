# 🌱 Database Seeders

Este directorio contiene todos los seeders para poblar la base de datos con datos de prueba realistas.

## 🚀 Uso Rápido

```bash
# Ejecutar todos los seeders
php artisan db:seed

# Ver estadísticas de la base de datos
php artisan db:stats

# Ver estadísticas detalladas
php artisan db:stats --detailed

# Refrescar base de datos y ejecutar seeders (⚠️ BORRA TODO)
php artisan migrate:fresh --seed
```

## 📦 Seeders Disponibles

| Seeder | Registros | Descripción |
|--------|-----------|-------------|
| `DatabaseSeeder` | - | Orquestador principal |
| `ChannelSeeder` | 13 | Canales organizacionales |
| `MediaSeeder` | 12 | Medios de distribución |
| `PostSeeder` | 11 | Posts de diferentes tipos |
| `AttachmentSeeder` | ~20 | Archivos adjuntos |
| `UserChannelSeeder` | ~40+ | Relaciones usuario-canal |

## ✅ Características

- **Prevención de duplicados:** Usa `firstOrCreate()` para evitar errores
- **Datos realistas:** Contenido contextualizado y coherente
- **Relaciones completas:** Todas las relaciones N:M establecidas
- **Idempotente:** Se puede ejecutar múltiples veces sin problemas
- **Mensajes informativos:** Feedback claro durante la ejecución

## 📊 Datos Generados

Después de ejecutar `php artisan db:seed`:

- **11 Usuarios** (1 admin + 10 regulares)
- **13 Canales** (departamentos, institutos, secretarías, centros)
- **12 Medios** (pantallas físicas, redes sociales, plataformas editoriales)
- **11 Posts** (varios tipos y estados)
- **20 Attachments** (según tipo de post)
- **40+ Relaciones** entre usuarios y canales

## 📚 Documentación Completa

Para información detallada, consulta:

- **[Guía Completa de Seeders](../../docs/DATABASE_SEEDERS_GUIDE.md)** - 400+ líneas de documentación
- **[Ejemplos de Consultas](../../docs/DATABASE_QUERY_EXAMPLES.md)** - Cómo consultar los datos
- **[Resumen de Implementación](../../docs/SEEDERS_IMPLEMENTATION_SUMMARY.md)** - Overview técnico

## 🔧 Personalización

Para personalizar los datos generados, edita los archivos de seeders:

```php
// Ejemplo: Agregar un nuevo canal en ChannelSeeder.php
[
    'name' => 'Tu Nuevo Canal',
    'description' => 'Descripción del canal',
    'type' => ChannelType::DEPARTMENT->value,
    'semantic_context' => 'Contexto para IA',
],
```

## 💡 Tips

- Ejecuta `php artisan db:stats` para ver un resumen de los datos
- Los seeders verifican duplicados automáticamente
- El usuario admin se crea desde variables de entorno (.env)
- Todos los medios generados están activos por defecto

## 🎯 Orden de Ejecución

```
DatabaseSeeder
├── Roles & Users
├── ChannelSeeder
├── MediaSeeder
├── PostSeeder (usa Users, Channels, Medias)
├── AttachmentSeeder (usa Posts)
└── UserChannelSeeder (usa Users, Channels)
```

---

**Autor:** Sistema de Gestión de Contenidos  
**Última actualización:** Octubre 2025
