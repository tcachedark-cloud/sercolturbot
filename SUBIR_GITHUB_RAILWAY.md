# 📤 SUBIR A GITHUB Y RAILWAY EN 5 PASOS

**Estado Actual:** Tu código está en Git local, listo para subir a GitHub  
**Tiempo estimado:** 5 minutos

---

## ✅ Paso 1: Crear Repositorio en GitHub

### 1.1 Ve a GitHub
- URL: https://github.com/new
- **Inicia sesión** con tu cuenta (o crea una en github.com)

### 1.2 Crear Nuevo Repositorio
```
Repository name:     sercolturbot
Description:         Sistema de gestión de reservas de tours con WhatsApp
Visibility:          Public (o Private si lo prefieres)
Initialize:          NO marques nada (ya tenemos código)
```

### 1.3 Click en "Create Repository"
- GitHub mostrará instrucciones para subir código
- **Copia las primeras 2 líneas:**

---

## ✅ Paso 2: Conectar Repositorio Local con GitHub

### 2.1 Abre PowerShell en tu proyecto

```powershell
cd C:\xampp\htdocs\SERCOLTURBOT
```

### 2.2 Ejecuta estos comandos (reemplaza USUARIO con tu usuario GitHub):

```powershell
git remote add origin https://github.com/TU_USUARIO/sercolturbot.git
git branch -M main
git push -u origin main
```

### 2.3 Completa la autenticación
Si te pide usuario/contraseña:
- **Usuario:** Tu usuario de GitHub
- **Contraseña:** Tu **Personal Access Token** (no contraseña normal)

#### Para generar Personal Access Token:
1. https://github.com/settings/tokens/new
2. Selecciona permisos: `repo` (acceso completo a repositorios)
3. Copia el token
4. Úsalo como "contraseña" en git

**Alternativa:** Usa autenticación SSH (más seguro, pero más complicado)

---

## ✅ Paso 3: Verificar en GitHub

### 3.1 Ve a tu repositorio
```
https://github.com/TU_USUARIO/sercolturbot
```

### 3.2 Deberías ver:
- ✅ Todos tus archivos PHP, config, documentación
- ✅ 88 archivos, 19 KB
- ✅ Commit: "Initial commit: SERCOLTURBOT Production Ready..."

---

## ✅ Paso 4: Conectar Railway con GitHub

### 4.1 Ve a Railway
- URL: https://railway.app
- **Inicia sesión o Sign Up** con GitHub

### 4.2 Crear Nuevo Proyecto
1. Click **"+ New Project"**
2. Selecciona **"Deploy from GitHub"**
3. Autoriza Railway con tu GitHub
4. Busca **"sercolturbot"**
5. Selecciona el repositorio
6. Click **"Deploy"**

### 4.3 Railway automáticamente:
- Detecta PHP
- Clona tu repositorio
- Construye la aplicación
- Ves los logs en tiempo real

**Tiempo:** ~2 minutos

---

## ✅ Paso 5: Agregar MySQL y Variables

### 5.1 Mientras Railway construye, abre otra pestaña
- Railway Dashboard → Tu proyecto

### 5.2 Agregar MySQL
1. Click **"+ Add Services"**
2. Busca **"MySQL"**
3. Click **"Add"**
4. Railway lo configura en ~30 segundos

### 5.3 Agregar Variables de Entorno
1. En tu proyecto → **Settings** → **Variables**
2. Click **"+ New Variable"**
3. Agregar estas variables:

```
WHATSAPP_PHONE_ID=123456789012345
WHATSAPP_ACCESS_TOKEN=EAAxxxxxxxxxxxxx
FACEBOOK_PAGE_ACCESS_TOKEN=(opcional)
INSTAGRAM_BUSINESS_ACCOUNT_ID=(opcional)
INSTAGRAM_ACCESS_TOKEN=(opcional)
APP_ENV=production
APP_DEBUG=false
```

### 5.4 Redeploy
- Click en **"Deployments"** → **"Redeploy"**
- Espera a ver "✅ Deployment successful"

---

## 🎉 ¡Listo!

Tu app está en production en:
```
https://sercolturbot-production.up.railway.app
```

---

## ✅ Verificación Final

### Checklist de Éxito:
- [ ] ✅ Repositorio creado en GitHub
- [ ] ✅ Código pusheado a GitHub
- [ ] ✅ Railway conectado con GitHub
- [ ] ✅ Despliegue en Railway completado
- [ ] ✅ MySQL agregado y corriendo
- [ ] ✅ Variables de entorno agregadas
- [ ] ✅ App accesible en https://sercolturbot-production.up.railway.app
- [ ] ✅ Base de datos inicializada (revisar Logs)

---

## 🆘 Problemas Comunes

### "Error: Cannot push to GitHub"
```
Solución:
1. Verifica que tu Personal Access Token es válido
2. Si expira → Genera uno nuevo
3. O usa SSH en lugar de HTTPS
```

### "Railway dice: 'No Procfile found'"
```
Solución:
Verificar que existe Procfile en la raíz del proyecto
Command: ls Procfile
Si no existe: Revisa que lo copiaste bien
```

### "Database connection failed"
```
Solución:
1. En Railway → Services → MySQL → Click
2. Verifica que está "Running"
3. Si está rojo → Haz click en restart
4. Redeploy tu app: Deployments → Redeploy
5. Aguarda 30 segundos
```

### "Variables no se aplican"
```
Solución:
1. Verifica que las agregaste en Settings → Variables
2. Haz Redeploy después de agregar variables
3. En Logs, busca que diga "Using env: production"
```

---

## 📊 Monitoreo

### Ver logs en vivo:
```powershell
# Si tienes Railway CLI instalado:
railway logs -f

# O en el Dashboard:
# Tu Proyecto → Deployments → Click en el despliegue → Logs
```

### Probar tu app:
```powershell
curl https://sercolturbot-production.up.railway.app/public/index.php

# Deberías ver HTML de tu app
```

### Ver estatus de MySQL:
```powershell
# En Railway Dashboard → Services → MySQL
# Deberías ver "Running" en verde
```

---

## 🔄 Hacer cambios futuros

Cada vez que quieras hacer cambios:

```powershell
# 1. Haz cambios en tu código
# 2. Commit y push a GitHub:
git add .
git commit -m "Tu descripción de cambios"
git push origin main

# 3. Railway automáticamente:
#    - Detecta el push
#    - Reconstruye la app
#    - Hace deploy
#    - Verás los logs en Dashboard
```

**Total: 5 minutos después del push**

---

## 🎓 Próximo: Configurar Dominio Personalizado (Opcional)

Si tienes un dominio propio (ej: sercoltur.com):

1. Railway → Networking → "+ New Domain"
2. Agrega: `api.sercoltur.com` (o la URL que quieras)
3. Railway genera certificado SSL automáticamente
4. Configura en tu registrador:
   ```
   CNAME sercoltur-production.up.railway.app
   ```

**Tiempo:** 2 minutos

---

## 📞 Soporte

- **Railway Docs:** https://docs.railway.app
- **GitHub Docs:** https://docs.github.com
- **Mi Guía de Deploy:** Ver `GUIA_DEPLOY_RAILWAY.md`

---

**¿Listo para subir?** 🚀

**Si tienes dudas en cualquier paso, avísame.**
