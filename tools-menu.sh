#!/bin/bash

# Colores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "
╔═══════════════════════════════════════════════════════════════╗
║                                                                 ║
║      🛠️  HERRAMIENTAS DE DIAGNÓSTICO - Ideology Wealth Advisors PWA    ║
║                                                                 ║
╚═══════════════════════════════════════════════════════════════╝
"

echo -e "${BLUE}Elige una opción:${NC}"
echo ""
echo "  1) 🔍 Verificar todos los recursos (32 archivos)"
echo "  2) 🔧 Corregir recursos faltantes"
echo "  3) 🚀 Iniciar servidor local"
echo "  4) 📊 Ver resumen de correcciones"
echo "  5) 🧹 Limpiar cache del navegador (instrucciones)"
echo "  6) ❌ Salir"
echo ""
read -p "Selecciona (1-6): " choice

case $choice in
  1)
    echo ""
    ./check-all-resources.sh
    ;;
  2)
    echo ""
    ./fix-resources.sh
    ;;
  3)
    echo ""
    ./start-server.sh
    ;;
  4)
    echo ""
    cat FIXES-SUMMARY.md
    ;;
  5)
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "🧹 CÓMO LIMPIAR CACHE DEL NAVEGADOR"
    echo ""
    echo "Para forzar una actualización completa:"
    echo ""
    echo "1️⃣  Hard Reload (Recarga Forzada)"
    echo "   • Windows/Linux: Ctrl + Shift + R"
    echo "   • macOS: Cmd + Shift + R"
    echo ""
    echo "2️⃣  Limpiar Service Worker"
    echo "   • Abre DevTools (F12)"
    echo "   • Application → Service Workers"
    echo "   • Click en 'Unregister'"
    echo "   • Recarga la página"
    echo ""
    echo "3️⃣  Limpiar Todo (Nuclear)"
    echo "   • Abre DevTools (F12)"
    echo "   • Application → Clear Storage"
    echo "   • Click en 'Clear site data'"
    echo "   • Recarga la página"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    ;;
  6)
    echo ""
    echo "👋 ¡Hasta pronto!"
    exit 0
    ;;
  *)
    echo ""
    echo "❌ Opción inválida. Por favor elige 1-6."
    ;;
esac

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
