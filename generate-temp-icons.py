#!/usr/bin/env python3
from PIL import Image, ImageDraw, ImageFont
import os

print("🎨 Generando iconos PWA temporales...")

# Crear directorio si no existe
os.makedirs("icons", exist_ok=True)

# Tamaños de iconos requeridos para PWA
sizes = [72, 96, 128, 144, 152, 192, 384, 512]

# Colores de marca
green = "#6fb950"
blue = "#0143a3"

for size in sizes:
    print(f"📐 Generando icono {size}x{size}...")
    
    # Crear imagen con gradiente
    img = Image.new('RGB', (size, size), blue)
    draw = ImageDraw.Draw(img)
    
    # Crear efecto de gradiente simple
    for y in range(size):
        # Interpolate between green and blue
        ratio = y / size
        r = int(int(green[1:3], 16) * (1 - ratio) + int(blue[1:3], 16) * ratio)
        g_val = int(int(green[3:5], 16) * (1 - ratio) + int(blue[3:5], 16) * ratio)
        b = int(int(green[5:7], 16) * (1 - ratio) + int(blue[5:7], 16) * ratio)
        
        color = (r, g_val, b)
        draw.line([(0, y), (size, y)], fill=color)
    
    # Agregar texto "1L" en el centro
    try:
        # Intentar usar una fuente, si no existe usar la default
        font_size = size // 3
        font = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", font_size)
    except:
        font = ImageFont.load_default()
    
    text = "1L"
    
    # Calcular posición del texto para centrarlo
    bbox = draw.textbbox((0, 0), text, font=font)
    text_width = bbox[2] - bbox[0]
    text_height = bbox[3] - bbox[1]
    
    x = (size - text_width) // 2
    y = (size - text_height) // 2
    
    # Dibujar texto blanco con borde
    draw.text((x, y), text, fill="white", font=font)
    
    # Guardar imagen
    filename = f"icons/icon-{size}x{size}.png"
    img.save(filename, "PNG")
    print(f"   ✅ {filename} creado")

print("\n🎉 ¡Todos los iconos PWA generados!")
print("\n📋 Iconos creados:")
for size in sizes:
    print(f"   ✅ icons/icon-{size}x{size}.png")

print("\n💡 Nota: Estos son iconos temporales. Puedes crear mejores usando generate-icons.html")
