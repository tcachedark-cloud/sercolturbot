# 🧪 TESTING Y CONFIGURACIÓN - SERCOLTURBOT

**Documento:** Guía completa de testing y configuración  
**Fecha:** 2025-01-14  
**Versión:** 1.0

---

## 🔧 CONFIGURACIÓN STEP-BY-STEP

### 1. Actualizar `config/config_empresarial.php`

Editar el archivo y cambiar los valores:

```php
<?php

// ═══════════════════════════════════════════════════════════════
// CONFIGURACIÓN PARA EMAIL
// ═══════════════════════════════════════════════════════════════
$EMPRESA_CONFIG['email'] = [
    'habilitado' => true,  // ← CAMBIAR A TRUE
    'host' => 'smtp.gmail.com',
    'puerto' => 587,
    'usuario' => 'tu_email@gmail.com',  // ← TU EMAIL AQUÍ
    'password' => 'tu_app_password',     // ← CONTRASEÑA DE APP
    'from_email' => 'notificaciones@sercoltur.com',
    'from_name' => 'SERCOLTUR'
];

// ═══════════════════════════════════════════════════════════════
// CONFIGURACIÓN PARA META BUSINESS (WhatsApp, Facebook, Instagram)
// ═══════════════════════════════════════════════════════════════
$EMPRESA_CONFIG['whatsapp'] = [
    'habilitado' => true,
    'phone_number_id' => 'ID_DE_META',  // ← OBTENER DE META BUSINESS
    'access_token' => 'TOKEN_DE_META'   // ← TOKEN DE ACCESO
];

$EMPRESA_CONFIG['facebook'] = [
    'habilitado' => false,
    'page_access_token' => ''
];

$EMPRESA_CONFIG['instagram'] = [
    'habilitado' => false,
    'business_account_id' => '',
    'access_token' => ''
];

// El resto permanece igual...
?>
```

---

## 📧 CONFIGURAR GMAIL SMTP

### Paso 1: Habilitar Verificación en 2 Pasos
1. Ir a https://myaccount.google.com/
2. Click en "Seguridad" (izquierda)
3. Scroll a "Verificación en 2 pasos"
4. Habilitar

### Paso 2: Crear Contraseña de Aplicación
1. En Security → App passwords
2. Seleccionar: Correo → Windows
3. Google generará una contraseña de 16 caracteres
4. **COPIAR Y GUARDAR EN CONFIG**

Ejemplo de contraseña: `xxxx xxxx xxxx xxxx`

---

## 🤖 CONFIGURAR META BUSINESS (WhatsApp/Facebook/Instagram)

### Paso 1: Crear Cuenta en Meta Business Manager
1. Ir a https://business.facebook.com/
2. Crear o iniciar sesión en cuenta de negocio
3. Agregar WhatsApp Business Account
4. Obtener Phone Number ID y Access Token

### Paso 2: Configurar Webhook
1. En settings → Webhooks
2. Configurar URL de webhook: `https://tudominio.com/public/whatsapp-api.php`
3. Crear token de verificación
4. Guardar configuración

---

## 🧪 TESTING MANUAL

### Test 1: Enviar Email

**Archivo de prueba:** `tests/test_email.php`

```php
<?php
require_once('../config/database.php');
require_once('../services/EmailService.php');

$pdo = getDatabase();
$email = new EmailService($pdo);

// Test: Enviar recordatorio de cita
$cliente = [
    'nombre' => 'Juan Pérez',
    'email' => 'tu_email@example.com'
];

$cita = [
    'fecha_hora' => '2025-02-15 14:30:00',
    'servicio' => 'Consultoría',
    'codigo' => 'CITA-250214-1234'
];

$resultado = $email->enviarRecordatorioCita($cliente, $cita);

echo "Status: " . ($resultado['success'] ? 'OK ✓' : 'ERROR ✗') . "\n";
echo "Email: " . $resultado['para'] . "\n";
echo "Respuesta: " . json_encode($resultado) . "\n";
?>
```

**Ejecutar:**
```powershell
php tests/test_email.php
```

**Resultado esperado:**
```
Status: OK ✓
Email: tu_email@example.com
Respuesta: {"success":true,"para":"tu_email@example.com",...}
```

---

### Test 2: Validar WhatsApp API

**Archivo:** `public/whatsapp-api.php`

Asegúrate de tener:
```php
$NOTIFICACIONES_CONFIG = [
    'whatsapp' => true,
    'email' => true,
    'facebook' => false,
    'instagram' => false,
]
```

**Test Manual:**
1. Usar Postman o curl
2. Enviar POST a `http://localhost/SERCOLTURBOT/public/whatsapp-api.php`
3. Con body:
```json
{
    "messages": [{
        "from": "+573022531580",
        "text": "Hola bot"
    }]
}
```

