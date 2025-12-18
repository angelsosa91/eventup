# Checklist de Deploy - Neo ERP

## Antes del Deploy

- [ ] Todas las migraciones usan el trait `SafeMigration`
- [ ] Migraciones probadas en ambiente local
- [ ] Código versionado y pusheado a Git
- [ ] Backup de la base de datos creado
- [ ] Verificar que no hay cambios destructivos

## Durante el Deploy

```bash
# 1. Conectar al servidor
ssh usuario@servidor

# 2. Ir al directorio del proyecto
cd /path/to/neo-erp

# 3. Hacer backup (IMPORTANTE)
# Usar herramientas de DigitalOcean o:
# docker compose exec -T app mysqldump -h DB_HOST -P DB_PORT -u DB_USER -p DB_NAME > backup_$(date +%Y%m%d_%H%M%S).sql

# 4. Actualizar código
git pull origin main

# 5. Ejecutar deploy
bash deploy.sh
```

## Después del Deploy

- [ ] Verificar que los contenedores están corriendo: `docker compose ps`
- [ ] Revisar logs: `docker compose logs -f app`
- [ ] Verificar que las migraciones se ejecutaron: `docker compose exec app php artisan migrate:status`
- [ ] Probar funcionalidades críticas en la aplicación
- [ ] Verificar que no hay errores 500

## Si Algo Sale Mal

### Rollback de Git (si el problema es de código):
```bash
git log --oneline  # Ver commits recientes
git reset --hard COMMIT_HASH  # Volver a commit anterior
docker compose down
docker compose build --no-cache
docker compose up -d
```

### Restaurar Backup de BD (si el problema es de datos):
```bash
# Restaurar desde backup
# mysql -h DB_HOST -P DB_PORT -u DB_USER -p DB_NAME < backup_YYYYMMDD_HHMMSS.sql
```

## Comandos Útiles

```bash
# Ver estado de contenedores
docker compose ps

# Ver logs en tiempo real
docker compose logs -f app

# Ver estado de migraciones
docker compose exec app php artisan migrate:status

# Limpiar cachés si hay problemas
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Reiniciar un servicio específico
docker compose restart app
docker compose restart worker

# Ver uso de recursos
docker stats
```

## Contactos de Emergencia

- DBA: [nombre/contacto]
- DevOps: [nombre/contacto]
- Backup de DigitalOcean: [link al panel]
