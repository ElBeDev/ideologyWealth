#!/usr/bin/env python3
import re

print("🚀 Agregando PWA a Services, FAQ y Contact...")

files_to_update = [
    "1life Financial - Services.html",
    "1life Financial - FAQ.html",
    "1life Financial - Contact Us.html"
]

# Leer el archivo About Us que ya tiene PWA
with open("1life Financial - About Us.html", "r", encoding="utf-8") as f:
    about_content = f.read()

# Extraer la sección PWA del head (desde <!-- PWA Manifest --> hasta antes de <!-- bootstrap 5 -->)
pwa_head_match = re.search(r'(<!-- PWA Manifest -->.*?)(<!-- bootstrap 5  -->)', about_content, re.DOTALL)
pwa_head = pwa_head_match.group(1) if pwa_head_match else ""

# Extraer el botón de instalación PWA
pwa_button_match = re.search(r'(<!-- PWA Install Button -->.*?</button>)', about_content, re.DOTALL)
pwa_button = pwa_button_match.group(1) if pwa_button_match else ""

# Extraer scripts y estilos PWA (desde <!-- PWA Service Worker --> hasta </style>)
pwa_scripts_match = re.search(r'(<!-- PWA Service Worker Registration -->.*?</style>)', about_content, re.DOTALL)
pwa_scripts = pwa_scripts_match.group(1) if pwa_scripts_match else ""

for filename in files_to_update:
    print(f"📝 Procesando: {filename}")
    
    try:
        with open(filename, "r", encoding="utf-8") as f:
            content = f.read()
        
        # 1. Agregar PWA meta tags en el head
        if "<!-- PWA Manifest -->" not in content:
            content = content.replace(
                "<!-- bootstrap 5  -->",
                f"{pwa_head}\n  <!-- bootstrap 5  -->"
            )
            print(f"   ✅ Meta tags PWA agregados")
        
        # 2. Agregar botón de instalación después del header
        if "<!-- PWA Install Button -->" not in content:
            content = content.replace(
                "<!-- header-section end  -->",
                f"<!-- header-section end  -->\n\n    {pwa_button}"
            )
            print(f"   ✅ Botón de instalación agregado")
        
        # 3. Agregar scripts y estilos PWA antes de </body>
        if "<!-- PWA Service Worker Registration -->" not in content:
            content = content.replace(
                "</body></html>",
                f"\n\n  {pwa_scripts}\n\n  \n\n</body></html>"
            )
            print(f"   ✅ Scripts y estilos PWA agregados")
        
        # Guardar archivo actualizado
        with open(filename, "w", encoding="utf-8") as f:
            f.write(content)
        
        print(f"   ✅ {filename} completado\n")
    
    except Exception as e:
        print(f"   ❌ Error en {filename}: {e}\n")

print("🎉 ¡PWA agregada a todas las páginas!")
print("\n📋 Archivos actualizados:")
for f in files_to_update:
    print(f"   ✅ {f}")
