# Neo ERP - Docker Configuration

## Storage and File Upload Handling

### Cómo funciona el storage en Docker

El sistema está configurado para manejar correctamente los archivos subidos (logos, documentos, etc.) en un entorno Docker:

#### 1. **Estructura de Directorios**

```
storage/
├── app/
│   └── public/           # Archivos públicamente accesibles
│       ├── logos/        # Logos de empresa
│       └── documents/    # Documentos generados
├── framework/
│   ├── cache/
│   ├── sessions/
│   └── views/
└── logs/
```

#### 2. **Enlace Simbólico**

El script `entrypoint.sh` automáticamente:
- Crea el enlace simbólico `public/storage -> storage/app/public`
- Asegura que todos los directorios existen
- Establece permisos correctos (775)
- Asigna ownership a `www-data:www-data`

#### 3. **Nginx Configuration**

Nginx está configurado para servir archivos storage de dos formas:

**Opción A: Via Symlink** (método tradicional de Laravel)
```
/public/storage/logos/imagen.png -> storage/app/public/logos/imagen.png
```

**Opción B: Via Alias** (configuración directa en nginx)
```nginx
location /storage/ {
    alias /var/www/html/storage/app/public/;
}
```

Esto garantiza que incluso si el symlink falla, nginx puede servir los archivos directamente.

#### 4. **Permisos**

Los permisos se establecen en múltiples etapas:

1. **Dockerfile**: Permisos básicos (775) durante la construcción
2. **entrypoint.sh**: Permisos dinámicos al iniciar el contenedor
3. **Usuario**: `www-data:www-data` (usuario web estándar en Alpine/Debian)

### Persistencia de Datos

Para **persistir** los archivos subidos entre reinicios del contenedor, usa volúmenes Docker:

#### docker-compose.yml
```yaml
services:
  app:
    image: neo-erp:latest
    volumes:
      - storage-data:/var/www/html/storage/app/public
      - logs-data:/var/www/html/storage/logs

volumes:
  storage-data:
    driver: local
  logs-data:
    driver: local
```

#### Docker Run
```bash
docker run -d \
  -v neo-erp-storage:/var/www/html/storage/app/public \
  -v neo-erp-logs:/var/www/html/storage/logs \
  neo-erp:latest
```

### Troubleshooting

#### Error 403 Forbidden en archivos storage

**Causa**: Permisos incorrectos o symlink no creado

**Solución**:
```bash
# Entrar al contenedor
docker exec -it neo-erp-app sh

# Verificar symlink
ls -la /var/www/html/public/storage

# Recrear symlink si es necesario
php artisan storage:link

# Verificar permisos
ls -la /var/www/html/storage/app/public
chmod -R 775 /var/www/html/storage
chown -R www-data:www-data /var/www/html/storage
```

#### Los archivos no persisten después de reiniciar

**Causa**: No hay volumen configurado

**Solución**: Agregar volúmenes Docker como se muestra arriba

#### El logo no aparece en los PDFs

**Causa**: La ruta del archivo no es accesible por DomPDF

**Solución**: El código usa `public_path('storage/...')` que apunta al directorio correcto

### Configuración para Desarrollo vs Producción

#### Desarrollo (XAMPP/Local)
- Usa `php artisan storage:link`
- Los archivos se guardan en `storage/app/public`
- Accesibles via `public/storage` (symlink)

#### Producción (Docker)
- El symlink se crea automáticamente en `entrypoint.sh`
- Nginx puede servir archivos directamente via alias
- Usa volúmenes para persistencia

### Configuración de XAMPP (Windows)

Si estás usando XAMPP en Windows, el symlink puede no funcionar correctamente debido a permisos de Windows. Solución:

1. **Agregar `+FollowSymLinks` en `.htaccess`**:
```apache
Options -MultiViews -Indexes +FollowSymLinks
```

2. **Ejecutar como administrador**:
```bash
# CMD como Administrador
mklink /D "C:\xampp\htdocs\neo-erp\public\storage" "C:\xampp\htdocs\neo-erp\storage\app\public"
```

3. **Verificar configuración de Apache**:
```apache
<Directory "C:/xampp/htdocs">
    Options +Indexes +FollowSymLinks +Includes
    AllowOverride All
    Require all granted
</Directory>
```

### Variables de Entorno Relacionadas

```env
# Filesystem
FILESYSTEM_DISK=local

# Upload limits (configurado en php.ini)
upload_max_filesize=20M
post_max_size=20M
```

### Logs y Debugging

Para ver logs relacionados con storage:

```bash
# Logs de Laravel
docker exec -it neo-erp-app tail -f /var/www/html/storage/logs/laravel.log

# Logs de Nginx
docker exec -it neo-erp-app tail -f /var/log/nginx/error.log

# Logs de PHP-FPM
docker exec -it neo-erp-app tail -f /var/log/php-fpm/error.log
```

### Seguridad

El directorio `storage/app/public` debe:
- ✅ Ser accesible públicamente via web
- ✅ Tener permisos 775 (rwxrwxr-x)
- ✅ Pertenecer a www-data:www-data
- ❌ NO exponer archivos sensibles (usa `storage/app/private` para eso)
- ❌ NO tener permisos 777 (inseguro)

### Respaldo de Archivos

Para respaldar archivos subidos:

```bash
# Backup
docker run --rm \
  -v neo-erp-storage:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/storage-backup.tar.gz /data

# Restore
docker run --rm \
  -v neo-erp-storage:/data \
  -v $(pwd):/backup \
  alpine sh -c "cd /data && tar xzf /backup/storage-backup.tar.gz --strip 1"
```
