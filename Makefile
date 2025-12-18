.PHONY: help build up down restart logs clean install deploy dev

# Default target
help:
	@echo "Neo ERP - Comandos disponibles:"
	@echo ""
	@echo "  Producción:"
	@echo "    make build      - Construir imágenes Docker"
	@echo "    make up         - Iniciar contenedores"
	@echo "    make down       - Detener contenedores"
	@echo "    make restart    - Reiniciar contenedores"
	@echo "    make logs       - Ver logs"
	@echo "    make deploy     - Deploy completo (build + up)"
	@echo "    make install    - Instalación inicial completa"
	@echo ""
	@echo "  Desarrollo:"
	@echo "    make dev-up     - Iniciar entorno de desarrollo"
	@echo "    make dev-down   - Detener entorno de desarrollo"
	@echo "    make dev-logs   - Ver logs de desarrollo"
	@echo ""
	@echo "  Mantenimiento:"
	@echo "    make clean      - Limpiar caché y archivos temporales"
	@echo "    make migrate    - Ejecutar migraciones"
	@echo "    make seed       - Ejecutar seeders"
	@echo "    make fresh      - Reset DB con migraciones y seeders"
	@echo "    make shell      - Acceder al contenedor de la app"
	@echo "    make db-shell   - Acceder a MySQL"
	@echo ""

# Production commands
build:
	docker-compose build --no-cache

up:
	docker-compose up -d

down:
	docker-compose down

restart:
	docker-compose restart

logs:
	docker-compose logs -f

deploy: build up
	@echo "Deployment completado!"

install:
	@echo "Instalación inicial de Neo ERP..."
	@cp .env.docker .env
	@echo "Por favor, edita el archivo .env y configura APP_KEY y las credenciales de BD"
	@read -p "Presiona Enter cuando hayas configurado .env..."
	docker-compose build
	docker-compose up -d
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan db:seed --force
	@echo "¡Instalación completada! Accede a http://localhost:8080"

# Development commands
dev-up:
	docker-compose -f docker-compose.dev.yml up -d

dev-down:
	docker-compose -f docker-compose.dev.yml down

dev-logs:
	docker-compose -f docker-compose.dev.yml logs -f

# Maintenance commands
clean:
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

migrate:
	docker-compose exec app php artisan migrate --force

seed:
	docker-compose exec app php artisan db:seed --force

fresh:
	docker-compose exec app php artisan migrate:fresh --seed --force

shell:
	docker-compose exec app sh

db-shell:
	docker-compose exec db mysql -u neo_user -p neo_erp

# Backup
backup:
	@echo "Creando backup de la base de datos..."
	@mkdir -p backups
	docker-compose exec db mysqldump -u neo_user -p neo_erp > backups/backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "Backup creado en backups/"
