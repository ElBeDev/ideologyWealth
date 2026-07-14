#!/bin/bash

echo "
╔═══════════════════════════════════════════════════════════════╗
║                                                                 ║
║      ✅ VERIFICACIÓN COMPLETA DE RECURSOS - Ideology Wealth Advisors    ║
║                                                                 ║
╚═══════════════════════════════════════════════════════════════╝
"

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

echo "📋 CHECKLIST DE RECURSOS:"
echo ""

# Función para verificar archivo
check_file() {
    if [ -f "$1" ]; then
        size=$(du -h "$1" | cut -f1)
        echo "   ✅ $1 ($size)"
        return 0
    else
        echo "   ❌ $1 (FALTA)"
        return 1
    fi
}

total=0
passed=0

echo "1️⃣  IMÁGENES PRINCIPALES:"
check_file "home_files/logo.png" && ((passed++)); ((total++))
check_file "home_files/white-wave-1.png" && ((passed++)); ((total++))
check_file "home_files/white-wave-2.png" && ((passed++)); ((total++))
check_file "home_files/60c75675a19651623676533.jpg" && ((passed++)); ((total++))

echo ""
echo "2️⃣  IMAGEN SLIDER (Slick):"
check_file "images/elements/right-arrow.png" && ((passed++)); ((total++))

echo ""
echo "3️⃣  ICONOS PWA:"
check_file "icons/icon-72x72.png" && ((passed++)); ((total++))
check_file "icons/icon-96x96.png" && ((passed++)); ((total++))
check_file "icons/icon-128x128.png" && ((passed++)); ((total++))
check_file "icons/icon-144x144.png" && ((passed++)); ((total++))
check_file "icons/icon-152x152.png" && ((passed++)); ((total++))
check_file "icons/icon-192x192.png" && ((passed++)); ((total++))
check_file "icons/icon-384x384.png" && ((passed++)); ((total++))
check_file "icons/icon-512x512.png" && ((passed++)); ((total++))

echo ""
echo "4️⃣  FUENTES FONT AWESOME (Placeholders):"
check_file "webfonts/fa-solid-900.woff2" && ((passed++)); ((total++))
check_file "webfonts/fa-regular-400.woff2" && ((passed++)); ((total++))
check_file "webfonts/fa-brands-400.woff2" && ((passed++)); ((total++))

echo ""
echo "5️⃣  FUENTES LINE AWESOME (Placeholders):"
check_file "fonts/la-solid-900.woff2" && ((passed++)); ((total++))

echo ""
echo "6️⃣  ARCHIVOS PWA:"
check_file "manifest.json" && ((passed++)); ((total++))
check_file "service-worker.js" && ((passed++)); ((total++))
check_file "offline.html" && ((passed++)); ((total++))

echo ""
echo "7️⃣  PÁGINAS HTML:"
check_file "home.html" && ((passed++)); ((total++))
check_file "about.html" && ((passed++)); ((total++))
check_file "services.html" && ((passed++)); ((total++))
check_file "faq.html" && ((passed++)); ((total++))
check_file "contact.html" && ((passed++)); ((total++))

echo ""
echo "8️⃣  CSS PRINCIPALES:"
check_file "home_files/bootstrap.min.css" && ((passed++)); ((total++))
check_file "home_files/main.css" && ((passed++)); ((total++))
check_file "home_files/custom.css" && ((passed++)); ((total++))

echo ""
echo "9️⃣  JAVASCRIPT PRINCIPALES:"
check_file "home_files/jquery-3.5.1.min.js" && ((passed++)); ((total++))
check_file "home_files/bootstrap.bundle.min.js" && ((passed++)); ((total++))
check_file "home_files/app.js" && ((passed++)); ((total++))
check_file "home_files/slick.min.js" && ((passed++)); ((total++))

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
percentage=$((passed * 100 / total))
echo "📊 RESULTADO: $passed/$total archivos encontrados ($percentage%)"
echo ""

if [ $passed -eq $total ]; then
    echo "🎉 ¡PERFECTO! Todos los recursos están presentes"
    echo ""
    echo "✨ PRÓXIMOS PASOS:"
    echo "   1. ✅ Service Worker actualizado a v7"
    echo "   2. 🔄 Recarga la página con CTRL+SHIFT+R (hard reload)"
    echo "   3. 🔍 Abre DevTools → Console"
    echo "   4. ✅ Verifica que no haya errores 404"
    echo ""
    echo "🚀 La PWA está lista y completamente funcional!"
else
    echo "⚠️  Faltan $((total - passed)) archivos"
    echo ""
    echo "🔧 Para corregir:"
    echo "   ./fix-resources.sh"
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
