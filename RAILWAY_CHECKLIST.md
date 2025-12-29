# ✅ CHECKLIST PARA SUBIR A RAILWAY

## Fase 1: Preparar Repositorio Git
- [ ] **1.1** Inicializar Git en la carpeta del proyecto
  ```powershell
  cd C:\xampp\htdocs\SERCOLTURBOT
  git init
  git config user.email "tu@email.com"
  git config user.name "Tu Nombre"
  ```

- [ ] **1.2** Crear repositorio en GitHub
  - Ve a https://github.com/new
  - Nombre: `sercolturbot`
  - Descripción: "Sistema de gestión de reservas de tours"
  - Privado/Público: Según prefieras
  - NO inicializes con README/License (ya tienes)

- [ ] **1.3** Agregar archivos y hacer commit
  ```powershell
  git add .
  git commit -m "Initial commit: SERCOLTURBOT ready for production"
  ```

- [ ] **1.4** Conectar con repositorio remoto
  ```powershell
  git remote add origin https://github.com/TU_USUARIO/sercolturbot.git
  git branch -M main
  git push -u origin main
  ```

---

## Fase 2: Crear y Configurar Proyecto en Railway
- [ ] **2.1** Crear cuenta en Railway
  - Ve a https://railway.app
  - Sign up con GitHub

- [ ] **2.2** Crear nuevo proyecto
  - Dashboard → "+ New Project"
  - Selecciona "Deploy from GitHub"
  - Autoriza Railway con tu GitHub
  - Busca "sercolturbot" y selecciona

- [ ] **2.3** Esperar despliegue inicial
  - Railway detectará PHP automáticamente
  - Verás logs de despliegue

---

## Fase 3: Añadir MySQL a Railway
- [ ] **3.1** Agregar servicio MySQL
  - En Dashboard → "+ Add Services"
  - Busca y selecciona "MySQL"
  - Click "Add"

- [ ] **3.2** Esperar que MySQL se inicialice
  - Verás variables de entorno generadas automáticamente:
    - `MYSQL_HOST`
    - `MYSQL_PORT`
    - `MYSQL_USER`
    - `MYSQL_PASSWORD`
    - `MYSQL_DATABASE`

---

## Fase 4: Configurar Variables de Entorno WhatsApp
- [ ] **4.1** Obtener Phone ID y Access Token de Meta
  - Ve a https://developers.facebook.com/
  - Tu App → WhatsApp → Configuración
  - Copia: **Phone Number ID**
  - Copia: **Access Token**

- [ ] **4.2** Agregar variables en Railway
  - En Dashboard → Settings → Variables
  - Añade:
    ```
    WHATSAPP_PHONE_ID = tu_phone_id_aqui
    WHATSAPP_ACCESS_TOKEN = tu_token_super_seguro_aqui
    FACEBOOK_PAGE_ACCESS_TOKEN = (opcional)
    INSTAGRAM_BUSINESS_ACCOUNT_ID = (opcional)
    INSTAGRAM_ACCESS_TOKEN = (opcional)
    APP_ENV = production
    APP_DEBUG = false
    ```

---

## Fase 5: Verificar Configuración en Railway
- [ ] **5.1** Ver logs de despliegue
  - Dashboard → Deployment → Logs
  - Busca "Database initializing..."
  - Confirma que dice "✅ Database initialized successfully"

- [ ] **5.2** Probar conexión a PHP
  - Railway te asignará un dominio (ej: `sercolturbot-production.up.railway.app`)
  - Ve a: `https://sercolturbot-production.up.railway.app/public/index.php`
  - Deberías ver tu app

- [ ] **5.3** Verificar conexión a BD
  - En tu navegador ve a:
    ```
    https://sercolturbot-production.up.railway.app/public/dashboard.php
    ```
  - Si carga sin errores de BD → ✅ Conectado

---

## Fase 6: Configurar Webhook de WhatsApp
- [ ] **6.1** Obtener URL de Railway
  - Dashboard → Networking
  - Copia tu dominio asignado
  - Ejemplo: `sercolturbot-production.up.railway.app`

