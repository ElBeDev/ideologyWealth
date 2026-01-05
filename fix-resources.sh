#!/bin/bash

echo "
╔═══════════════════════════════════════════════════════════════╗
║                                                                 ║
║      🔧 SOLUCIONADOR DE RECURSOS FALTANTES - 1Life Financial   ║
║                                                                 ║
╚═══════════════════════════════════════════════════════════════╝
"

cd "/Users/bener/GitHub/1lifefinancial.com"

echo "📁 Creando estructura de carpetas necesarias..."
mkdir -p images/elements
mkdir -p webfonts
mkdir -p fonts

echo ""
echo "🎨 Creando right-arrow.png placeholder (imagen simple SVG → PNG)..."

# Crear un SVG simple de flecha
cat > right-arrow.svg << 'EOF'
<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20">
  <path fill="#6fb950" d="M10 5l5 5-5 5V5z"/>
</svg>
EOF

# Convertir SVG a PNG usando Python (más portable que ImageMagick)
python3 << 'PYEOF'
from PIL import Image, ImageDraw
import os

# Crear una imagen simple de flecha verde
img = Image.new('RGBA', (20, 20), (255, 255, 255, 0))
draw = ImageDraw.Draw(img)

# Dibujar una flecha simple (triángulo verde apuntando a la derecha)
points = [(5, 5), (15, 10), (5, 15)]
draw.polygon(points, fill='#6fb950')

# Guardar en la ubicación correcta
img.save('images/elements/right-arrow.png')
print("✅ right-arrow.png creado")
PYEOF

# Limpiar archivo temporal
rm -f right-arrow.svg

echo ""
echo "📝 Creando archivos de fuentes vacíos (placeholders)..."
echo "   Nota: Los iconos de Font Awesome y Line Awesome NO son necesarios"
echo "   porque ya están cargados desde CDN en los archivos CSS."
echo ""

# Crear placeholders vacíos para evitar 404s
touch webfonts/fa-solid-900.woff2
touch webfonts/fa-solid-900.woff
touch webfonts/fa-solid-900.ttf
touch webfonts/fa-regular-400.woff2
touch webfonts/fa-regular-400.woff
touch webfonts/fa-regular-400.ttf
touch webfonts/fa-brands-400.woff2
touch webfonts/fa-brands-400.woff
touch webfonts/fa-brands-400.ttf
touch fonts/la-solid-900.woff2
touch fonts/la-solid-900.woff
touch fonts/la-solid-900.ttf

echo "✅ Archivos de fuentes placeholder creados (evitan errores 404)"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎯 SOLUCIÓN APLICADA:"
echo "   • ✅ right-arrow.png creado en images/elements/"
echo "   • ✅ Placeholders de fuentes creados (9 archivos Font Awesome)"
echo "   • ✅ Placeholders de fuentes creados (3 archivos Line Awesome)"
echo ""
echo "📌 NOTA IMPORTANTE:"
echo "   Los iconos se ven correctamente porque se cargan desde CDN."
echo "   Los archivos locales son solo para evitar errores 404 en la consola."
echo ""
echo "🔄 Ahora necesitas:"
echo "   1. Recargar la página (Cmd+R o F5)"
echo "   2. Los errores 404 deberían desaparecer"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
