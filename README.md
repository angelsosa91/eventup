# Neo ERP

Sistema de Gestión Empresarial (ERP) moderno y completo desarrollado con Laravel 12, diseñado específicamente para empresas en Paraguay con soporte para el sistema de IVA local.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php)
![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Módulos del Sistema](#-módulos-del-sistema)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
  - [Instalación con Docker (Recomendado)](#instalación-con-docker-recomendado)
  - [Instalación Manual](#instalación-manual)
- [Configuración](#-configuración)
- [Uso](#-uso)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Stack Tecnológico](#-stack-tecnológico)
- [Desarrollo](#-desarrollo)
- [Deployment](#-deployment)
- [API](#-api)
- [Contribución](#-contribución)
- [Licencia](#-licencia)

## 🚀 Características

- **Multi-tenant**: Soporte para múltiples empresas en una sola instalación
- **Sistema de IVA Paraguay**: Cálculo automático de IVA (10%, 5%, Exento)
- **Gestión Completa de Inventario**: Control de stock con ajustes y trazabilidad
- **Ventas y Facturación**: Sistema completo de ventas con múltiples métodos de pago
- **Compras**: Gestión de proveedores y compras con control de stock
- **Créditos**: Sistema integrado de cuentas por cobrar y pagar con pagos parciales
- **Caja Diaria**: Arqueo de caja con conciliación automática
- **Reportes en Tiempo Real**: Dashboard con métricas y gráficos interactivos
- **Control de Gastos**: Categorización y seguimiento de gastos operativos
- **Sistema de Roles y Permisos**: Control granular de accesos
- **Interfaz Moderna**: UI responsiva con Bootstrap 5 y jEasyUI
- **RESTful API**: API JSON para integraciones

## 📦 Módulos del Sistema

### 1. Dashboard
- Métricas en tiempo real
- Gráficos de ventas, compras y gastos
- Productos más vendidos
- Alertas de stock bajo
- Resumen financiero

### 2. Gestión de Clientes
- Registro completo de clientes
- Histórico de compras
- Cuentas por cobrar
- Búsqueda avanzada

### 3. Gestión de Proveedores
- Registro de proveedores
- Histórico de compras
- Cuentas por pagar
- Gestión de contactos

### 4. Inventario
- **Productos**: Catálogo con categorías, precios y stock
- **Categorías**: Organización jerárquica de productos
- **Ajustes**: Entrada/salida de inventario con trazabilidad
- **Alertas**: Notificación de stock bajo

### 5. Ventas
- Punto de venta intuitivo
- Facturación con IVA incluido
- Múltiples métodos de pago (efectivo, crédito)
- Ventas a crédito con plazos
- Estados: Borrador → Confirmado → Cancelado
- Impresión de facturas

### 6. Compras
- Registro de compras con proveedores
- Actualización automática de stock
- Compras a crédito
- Control de costos
- Cálculo de IVA en compras

### 7. Cuentas por Cobrar
- Generación automática desde ventas a crédito
- Registro de pagos parciales/totales
- Estados: Pendiente → Parcial → Pagado
- Vista consolidada por cliente
- Histórico de cobros

### 8. Cuentas por Pagar
- Generación automática desde compras a crédito
- Registro de pagos a proveedores
- Control de vencimientos
- Vista consolidada por proveedor
- Histórico de pagos

### 9. Caja (Arqueo Diario)
- Apertura de caja con saldo inicial
- Registro de movimientos (ingresos/egresos)
- Categorización por concepto
- Cierre de caja con conciliación
- Detección de faltantes/sobrantes
- Histórico de arqueos

### 10. Gastos
- Categorización de gastos
- Estados: Pendiente → Pagado
- Métodos de pago
- Filtros y búsquedas
- Reportes de gastos

### 11. Reportes
- **Ventas**: Por período, producto, cliente
- **Compras**: Por proveedor, período
- **Inventario**: Stock actual, movimientos
- **Gastos**: Por categoría, período
- **Resumen Financiero**: Balance general
- Exportación a Excel/PDF

### 12. Configuración
- Datos de la empresa
- Configuración de impuestos
- Parámetros del sistema
- Backup y restauración

### 13. Usuarios y Roles
- Gestión de usuarios
- Roles personalizables
- Permisos granulares
- Auditoría de accesos

## 💻 Requisitos

### Para Instalación con Docker (Recomendado)
- Docker 20.10+
- Docker Compose 2.0+
- Git

### Para Instalación Manual
- PHP 8.3+
- Composer 2.0+
- MySQL 8.0+ o PostgreSQL 14+
- Nginx o Apache
- Node.js 18+ (opcional, para desarrollo)
- Redis (opcional, para mejor performance)

## 📥 Instalación

### Instalación con Docker (Recomendado)

#### Linux/Mac

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/neo-erp.git
cd neo-erp

# 2. Ejecutar el script de deployment
chmod +x deploy.sh
./deploy.sh

# El script te guiará en la configuración
```

#### Windows

```batch
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/neo-erp.git
cd neo-erp

# 2. Ejecutar el script de deployment
deploy.bat
```

#### Usando Makefile

```bash
# Instalación completa interactiva
make install

# O paso a paso:
make build    # Construir imágenes
make up       # Iniciar contenedores
make migrate  # Ejecutar migraciones
make seed     # Ejecutar seeders
```

La aplicación estará disponible en: `http://localhost:8080`

**Credenciales por defecto:**
- Email: `admin@neo-erp.com`
- Password: `password`

⚠️ **IMPORTANTE**: Cambia estas credenciales inmediatamente en producción.

### Instalación Manual

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/neo-erp.git
cd neo-erp

# 2. Instalar dependencias
composer install --optimize-autoloader --no-dev

# 3. Configurar el entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar la base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=neo_erp
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_password

# 5. Ejecutar migraciones y seeders
php artisan migrate --seed

# 6. Crear enlace simbólico para storage
php artisan storage:link

# 7. Optimizar para producción (opcional)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Configurar permisos
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 9. Iniciar el servidor (desarrollo)
php artisan serve
```

## ⚙️ Configuración

### Variables de Entorno Principales

```env
# Aplicación
APP_NAME="Neo ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Base de Datos
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=neo_erp
DB_USERNAME=neo_user
DB_PASSWORD=tu_password_seguro

# Cache y Sesiones (con Redis)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis

# Correo (configurar según tu proveedor)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_password
MAIL_ENCRYPTION=tls
```

### Configuración de IVA

El sistema soporta las tasas de IVA de Paraguay:
- 10% (tasa estándar)
- 5% (tasa reducida)
- Exento (sin IVA)

El IVA se calcula automáticamente usando la fórmula: `IVA = monto * tasa / (100 + tasa)`

## 🎯 Uso

### Flujo de Trabajo Típico

#### 1. Configuración Inicial
1. Acceder al sistema con las credenciales de administrador
2. Ir a **Configuración** y actualizar datos de la empresa
3. Crear usuarios y asignar roles
4. Configurar categorías de productos y gastos

#### 2. Gestión de Maestros
1. Registrar **Clientes** en el módulo de Clientes
2. Registrar **Proveedores** en el módulo de Proveedores
3. Crear **Categorías** de productos
4. Registrar **Productos** con precios y stock inicial

#### 3. Operaciones Diarias

**Apertura de Caja:**
1. Ir a **Caja** → Apertura de Caja
2. Ingresar saldo inicial
3. Confirmar apertura

**Realizar una Venta:**
1. Ir a **Ventas** → Nueva Factura
2. Seleccionar cliente (opcional)
3. Agregar productos
4. Seleccionar método de pago (efectivo/crédito)
5. Si es crédito, especificar días de plazo
6. Confirmar venta

**Registrar una Compra:**
1. Ir a **Compras** → Nueva Compra
2. Seleccionar proveedor
3. Agregar productos
4. Seleccionar método de pago
5. Confirmar compra (actualiza stock automáticamente)

**Gestionar Cobros:**
1. Ir a **Cuentas por Cobrar**
2. Seleccionar cuenta pendiente
3. Registrar pago (parcial o total)
4. El sistema actualiza automáticamente el saldo

**Cierre de Caja:**
1. Ir a **Caja** → Ver Caja Actual
2. Revisar movimientos del día
3. Hacer clic en "Cerrar Caja"
4. Ingresar el monto real contado
5. El sistema calcula diferencias automáticamente

### Comandos Útiles con Docker

```bash
# Ver logs
docker-compose logs -f

# Acceder al contenedor
docker-compose exec app sh

# Ejecutar comandos Artisan
docker-compose exec app php artisan [comando]

# Reiniciar servicios
docker-compose restart

# Detener todos los servicios
docker-compose down

# Ver estado de contenedores
docker-compose ps

# Crear backup de BD
docker-compose exec db mysqldump -u neo_user -p neo_erp > backup.sql

# Restaurar backup
docker-compose exec -T db mysql -u neo_user -p neo_erp < backup.sql
```

## 📁 Estructura del Proyecto

```
neo-erp/
├── app/
│   ├── Http/
│   │   └── Controllers/      # Controladores del sistema
│   ├── Models/               # Modelos Eloquent
│   └── Traits/               # Traits (BelongsToTenant)
├── database/
│   ├── migrations/           # Migraciones de base de datos
│   └── seeders/              # Seeders
├── docker/                   # Configuración Docker
│   ├── nginx/                # Config Nginx
│   ├── php/                  # Config PHP
│   ├── mysql/                # Config MySQL
│   ├── supervisor/           # Config Supervisor
│   └── entrypoint.sh         # Script de inicio
├── resources/
│   └── views/                # Vistas Blade
│       ├── layouts/          # Layouts principales
│       ├── dashboard/        # Dashboard
│       ├── sales/            # Ventas
│       ├── purchases/        # Compras
│       ├── account-receivables/  # Cuentas por cobrar
│       ├── account-payables/     # Cuentas por pagar
│       ├── cash-registers/   # Caja
│       └── ...
├── routes/
│   └── web.php               # Rutas de la aplicación
├── docker-compose.yml        # Docker Compose (producción)
├── docker-compose.dev.yml    # Docker Compose (desarrollo)
├── Dockerfile                # Dockerfile (producción)
├── Dockerfile.dev            # Dockerfile (desarrollo)
├── Makefile                  # Comandos make
├── deploy.sh                 # Script deployment Linux/Mac
├── deploy.bat                # Script deployment Windows
└── README.md                 # Este archivo
```

## 🛠 Stack Tecnológico

### Backend
- **Framework**: Laravel 12
- **PHP**: 8.3+
- **Base de Datos**: MySQL 8.0 / PostgreSQL 14+
- **Cache**: Redis 7
- **Queue**: Redis

### Frontend
- **CSS Framework**: Bootstrap 5.3
- **Icons**: Bootstrap Icons
- **DataGrid**: jEasyUI
- **Charts**: Chart.js (en Dashboard)

### Infraestructura
- **Web Server**: Nginx
- **PHP-FPM**: PHP 8.3 FPM
- **Process Manager**: Supervisor
- **Containerización**: Docker + Docker Compose

## 🔧 Desarrollo

### Entorno de Desarrollo con Docker

```bash
# Iniciar entorno de desarrollo
docker-compose -f docker-compose.dev.yml up -d

# Incluye:
# - Hot reload de código
# - Xdebug configurado
# - PHPMyAdmin en http://localhost:8081
# - Logs detallados
```

### Comandos de Desarrollo

```bash
# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Crear nueva migración
php artisan make:migration create_nombre_tabla

# Crear nuevo modelo con migración
php artisan make:model NombreModelo -m

# Crear nuevo controlador
php artisan make:controller NombreController

# Ejecutar tests (si están configurados)
php artisan test

# Ver rutas
php artisan route:list
```

### Buenas Prácticas

1. **Multi-tenancy**: Todos los modelos deben usar el trait `BelongsToTenant`
2. **Validación**: Validar todos los inputs en los controladores
3. **Transacciones**: Usar `DB::transaction()` para operaciones críticas
4. **Autorización**: Implementar políticas de acceso
5. **Logging**: Registrar operaciones importantes
6. **Migraciones**: Nunca modificar migraciones ya ejecutadas en producción

## 🚀 Deployment

### Deployment en Servidor VPS/Cloud

1. **Preparar el servidor:**
```bash
# Instalar Docker y Docker Compose
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose
```

2. **Clonar y configurar:**
```bash
git clone https://github.com/tu-usuario/neo-erp.git
cd neo-erp
cp .env.docker .env
# Editar .env con credenciales seguras
```

3. **Deploy:**
```bash
./deploy.sh
```

4. **Configurar dominio y SSL (con Nginx Proxy):**
```bash
# Usar nginx-proxy + letsencrypt para SSL automático
# Ver: https://github.com/nginx-proxy/nginx-proxy
```

### Backup y Restauración

```bash
# Backup completo
make backup

# Backup manual de BD
docker-compose exec db mysqldump -u neo_user -p neo_erp > backup_$(date +%Y%m%d).sql

# Restaurar backup
docker-compose exec -T db mysql -u neo_user -p neo_erp < backup.sql

# Backup de archivos
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/
```

### Monitoreo

El sistema incluye:
- Health check endpoint: `/health`
- Logs centralizados en Docker
- Supervisor para gestión de procesos

## 📡 API

El sistema expone una API RESTful JSON. Todas las rutas requieren autenticación.

### Endpoints Principales

```
GET    /api/products          # Listar productos
POST   /api/products          # Crear producto
GET    /api/products/{id}     # Ver producto
PUT    /api/products/{id}     # Actualizar producto
DELETE /api/products/{id}     # Eliminar producto

GET    /api/sales             # Listar ventas
POST   /api/sales             # Crear venta
GET    /api/sales/{id}        # Ver venta

# ... más endpoints según necesidad
```

## 🤝 Contribución

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Autores

- **Equipo de Desarrollo Neo ERP**

## 📞 Soporte

Para soporte y consultas:
- Email: soporte@neo-erp.com
- Issues: https://github.com/tu-usuario/neo-erp/issues
- Documentación: https://docs.neo-erp.com

## 🙏 Agradecimientos

- Laravel Framework
- Bootstrap Team
- jEasyUI
- La comunidad de código abierto

---

**Neo ERP** - Sistema de Gestión Empresarial Moderno
