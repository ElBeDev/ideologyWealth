#!/bin/bash

echo "🚀 Agregando PWA a todas las páginas HTML..."

# Función para agregar PWA a un archivo
add_pwa_to_file() {
    local file="$1"
    local page_name="$2"
    
    echo "📝 Procesando: $file"
    
    # Backup
    cp "$file" "$file.pwa-backup"
    
    # 1. Agregar meta tags PWA en el <head> después de los meta tags existentes
    # Buscar la línea con apple-touch-icon y agregar después
    sed -i '' '/<link rel="apple-touch-icon"/a\
\
  <!-- PWA Manifest -->\
  <link rel="manifest" href="./manifest.json">\
  \
  <!-- Theme Color for PWA -->\
  <meta name="theme-color" content="#6fb950">\
  <meta name="msapplication-TileColor" content="#6fb950">\
  \
  <!-- iOS Specific Meta Tags -->\
  <meta name="apple-mobile-web-app-capable" content="yes">\
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">\
  <meta name="apple-mobile-web-app-title" content="Ideology Wealth Advisors">\
  \
  <!-- Additional PWA Icons -->\
  <link rel="apple-touch-icon" sizes="72x72" href="./icons/icon-72x72.png">\
  <link rel="apple-touch-icon" sizes="96x96" href="./icons/icon-96x96.png">\
  <link rel="apple-touch-icon" sizes="128x128" href="./icons/icon-128x128.png">\
  <link rel="apple-touch-icon" sizes="144x144" href="./icons/icon-144x144.png">\
  <link rel="apple-touch-icon" sizes="152x152" href="./icons/icon-152x152.png">\
  <link rel="apple-touch-icon" sizes="192x192" href="./icons/icon-192x192.png">\
  <link rel="apple-touch-icon" sizes="384x384" href="./icons/icon-384x384.png">\
  <link rel="apple-touch-icon" sizes="512x512" href="./icons/icon-512x512.png">
' "$file"
    
    echo "   ✅ Meta tags PWA agregados"
    
    # 2. Agregar botón de instalación después del header
    sed -i '' '/<!-- header-section end  -->/a\
\
    <!-- PWA Install Button -->\
    <button id="pwa-install-btn" class="pwa-install-button" style="display: none;">\
        <i class="fas fa-download"></i>\
        <span>Instalar App</span>\
    </button>
' "$file"
    
    echo "   ✅ Botón de instalación agregado"
}

# Procesar archivos
add_pwa_to_file "Ideology Wealth Advisors - About Us.html" "About Us"
add_pwa_to_file "Ideology Wealth Advisors - Services.html" "Services"
add_pwa_to_file "Ideology Wealth Advisors - FAQ.html" "FAQ"
add_pwa_to_file "Ideology Wealth Advisors - Contact Us.html" "Contact Us"

echo ""
echo "🎉 PWA agregada a todas las páginas!"
echo ""
echo "⚠️  IMPORTANTE: Ahora necesitas agregar el JavaScript y CSS manualmente"
echo "   o ejecutar el siguiente script para completar la instalación."
