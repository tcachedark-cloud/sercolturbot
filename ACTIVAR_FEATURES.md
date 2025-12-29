# ⚡ GUÍA RÁPIDA - ACTIVAR CARACTERÍSTICAS EXISTENTES

## SERCOLTURBOT - Configuración de Funcionalidades

---

## 1️⃣ ACTIVAR NOTIFICACIONES POR EMAIL

### Paso 1: Configurar cuenta Gmail
1. Ir a [myaccount.google.com/security](https://myaccount.google.com/security)
2. Activar "Verificación en 2 pasos"
3. Ir a [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
4. Seleccionar App: **Mail**
5. Seleccionar Dispositivo: **Windows Computer**
6. Copiar contraseña generada (16 caracteres)

### Paso 2: Configurar en SERCOLTURBOT
Editar: `config/config_empresarial.php`

```php
'email' => [
    'habilitado' => true,  // ← Cambiar a true
    'host' => 'smtp.gmail.com',
    'puerto' => 587,
    'usuario' => 'tu_email@gmail.com',  // ← Tu Gmail
    'password' => 'xxxx xxxx xxxx xxxx',  // ← Contraseña de app
    'from_email' => 'noreply@sercoltur.com',
    'from_name' => 'SERCOLTUR',
],
```

### Paso 3: Probar envío
En terminal:
```bash
php -r "
require 'config/config_empresarial.php';
\$config = require 'config/config_empresarial.php';
mail('admin@sercoltur.com', 'Test', 'Email configurado correctamente', 
     'From: ' . \$config['email']['from_email']);
echo 'Email enviado!';
"
```

**Tiempo**: 10 minutos  
**Resultado**: ✅ Emails automáticos en reportes semanales

---

## 2️⃣ CONFIGURAR WHATSAPP, FACEBOOK, INSTAGRAM

### Opción A: Vía Dashboard

1. Ir a: `http://localhost/SERCOLTURBOT/public/dashboard.php`
2. Login con usuario admin
3. Ir a panel de FAQs (cuando esté implementado)
4. Crear nueva FAQ

### Opción B: Directamente en BD

```sql
INSERT INTO faqs (pregunta, respuesta_corta, respuesta, palabras_clave, activo) 
VALUES (
    '¿Cuál es el horario de atención?',
    'Lunes a viernes de 8am a 6pm',
    'Nuestro horario de atención es:\n- Lunes a Viernes: 8:00 AM a 6:00 PM\n- Sábado: 9:00 AM a 2:00 PM\n- Domingo: Cerrado',
    '["horario", "atencion", "abierto"]',
    1
);
```

### Opción C: Excel a Base de Datos
1. Crear Excel con columnas: pregunta, respuesta_corta, respuesta, palabras_clave
2. Exportar como CSV
3. Ejecutar:
```bash
mysql -u root -p sercolturbot < importar_faqs.sql
```

**Tiempo**: 30 minutos  
**Resultado**: ✅ FAQs automáticas en respuestas

---

## 4️⃣ CONFIGURAR RECORDATORIOS DE CITAS

### Paso 1: Crear tabla de recordatorios
```sql
ALTER TABLE citas ADD COLUMN recordatorio_enviado BOOLEAN DEFAULT 0;
```

### Paso 2: Crear script de recordatorios
Crear archivo: `cron/send_reminders.php`

```php
<?php
require_once __DIR__ . '/../config/database.php';

$pdo = new PDO("mysql:host=localhost;dbname=sercolturbot", "root", "C121672@c");

// Buscar citas próximas a recordar (60 minutos)
$stmt = $pdo->prepare("
    SELECT c.*, cl.telefono, cl.nombre
    FROM citas c
    JOIN clientes cl ON c.cliente_id = cl.id
    WHERE c.estado = 'confirmada'
    AND c.recordatorio_enviado = 0
    AND c.fecha_hora BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 65 MINUTE)
");

$stmt->execute();
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($citas as $cita) {
    $msg = "📋 RECORDATORIO DE CITA\n\n";
    $msg .= "⏰ Tu cita es en aproximadamente 60 minutos\n";
    $msg .= "🕐 Hora: " . substr($cita['fecha_hora'], 11, 5) . "\n";
    $msg .= "🎯 Servicio: {$cita['servicio']}\n\n";
    $msg .= "¿Necesitas cancelar o reprogramar?";
    
    // Enviar por WhatsApp
    enviarWhatsApp($cita['telefono'], $msg);
    
    // Marcar como enviado
    $pdo->prepare("UPDATE citas SET recordatorio_enviado = 1 WHERE id = ?")
        ->execute([$cita['id']]);
}

echo "Recordatorios enviados: " . count($citas);
?>
```

### Paso 3: Configurar Cron Job
**En Windows (Usar Task Scheduler)**:
1. Abrir Task Scheduler
2. Crear nueva tarea
3. Acción: `C:\xampp\php\php.exe C:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php`
4. Repetir cada 5 minutos

**En Linux**:
```bash
*/5 * * * * /usr/bin/php /home/usuario/SERCOLTURBOT/cron/send_reminders.php
```

**Tiempo**: 20 minutos  
**Resultado**: ✅ Recordatorios automáticos a clientes

---

## 5️⃣ GENERAR REPORTES SEMANALES

### Configuración automática
El sistema ya está configurado. Solo requiere cron job:

```bash
# Ejecutar todos los domingos a las 2 AM
0 2 * * 0 /usr/bin/php /ruta/SERCOLTURBOT/public/whatsapp-api.php?action=generar_reporte
```

### Verificar reportes generados
1. Ir a BD
2. Consultar tabla: `reportes`
3. Ver archivos en: `/public/reportes/`

**Resultado**: ✅ Reportes semanales automáticos

---

## 6️⃣ ACTIVAR IA AVANZADA (GPT-5 Mini)

### Paso 1: Configurar en código
Archivo: `public/whatsapp-api.php` (línea 16-21)

```php
$GPT5_MINI_CONFIG = [
    'habilitado' => true,  // ✅ Ya está habilitado
    'modelo' => 'gpt-5-mini',
    'descripcion' => 'IA Avanzada para respuestas inteligentes',
    'aplicable_a_todos' => true  // ← true = para todos los clientes
];
```

### Paso 2: Integrar con API OpenAI (Opcional)
Si quieres usar OpenAI en lugar del framework:

```php
function obtenerRespuestaIA($pregunta) {
    $apiKey = 'tu_openai_key';
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente de SERCOLTUR'],
                ['role' => 'user', 'content' => $pregunta]
            ]
        ]),
        CURLOPT_RETURNTRANSFER => true
    ]);
    
    $response = json_decode(curl_exec($ch), true);
    return $response['choices'][0]['message']['content'];
}
```

**Tiempo**: 15 minutos  
**Resultado**: ✅ Respuestas inteligentes automáticas

---

## 7️⃣ INTEGRAR GOOGLE CALENDAR

### Paso 1: Crear proyecto en Google Cloud
1. Ir a [console.cloud.google.com](https://console.cloud.google.com)
2. Crear nuevo proyecto: "SERCOLTURBOT"
3. Ir a APIs: habilitar "Google Calendar API"

### Paso 2: Crear credenciales OAuth
1. En API Settings → Credenciales
2. Crear OAuth 2.0 ID de cliente
3. Tipo: Aplicación web
4. URI autorizados: `http://localhost`, `https://tudominio.com`
5. Descargar JSON (guardar como: `config/google_credentials.json`)

### Paso 3: Configurar en SERCOLTURBOT
```php
'google_calendar' => [
    'habilitado' => true,  // ← Cambiar a true
    'credentials_path' => __DIR__ . '/google_credentials.json',
    'calendar_id' => 'primary', // o tu calendar ID
],
```

### Paso 4: Instalar Google Library
```bash
composer require google/apiclient
```

**Tiempo**: 45 minutos  
**Resultado**: ✅ Citas sincronizadas con Google Calendar

---

## 8️⃣ INTEGRAR PAGOS CON WOMPI

### Paso 1: Crear cuenta en Wompi
1. Ir a [wompi.co](https://wompi.co)
2. Registrar empresa
3. Crear API Keys (Public + Private)

### Paso 2: Configurar en SERCOLTURBOT
```php
'wompi' => [
    'habilitado' => true,  // ← Cambiar a true
    'ambiente' => 'sandbox',  // sandbox para tests, production para live
    'public_key' => 'pub_xxxxx',
    'private_key' => 'priv_xxxxx',
],
```

### Paso 3: Crear botón de pago en dashboard
En `public/dashboard.php`:
```php
<a href="crear_link_pago.php?reserva_id=<?= $reserva['id'] ?>" class="btn btn-success">
    💰 Generar Link de Pago
</a>
```

**Tiempo**: 1 hora  
**Resultado**: ✅ Pagos online integrados

---

## ✅ CHECKLIST DE ACTIVACIÓN

```
[ ] Email configurado (Gmail SMTP)
[ ] WhatsApp conectado (Meta Business)
[ ] Facebook configurado (Page Access Token)
[ ] Instagram conectado (Business Account)
[ ] FAQs agregadas a base de datos
[ ] Cron job de recordatorios configurado
[ ] Reportes semanales habilitados
[ ] IA avanzada activada
[ ] Google Calendar integrado
[ ] Wompi para pagos configurado
[ ] Dashboard accesible y funcional
[ ] WhatsApp Bot respondiendo
```

---

## 📊 VERIFICACIÓN FINAL

### Test 1: Enviar mensaje de prueba
```
Usuario escribe en WhatsApp: "Hola"
Bot responde en < 2 segundos: "¡Hola! 👋 Bienvenido a SERCOLTUR..."
```

### Test 2: Agendar cita
```
Usuario escribe: "Cita"
Bot inicia flujo de agendamiento ✅
```

### Test 3: Dashboard
```
Ir a: http://localhost/SERCOLTURBOT/public/dashboard.php
Login exitoso ✅
Datos actualizados en tiempo real ✅
```

### Test 4: Reportes
```
Verificar: public/reportes/
Última entrada en tabla `reportes` dentro de 7 días ✅
```

---

## 🚨 SOLUCIÓN DE PROBLEMAS

### "El bot no responde"
1. Verificar webhook en Meta
2. Revisar logs: `public/whatsapp_log.txt`
3. Confirmar base de datos activa

### "Email no se envía"
1. Verificar SMTP correctamente en `config_empresarial.php`
2. Ver error en PHP: `php -r "echo ini_get('SMTP');"`
3. Usar [mailtrap.io](https://mailtrap.io) para tests

### "FAQs no se muestran"
1. Verificar tabla: `SHOW TABLES LIKE 'faqs';`
2. Revisar palabras clave: `SELECT * FROM faqs;`
3. Asegurar `activo = 1`

### "Google Calendar no sincroniza"
1. Verificar archivo JSON en `config/`
2. Revisar credenciales válidas
3. Confirmar Calendar ID correcto

---

## 📞 SOPORTE

**Para ayuda**:
📧 info@sercoltur.com  
💬 +57 302 253 1580  
📚 Documentación: `/README.md`

---

**Última actualización**: 29/12/2025  
**Versión**: 1.0  
**Estado**: 🟢 Listo para producción
