# 🚀 PLAN DE IMPLEMENTACIÓN - CARACTERÍSTICAS PENDIENTES

## SERCOLTURBOT - Roadmap 2026

---

## FASE 1: COMPLETAR LO EXISTENTE (1-2 semanas)

### 1️⃣ Activar Notificaciones por Email
**Archivo**: `config/config_empresarial.php`

```php
// CAMBIAR DE:
'email' => [
    'habilitado' => false,
    ...
]

// A:
'email' => [
    'habilitado' => true,
    'host' => 'smtp.gmail.com',
    'puerto' => 587,
    'usuario' => 'tu@gmail.com',
    'password' => 'tu_contraseña_aplicacion',
    'from_email' => 'noreply@sercoltur.com',
    'from_name' => 'SERCOLTUR'
]
```

**Pasos**:
1. Crear cuenta Gmail
2. Activar autenticación de 2 factores
3. Generar contraseña de aplicación
4. Guardar credenciales en `config_empresarial.php`

**Tiempo estimado**: 30 minutos

---

### 2️⃣ Implementar Recordatorios Automáticos
**Ubicación**: Crear archivo `services/ReminderService.php`

```php
<?php
class ReminderService {
    private $pdo;
    private $config;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->config = require(__DIR__ . '/../config/config_empresarial.php');
    }
    
    public function enviarRecordatorios() {
        // Buscar citas en los próximos 60 minutos
        $stmt = $this->pdo->prepare("
            SELECT c.*, cl.telefono 
            FROM citas c
            JOIN clientes cl ON c.cliente_id = cl.id
            WHERE c.estado = 'confirmada'
            AND c.fecha_hora BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 60 MINUTE)
            AND c.recordatorio_enviado = 0
        ");
        
        $stmt->execute();
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($citas as $cita) {
            $this->enviarRecordatorio($cita);
        }
    }
    
    private function enviarRecordatorio($cita) {
        $msg = "📋 RECORDATORIO DE CITA\n\n";
        $msg .= "👤 {$cita['nombre']}\n";
        $msg .= "🎯 Servicio: {$cita['servicio']}\n";
        $msg .= "⏰ En: 60 minutos\n";
        $msg .= "🕐 Hora: {$cita['fecha_hora']}\n\n";
        $msg .= "¿Confirmas tu asistencia?";
        
        // Enviar por WhatsApp
        enviarWhatsApp($cita['telefono'], $msg);
        
        // Marcar como enviado
        $this->pdo->prepare("UPDATE citas SET recordatorio_enviado = 1 WHERE id = ?")
            ->execute([$cita['id']]);
    }
}
?>
```

**Cron Job** (ejecutar cada 5 minutos):
```bash
*/5 * * * * php /ruta/a/SERCOLTURBOT/services/ReminderService.php
```

**Tiempo estimado**: 45 minutos

---

### 3️⃣ Completar Sistema de FAQs
**Archivo**: Crear `admin/faqs.php`

```php
<?php
session_start();
if (!isset($_SESSION['user_id'])) header('Location: ../public/login.php');

$pdo = new PDO("mysql:host=localhost;dbname=sercolturbot", "root", "C121672@c");

// CRUD de FAQs
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'crear') {
        $stmt = $pdo->prepare("
            INSERT INTO faqs 
            (pregunta, respuesta_corta, respuesta, palabras_clave, activo) 
            VALUES (?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $_POST['pregunta'],
            $_POST['respuesta_corta'],
            $_POST['respuesta'],
            json_encode(explode(',', $_POST['palabras_clave']))
        ]);
    }
}

// Obtener FAQs
$faqs = $pdo->query("SELECT * FROM faqs ORDER BY veces_consultada DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestionar FAQs - SERCOLTURBOT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Segoe UI, sans-serif; background: #0d0d14; color: #e0e0e0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { padding: 20px; background: #1a1a2e; border-bottom: 1px solid #252545; margin-bottom: 20px; }
        .faq-grid { display: grid; gap: 15px; }
        .faq-card { background: #1a1a2e; padding: 20px; border-radius: 8px; border-left: 3px solid #6366f1; }
        .faq-card h3 { color: #6366f1; margin-bottom: 10px; }
        .faq-stats { font-size: 12px; color: #888; margin-top: 10px; }
        .btn { padding: 8px 15px; background: #6366f1; color: #fff; border: none; border-radius: 6px; cursor: pointer; }
        .btn-danger { background: #ef4444; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📚 Gestionar FAQs</h1>
    </div>
    
    <div class="container">
        <!-- Formulario para agregar FAQ -->
        <form method="POST" style="background: #1a1a2e; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h2 style="margin-bottom: 15px;">Agregar Nueva FAQ</h2>
            <input type="hidden" name="action" value="crear">
            
            <input type="text" name="pregunta" placeholder="Pregunta" required style="width: 100%; padding: 10px; margin-bottom: 10px; background: #252545; color: #fff; border: 1px solid #252545; border-radius: 6px;">
            
            <textarea name="respuesta_corta" placeholder="Respuesta corta" required style="width: 100%; padding: 10px; margin-bottom: 10px; background: #252545; color: #fff; border: 1px solid #252545; border-radius: 6px; height: 60px;"></textarea>
            
            <textarea name="respuesta" placeholder="Respuesta completa" required style="width: 100%; padding: 10px; margin-bottom: 10px; background: #252545; color: #fff; border: 1px solid #252545; border-radius: 6px; height: 100px;"></textarea>
            
            <input type="text" name="palabras_clave" placeholder="Palabras clave (separadas por comas)" required style="width: 100%; padding: 10px; margin-bottom: 10px; background: #252545; color: #fff; border: 1px solid #252545; border-radius: 6px;">
            
            <button type="submit" class="btn">📝 Guardar FAQ</button>
        </form>
        
        <!-- Listado de FAQs -->
        <div class="faq-grid">
            <?php foreach ($faqs as $faq): ?>
            <div class="faq-card">
                <h3>❓ <?= htmlspecialchars($faq['pregunta']) ?></h3>
                <p><?= htmlspecialchars($faq['respuesta_corta']) ?></p>
                <div class="faq-stats">
                    📊 Consultada <?= $faq['veces_consultada'] ?> veces
                    | Estado: <?= $faq['activo'] ? '✅' : '❌' ?>
                </div>
                <button class="btn btn-danger" onclick="eliminarFAQ(<?= $faq['id'] ?>)">🗑️ Eliminar</button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
```

