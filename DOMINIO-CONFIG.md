# 🌐 Configuración del Dominio 1stlifefinancial.com

## ✅ Cambios Realizados en el VPS

### **1. Nginx Configurado**
- ✅ Dominio agregado: `1stlifefinancial.com`
- ✅ Dominio con www: `www.1stlifefinancial.com`
- ✅ IP del VPS: `62.72.7.44`
- ✅ Nginx recargado y funcionando

### **2. Manifest.json Actualizado**
- ✅ `start_url`: https://1stlifefinancial.com/
- ✅ `scope`: https://1stlifefinancial.com/
- ✅ `theme_color`: #83af40 (verde del brand)

---

## 🔧 LO QUE DEBES HACER EN HOSTINGER

### **Paso 1: Editar el Registro A**

En el panel de Hostinger, en "Administrar registros DNS":

1. **Buscar el registro A** que dice:
   ```
   Type: A
   Nombre: @
   Apunta a: 84.32.84.32
   ```

2. **Hacer clic en "Editar"** 

3. **Cambiar "Apunta a"** de `84.32.84.32` a:
   ```
   62.72.7.44
   ```

4. **Guardar cambios**

**El registro debe quedar así:**
```
Type: A
Nombre: @
Apunta a: 62.72.7.44
TTL: 14400 (o cambiar a 300 para más rápido)
```

---

### **Paso 2: Verificar el Registro CNAME**

Debe existir este registro (ya lo tienes):
```
Type: CNAME
Nombre: www
Apunta a: 1stlifefinancial.com
TTL: 300
```

✅ Este ya está correcto, no necesitas tocarlo.

---

## ⏱️ Tiempo de Propagación

Después de cambiar el registro A:
- **Mínimo:** 15 minutos
- **Máximo:** 24 horas
- **Promedio:** 1-2 horas

Puedes verificar si el DNS se ha propagado en: https://dnschecker.org/

---

## 🔍 Verificar que Funciona

### **Método 1: Ping**
```bash
ping 1stlifefinancial.com
```
Debe responder desde: `62.72.7.44`

### **Método 2: Navegador**
Abre en tu navegador:
- http://1stlifefinancial.com
- http://www.1stlifefinancial.com
- http://62.72.7.44

Todos deben mostrar tu sitio web.

### **Método 3: nslookup**
```bash
nslookup 1stlifefinancial.com
```
Debe responder:
```
Server:  dns.google
Address:  8.8.8.8

Name:    1stlifefinancial.com
Address:  62.72.7.44
```

---

## 🔐 Paso 3: Instalar SSL (HTTPS) - OPCIONAL PERO RECOMENDADO

Una vez que el dominio esté funcionando (después de la propagación DNS), ejecuta:

```bash
ssh vps-1life 'sudo certbot --nginx -d 1stlifefinancial.com -d www.1stlifefinancial.com'
```

Esto instalará un certificado SSL gratuito de Let's Encrypt y tu sitio estará disponible en:
- ✅ https://1stlifefinancial.com
- ✅ https://www.1stlifefinancial.com

---

## 📋 Resumen de Configuración

### **DNS en Hostinger:**
```
A       @       62.72.7.44      14400
CNAME   www     1stlifefinancial.com    300
```

### **Nginx en VPS:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name 1stlifefinancial.com www.1stlifefinancial.com 62.72.7.44;
    
    root /var/www/1lifefinancial;
    index index.html index.php;
    
    location / {
        try_files $uri $uri/ =404;
    }
    
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
}
```

---

## ✅ Estado Actual

- ✅ VPS configurado y listo: `62.72.7.44`
- ✅ Nginx configurado para responder al dominio
- ✅ Archivos desplegados en `/var/www/1lifefinancial/`
- ✅ PHP funcionando correctamente
- ⏳ **FALTA:** Cambiar el registro A en Hostinger DNS
- ⏳ **FALTA:** Esperar propagación DNS (15 min - 24 hrs)
- ⏳ **OPCIONAL:** Instalar SSL después de propagación

---

## 🎯 Acceso Actual

**Mientras el DNS no se propague, puedes acceder por:**
- http://62.72.7.44 ✅ (Funciona ahora)
- http://srv1131803.hstgr.cloud ✅ (Funciona ahora)

**Después de cambiar el DNS en Hostinger:**
- http://1stlifefinancial.com ✅ (Funcionará después de propagación)
- http://www.1stlifefinancial.com ✅ (Funcionará después de propagación)

---

## 🚀 TODO ESTÁ LISTO EN EL VPS

Solo necesitas:
1. Cambiar el registro A en Hostinger: `84.32.84.32` → `62.72.7.44`
2. Esperar la propagación DNS
3. (Opcional) Instalar SSL con certbot
