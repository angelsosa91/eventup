#!/bin/bash

# Script de setup automatizado para nuevo servidor
# Ejecutar en el servidor Ubuntu nuevo como root
# curl -sSL https://tu-repo/setup-server.sh | bash -s -- cliente.com admin@cliente.com

set -e

DOMAIN=$1
ADMIN_EMAIL=$2

if [ -z "$DOMAIN" ] || [ -z "$ADMIN_EMAIL" ]; then
    echo "❌ Uso: ./setup-server.sh dominio.com admin@email.com"
    exit 1
fi

echo "════════════════════════════════════════"
echo "🚀 Setup automático Neo ERP"
echo "════════════════════════════════════════"
echo "🌐 Dominio: $DOMAIN"
echo "📧 Email: $ADMIN_EMAIL"
echo ""

# 1. Actualizar sistema
echo "📦 Actualizando sistema..."
apt update && apt upgrade -y

# 2. Instalar Docker
echo "🐳 Instalando Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
fi

# 3. Instalar Docker Compose
echo "🐳 Instalando Docker Compose..."
if ! command -v docker compose &> /dev/null; then
    apt install docker-compose-plugin -y
fi

# 4. Instalar NGINX
echo "🌐 Instalando NGINX..."
apt install nginx -y
systemctl enable nginx

# 5. Instalar Certbot
echo "🔒 Instalando Certbot..."
apt install certbot python3-certbot-nginx -y

# 6. Configurar Firewall
echo "🛡️ Configurando Firewall..."
ufw --force enable
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp

# 7. Clonar repositorio (ajusta según tu repo)
echo "📥 Clonando aplicación..."
cd /home
# git clone https://github.com/tu-usuario/neo-erp.git
# cd neo-erp

echo "⚠️  Configuración manual requerida:"
echo "1. Copiar .env de plantilla"
echo "2. Configurar DB_HOST, DB_PASSWORD"
echo "3. Ejecutar: docker compose up -d"
echo "4. Configurar NGINX para dominio: $DOMAIN"
echo "5. Obtener SSL: certbot --nginx -d $DOMAIN"

echo ""
echo "✅ Servidor base configurado"
echo "📖 Continúa con el manual de deployment"
