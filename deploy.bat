@echo off
setlocal enabledelayedexpansion

echo ======================================
echo Neo ERP - Script de Deployment
echo ======================================
echo.

REM Check if .env exists
if not exist .env (
    echo Archivo .env no encontrado. Copiando .env.docker...
    copy .env.docker .env
    echo.
    echo IMPORTANTE: Edita el archivo .env y configura:
    echo   - APP_KEY (se puede generar con: php artisan key:generate)
    echo   - DB_PASSWORD
    echo   - DB_ROOT_PASSWORD
    echo   - Otras configuraciones segun tu entorno
    echo.
    pause
)

echo 1. Construyendo imagenes Docker...
docker-compose build --no-cache

echo.
echo 2. Iniciando contenedores...
docker-compose up -d

echo.
echo 3. Esperando que la base de datos este lista...
timeout /t 10 /nobreak > nul

echo.
echo 4. Generando APP_KEY si no existe...
docker-compose exec app php artisan key:generate --force

echo.
echo 5. Ejecutando migraciones...
docker-compose exec app php artisan migrate --force

echo.
echo 6. Ejecutando seeders (primera vez)...
set /p run_seeders="Deseas ejecutar los seeders? (s/N): "
if /i "!run_seeders!"=="s" (
    docker-compose exec app php artisan db:seed --force
)

echo.
echo 7. Optimizando aplicacion...
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

echo.
echo ======================================
echo Deployment completado exitosamente!
echo ======================================
echo.
echo La aplicacion esta disponible en: http://localhost:8080
echo.
echo Credenciales por defecto:
echo   Email: admin@neo-erp.com
echo   Password: password
echo.
echo Comandos utiles:
echo   - Ver logs: docker-compose logs -f
echo   - Detener: docker-compose down
echo   - Reiniciar: docker-compose restart
echo.
pause
