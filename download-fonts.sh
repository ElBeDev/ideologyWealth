#!/bin/bash

# Script para descargar las fuentes faltantes de Font Awesome y Line Awesome
# Esto soluciona los errores 404 de fuentes

echo "📥 Descargando fuentes necesarias..."
echo "===================================="
echo ""

# Crear directorios si no existen
mkdir -p webfonts
mkdir -p fonts

echo "📁 Directorios creados/verificados: webfonts/ y fonts/"
echo ""

# Font Awesome 5.15.4 (versión gratuita más estable)
FA_VERSION="5.15.4"
FA_BASE_URL="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/${FA_VERSION}/webfonts"

echo "🔤 Descargando Font Awesome v${FA_VERSION}..."

# Font Awesome Solid
echo "  → fa-solid-900.woff2"
curl -sL "${FA_BASE_URL}/fa-solid-900.woff2" -o webfonts/fa-solid-900.woff2

echo "  → fa-solid-900.woff"
curl -sL "${FA_BASE_URL}/fa-solid-900.woff" -o webfonts/fa-solid-900.woff

echo "  → fa-solid-900.ttf"
curl -sL "${FA_BASE_URL}/fa-solid-900.ttf" -o webfonts/fa-solid-900.ttf

# Font Awesome Regular
echo "  → fa-regular-400.woff2"
curl -sL "${FA_BASE_URL}/fa-regular-400.woff2" -o webfonts/fa-regular-400.woff2

echo "  → fa-regular-400.woff"
curl -sL "${FA_BASE_URL}/fa-regular-400.woff" -o webfonts/fa-regular-400.woff

echo "  → fa-regular-400.ttf"
curl -sL "${FA_BASE_URL}/fa-regular-400.ttf" -o webfonts/fa-regular-400.ttf

# Font Awesome Brands
echo "  → fa-brands-400.woff2"
curl -sL "${FA_BASE_URL}/fa-brands-400.woff2" -o webfonts/fa-brands-400.woff2

echo "  → fa-brands-400.woff"
curl -sL "${FA_BASE_URL}/fa-brands-400.woff" -o webfonts/fa-brands-400.woff

echo "  → fa-brands-400.ttf"
curl -sL "${FA_BASE_URL}/fa-brands-400.ttf" -o webfonts/fa-brands-400.ttf

echo ""
echo "🔤 Descargando Line Awesome..."

# Line Awesome 1.3.0
LA_BASE_URL="https://maxst.icons8.com/vue-static/landings/line-awesome/font-awesome-line-awesome/css/fonts"

echo "  → la-solid-900.woff2"
curl -sL "https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/fonts/la-solid-900.woff2" -o fonts/la-solid-900.woff2

echo "  → la-solid-900.woff"
curl -sL "https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/fonts/la-solid-900.woff" -o fonts/la-solid-900.woff

echo "  → la-solid-900.ttf"
curl -sL "https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/fonts/la-solid-900.ttf" -o fonts/la-solid-900.ttf

echo ""
echo "🔍 Verificando archivos descargados..."
echo ""

# Verificar que los archivos existen y tienen tamaño > 0
error_count=0

for file in webfonts/fa-solid-900.woff2 webfonts/fa-solid-900.woff webfonts/fa-solid-900.ttf \
            webfonts/fa-regular-400.woff2 webfonts/fa-regular-400.woff webfonts/fa-regular-400.ttf \
            webfonts/fa-brands-400.woff2 webfonts/fa-brands-400.woff webfonts/fa-brands-400.ttf \
            fonts/la-solid-900.woff2 fonts/la-solid-900.woff fonts/la-solid-900.ttf; do
    if [ -f "$file" ] && [ -s "$file" ]; then
        size=$(ls -lh "$file" | awk '{print $5}')
        echo "  ✅ $file ($size)"
    else
        echo "  ❌ $file - No descargado o vacío"
        error_count=$((error_count + 1))
    fi
done

echo ""

if [ $error_count -eq 0 ]; then
    echo "🎉 ¡Todas las fuentes descargadas correctamente!"
    echo ""
    echo "📝 Próximo paso:"
    echo "   Recarga la página en tu navegador (Ctrl+Shift+R / Cmd+Shift+R)"
    echo "   Los iconos deberían mostrarse correctamente ahora."
else
    echo "⚠️  Se encontraron $error_count errores."
    echo "   Algunas fuentes pueden no haberse descargado correctamente."
    echo "   Intenta ejecutar el script nuevamente o descárgalas manualmente."
fi

echo ""
