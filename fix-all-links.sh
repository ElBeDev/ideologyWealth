#!/bin/bash

# Script para actualizar todos los enlaces en los archivos HTML a rutas locales

echo "🔧 Actualizando enlaces en todos los archivos HTML..."

# Array de archivos a actualizar
files=(
    "1life Financial - Services.html"
    "1life Financial - FAQ.html"
    "1life Financial - Contact Us.html"
)

for file in "${files[@]}"; do
    echo "📝 Actualizando: $file"
    
    # Backup del archivo original
    cp "$file" "$file.backup"
    
    # Reemplazar enlaces del menú
    sed -i '' 's|href="https://1lifefinancial.com/"|href="./1life Financial - Home.html"|g' "$file"
    sed -i '' 's|href="https://1lifefinancial.com/about-us"|href="./1life Financial - About Us.html"|g' "$file"
    sed -i '' 's|href="https://1lifefinancial.com/services"|href="./1life Financial - Services.html"|g' "$file"
    sed -i '' 's|href="https://1lifefinancial.com/faq"|href="./1life Financial - FAQ.html"|g' "$file"
    sed -i '' 's|href="https://1lifefinancial.com/contact"|href="./1life Financial - Contact Us.html"|g' "$file"
    
    # Reemplazar botones de login/register con alertas
    sed -i '' 's|href="https://1lifefinancial.com/login"|href="#" onclick="alert('\''Login functionality coming soon'\''); return false;"|g' "$file"
    sed -i '' 's|href="https://1lifefinancial.com/register"|href="#" onclick="alert('\''Register functionality coming soon'\''); return false;"|g' "$file"
    
    # Reemplazar enlaces de páginas de políticas
    sed -i '' 's|href="https://1lifefinancial.com/page/111-company-policy"|href="#" onclick="alert('\''Policy page coming soon'\''); return false;"|g' "$file"
    sed -i '' 's|href="https://1lifefinancial.com/page/85-terms-of-services"|href="#" onclick="alert('\''Terms page coming soon'\''); return false;"|g' "$file"
    sed -i '' 's|href="https://1lifefinancial.com/page/84-privacy-policy"|href="#" onclick="alert('\''Privacy page coming soon'\''); return false;"|g' "$file"
    
    echo "   ✅ $file actualizado"
done

echo ""
echo "🎉 ¡Todos los archivos actualizados!"
echo ""
echo "📋 Archivos modificados:"
for file in "${files[@]}"; do
    echo "   ✅ $file"
done
echo ""
echo "💾 Backups creados con extensión .backup"
