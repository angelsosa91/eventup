#!/bin/bash

# Script para descargar librerías jQuery y jEasyUI localmente
# Ejecutar desde la raíz del proyecto: bash scripts/download-assets.sh

set -e

echo "📥 Descargando librerías jQuery y jEasyUI..."

# Crear directorios si no existen
mkdir -p public/vendor/jquery
mkdir -p public/vendor/easyui/themes/default
mkdir -p public/vendor/easyui/themes/icon
mkdir -p public/vendor/easyui/locale

# Descargar jQuery
echo "⬇️  Descargando jQuery 3.7.1..."
curl -o public/vendor/jquery/jquery-3.7.1.min.js https://code.jquery.com/jquery-3.7.1.min.js

# Descargar jEasyUI JS
echo "⬇️  Descargando jEasyUI JavaScript..."
curl -o public/vendor/easyui/jquery.easyui.min.js https://www.jeasyui.com/easyui/jquery.easyui.min.js

# Descargar localización en español
echo "⬇️  Descargando jEasyUI idioma español..."
curl -o public/vendor/easyui/locale/easyui-lang-es.js https://www.jeasyui.com/easyui/locale/easyui-lang-es.js

# Descargar CSS
echo "⬇️  Descargando jEasyUI CSS..."
curl -o public/vendor/easyui/themes/default/easyui.css https://www.jeasyui.com/easyui/themes/default/easyui.css

# Descargar iconos CSS
echo "⬇️  Descargando jEasyUI iconos..."
curl -o public/vendor/easyui/themes/icon.css https://www.jeasyui.com/easyui/themes/icon.css

# Descargar imágenes de iconos (necesarias para icon.css)
echo "⬇️  Descargando imágenes de iconos..."
mkdir -p public/vendor/easyui/themes/icons
curl -o public/vendor/easyui/themes/icons/blank.gif https://www.jeasyui.com/easyui/themes/icons/blank.gif
curl -o public/vendor/easyui/themes/icons/loading.gif https://www.jeasyui.com/easyui/themes/icons/loading.gif

# Descargar imágenes del tema default
echo "⬇️  Descargando imágenes del tema..."
mkdir -p public/vendor/easyui/themes/default/images
for image in \
    "blank.gif" \
    "loading.gif" \
    "panel_tools.png" \
    "layout_arrows.png" \
    "accordion_arrows.png" \
    "tree_arrows.png" \
    "tabs_icons.png" \
    "datagrid_icons.png" \
    "calendar_arrows.png" \
    "combo_arrow.png" \
    "spinner_arrows.png" \
    "linkbutton_bg.png" \
    "pagination_icons.png" \
    "textbox_bg.gif" \
    "validatebox_warning.png"
do
    echo "  - $image"
    curl -s -o public/vendor/easyui/themes/default/images/$image https://www.jeasyui.com/easyui/themes/default/images/$image || true
done

echo ""
echo "✅ Librerías descargadas exitosamente en public/vendor/"
echo ""
echo "📋 Ahora actualiza resources/views/layouts/app.blade.php para usar las versiones locales"
echo ""
