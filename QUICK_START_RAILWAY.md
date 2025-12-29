# ⚡ QUICK START: SUBIR A PRODUCTION EN 15 MINUTOS

**⏱️ Tiempo total: ~15 minutos**  
**📊 Complejidad: Muy fácil**  
**🎯 Resultado: Tu app en https://tuapp.up.railway.app**

---

## 🚀 PASO 1: GitHub (5 min)

### A) Crear repositorio
1. https://github.com/new
2. Nombre: `sercolturbot`
3. Descripción: `Sistema de gestión de tours`
4. **Click "Create repository"**

### B) Subir código
```powershell
cd C:\xampp\htdocs\SERCOLTURBOT

git remote add origin https://github.com/TU_USUARIO/sercolturbot.git
git branch -M main
git push -u origin main
```

**Pide usuario/contraseña:**
- Usuario: Tu usuario GitHub
- Contraseña: Tu Personal Access Token
  - Generar en: https://github.com/settings/tokens/new
  - Permisos: Solo marca "repo"

---

## 🚀 PASO 2: Railway (5 min)

### A) Conectar con GitHub
1. https://railway.app
2. **Sign up** con GitHub (o inicia sesión)
3. **"+ New Project"**
4. **"Deploy from GitHub"**
5. Autoriza Railway
6. Busca y selecciona: **sercolturbot**
7. **Click Deploy**

### B) Esperar despliegue
- Ves logs en vivo
- Espera hasta ver "✅ Build successful"
- **Tarda ~2 min**

---

## 🚀 PASO 3: Agregar MySQL (2 min)

### A) En tu proyecto Railway
1. Click **"+ Add Services"**
2. Busca **MySQL**
3. Click **"Add"**
4. Esperar ~30 segundos

**Railway genera automáticamente:**
- `MYSQL_HOST`
- `MYSQL_USER`
- `MYSQL_PASSWORD`
- `MYSQL_DATABASE`

---

## 🚀 PASO 4: Configurar Variables WhatsApp (2 min)

### A) Obtener credenciales Meta
1. https://developers.facebook.com/
2. Tu App → WhatsApp → Configuración
3. **Copiar:**
   - Phone Number ID (ej: `123456789012345`)
   - Access Token (ej: `EAA...`)

### B) Agregar en Railway
1. Tu proyecto → **Settings** → **Variables**
2. Click **"+ New Variable"**
3. Agregar:

```
WHATSAPP_PHONE_ID = [tu Phone ID]
WHATSAPP_ACCESS_TOKEN = [tu Access Token]
APP_ENV = production
APP_DEBUG = false
```

4. Click **"Redeploy"** en Deployments
5. Esperar ~1 minuto

---

## ✅ LISTO!

### Tu app está en:
```
https://sercolturbot-production.up.railway.app
```

### Verificar que funciona:
1. Abre esa URL en navegador
2. Deberías ver tu dashboard
3. Si funciona → ✅ Éxito

---

## 📞 Problemas Rápidos?

| Problema | Solución |
|----------|----------|
| GitHub pide contraseña | Usa Personal Access Token, no contraseña normal |
| Railway no detecta PHP | Verifica que existe `Procfile` en raíz |
| MySQL no conecta | Redeploy después de agregar variables |
| Página en blanco | Revisa Logs en Railway Dashboard |

---

## 📖 Documentación Completa

Si necesitas más detalle:
- `SUBIR_GITHUB_RAILWAY.md` - Paso a paso con imágenes mentales
- `GUIA_DEPLOY_RAILWAY.md` - Guía completa (profesional)
- `OBTENER_CREDENCIALES_WHATSAPP.md` - Detalles de credenciales

---

**¡Eso es todo! Tu app está en production.** 🎉