**Tiempo estimado**: 1 hora

---

## FASE 2: INTEGRACIONES EXTERNAS (2-4 semanas)

### 4️⃣ Integración con Google Calendar
**Archivo**: Crear `services/GoogleCalendarService.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

class GoogleCalendarService {
    private $client;
    private $config;
    
    public function __construct() {
        $this->config = require(__DIR__ . '/../config/config_empresarial.php');
        
        if (!$this->config['google_calendar']['habilitado']) return;
        
        $this->client = new \Google_Client();
        $this->client->setAuthConfig($this->config['google_calendar']['credentials_path']);
        $this->client->addScope(\Google_Service_Calendar::CALENDAR);
    }
    
    public function crearEventoCita($cita) {
        $service = new \Google_Service_Calendar($this->client);
        
        $event = new \Google_Service_Calendar_Event([
            'summary' => "CITA: {$cita['nombre']} - {$cita['servicio']}",
            'description' => "Cliente: {$cita['nombre']}\nServicio: {$cita['servicio']}",
            'start' => ['dateTime' => $cita['fecha_hora']],
            'end' => ['dateTime' => date('Y-m-d H:i:s', strtotime($cita['fecha_hora']) + 1800)],
            'reminders' => [
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 60],
                    ['method' => 'popup', 'minutes' => 15]
                ]
            ]
        ]);
        
        return $service->events->insert(
            $this->config['google_calendar']['calendar_id'],
            $event
        );
    }
}
?>
```

**Requiere**:
1. Google Cloud Console account
2. OAuth 2.0 credentials
3. Google Calendar API habilitado
4. `composer require google/apiclient`

**Tiempo estimado**: 2 horas

---

### 5️⃣ Integración con Pagos (Wompi)
**Archivo**: Crear `services/PaymentService.php`

```php
<?php
class PaymentService {
    private $config;
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->config = require(__DIR__ . '/../config/config_empresarial.php');
    }
    
    public function crearLinkPago($reserva) {
        if (!$this->config['wompi']['habilitado']) return null;
        
        $url = 'https://api.sandbox.wompi.co/v1/transactions';
        
        $data = [
            'amount_in_cents' => $reserva['precio_total'] * 100,
            'currency' => 'COP',
            'customer_email' => $reserva['cliente_email'],
            'reference' => 'RES-' . $reserva['id'],
            'redirect_url' => 'https://tudominio.com/pago-exitoso.php'
        ];
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->config['wompi']['private_key'],
                'Content-Type: application/json'
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);
        
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        return $response['data']['redirect_url'] ?? null;
    }
    
    public function verificarPago($transaction_id) {
        $url = "https://api.sandbox.wompi.co/v1/transactions/$transaction_id";
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->config['wompi']['private_key']
            ],
            CURLOPT_RETURNTRANSFER => true
        ]);
        
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        if ($response['data']['status'] == 'APPROVED') {
            $this->pdo->prepare("UPDATE reservas SET estado = 'pagada' WHERE id = ?")
                ->execute([$response['data']['reference']]);
            return true;
        }
        return false;
    }
}
?>
```

**Requiere**:
1. Cuenta de Wompi
2. API Keys
3. Webhook para confirmar pagos

