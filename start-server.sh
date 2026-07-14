#!/bin/bash

# Ideology Wealth Advisors - PWA Local Server Starter
# Este script inicia un servidor local para probar la PWA

echo "🚀 Iniciando servidor local para Ideology Wealth Advisors PWA..."
echo ""

# Detectar el puerto (default: 8000)
PORT=${1:-8000}

# Función para abrir el navegador
open_browser() {
    sleep 2
    if command -v open &> /dev/null; then
        # macOS
        open "http://localhost:$PORT"
    elif command -v xdg-open &> /dev/null; then
        # Linux
        xdg-open "http://localhost:$PORT"
    elif command -v start &> /dev/null; then
        # Windows
        start "http://localhost:$PORT"
    else
        echo "Por favor, abre manualmente: http://localhost:$PORT"
    fi
}

# Intentar iniciar servidor con Python 3
if command -v python3 &> /dev/null; then
    echo "✅ Usando Python 3..."
    echo "📡 Servidor corriendo en: http://localhost:$PORT"
    echo "🔍 PWA Checker: http://localhost:$PORT/pwa-checker.html"
    echo "🎨 Icon Generator: http://localhost:$PORT/generate-icons.html"
    echo ""
    echo "Presiona Ctrl+C para detener el servidor"
    echo ""
    
    open_browser &
    python3 -m http.server $PORT
    
# Intentar con Python 2
elif command -v python &> /dev/null; then
    echo "✅ Usando Python 2..."
    echo "📡 Servidor corriendo en: http://localhost:$PORT"
    echo "🔍 PWA Checker: http://localhost:$PORT/pwa-checker.html"
    echo "🎨 Icon Generator: http://localhost:$PORT/generate-icons.html"
    echo ""
    echo "Presiona Ctrl+C para detener el servidor"
    echo ""
    
    open_browser &
    python -m SimpleHTTPServer $PORT
    
# Intentar con PHP
elif command -v php &> /dev/null; then
    echo "✅ Usando PHP..."
    echo "📡 Servidor corriendo en: http://localhost:$PORT"
    echo "🔍 PWA Checker: http://localhost:$PORT/pwa-checker.html"
    echo "🎨 Icon Generator: http://localhost:$PORT/generate-icons.html"
    echo ""
    echo "Presiona Ctrl+C para detener el servidor"
    echo ""
    
    open_browser &
    php -S localhost:$PORT
    
# Intentar con Node.js (si tiene http-server instalado)
elif command -v http-server &> /dev/null; then
    echo "✅ Usando Node.js http-server..."
    echo "📡 Servidor corriendo en: http://localhost:$PORT"
    echo "🔍 PWA Checker: http://localhost:$PORT/pwa-checker.html"
    echo "🎨 Icon Generator: http://localhost:$PORT/generate-icons.html"
    echo ""
    echo "Presiona Ctrl+C para detener el servidor"
    echo ""
    
    open_browser &
    http-server -p $PORT
    
else
    echo "❌ No se encontró ningún servidor disponible."
    echo ""
    echo "Por favor, instala una de estas opciones:"
    echo "  - Python 3: https://www.python.org/downloads/"
    echo "  - Node.js http-server: npm install -g http-server"
    echo "  - PHP: https://www.php.net/downloads"
    echo ""
    echo "O usa la extensión 'Live Server' en VS Code"
    exit 1
fi
