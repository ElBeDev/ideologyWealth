#!/bin/bash

echo "🚀 Aplicando PWA completa a todas las páginas..."

for file in "1life Financial - Services.html" "1life Financial - FAQ.html" "1life Financial - Contact Us.html"; do
    echo "📝 Procesando: $file"
    
    # Copiar desde About Us.html que ya tiene PWA
    # Extraer sección PWA del head
    sed -n '/<!-- PWA Manifest -->/,/<!-- bootstrap 5  -->/p' "1life Financial - About Us.html" > /tmp/pwa-head.txt
    
    # Buscar e insertar en el archivo objetivo
    sed -i'.pwa2' '/<!-- bootstrap 5  -->/r /tmp/pwa-head.txt' "$file"
    # Eliminar duplicado
    sed -i'.pwa3' '/<!-- bootstrap 5  -->/,+25d' "$file"
    sed -i'.pwa4' '/<!-- PWA Manifest -->/r /tmp/pwa-head.txt' "$file"
    
    echo "   ✅ $file actualizado"
done

echo "🎉 ¡PWA aplicada a todas las páginas!"
