# 🧪 Demo del Sistema de Logs

Este archivo contiene ejemplos prácticos para probar el sistema de logs.

## 📝 Generar Logs de Prueba

### Desde Tinker
```bash
php artisan tinker
```

Luego ejecuta:
```php
Log::info('Información importante del sistema');
Log::debug('Mensaje de depuración');
Log::warning('Advertencia del sistema');
Log::error('Error detectado en el sistema');

// Logs con contexto
Log::info('Usuario autenticado', ['user_id' => 1, 'email' => 'admin@test.com']);
Log::error('Error en base de datos', ['query' => 'SELECT * FROM users', 'error' => 'Connection timeout']);

// Simular varios logs
for ($i = 1; $i <= 10; $i++) {
    Log::info("Log de prueba número {$i}");
}
```

### Desde PHP en línea
```bash
cd /home/eormeno/Escritorio/microservicios-api
php -r "
require 'vendor/autoload.php';
\$app = require_once 'bootstrap/app.php';
\$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

Log::info('✅ Sistema iniciado correctamente');
Log::debug('🔍 Modo debug activado');
Log::warning('⚠️ Memoria al 80%');
Log::error('❌ Fallo en conexión a base de datos');
Log::critical('🚨 Sistema crítico comprometido');
"
```

## 🖥️ Probar Visor Web

### 1. Iniciar el servidor
```bash
php artisan serve
```

### 2. Abrir en el navegador
```
http://localhost:8000/logs
```

### 3. Probar funcionalidades
- ✅ Cambiar archivo de log
- ✅ Ajustar número de líneas (50, 100, 500)
- ✅ Buscar términos específicos ("error", "warning", "info")
- ✅ Activar auto-actualización
- ✅ Descargar log completo
- ✅ Limpiar archivo de log

## 🔧 Probar Script Bash

### Ver logs básico
```bash
./logs.sh
./logs.sh -v 50
./logs.sh -v 100
```

### Seguir logs en tiempo real
```bash
# Terminal 1: Generar logs continuamente
watch -n 1 'php -r "require \"vendor/autoload.php\"; \$app = require_once \"bootstrap/app.php\"; \$app->make(\"Illuminate\\Contracts\\Console\\Kernel\")->bootstrap(); Log::info(\"Log automático: \" . date(\"H:i:s\"));"'

# Terminal 2: Ver logs en tiempo real
./logs.sh -t
```

### Buscar en logs
```bash
./logs.sh -s "error"
./logs.sh -s "warning"
./logs.sh -s "Sistema"
./logs.sh -s "Usuario"
```

### Filtrar por nivel
```bash
./logs.sh -e    # Solo errores
./logs.sh -w    # Solo warnings
```

### Usar Laravel Pail
```bash
./logs.sh -p
# O directamente:
php artisan pail --timeout=0
```

## 📊 Probar Comando Artisan

### Ver logs
```bash
php artisan logs:view
php artisan logs:view --lines=100
php artisan logs:view --lines=500
```

### Seguir logs
```bash
php artisan logs:view --tail
```

### Limpiar logs
```bash
php artisan logs:view --clear
```

### Especificar archivo
```bash
php artisan logs:view --file=laravel.log --lines=100
php artisan logs:view --file=laravel.log --tail
```

## 🎯 Escenarios de Prueba

### Escenario 1: Debugging de Errores
```bash
# 1. Generar error
php artisan tinker
>>> throw new Exception('Error de prueba para debugging');

# 2. Ver el error
./logs.sh -e

# 3. Buscar detalles
./logs.sh -s "Exception"

# 4. Ver en el navegador
# Acceder a http://localhost:8000/logs y buscar "Exception"
```

### Escenario 2: Monitoreo en Tiempo Real
```bash
# Terminal 1: Iniciar servidor
php artisan serve

# Terminal 2: Seguir logs
./logs.sh -t

# Terminal 3: Generar tráfico
curl http://localhost:8000/
curl http://localhost:8000/api/games
```

