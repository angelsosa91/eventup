# Guía de Deploy

## 🚀 Deploy Rápido (Recomendado para cambios de código)

Para cambios en código PHP, vistas Blade, configuraciones Laravel, etc.

```bash
# En el servidor de producción
cd /ruta/al/proyecto
bash scripts/deploy-fast.sh
```

**Tiempo:** ~30 segundos
**Usa cuando:**
- ✅ Cambios en código PHP
- ✅ Cambios en vistas Blade
- ✅ Cambios en configuraciones
- ✅ Nuevas migraciones
- ✅ Cambios menores en composer.json

---

## 🏗️ Deploy Completo (Solo cuando sea necesario)

Para cambios en la infraestructura Docker.

```bash
# En el servidor de producción
cd /ruta/al/proyecto
bash scripts/deploy-full.sh
```

**Tiempo:** ~5-10 minutos
**Usa cuando:**
- ✅ Cambios en Dockerfile
- ✅ Cambios en dependencias del sistema (apk, apt)
- ✅ Cambios en configuración de PHP/Nginx/Supervisor
- ✅ Primera instalación
- ✅ Actualización de versión de PHP

---

## 📋 Comparación

| Característica | Deploy Rápido | Deploy Completo |
|----------------|---------------|-----------------|
| Tiempo | ~30 segundos | ~5-10 minutos |
| Reconstruye imagen | ❌ No | ✅ Sí |
| Actualiza código | ✅ Sí | ✅ Sí |
| Ejecuta migraciones | ✅ Sí | ✅ Sí |
| Downtime | Mínimo (~5s) | Moderado (~30s) |
| Uso de CPU/RAM | Bajo | Alto |

---

## 🔧 ¿Cómo funciona?

### Deploy Rápido
1. `git pull` - Descarga el código nuevo
2. Detecta cambios en composer y ejecuta install si es necesario
3. Limpia cachés de Laravel
4. Re-optimiza cachés
5. Ejecuta migraciones si hay nuevas
6. Reinicia contenedores (sin rebuild)

### Deploy Completo
1. `git pull` - Descarga el código nuevo
2. Detiene todos los contenedores
3. **Reconstruye las imágenes Docker desde cero**
4. Levanta los contenedores nuevos
5. Ejecuta migraciones
6. Optimiza cachés

---

## 💡 Mejores Prácticas

### Desarrollo Local
```bash
# Hacer cambios en el código
git add .
git commit -m "Descripción del cambio"
git push origin main
```

### Producción
```bash
# SSH al servidor
ssh root@tu-servidor

# Navegar al proyecto
cd /ruta/al/proyecto

# Deploy rápido (99% de los casos)
bash scripts/deploy-fast.sh
```

---

## 🐛 Troubleshooting

### Si el deploy rápido falla
```bash
# Ver logs
docker compose logs -f app

# Limpiar todo y hacer deploy completo
bash scripts/deploy-full.sh
```

### Si necesitas rollback
```bash
# Volver al commit anterior
git reset --hard HEAD~1

# Deploy rápido
bash scripts/deploy-fast.sh
```

### Verificar estado de la aplicación
```bash
# Ver contenedores corriendo
docker compose ps

# Ver logs en tiempo real
docker compose logs -f app

# Ver uso de recursos
docker stats
```

---

## 🎯 Ejemplos de Uso

### Caso 1: Corrección de bug en código PHP
```bash
# En local
git commit -m "Fix: Corrige error en confirmación de ventas"
git push

# En servidor
bash scripts/deploy-fast.sh
```
**Tiempo total: ~1 minuto**

### Caso 2: Actualizar PHP de 8.2 a 8.3
```bash
# En local
# Editar Dockerfile: FROM php:8.3-fpm-alpine
git commit -m "Upgrade PHP to 8.3"
git push

# En servidor
bash scripts/deploy-full.sh
```
**Tiempo total: ~10 minutos**

### Caso 3: Nueva migración de base de datos
```bash
# En local
git commit -m "Add new migration for journal_entries period field"
git push

# En servidor
bash scripts/deploy-fast.sh  # Detecta automáticamente la migración
```
**Tiempo total: ~30 segundos**

---

## ⚙️ Configuración

Los scripts usan estas variables de entorno del archivo `.env`:
- `APP_ENV` - Debe ser `production`
- `APP_DEBUG` - Debe ser `false`
- `DB_*` - Configuración de base de datos

---

## 📝 Notas Importantes

1. **Siempre** usa `deploy-fast.sh` para cambios de código
2. **Solo** usa `deploy-full.sh` cuando cambies el Dockerfile
3. Los volúmenes permiten que el código del host se sincronice automáticamente
4. Las dependencias de `vendor/` se mantienen en la imagen Docker
5. Los logs y storage se mantienen en volúmenes persistentes

---

## 🔒 Seguridad

- Los scripts requieren acceso SSH al servidor
- Git pull requiere autenticación (configura SSH keys)
- Los comandos Docker requieren permisos de root o usuario en grupo docker

---

## 📚 Referencias

- [Documentación Docker](https://docs.docker.com/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Docker Compose Volumes](https://docs.docker.com/storage/volumes/)
