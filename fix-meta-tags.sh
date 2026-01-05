#!/bin/bash

# Script para actualizar meta tags deprecados en todas las páginas HTML

echo "🔧 Actualizando meta tags de PWA..."
echo "===================================="
echo ""

# Buscar todos los archivos HTML
html_files=$(find . -maxdepth 1 -name "*.html" -type f | grep -E "(Home|About|Services|FAQ|Contact)")

count=0
for file in $html_files; do
    echo "📝 Procesando: $file"
    
    # Agregar la meta tag moderna justo después de apple-mobile-web-app-capable
    # Solo si no existe ya
    if ! grep -q "mobile-web-app-capable" "$file"; then
        # Usar sed para agregar después de la primera ocurrencia de apple-mobile-web-app-capable
        sed -i '' '/<meta name="apple-mobile-web-app-capable" content="yes">/a\
  <meta name="mobile-web-app-capable" content="yes">
' "$file"
        echo "   ✅ Agregada meta tag mobile-web-app-capable"
        count=$((count + 1))
    else
        echo "   ℹ️  Ya tiene la meta tag moderna"
    fi
done

echo ""
echo "🎉 Proceso completado: $count archivos actualizados"
echo ""
