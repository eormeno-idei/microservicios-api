# Migraciones y Modelos Difexa

## Introducción

En este tutorial aprenderás a crear las migraciones y modelos Eloquent para un sistema de gestión de contenido Difexa. Trabajaremos con entidades principales, tablas pivot y enumeraciones (enums).

## ¿Qué son los Enums?

Los enums (enumeraciones) son tipos de datos que permiten definir un conjunto fijo de valores posibles para un campo. Laravel soporta enums nativos de PHP 8.1+ y también mediante validaciones de base de datos.

## Comandos Artisan Básicos

### Crear Migraciones
```bash
php artisan make:migration create_nombre_tabla_table
```

### Crear Modelos
```bash
php artisan make:model NombreModelo
```

### Crear Modelo con Migración
```bash
php artisan make:model NombreModelo -m
```

### Ejecutar Migraciones
```bash
php artisan migrate
```

## Paso 1: Creación de Enums

Primero, creamos los enums que utilizaremos en nuestros modelos:

```php
<?php

namespace App\Enums;

enum PostType: string
{
    case ARTICLE = 'article';
    case VIDEO = 'video';
    case PODCAST = 'podcast';
    case IMAGE = 'image';
}
```

```php
<?php

namespace App\Enums;

enum MediaType: string
{
    case PHISICAL_SCREEN = 'phisical_screen';
    case SOCIAL_MEDIA = 'social_media';
    case EDITORIAL_PLATFORM = 'editorial_platform';
}
```

```php
<?php

namespace App\Enums;

enum ChannelType: string
{
    case DEPARTMENT = 'department';
    case INSTITUTE = 'institute';
    case SECRETARY = 'secretary';
    case CENTER = 'center';
}
```

```php
<?php

namespace App\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
```
