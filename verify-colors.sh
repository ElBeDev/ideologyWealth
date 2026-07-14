#!/bin/bash

# Script de verificación visual de colores
# Muestra una vista previa de los colores aplicados

echo "🎨 Ideology Wealth Advisors - Verificación de Colores de Marca"
echo "=================================================="
echo ""
echo "✅ COLORES ACTUALES:"
echo ""
echo "🟢 Verde Primario: #6fb950"
echo "   └─ Botones principales, enlaces hover, elementos destacados"
echo ""
echo "🔵 Azul Oscuro: #0143a3"
echo "   └─ Fondos oscuros, headers, footers, contraste"
echo ""
echo "🟢 Verde Hover: #5ea840"
echo "   └─ Estados hover de botones y elementos interactivos"
echo ""
echo "🌈 Gradiente: linear-gradient(135deg, #6fb950 0%, #0143a3 100%)"
echo "   └─ Headers, fondos especiales, PWA install button"
echo ""
echo "=================================================="
echo ""
echo "🔍 Verificando archivos CSS..."
echo ""

# Contar archivos CSS modificados
css_count=$(find . -name "*.css" ! -path "*/node_modules/*" ! -path "*/.git/*" -type f | wc -l | xargs)
backup_count=$(find . -name "*.css.backup" -type f | wc -l | xargs)

echo "📁 Archivos CSS encontrados: $css_count"
echo "💾 Archivos backup creados: $backup_count"
echo ""

# Verificar que no queden colores rojos
red_colors=$(grep -r "#fb3b47\|#e84351\|#ea5455" --include="*.css" --exclude="*.backup" . 2>/dev/null | wc -l | xargs)

if [ "$red_colors" -eq 0 ]; then
    echo "✅ ¡Perfecto! No se encontraron colores rojos en archivos CSS"
else
    echo "⚠️  Advertencia: Se encontraron $red_colors referencias a colores rojos"
fi

echo ""
echo "🌐 Para ver los cambios:"
echo "   1. Abre: Ideology Wealth Advisors - Home.html en tu navegador"
echo "   2. Verifica que todos los elementos sean verde (#6fb950) o azul (#0143a3)"
echo "   3. Prueba hover en botones y enlaces"
echo ""
echo "🚀 Siguiente paso: Prueba la aplicación PWA"
echo "   - Abre Chrome DevTools > Application > Manifest"
echo "   - Verifica theme_color: #6fb950"
echo "   - Prueba el botón 'Instalar App'"
echo ""
