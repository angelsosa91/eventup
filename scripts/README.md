# 📜 Scripts de Deploy - NEO ERP

Scripts para desplegar la aplicación en producción de forma segura.

## 🎯 Scripts Disponibles

### 1. `deploy-fast.sh` (Deploy Rápido)
**Uso:** Para cambios menores sin rebuild de Docker
**Características:**
- ✅ Sin rebuild de imágenes
- ✅ Ejecuta migraciones automáticamente
- ✅ Limpia y optimiza cachés
- ⚠️ **NO hace backup** (usar solo para cambios menores)
- ⚠️ **NO activa modo mantenimiento**

**Tiempo:** ~30 segundos

```bash
cd /path/to/neo-erp
./scripts/deploy-fast.sh
```

**Cuándo usar:**
- Cambios en vistas o controladores
- Actualizaciones de texto
- Cambios que NO afectan la BD

---

### 2. `deploy-production.sh` (Deploy Completo) ⭐ RECOMENDADO
**Uso:** Deploy completo con todas las protecciones
**Características:**
- ✅ Backup automático de BD
- ✅ Modo mantenimiento
- ✅ Ejecuta migraciones y seeders
- ✅ Verificación de salud
- ✅ Rollback automático si falla
- ✅ Notificaciones de estado

**Tiempo:** ~2 minutos

```bash
cd /path/to/neo-erp
./scripts/deploy-production.sh
```

**Cuándo usar:**
- Nuevas funcionalidades
- Cambios en base de datos
- Updates importantes
- **SIEMPRE que haya migraciones**

---

### 3. `deploy-credit-notes.sh` (Deploy Específico)
**Uso:** Solo para desplegar módulo de Notas de Crédito
**Características:**
- ✅ Backup específico
- ✅ Modo mantenimiento
- ✅ Migración específica
- ✅ Verificación de tablas y permisos
- ✅ Testing automatizado

**Tiempo:** ~1 minuto

```bash
cd /path/to/neo-erp
./scripts/deploy-credit-notes.sh
```

**Cuándo usar:**
- Primera vez desplegando Notas de Crédito
- Verificar que el módulo se instaló correctamente

---

## ⚙️ Configuración Previa

### 1. Dar permisos de ejecución
```bash
chmod +x scripts/*.sh
```

### 2. Configurar variables de entorno
Editar cada script y ajustar:
```bash
DB_CONTAINER="neo-erp-db-1"  # Nombre de tu contenedor de BD
APP_URL="https://tu-dominio.com"  # Tu URL de producción
MYSQL_ROOT_PASSWORD="tu-password"  # Password de MySQL
```

### 3. Verificar configuración de Docker
```bash
docker compose ps  # Verificar nombres de contenedores
```

---

## 🔐 Seguridad y Backups

### Ubicación de Backups
```
neo-erp/
└── backups/
    ├── backup_20251210_143022.sql
    ├── backup_20251210_150530.sql
    └── backup_credit_notes_20251210_152045.sql
```

### Restaurar un Backup
```bash
# Listar backups disponibles
ls -lh backups/

# Restaurar backup específico
docker compose exec -T db mysql \
    -u root \
    -p \
    neo_erp < backups/backup_TIMESTAMP.sql
```

### Retención de Backups
- **Automática:** Se mantienen los últimos 5 backups
- **Manual:** Puedes guardar backups importantes en otro directorio

---

## 🚨 Plan de Rollback

Si algo sale mal durante el deploy:

### Opción 1: Rollback Automático
El script `deploy-production.sh` hace rollback automático si detecta errores.

### Opción 2: Rollback Manual
```bash
# 1. Restaurar código
git reset --hard HEAD@{1}

# 2. Restaurar base de datos
docker compose exec -T db mysql \
    -u root \
    -p \
    neo_erp < backups/backup_ULTIMO.sql

# 3. Limpiar cachés
docker compose exec app php artisan optimize:clear

# 4. Desactivar mantenimiento
docker compose exec app php artisan up
```

---

## 📊 Monitoreo Post-Deploy

### Verificar Logs
```bash
# Ver logs en tiempo real
docker compose logs -f app

# Ver últimos 100 logs
docker compose logs --tail=100 app

# Ver errores específicos
docker compose logs app | grep ERROR
```

### Verificar Estado de Contenedores
```bash
docker compose ps
docker compose top
```

### Verificar Base de Datos
```bash
docker compose exec db mysql -u root -p -e "
    USE neo_erp;
    SHOW TABLES LIKE 'credit%';
    SELECT COUNT(*) FROM credit_notes;
"
```

### Verificar Aplicación
```bash
# Test de respuesta
curl -I https://tu-dominio.com/login

# Test de API
curl https://tu-dominio.com/api/health
```

---

## 🐛 Troubleshooting

### Error: "permission denied"
```bash
chmod +x scripts/*.sh
```

### Error: "database connection failed"
```bash
# Verificar contenedor de BD
docker compose ps db

# Reiniciar contenedor
docker compose restart db
```

### Error: "migration already exists"
```bash
# Ver estado de migraciones
docker compose exec app php artisan migrate:status

# Rollback de última migración
docker compose exec app php artisan migrate:rollback --step=1
```

### Error: "disk space full"
```bash
# Verificar espacio
df -h

# Limpiar logs antiguos
docker compose exec app php artisan log:clear

# Limpiar backups antiguos
rm backups/backup_OLD*.sql
```

---

## 📝 Checklist Pre-Deploy

Antes de ejecutar cualquier script:

- [ ] Backup manual de la BD (extra seguridad)
- [ ] Notificar a usuarios (si es horario laboral)
- [ ] Verificar que no hay operaciones críticas en curso
- [ ] Tener acceso SSH al servidor
- [ ] Verificar espacio en disco disponible
- [ ] Probar en ambiente de staging primero
- [ ] Revisar logs de errores recientes

---

## 📞 Soporte

Si tienes problemas durante el deploy:

1. **Revisa los logs:** `docker compose logs -f app`
2. **Verifica backups:** `ls -lh backups/`
3. **Rollback si es necesario:** Ver sección "Plan de Rollback"
4. **Contacta al equipo de desarrollo**

---

## 🎓 Mejores Prácticas

1. **Siempre hacer backup** antes de deploy
2. **Usar `deploy-production.sh`** para cambios importantes
3. **Desplegar en horarios de bajo tráfico** (madrugada, fines de semana)
4. **Monitorear logs** durante 10-15 minutos post-deploy
5. **Tener plan de rollback listo**
6. **Documentar cualquier incidente**
7. **Mantener backups por al menos 7 días**

---

**Última actualización:** 2025-12-10
**Versión:** 1.0.0
