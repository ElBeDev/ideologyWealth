# 📋 PWA Deployment Checklist - 1life Financial

## Pre-Despliegue

### 1. Generar Iconos
- [ ] Abrir `generate-icons.html` en el navegador
- [ ] Hacer clic en "Generate All Icons"
- [ ] Descargar todos los iconos (72x72 hasta 512x512)
- [ ] Colocar iconos en la carpeta `/icons/`
- [ ] (Opcional) Reemplazar con logos personalizados

### 2. Verificar Configuración Local
- [ ] Ejecutar `./start-server.sh` (Mac/Linux) o `start-server.bat` (Windows)
- [ ] Abrir `http://localhost:8000/pwa-checker.html`
- [ ] Verificar que todos los checks estén en verde ✅
- [ ] Probar instalación de PWA localmente

### 3. Pruebas de Funcionalidad
- [ ] Navegar por todas las secciones del sitio
- [ ] Verificar que todas las imágenes cargan correctamente
- [ ] Probar el formulario de newsletter
- [ ] Revisar responsive en móvil (DevTools > Toggle Device Toolbar)
- [ ] Probar modo offline (DevTools > Network > Offline)

### 4. Optimización
- [ ] Revisar que todos los archivos CSS/JS estén minificados
- [ ] Comprimir imágenes grandes
- [ ] Verificar que no hay enlaces rotos
- [ ] Corregir errores de consola (F12)

---

## Despliegue

### Opción A: GitHub Pages (Gratis)

**Requisitos:**
- Cuenta de GitHub
- Git instalado

**Pasos:**
```bash
# 1. Inicializar repositorio
cd /Users/bener/GitHub/1lifefinancial.com
git init

# 2. Agregar archivos
git add .
git commit -m "Initial commit - PWA ready"

# 3. Crear repositorio en GitHub
# Ve a github.com y crea un nuevo repositorio llamado "1lifefinancial"

# 4. Conectar y subir
git branch -M main
git remote add origin https://github.com/TU-USUARIO/1lifefinancial.git
git push -u origin main

# 5. Activar GitHub Pages
# Settings > Pages > Source: main branch > Save
```

**URL Final:** `https://TU-USUARIO.github.io/1lifefinancial/`

✅ Checklist GitHub Pages:
- [ ] Repositorio creado
- [ ] Código subido
- [ ] GitHub Pages activado
- [ ] URL funcionando con HTTPS
- [ ] PWA instalable

---

### Opción B: Netlify (Recomendado - Gratis)