**Tiempo estimado**: 2-3 horas

---

## FASE 3: CARACTERÍSTICAS AVANZADAS (1-3 meses)

### 6️⃣ Sistema de Cotizaciones
**Archivo**: Crear `services/QuotationService.php`

- Motor de cálculo dinámico
- Generación de PDF
- Envío por email
- Validación de disponibilidad

**Tiempo estimado**: 1 semana

---

### 7️⃣ Integración CRM
**Servicios soportados**: Zoho, HubSpot, Salesforce

- Sincronización de contactos
- Sincronización de oportunidades
- Webhooks bidireccionales
- Mapeo de campos

**Tiempo estimado**: 2-3 semanas

---

### 8️⃣ Multi-agente
**Componentes**:

- Router inteligente
- Especialización por dominio
- Coordinación entre agentes
- Escalamiento automático

**Tiempo estimado**: 2-4 semanas

---

### 9️⃣ Backup en la Nube
**Proveedores**: AWS S3, Google Cloud Storage, Azure

```bash
# Cron job para backup diario
0 2 * * * /usr/bin/php /ruta/backup.php
```

**Tiempo estimado**: 1 semana

---

### 🔟 Seguridad Avanzada
- SSL/TLS
- Encriptación de datos
- GDPR compliance
- Auditoría de seguridad

**Tiempo estimado**: 2 semanas

---

## 📊 DIAGRAMA DE IMPLEMENTACIÓN

```
┌─────────────────────────────────────────┐
│     FASE 1: COMPLETAR (1-2 semanas)    │
├─────────────────────────────────────────┤
│ ✅ Email Notifications                  │
│ ✅ Automatic Reminders                  │
│ ✅ Complete FAQ System                  │
│ ✅ Quotation System                     │
└─────────────────────────────────────────┘
            ↓
┌─────────────────────────────────────────┐
│   FASE 2: INTEGRACIONES (2-4 semanas)  │
├─────────────────────────────────────────┤
│ ✅ Google Calendar                      │
│ ✅ Payment Systems (Wompi)              │
│ ✅ Cloud Backup                         │
│ ✅ Advanced Reports                     │
└─────────────────────────────────────────┘
            ↓
┌─────────────────────────────────────────┐
│  FASE 3: AVANZADAS (1-3 meses)        │
├─────────────────────────────────────────┤
│ ✅ CRM Integration                      │
│ ✅ Multi-Agent System                   │
│ ✅ Machine Learning                     │
│ ✅ Advanced Security                    │
└─────────────────────────────────────────┘
```

---

## 💾 BASE DE DATOS - TABLAS REQUERIDAS

Para las nuevas características, agregar a `database.sql`:

```sql
-- Tabla para Google Calendar
CREATE TABLE google_calendar_sync (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT UNIQUE,
    google_event_id VARCHAR(255),
    sincronizado BOOLEAN DEFAULT 1,
    fecha_sync TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas(id)
);

-- Tabla para Pagos
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT UNIQUE,
    monto DECIMAL(10,2),
    metodo VARCHAR(50), -- wompi, paypal, stripe
    transaction_id VARCHAR(255),
    estado VARCHAR(50), -- pendiente, completado, fallido
    fecha_pago TIMESTAMP,
    FOREIGN KEY (reserva_id) REFERENCES reservas(id)
);

-- Tabla para Recordatorios
CREATE TABLE recordatorios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT,
    enviado BOOLEAN DEFAULT 0,
    fecha_envio TIMESTAMP,
    canal VARCHAR(50), -- whatsapp, email, sms
    FOREIGN KEY (cita_id) REFERENCES citas(id)
);

-- Tabla para Integraciones CRM
CREATE TABLE crm_sync (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT,
    crm VARCHAR(50), -- zoho, hubspot, salesforce
    crm_contact_id VARCHAR(255),
    sincronizado BOOLEAN DEFAULT 1,
    fecha_sync TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- Tabla de Auditoría
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100),
    tabla VARCHAR(50),
    registro_id INT,
    cambios JSON,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
);
```

---

## 🎯 ESTIMACIÓN TOTAL

| Fase | Tiempo | Estado |
|---|---|---|
| Fase 1 | 1-2 semanas | 🟡 En Progreso |
| Fase 2 | 2-4 semanas | ❌ No iniciada |
| Fase 3 | 1-3 meses | ❌ No iniciada |
| **Total** | **6-9 semanas** | 🔵 Disponible |

---

## 📞 SUPPORT & QUESTIONS

Para preguntas sobre la implementación:

📧 Email: dev@sercoltur.com  
💬 WhatsApp: +57 302 253 1580  
📞 Tel: +57 300 123 4567

---

**Última actualización**: 29/12/2025  
**Responsable**: SERCOLTUR Development Team  
**Estado**: 🟢 Plan Aprobado