### Escenario 3: Análisis de Logs Antiguos
```bash
# Ver últimas 500 líneas
./logs.sh -v 500

# Buscar errores específicos
./logs.sh -s "QueryException"
./logs.sh -s "SQLSTATE"

# Descargar para análisis
# Desde el visor web: click en "Descargar"
```

### Escenario 4: Limpieza y Mantenimiento
```bash
# Ver tamaño del archivo
ls -lh storage/logs/laravel.log

# Limpiar log (con confirmación)
./logs.sh -c

# Verificar que se limpió
./logs.sh -v 10
```

## 🔬 Pruebas Avanzadas

### Generar Carga de Logs
```bash
# Script para generar 100 logs
for i in {1..100}; do
    php -r "
    require 'vendor/autoload.php';
    \$app = require_once 'bootstrap/app.php';
    \$app->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();
    Log::info('Log número $i de 100');
    "
    echo "Generado log $i/100"
done
```

### Probar Rendimiento
```bash
# Medir tiempo de carga
time ./logs.sh -v 1000

# Comparar con grep directo
time grep -i "error" storage/logs/laravel.log | tail -n 100
```

### Probar Búsqueda Compleja
```bash
# Buscar múltiples términos
./logs.sh -s "error" | grep -i "database"

# Contar errores
./logs.sh -e | wc -l

# Buscar por fecha
grep "2025-10-21" storage/logs/laravel.log | ./logs.sh -v 50
```

## 📈 Validación de Funcionalidades

### Checklist de Pruebas

#### Visor Web
- [ ] Carga la interfaz correctamente
- [ ] Muestra logs con colores (rojo=error, amarillo=warning, azul=info)
- [ ] Selector de archivos funciona
- [ ] Input de líneas funciona (10-5000)
- [ ] Búsqueda filtra correctamente
- [ ] Auto-actualización funciona cada 5 segundos
- [ ] Botón descargar genera archivo
- [ ] Botón limpiar vacía el log (con confirmación)
- [ ] Scroll automático al final funciona
- [ ] Info bar muestra tamaño y última actualización

#### Script Bash
- [ ] Ayuda (-h) muestra todas las opciones
- [ ] Ver logs (-v) con diferentes cantidades
- [ ] Tail (-t) sigue logs en tiempo real
- [ ] Búsqueda (-s) encuentra términos
- [ ] Filtro de errores (-e) solo muestra errores
- [ ] Filtro de warnings (-w) solo muestra warnings
- [ ] Limpiar (-c) vacía el log con confirmación
- [ ] Pail (-p) usa Laravel Pail
- [ ] Colores funcionan correctamente

#### Comando Artisan
- [ ] logs:view muestra logs
- [ ] --lines cambia cantidad de líneas
- [ ] --tail sigue logs en tiempo real
- [ ] --clear limpia el archivo
- [ ] --file especifica archivo diferente

## 🐛 Casos de Prueba de Error

### Log inexistente
```bash
./logs.sh -f no_existe.log
# Debe mostrar: "Error: Archivo de log no encontrado"
```

### Búsqueda sin resultados
```bash
./logs.sh -s "texto_que_no_existe_123456"
# Debe mostrar resultado vacío sin error
```

### Archivo muy grande
```bash
# Generar archivo grande (>10MB)
# El visor web debe advertir y limitar líneas
```

## 📊 Métricas de Éxito

Un sistema de logs funcional debe:
- ✅ Cargar en < 2 segundos
- ✅ Buscar en < 1 segundo
- ✅ Actualizar en tiempo real sin lag
- ✅ Manejar archivos de hasta 10MB sin problemas
- ✅ No causar errores en el servidor
- ✅ Ser responsive en diferentes dispositivos

---

**¡Prueba todas las funcionalidades y reporta cualquier problema!** 🚀