- [ ] **6.2** Configurar Webhook en Meta
  - Ve a https://developers.facebook.com/
  - Tu App → WhatsApp → Configuración
  - En "Webhook URL", pon:
    ```
    https://sercolturbot-production.up.railway.app/routes/whatsapp_webhook.php
    ```
  - En "Verify Token", usa el mismo token que usas localmente
  - Suscribirse a eventos: `messages` y `message_template_status_update`

- [ ] **6.3** Verificar Webhook
  - Railway → Logs
  - Busca mensajes tipo "[Webhook] Token verificado"
  - Si ve errores 403 → Revisa el token

---

## Fase 7: Testear Funcionalidad
- [ ] **7.1** Test de envío WhatsApp
  ```powershell
  $body = @{
    action = "test-whatsapp"
    numero = "573011773292"  # Tu número
  } | ConvertTo-Json
  
  curl.exe -X POST `
    -H "Content-Type: application/json" `
    -d $body `
    https://sercolturbot-production.up.railway.app/public/dashboard-api.php
  ```

- [ ] **7.2** Test de Webhook
  - Envía un mensaje desde WhatsApp a tu número Business
  - Verifica en Railway → Logs
  - Deberías ver: "[Webhook] Mensaje recibido de ..."

- [ ] **7.3** Test de BD
  ```powershell
  # Desde Railway, ejecutar SQL
  # O mediante tu app: ir a /public/dashboard.php
  # Crear una reserva y verificar en BD
  ```

---

## Fase 8: Configurar Dominio Personalizado (Opcional)
- [ ] **8.1** Si tienes dominio propio
  - Railway → Networking → "+ New"
  - Agrega: `api.tudominio.com`
  - Configurar DNS en tu registrador:
    ```
    CNAME → sercolturbot-production.up.railway.app
    ```

- [ ] **8.2** Railway genera SSL automáticamente
  - Usa https:// (gratuito con Let's Encrypt)

---

## 🆘 Troubleshooting

### Error: "Cannot connect to MySQL"
```
Solución:
1. Ve a Railway → Services → MySQL
2. Copia todas las variables: MYSQL_HOST, MYSQL_USER, etc.
3. En tu proyecto → Settings → Variables
4. Verifica que estén todas presentes
5. Redeploy: Dashboard → Deployment → Redeploy
```

### Error: "WhatsApp token inválido"
```
Solución:
1. Ve a https://developers.facebook.com/
2. Tu App → Configuración → Access Token
3. Copia el token completo (sin espacios)
4. En Railway → Variables → WHATSAPP_ACCESS_TOKEN
5. Actualiza y redeploy
```

### Base de datos no se inicializa
```
Solución:
1. En Railway → Logs, busca "init-database.php"
2. Si ve error de conexión → Espera 30 segundos después de agregar MySQL
3. Redeploy manualmente: Deployment → Redeploy
```

### Webhook retorna 403
```
Solución:
1. En /routes/whatsapp_webhook.php, verifica variable $verify_token
2. En Meta Business, revisa que el token coincida
3. Si no coincide, actualiza en Meta y en tu código
```

---

## 📋 Archivos Creados/Modificados

| Archivo | Estado | Descripción |
|---------|--------|------------|
| `composer.json` | ✅ Creado | Dependencias PHP |
| `.gitignore` | ✅ Creado | Archivos ignorados en Git |
| `Procfile` | ✅ Creado | Instrucciones para Railway |
| `railway.json` | ✅ Creado | Config de Railway |
| `php.ini` | ✅ Creado | Configuración PHP para Railway |
| `.env.example` | ✅ Creado | Template de variables de entorno |
| `config/database.php` | ✅ Modificado | Lee variables de entorno |
| `config/config_empresarial.php` | ✅ Modificado | Lee credenciales WhatsApp de env |
| `setup/init-database.php` | ✅ Creado | Script de inicialización de BD |
| `GUIA_DEPLOY_RAILWAY.md` | ✅ Creado | Guía completa |

---

## 🚀 Resumen Rápido

**El sistema está listo para production. Solo necesitas:**

1. ✅ Subir código a GitHub
2. ✅ Conectar Railway con tu repo
3. ✅ Agregar variables de entorno (credenciales WhatsApp)
4. ✅ Railway automáticamente:
   - Detecta PHP
   - Configura servidor
   - Importa BD
   - Inicia la app

**Tiempo total: ~5-10 minutos**

¿Empezamos? ¿En qué paso estás?
