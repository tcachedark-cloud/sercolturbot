# 🚀 Guía de Despliegue en Railway

## Requisitos Previos

1. ✅ **Cuenta en Railway** → [https://railway.app](https://railway.app)
2. ✅ **Repositorio Git** (GitHub, GitLab o Gitbucket)
3. ✅ **Credenciales WhatsApp Cloud API**
   - Phone ID
   - Access Token
4. ✅ **Dominio (opcional)** - Railway proporciona uno gratuito

---

## Paso 1: Preparar el Repositorio Git

### 1.1 Inicializar Git (si no lo tienes)
```powershell
cd C:\xampp\htdocs\SERCOLTURBOT
git init
git add .
git commit -m "Initial commit: SERCOLTURBOT system"
```

### 1.2 Crear repositorio en GitHub
1. Ve a [https://github.com/new](https://github.com/new)
2. Nombre: `sercolturbot`
3. Descripción: "Sistema de gestión de reservas de tours con WhatsApp"
4. Público o Privado (recomendado: Privado)
5. NO inicializar con README (ya tienes)
6. Crear repositorio

### 1.3 Conectar a GitHub
```powershell
git remote add origin https://github.com/TU_USUARIO/sercolturbot.git
git branch -M main
git push -u origin main
```

---

## Paso 2: Crear Proyecto en Railway

### 2.1 Crear un nuevo proyecto
1. Ve a [https://railway.app/dashboard](https://railway.app/dashboard)
2. Click en **"+ New Project"**
3. Selecciona **"Deploy from GitHub"**
4. Autoriza Railway con tu cuenta GitHub
5. Busca y selecciona el repositorio `sercolturbot`
6. Click en **"Deploy"**

### 2.2 Railway detectará automáticamente:
- ✅ PHP (por los archivos .php)
- ✅ MySQL (lo añadiremos manualmente)

---

## Paso 3: Configurar Base de Datos MySQL

### 3.1 Añadir MySQL a tu proyecto Railway
1. En el Dashboard de Railway, ve a tu proyecto
2. Click en **"+ Add Services"**
3. Selecciona **"MySQL"**
4. Click en **"Add"**
5. Railway creará automáticamente variables de entorno:
   ```
   MYSQL_HOST=
   MYSQL_PASSWORD=
   MYSQL_ROOT_PASSWORD=
   MYSQL_DATABASE=
   MYSQL_USER=
   ```

### 3.2 Conectar PHP a MySQL en Railway
Edita el archivo `config/database.php`:

```php
<?php
// Detectar si estamos en Railway (producción) o local (desarrollo)
if ($_ENV['RAILWAY_ENVIRONMENT_NAME'] ?? false) {
    // En Railway
    define('DB_HOST', $_ENV['MYSQL_HOST']);
    define('DB_USER', $_ENV['MYSQL_USER']);
    define('DB_PASSWORD', $_ENV['MYSQL_PASSWORD']);
    define('DB_NAME', $_ENV['MYSQL_DATABASE']);
} else {
    // En local (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASSWORD', 'C121672@c');
    define('DB_NAME', 'sercolturbot');
}

define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', $_ENV['MYSQL_PORT'] ?? 3306);

try {
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
?>
```

---

## Paso 4: Configurar Variables de Entorno

### 4.1 En el Dashboard de Railway
1. Ve a tu proyecto → **Settings** → **Variables**
2. Añade estas variables:

```
WHATSAPP_PHONE_ID = tu_phone_id
WHATSAPP_ACCESS_TOKEN = tu_token_seguro
FACEBOOK_PAGE_ACCESS_TOKEN = tu_facebook_token
INSTAGRAM_BUSINESS_ACCOUNT_ID = tu_instagram_id
INSTAGRAM_ACCESS_TOKEN = tu_instagram_token
APP_ENV = production
APP_DEBUG = false
```

### 4.2 Leerlas en PHP
```php
<?php
// En config/config_empresarial.php o dashboard-api.php
define('WHATSAPP_PHONE_ID', $_ENV['WHATSAPP_PHONE_ID'] ?? '');
define('WHATSAPP_ACCESS_TOKEN', $_ENV['WHATSAPP_ACCESS_TOKEN'] ?? '');
define('FACEBOOK_TOKEN', $_ENV['FACEBOOK_PAGE_ACCESS_TOKEN'] ?? '');
?>
```

---

## Paso 5: Importar Base de Datos

### 5.1 Crear script de inicialización
Crea archivo `setup/init-database.php`:

```php
<?php
// Este script se ejecuta automáticamente en Railway
require_once __DIR__ . '/../config/database.php';

// Importar esquema de base de datos
$sqlFile = __DIR__ . '/database.sql';
if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    
    // Dividir por `;` y ejecutar cada statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "[✓] Ejecutado: " . substr($statement, 0, 50) . "...\n";
            } catch (Exception $e) {
                echo "[✗] Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n✅ Base de datos inicializada correctamente\n";
} else {
    echo "⚠️  No se encontró setup/database.sql\n";
}
?>
```

### 5.2 Ejecutar desde Railway
```bash
# En la sección de "Deployment" en Railway
# O manually via SSH
php setup/init-database.php
```

---

## Paso 6: Configurar Webhook de WhatsApp

### 6.1 Obtener URL de Railway
En tu Dashboard de Railway:
1. Ve a tu proyecto
2. En "Networking", copia la **Railway-assigned domain**
   Ejemplo: `sercolturbot-production.up.railway.app`

### 6.2 Configurar Webhook en Meta Business
1. Ve a [Facebook Developers](https://developers.facebook.com/)
2. App → Configuración → Webhooks
3. URL del Webhook:
   ```
   https://sercolturbot-production.up.railway.app/routes/whatsapp_webhook.php
   ```
4. Token de verificación: Tu token seguro (mismo que usas localmente)
5. Suscribirse a eventos:
   - messages
   - message_template_status_update

### 6.3 Verificar en Railway
En `routes/whatsapp_webhook.php` asegúrate que esté leyendo variables de entorno:

```php
<?php
// Verificar token
$token = $_ENV['WEBHOOK_VERIFY_TOKEN'] ?? 'tu_token_aqui';

if ($_GET['hub_token'] !== $token) {
    http_response_code(403);
    die('Token inválido');
}
// ... resto del webhook
?>
```

---

## Paso 7: Monitoreo y Logs

### 7.1 Ver logs en Railway
```bash
# En Railway Dashboard → Deployment → Logs
# O vía CLI:
railway logs -f
```

### 7.2 Crear logs estructurados
```php
<?php
function logToFile($message, $type = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] [$type] $message\n";
    
    // En Railway, usar directorio temporal o cloudinary para logs
    if (getenv('RAILWAY_ENVIRONMENT_NAME')) {
        error_log($logMessage);
    } else {
        file_put_contents(__DIR__ . '/../logs/app.log', $logMessage, FILE_APPEND);
    }
}
?>
```

---

## Paso 8: Verificación Final

### Checklist antes de ir a producción:
- [ ] ✅ Repositorio Git actualizado
- [ ] ✅ Archivos sensibles (.env) en .gitignore
- [ ] ✅ Variables de entorno configuradas en Railway
- [ ] ✅ Base de datos MySQL importada
- [ ] ✅ Webhook configurado en Meta
- [ ] ✅ Conexión a la BD desde Railway funcionando
- [ ] ✅ WhatsApp enviando mensajes correctamente
- [ ] ✅ HTTPS funciona (Railway lo proporciona)

### Prueba de envío WhatsApp
```bash
# Curlear tu API desde Railway
curl -X POST https://sercolturbot-production.up.railway.app/public/dashboard-api.php \
  -H "Content-Type: application/json" \
  -d '{"action":"test-whatsapp","numero":"573011773292"}'
```

---

## Paso 9: Dominio Personalizado (Opcional)

1. En Railway → Networking → Custom Domain
2. Añade tu dominio (ej: `api.tudominio.com`)
3. Configura DNS en tu registrador:
   ```
   CNAME → sercolturbot-production.up.railway.app
   ```
4. Railway genera certificado SSL automáticamente (Let's Encrypt)

---

## 🚨 Troubleshooting

### Error: "Cannot connect to MySQL"
```php
// Verificar variables
echo $_ENV['MYSQL_HOST']; 
echo $_ENV['MYSQL_USER'];
```

### Error: "PHP not found"
→ Asegúrate que Railway detectó `Procfile` y `public/` como webroot

### Error: "Webhook inválido"
→ Verifica que `whatsapp_webhook.php` retorna `{"message":"ok"}` en GET con token válido

### Base de datos no se importa automáticamente
→ Crea un script `setup/post-deploy.sh`:
```bash
#!/bin/bash
php setup/init-database.php
```

---

## 📞 Soporte

- **Railway Docs**: https://docs.railway.app
- **PHP en Railway**: https://docs.railway.app/guides/php
- **Meta WhatsApp API**: https://developers.facebook.com/docs/whatsapp/

---

**¿Necesitas ayuda en algún paso específico?** Avísame cuál y lo hacemos juntos.
