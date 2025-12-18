@echo off
REM Script para descargar librerías jQuery y jEasyUI localmente (Windows)
REM Ejecutar desde la raíz del proyecto

echo Descargando librerías jQuery y jEasyUI...

REM Crear directorios si no existen
if not exist "public\vendor\jquery" mkdir "public\vendor\jquery"
if not exist "public\vendor\easyui\themes\default\images" mkdir "public\vendor\easyui\themes\default\images"
if not exist "public\vendor\easyui\themes\icons" mkdir "public\vendor\easyui\themes\icons"
if not exist "public\vendor\easyui\locale" mkdir "public\vendor\easyui\locale"

echo.
echo Descargando jQuery 3.7.1...
curl -o public\vendor\jquery\jquery-3.7.1.min.js https://code.jquery.com/jquery-3.7.1.min.js

echo.
echo Descargando jEasyUI JavaScript...
curl -o public\vendor\easyui\jquery.easyui.min.js https://www.jeasyui.com/easyui/jquery.easyui.min.js

echo.
echo Descargando jEasyUI idioma español...
curl -o public\vendor\easyui\locale\easyui-lang-es.js https://www.jeasyui.com/easyui/locale/easyui-lang-es.js

echo.
echo Descargando jEasyUI CSS...
curl -o public\vendor\easyui\themes\default\easyui.css https://www.jeasyui.com/easyui/themes/default/easyui.css

echo.
echo Descargando jEasyUI iconos CSS...
curl -o public\vendor\easyui\themes\icon.css https://www.jeasyui.com/easyui/themes/icon.css

echo.
echo Descargando imágenes de iconos...
curl -o public\vendor\easyui\themes\icons\blank.gif https://www.jeasyui.com/easyui/themes/icons/blank.gif
curl -o public\vendor\easyui\themes\icons\loading.gif https://www.jeasyui.com/easyui/themes/icons/loading.gif

echo.
echo Descargando imágenes del tema default...
curl -s -o public\vendor\easyui\themes\default\images\blank.gif https://www.jeasyui.com/easyui/themes/default/images/blank.gif
curl -s -o public\vendor\easyui\themes\default\images\loading.gif https://www.jeasyui.com/easyui/themes/default/images/loading.gif
curl -s -o public\vendor\easyui\themes\default\images\panel_tools.png https://www.jeasyui.com/easyui/themes/default/images/panel_tools.png
curl -s -o public\vendor\easyui\themes\default\images\layout_arrows.png https://www.jeasyui.com/easyui/themes/default/images/layout_arrows.png
curl -s -o public\vendor\easyui\themes\default\images\accordion_arrows.png https://www.jeasyui.com/easyui/themes/default/images/accordion_arrows.png
curl -s -o public\vendor\easyui\themes\default\images\tree_arrows.png https://www.jeasyui.com/easyui/themes/default/images/tree_arrows.png
curl -s -o public\vendor\easyui\themes\default\images\tabs_icons.png https://www.jeasyui.com/easyui/themes/default/images/tabs_icons.png
curl -s -o public\vendor\easyui\themes\default\images\datagrid_icons.png https://www.jeasyui.com/easyui/themes/default/images/datagrid_icons.png
curl -s -o public\vendor\easyui\themes\default\images\calendar_arrows.png https://www.jeasyui.com/easyui/themes/default/images/calendar_arrows.png
curl -s -o public\vendor\easyui\themes\default\images\combo_arrow.png https://www.jeasyui.com/easyui/themes/default/images/combo_arrow.png
curl -s -o public\vendor\easyui\themes\default\images\spinner_arrows.png https://www.jeasyui.com/easyui/themes/default/images/spinner_arrows.png
curl -s -o public\vendor\easyui\themes\default\images\linkbutton_bg.png https://www.jeasyui.com/easyui/themes/default/images/linkbutton_bg.png
curl -s -o public\vendor\easyui\themes\default\images\pagination_icons.png https://www.jeasyui.com/easyui/themes/default/images/pagination_icons.png
curl -s -o public\vendor\easyui\themes\default\images\textbox_bg.gif https://www.jeasyui.com/easyui/themes/default/images/textbox_bg.gif
curl -s -o public\vendor\easyui\themes\default\images\validatebox_warning.png https://www.jeasyui.com/easyui/themes/default/images/validatebox_warning.png

echo.
echo ========================================
echo Librerías descargadas exitosamente!
echo ========================================
echo.
echo Archivos creados en public\vendor\
echo.
pause