**Resultado esperado:** Respuesta del bot en WhatsApp
?>
```

**Ejecutar:**
```powershell
php tests/test_telegram.php
```

---

### Test 3: Recordatorios Automáticos

**Ejecutar manualmente:**
```powershell
php cron/send_reminders.php
```

**Log esperado:**
```
[2025-01-14 14:30:00] ✓ Sin citas para recordar en este momento
[2025-01-14 14:30:00] ✅ Recordatorios completados
```

---

## 📋 CHECKLIST DE CONFIGURACIÓN

### Email
- [ ] Actualizar `config_empresarial.php` con habilitado=true
- [ ] Ingresar email de Gmail
- [ ] Ingresar contraseña de aplicación
- [ ] Ejecutar test_email.php
- [ ] Verificar recepción en inbox

### Meta Business (WhatsApp, Facebook, Instagram)
- [ ] Crear cuenta en https://business.facebook.com/
- [ ] Obtener Phone Number ID de WhatsApp
- [ ] Obtener Access Token de Meta
- [ ] Actualizar config_empresarial.php
- [ ] Configurar Webhook en Meta
- [ ] Verificar recepción en WhatsApp

### FAQs
- [ ] Acceder a `/admin/faqs.php`
- [ ] Crear mínimo 3 FAQs de prueba
- [ ] Prueba: Crear, Editar, Desactivar, Eliminar

### Recordatorios
- [ ] Ejecutar script manualmente
- [ ] Verificar log en `logs/reminders.log`
- [ ] Configurar Windows Task Scheduler

---

## ⏱️ CONFIGURAR WINDOWS TASK SCHEDULER

### Crear Tarea para Recordatorios

**Paso 1: Abrir Task Scheduler**
- Windows + R
- Escribir: `taskschd.msc`
- Enter

**Paso 2: Crear Tarea Nueva**
1. Click derecho → "Create Basic Task"
2. Nombre: `SERCOLTUR Recordatorios`
3. Descripción: `Enviar recordatorios automáticos de citas`
4. Click "Next"

**Paso 3: Trigger (Cuándo ejecutar)**
1. Seleccionar: "Repeat a task"
2. Frecuencia: "Daily"
3. Repeate every: `5 minutes`
4. Click "Next"

**Paso 4: Action (Qué ejecutar)**
1. Seleccionar: "Start a program"
2. Program: `C:\xampp\php\php.exe`
3. Arguments: `C:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php`
4. Click "Next"

**Paso 5: Finish**
1. Check: "Open the Properties dialog"
2. Click "Finish"

**En Properties:**
- Tab "General":
  - Check: "Run whether user is logged in or not"
  - Run with highest privileges: YES
  
- Tab "Triggers":
  - Edit → Repeat task every 5 minutes
  - For a duration of: "Indefinitely"

- Tab "Conditions":
  - Uncheck: "Stop if the computer switches to battery power"

- Click "OK"

---

## 🔍 MONITOREO Y LOGS

### Ver logs de Email
```powershell
Get-Content "c:\xampp\htdocs\SERCOLTURBOT\public\email_log.txt" -Tail 20
```

### Ver logs de Recordatorios
```powershell
Get-Content "c:\xampp\htdocs\SERCOLTURBOT\logs\reminders.log" -Tail 50
```

### Ver logs del Bot
```powershell
Get-Content "c:\xampp\htdocs\SERCOLTURBOT\public\whatsapp_log.txt" -Tail 30
```

---

## 🐛 TROUBLESHOOTING

### "SMTP Connection Failed"
```
Problema: Email no envía
Solución:
  1. Verificar contraseña de app en Gmail
  2. Verificar puerto 587 no bloqueado
  3. Verificar habilitado=true
  4. Probar con otro email para descartar blockers
```

### "Meta API Error"
```
Problema: WhatsApp/Facebook/Instagram no envía mensajes
Solución:
  1. Verificar access_token válido
  2. Verificar phone_number_id correcto
  3. Verificar webhook configurado en Meta
  4. Revisar logs de Meta Business Manager
```

### "Task Scheduler no ejecuta"
```
Problema: Cron job no corre
Solución:
  1. Verificar path absoluto correcto
  2. Ejecutar manualmente para validar
  3. Check "Run with highest privileges"
  4. Ver Event Viewer para errores
```

### "Tabla citas no existe"
```
Problema: Error en recordatorios
Solución:
  1. Ejecutar setup/database.sql
  2. O ejecutar UPDATE script:
     mysql -u root -p"C121672@c" sercolturbot < setup/update_reminders_schema.sql
```

---

## 📊 DASHBOARD DE MONITOREO

Crear archivo `public/monitor.php` para monitoreo en tiempo real:

```php
<?php
session_start();
if (!isset($_SESSION['admin_loggedin'])) die('Acceso denegado');

$logs = [
    'email' => file_exists('../public/email_log.txt') 
        ? array_slice(file('../public/email_log.txt'), -10) 
        : [],
    'reminders' => file_exists('../logs/reminders.log') 
        ? array_slice(file('../logs/reminders.log'), -10) 
        : [],
    'bot' => file_exists('../public/whatsapp_log.txt') 
        ? array_slice(file('../public/whatsapp_log.txt'), -10) 
        : []
];

header('Content-Type: application/json');
echo json_encode($logs, JSON_PRETTY_PRINT);
?>
```

Acceder en: `http://localhost/SERCOLTURBOT/public/monitor.php`

---

## ✅ VALIDACIÓN FINAL

Antes de ir a producción:

```powershell
# Test 1: PHP Syntax
php -l c:\xampp\htdocs\SERCOLTURBOT\public\whatsapp-api.php
php -l c:\xampp\htdocs\SERCOLTURBOT\services\EmailService.php

# Test 2: Base de datos
mysql -u root -p"C121672@c" sercolturbot -e "SELECT * FROM faqs LIMIT 1;"
mysql -u root -p"C121672@c" sercolturbot -e "SELECT * FROM citas LIMIT 1;"

# Test 3: Servicios
php c:\xampp\htdocs\SERCOLTURBOT\tests\test_email.php
php c:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php
```

---

**Documentación generada:** 2025-01-14  
**Última actualización:** Phase 1 Completado