**Pasos:**
1. [ ] Ir a [netlify.com](https://netlify.com)
2. [ ] Crear cuenta (GitHub, GitLab, o Email)
3. [ ] Click en "Add new site" > "Deploy manually"
4. [ ] Arrastrar la carpeta `1lifefinancial.com` completa
5. [ ] Esperar deployment (1-2 minutos)
6. [ ] Cambiar nombre del sitio: Site settings > Change site name

**URL Final:** `https://1lifefinancial.netlify.app`

✅ Checklist Netlify:
- [ ] Cuenta creada
- [ ] Sitio desplegado
- [ ] HTTPS automático activado
- [ ] Nombre personalizado configurado
- [ ] PWA instalable

**Comandos Git (opcional para auto-deploy):**
```bash
# Conectar con Git para auto-deployment
git init
git add .
git commit -m "Initial commit"
# Conectar con GitHub y vincular en Netlify
```

---

### Opción C: Vercel (Gratis)

**Pasos:**
```bash
# 1. Instalar Vercel CLI
npm install -g vercel

# 2. Login
vercel login

# 3. Deploy
cd /Users/bener/GitHub/1lifefinancial.com
vercel --prod

# Seguir las instrucciones en pantalla
```

**URL Final:** `https://1lifefinancial.vercel.app`

✅ Checklist Vercel:
- [ ] CLI instalado
- [ ] Login completado
- [ ] Deploy exitoso
- [ ] HTTPS automático
- [ ] PWA instalable

---

### Opción D: Firebase Hosting (Gratis)

**Pasos:**
```bash
# 1. Instalar Firebase CLI
npm install -g firebase-tools

# 2. Login
firebase login

# 3. Inicializar
cd /Users/bener/GitHub/1lifefinancial.com
firebase init hosting
# Seleccionar: Usar directorio actual
# Public directory: . (punto)
# Single-page app: No
# GitHub deployment: No

# 4. Deploy
firebase deploy --only hosting
```

**URL Final:** `https://1lifefinancial-XXXXX.web.app`

✅ Checklist Firebase:
- [ ] CLI instalado
- [ ] Proyecto creado
- [ ] Hosting configurado
- [ ] Deploy exitoso
- [ ] PWA instalable

---

## Post-Despliegue

### 1. Verificación PWA en Producción
- [ ] Abrir sitio en Chrome/Edge
- [ ] Abrir DevTools (F12)
- [ ] Tab "Application" > Manifest: verificar sin errores
- [ ] Tab "Application" > Service Workers: verificar activo
- [ ] Tab "Lighthouse" > Generate report > PWA score 100%

### 2. Prueba de Instalación
- [ ] Desktop: Aparecer ícono de instalación en barra de direcciones
- [ ] Móvil: "Add to Home Screen" disponible
- [ ] Instalar y verificar que funciona como app nativa
- [ ] Probar funcionalidad offline

### 3. Pruebas en Dispositivos
- [ ] iPhone/iPad (Safari)
- [ ] Android (Chrome)
- [ ] Desktop Chrome
- [ ] Desktop Edge
- [ ] Desktop Safari (si tienes Mac)

### 4. SEO y Meta Tags
- [ ] Verificar título en pestaña
- [ ] Verificar descripción en búsqueda Google
- [ ] Verificar Open Graph (compartir en redes sociales)
- [ ] Verificar favicon visible

### 5. Rendimiento
- [ ] Lighthouse Performance > 90
- [ ] Lighthouse Best Practices > 90
- [ ] Lighthouse Accessibility > 90
- [ ] Lighthouse SEO > 90
- [ ] Lighthouse PWA = 100

---

## Mantenimiento

### Actualizaciones
```bash
# 1. Hacer cambios en archivos locales
# 2. Probar localmente con ./start-server.sh
# 3. Actualizar versión del cache en service-worker.js:
#    const CACHE_NAME = '1life-financial-v2'; // incrementar versión

# Para GitHub Pages:
git add .
git commit -m "Update: descripción del cambio"
git push

# Para Netlify (si usas Git):
git push

# Para Netlify (manual):
# Arrastrar carpeta actualizada en dashboard

# Para Vercel:
vercel --prod

# Para Firebase:
firebase deploy --only hosting
```

### Monitoreo
- [ ] Configurar Google Analytics (opcional)
- [ ] Configurar Google Search Console
- [ ] Verificar errores 404
- [ ] Revisar tiempo de carga
- [ ] Monitorear instalaciones de PWA

---

## Dominio Personalizado (Opcional)

### Para Netlify:
1. [ ] Comprar dominio (Namecheap, GoDaddy, etc.)
2. [ ] Netlify > Domain settings > Add custom domain
3. [ ] Configurar DNS según instrucciones
4. [ ] Esperar propagación (24-48 horas)
5. [ ] HTTPS automático activado

### Para Vercel:
1. [ ] Comprar dominio
2. [ ] Vercel > Domains > Add
3. [ ] Configurar DNS
4. [ ] HTTPS automático

### Para Firebase:
1. [ ] Comprar dominio
2. [ ] Firebase Console > Hosting > Add custom domain
3. [ ] Seguir instrucciones DNS
4. [ ] HTTPS automático

---

## Troubleshooting

### PWA no se instala
✅ Verificar:
- HTTPS activo (no HTTP)
- manifest.json accesible
- Service Worker registrado
- Al menos 2 iconos (192x192 y 512x512)

### Service Worker no actualiza
✅ Solución:
- Incrementar versión en `CACHE_NAME`
- Hard refresh (Ctrl+Shift+R)
- DevTools > Application > Service Workers > Unregister

### Iconos no aparecen
✅ Solución:
- Verificar rutas en manifest.json
- Verificar que archivos existan en /icons/
- Limpiar caché del navegador

### Offline no funciona
✅ Solución:
- Verificar Service Worker activo
- Revisar console para errores
- Verificar que archivos estén en cache

---

## 🎉 Completado

Una vez completado todo:
- [ ] PWA desplegada con HTTPS
- [ ] Score Lighthouse PWA = 100%
- [ ] Instalable en móvil y desktop
- [ ] Funciona offline
- [ ] Iconos personalizados
- [ ] Dominio personalizado (opcional)

**¡Tu PWA está lista y funcionando! 🚀**

---

**URLs Importantes:**
- Producción: ____________________
- PWA Checker: ____________________/pwa-checker.html
- Lighthouse: DevTools > Lighthouse
- Analytics: ____________________

**Fecha de Deploy:** ____________________
**Versión:** v1.0.0
