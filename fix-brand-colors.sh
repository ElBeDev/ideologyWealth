#!/bin/bash

# Script para reemplazar todos los colores rojos de la plantilla original
# con los colores oficiales de la marca 1Life Financial
#
# Colores de marca:
# - Verde primario: #6fb950
# - Azul oscuro: #0143a3
# - Verde hover: #5ea840

echo "🎨 Actualizando colores de marca en todos los archivos CSS..."

# Buscar todos los archivos CSS en el proyecto
find . -type f -name "*.css" ! -path "*/node_modules/*" ! -path "*/.git/*" | while read -r file; do
    echo "📝 Procesando: $file"
    
    # Crear backup del archivo
    cp "$file" "$file.backup"
    
    # Reemplazar colores rojos por verde primario (#6fb950)
    # - #fb3b47 -> #6fb950
    # - #e84351 -> #6fb950
    # - #ea5455 -> #6fb950
    # - #ef774c, #ed684f, #e73351 (gradientes) -> #6fb950
    
    sed -i '' 's/#fb3b47/#6fb950/gi' "$file"
    sed -i '' 's/#e84351/#6fb950/gi' "$file"
    sed -i '' 's/#ea5455/#6fb950/gi' "$file"
    
    # Reemplazar gradientes rojos por gradientes de marca
    # De: linear-gradient(-103deg, #ef774c 0%, #ed684f 35%, #e84351 76%, #e73351 100%)
    # A: linear-gradient(135deg, #6fb950 0%, #0143a3 100%)
    
    sed -i '' 's/linear-gradient(-103deg, #ef774c 0%, #ed684f 35%, #e84351 76%, #e73351 100%)/linear-gradient(135deg, #6fb950 0%, #0143a3 100%)/gi' "$file"
    
    # Reemplazar colores individuales del gradiente
    sed -i '' 's/#ef774c/#6fb950/gi' "$file"
    sed -i '' 's/#ed684f/#6fb950/gi' "$file"
    sed -i '' 's/#e73351/#6fb950/gi' "$file"
    
    # Reemplazar #00a6f7 (azul claro de la plantilla) por verde primario
    sed -i '' 's/#00a6f7/#6fb950/gi' "$file"
    
    echo "✅ Actualizado: $file"
done

echo ""
echo "🎉 ¡Todos los archivos CSS han sido actualizados con los colores de marca 1Life Financial!"
echo ""
echo "📋 Resumen de cambios:"
echo "   #fb3b47 → #6fb950 (verde primario)"
echo "   #e84351 → #6fb950 (verde primario)"
echo "   #ea5455 → #6fb950 (verde primario)"
echo "   #00a6f7 → #6fb950 (verde primario)"
echo "   Gradientes → linear-gradient(135deg, #6fb950 0%, #0143a3 100%)"
echo ""
echo "💾 Los archivos originales se guardaron con extensión .backup"
echo ""
echo "🔍 Para verificar los cambios, compara los archivos:"
echo "   diff archivo.css archivo.css.backup"
