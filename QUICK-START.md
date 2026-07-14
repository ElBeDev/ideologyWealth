# 🚀 Quick Start Guide - Ideology Wealth Advisors PWA

## ⚡ Inicio Rápido (3 pasos)

### 1️⃣ Generar Iconos (1 minuto)
```bash
# Abre este archivo en tu navegador:
open generate-icons.html

# Click en "Generate All Icons"
# Mueve los archivos descargados a la carpeta /icons/
```

### 2️⃣ Probar Localmente (30 segundos)
```bash
# Mac/Linux:
./start-server.sh

# Windows:
start-server.bat

# Abre: http://localhost:8000
```

### 3️⃣ Desplegar Online (2 minutos)
```bash
# Opción más fácil - Netlify:
# 1. Ve a netlify.com
# 2. Arrastra la carpeta completa
# 3. ¡Listo! Tendrás tu URL con HTTPS
```

---

## 📱 Probar PWA

### En tu computadora:
1. Abre Chrome: `http://localhost:8000`
2. Verás botón "Install App" en esquina inferior derecha
3. Click para instalar
4. ¡Ahora funciona como app nativa!

### En tu móvil:
1. Encuentra tu IP: `ifconfig | grep inet` (Mac/Linux)
2. En móvil: `http://TU-IP:8000`
3. Safari/Chrome mostrará "Add to Home Screen"
4. ¡Instalada!

---

## 🔍 Verificar que todo funciona

```bash
# Abre el checker:
http://localhost:8000/pwa-checker.html

# Debe mostrar: ✅ 10/10
```

---

## 🌐 URLs de Utilidad

| Herramienta | URL |
|-------------|-----|
| Sitio Principal | `http://localhost:8000` |
| PWA Checker | `http://localhost:8000/pwa-checker.html` |
| Generador de Iconos | `http://localhost:8000/generate-icons.html` |
| Página Offline | `http://localhost:8000/offline.html` |

---

## 📦 Despliegue Rápido

### GitHub Pages (Gratis + HTTPS)
```bash
git init
git add .
git commit -m "PWA ready"
git branch -M main
git remote add origin https://github.com/TU-USUARIO/ideologywealthadvisors.git
git push -u origin main

# Activa en: Settings > Pages > Deploy from main
# URL: https://TU-USUARIO.github.io/ideologywealthadvisors/
```

### Netlify (Más fácil)
1. Ir a [netlify.com](https://netlify.com)
2. Drag & Drop la carpeta
3. Listo! URL: `https://NOMBRE.netlify.app`

### Vercel
```bash
npm i -g vercel
vercel --prod
```

---

## ❓ Problemas Comunes

### "Service Worker no se registra"
✅ Usa servidor HTTP (no file://)
```bash
./start-server.sh
```

### "No aparece botón Install"
✅ Verifica:
- HTTPS o localhost
- Iconos en /icons/
- manifest.json accesible

### "Offline no funciona"
✅ Espera 1 minuto después de cargar la página
✅ El Service Worker necesita tiempo para activarse

---

## 📚 Documentación Completa

- `README-PWA.md` - Documentación completa de PWA
- `DEPLOYMENT-CHECKLIST.md` - Checklist paso a paso
- Este archivo - Guía rápida

---

## 🆘 Ayuda

### Comandos útiles:
```bash
# Ver archivos del proyecto
ls -la

# Iniciar servidor (puerto personalizado)
./start-server.sh 3000

# Ver tu IP local
ifconfig | grep inet  # Mac/Linux
ipconfig              # Windows
```

### DevTools Chrome (F12):
- **Application** → Ver manifest y service worker
- **Lighthouse** → Generar reporte PWA
- **Network** → Probar modo offline

---

## ✨ Next Steps

1. ✅ Genera iconos con tu logo
2. ✅ Prueba localmente
3. ✅ Despliega online
4. ✅ Comparte tu PWA!

**¡Tu PWA está lista en menos de 5 minutos! 🎉**
