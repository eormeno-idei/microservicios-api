# Configuración de Uploads en Producción

## Problema Común: Error 413 (Request Entity Too Large)

Al intentar subir archivos en producción, puedes encontrar el error:
```
POST /api/upload/temporary 413 (Request Entity Too Large)
```

Esto ocurre porque los límites de tamaño configurados en el servidor son menores que el archivo que intentas subir.

## Diagnóstico Rápido

Ejecuta estos comandos para verificar la configuración actual:

```bash
# Ver límites de PHP
php -i | grep -E 'upload_max_filesize|post_max_size|memory_limit'

# Ver límites de Nginx
sudo nginx -T 2>/dev/null | grep client_max_body_size

# Ver versión de PHP (para saber qué php.ini editar)
php -v
```

**Valores esperados para nuestra aplicación:**
- `upload_max_filesize`: mínimo 10M
- `post_max_size`: mínimo 12M (siempre mayor que upload_max_filesize)
- `client_max_body_size`: mínimo 10M

## Solución

### Paso 1: Configurar PHP-FPM

```bash
# Editar configuración de PHP-FPM (NO el CLI)
# Ajusta la versión según tu servidor (8.3, 8.2, etc.)
sudo nano /etc/php/8.3/fpm/php.ini
```

Busca y modifica estas líneas:
```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
memory_limit = 256M
```

**Nota:** `post_max_size` debe ser mayor que `upload_max_filesize` porque incluye otros datos del POST además del archivo.

### Paso 2: Configurar Nginx

```bash
# Editar configuración del sitio
sudo nano /etc/nginx/sites-available/damogame.com
```

Agrega dentro del bloque `server`:
```nginx
server {
    server_name damogame.com www.damogame.com;
    root /var/www/microservicios-api/public;
    
    # Límite de tamaño del body (uploads)
    client_max_body_size 10M;
    
    # Timeouts para uploads grandes (opcional)
    client_body_timeout 300s;
    
    # ... resto de la configuración
}
```

### Paso 3: Verificar Sintaxis

```bash
# Verificar que la configuración de Nginx es correcta
sudo nginx -t
```

Debes ver:
```
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### Paso 4: Reiniciar Servicios

```bash
# Reiniciar PHP-FPM (ajusta la versión)
sudo systemctl restart php8.3-fpm

# Reiniciar Nginx
sudo systemctl restart nginx
```

### Paso 5: Verificar Cambios

```bash
# Verificar PHP-FPM
php-fpm8.3 -i 2>/dev/null | grep -E 'upload_max_filesize|post_max_size'

# Verificar Nginx
sudo nginx -T 2>/dev/null | grep client_max_body_size
```

Debes ver:
```
upload_max_filesize => 10M => 10M
post_max_size => 12M => 12M
client_max_body_size 10M;
```

## Probar el Upload

### Desde el Navegador
1. Accede a https://damogame.com/profile
2. Intenta subir una imagen de ~1-2MB
3. Verifica en la consola del navegador que no haya errores 413

### Desde la Línea de Comandos
```bash
# Crear archivo de prueba de 1MB
dd if=/dev/zero of=test-1mb.jpg bs=1M count=1

# Probar upload
curl -X POST -F "file=@test-1mb.jpg" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  https://damogame.com/api/upload/temporary

# Limpiar
rm test-1mb.jpg
```

## Valores Recomendados por Tipo de Aplicación

### Aplicación con imágenes de perfil (nuestro caso)
```ini
# PHP
upload_max_filesize = 10M
post_max_size = 12M
```
```nginx
# Nginx
client_max_body_size 10M;
```

### Aplicación con documentos grandes
```ini
# PHP
upload_max_filesize = 50M
post_max_size = 52M
```
```nginx
# Nginx
client_max_body_size 50M;
```

### Aplicación con videos
```ini
# PHP
upload_max_filesize = 200M
post_max_size = 210M
max_execution_time = 600
```
```nginx
# Nginx
client_max_body_size 200M;
client_body_timeout 600s;
```

## Troubleshooting

### El error persiste después de los cambios

1. **Verificar que editaste el php.ini correcto:**
```bash
# Ver cuál php.ini está usando PHP-FPM
php-fpm8.3 -i | grep "Loaded Configuration File"
```

2. **Verificar que los servicios se reiniciaron:**
```bash
sudo systemctl status php8.3-fpm
sudo systemctl status nginx
```

3. **Limpiar caché del navegador:**
- Presiona Ctrl+Shift+R (o Cmd+Shift+R en Mac)
- O abre en modo incógnito

4. **Revisar logs:**
```bash
# Nginx error log
sudo tail -f /var/log/nginx/error.log

# PHP-FPM error log
sudo tail -f /var/log/php8.3-fpm.log

# Laravel log
tail -f /var/www/microservicios-api/storage/logs/laravel.log
```

### Error: "502 Bad Gateway" después de los cambios

```bash
# Verificar que PHP-FPM está corriendo
sudo systemctl status php8.3-fpm

# Si está parado, iniciarlo
sudo systemctl start php8.3-fpm
```

### Error: "File too large" desde Laravel

Verifica el límite en tu código (`UploaderBuilder`):
```php
// En ProfileService.php
$uploaderProfile = UIBuilder::uploader('uploader_profile')
    ->maxSize(2)  // ← Este valor debe ser menor que el límite del servidor
    ->aspect('1:1');
```

## Checklist de Deployment

Cada vez que configures un servidor nuevo:

- [ ] Configurar `upload_max_filesize` en `/etc/php/X.X/fpm/php.ini`
- [ ] Configurar `post_max_size` en `/etc/php/X.X/fpm/php.ini`
- [ ] Configurar `client_max_body_size` en `/etc/nginx/sites-available/SITIO`
- [ ] Ejecutar `sudo nginx -t` para verificar sintaxis
- [ ] Reiniciar PHP-FPM: `sudo systemctl restart phpX.X-fpm`
- [ ] Reiniciar Nginx: `sudo systemctl restart nginx`
- [ ] Probar upload desde el navegador
- [ ] Verificar logs: `sudo tail -f /var/log/nginx/error.log`

## Seguridad

**Importante:** No configures valores excesivamente altos sin necesidad:

❌ **Mal:**
```ini
upload_max_filesize = 1000M  # Permite archivos de 1GB
```

✅ **Bien:**
```ini
upload_max_filesize = 10M    # Suficiente para imágenes de perfil
```

**Razones:**
- Previene ataques de denegación de servicio (DoS)
- Reduce uso de memoria del servidor
- Mejora experiencia de usuario (uploads más rápidos)

## Comandos Útiles

```bash
# Ver todos los límites de PHP
php -i | grep -i max

# Ver configuración completa de Nginx
sudo nginx -T

# Recargar Nginx sin downtime
sudo nginx -s reload

# Ver procesos de PHP-FPM
ps aux | grep php-fpm

# Ver uso de memoria
free -h

# Ver espacio en disco
df -h
```

## Referencias

- [PHP: Runtime Configuration](https://www.php.net/manual/en/ini.core.php)
- [Nginx: client_max_body_size](http://nginx.org/en/docs/http/ngx_http_core_module.html#client_max_body_size)
- [Laravel: File Uploads](https://laravel.com/docs/11.x/requests#files)

