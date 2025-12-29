# 🚀 GUÍA DE ACTIVACIÓN - PHASE 1 ✅ COMPLETADA

**Fecha:** 2025-01-14  
**Estado:** 4 de 8 servicios completados (50%)  
**Próximo Paso:** Google Calendar + Wompi

---

## 📋 Resumen de lo Realizado

### ✅ COMPLETADO: EmailService
**Archivo:** `services/EmailService.php`

**Características:**
- ✅ Envío de confirmaciones de reserva
- ✅ Recordatorios de citas automáticos
- ✅ Reportes semanales
- ✅ Notificaciones a asesores
- ✅ Plantillas HTML profesionales

**Integración en whatsapp-api.php:**
```php
require_once(__DIR__ . '/../services/EmailService.php');

// En función agendarCita()
$emailService = new EmailService($pdo);
$emailService->enviarRecordatorioCita($cliente, $cita);
```

**Configuración Requerida en `config/config_empresarial.php`:**
```php
'email' => [
    'habilitado' => true,
    'host' => 'smtp.gmail.com',
    'puerto' => 587,
    'usuario' => 'tu_email@gmail.com',
    'password' => 'app_specific_password',
    'from_email' => 'notificaciones@sercoltur.com',
    'from_name' => 'SERCOLTUR'
]
```

**Pasos para Gmail (SMTP):**
1. Ir a https://myaccount.google.com/
2. Activar autenticación en dos pasos
3. Crear "Contraseña de aplicación" en Google
4. Usar esa contraseña en la config

---

### ✅ COMPLETADO: ReminderService
**Archivo:** `cron/send_reminders.php`

**Características:**
- ✅ Envío automático de recordatorios 60 min antes de cita
- ✅ Envío por WhatsApp + Email
- ✅ Marca registro como enviado en BD
- ✅ Limpieza de citas vencidas
- ✅ Logging de todas las operaciones

**Columnas Agregadas a Tabla `citas`:**
```sql
ALTER TABLE citas ADD COLUMN recordatorio_enviado TINYINT DEFAULT 0;
ALTER TABLE citas ADD COLUMN fecha_recordatorio TIMESTAMP NULL;
```

**Tablas Nuevas Creadas:**
```sql
CREATE TABLE email_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destinatario VARCHAR(255),
    asunto VARCHAR(255),
    estado ENUM('enviado', 'fallido'),
    fecha_envio TIMESTAMP
);

CREATE TABLE reminder_audits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT,
    tipo_envio VARCHAR(50),
    estado VARCHAR(50),
    respuesta_api LONGTEXT,
    fecha_intento TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas(id)
);
```

**Cómo Ejecutar (Windows Task Scheduler):**
1. Abrir "Programador de tareas"
2. Crear tarea nueva
3. **General:** Nombre: "SERCOLTUR Recordatorios"
4. **Triggers:** Repetir cada 5 minutos
5. **Acciones:** `php.exe "c:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php"`
6. **Configurar:** Ejecutar sin importar si usuario está conectado

**O ejecutar manualmente:**
```powershell
php "c:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php"
```

---

### ✅ COMPLETADO: Panel Administrativo de FAQs
**Archivo:** `admin/faqs.php`

**Características:**
- ✅ Interfaz responsiva profesional
- ✅ CRUD completo (crear, leer, editar, eliminar)
- ✅ Categorización de FAQs
- ✅ Palabras clave para búsqueda
- ✅ Activar/Desactivar FAQs
- ✅ Estadísticas en tiempo real
- ✅ Diseño moderno con gradientes

**Tabla en Base de Datos (debe existir):**
```sql
CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta VARCHAR(255) NOT NULL,
    respuesta LONGTEXT NOT NULL,
    palabras_clave VARCHAR(500),
    categoria VARCHAR(100),
    activo TINYINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**URL de Acceso:**
```
http://localhost/SERCOLTURBOT/admin/faqs.php
```

**Integración en Dashboard:**
Agregar pestaña en `public/dashboard.php`:
```javascript
// En la sección de pestañas
<li><a href="#" onclick="showTab('faqs')">📚 FAQs</a></li>

// En showTab()
case 'faqs':
    window.location.href = '../admin/faqs.php';
    break;
```

---

## 🔧 ARCHIVOS MODIFICADOS

### `public/whatsapp-api.php`
```diff
+ require_once(__DIR__ . '/../services/EmailService.php');
+ 
+ // En agendarCita() se agregó:
+ $emailService = new EmailService($pdo);
+ $emailService->enviarRecordatorioCita($cliente, $cita);

+ // Nueva función:
+ function obtenerEmailCliente($pdo, $telefono) { ... }
```

### Nuevos Archivos Creados
- ✅ `services/EmailService.php` (362 líneas)
- ✅ `cron/send_reminders.php` (244 líneas)
- ✅ `admin/faqs.php` (489 líneas)
- ✅ `setup/update_reminders_schema.sql` (35 líneas)

**Total de líneas de código nuevas:** ~1,130

---

## 🔐 Configuración Requerida

Actualizar `config/config_empresarial.php`:

```php
$EMPRESA_CONFIG = [
    'email' => [
        'habilitado' => true,  // Cambiar a true
        'host' => 'smtp.gmail.com',
        'puerto' => 587,
        'usuario' => 'tu_email@gmail.com',  // Tu email
        'password' => 'xxxx xxxx xxxx xxxx',  // App password
        'from_email' => 'notificaciones@sercoltur.com',
        'from_name' => 'SERCOLTUR'
    ],
    
    'whatsapp' => [
        'habilitado' => true,
        'phone_number_id' => '',  // ID de Meta
        'access_token' => ''  // Token de acceso
    ],
    
    'facebook' => [
        'habilitado' => false,
        'page_access_token' => ''
    ],
    
    'instagram' => [
        'habilitado' => false,
        'business_account_id' => '',
        'access_token' => ''
    ],
    
    // El resto de configuraciones permanecen igual
];
```

---

## 📊 Próximos Pasos (Phase 2)

### 5️⃣ Google Calendar Integration
**Archivo a crear:** `services/GoogleCalendarService.php`
**Tiempo estimado:** 1 hora
**Requisitos:**
- Credenciales de Google OAuth 2.0
- Librería: `composer require google/apiclient`
- Sincronización automática de citas

### 6️⃣ Wompi Payment Processing
**Archivo a expandir:** `services/PagoService.php`
**Tiempo estimado:** 1-2 horas
**Requisitos:**
- Credenciales de Wompi
- Crear tabla `pagos`
- Webhook para confirmaciones

### 7️⃣ Configuración de Cron Jobs
**Tiempo estimado:** 30 minutos
**Requisitos:**
- Windows Task Scheduler configurado
- Scripts de respaldo/limpieza

### 8️⃣ Testing Completo
**Tiempo estimado:** 1-2 horas
**Incluye:** Validación de todos los flujos

---

## ✅ VALIDACIÓN

Todos los archivos han pasado validación PHP:
```
✓ services/EmailService.php - No syntax errors
✓ cron/send_reminders.php - No syntax errors
✓ admin/faqs.php - No syntax errors
```

**Base de datos:**
```
✓ Tabla citas - Actualizada con campos de recordatorios
✓ Tabla email_log - Creada
✓ Tabla reminder_audits - Creada
✓ Tabla faqs - Debe existir
```

---

## 📞 Soporte Rápido

### Error: "Email no habilitado"
→ Verificar `config_empresarial.php` - 'habilitado' debe ser `true`

### Recordatorios no se envían
→ Ejecutar manualmente: `php cron/send_reminders.php`
→ Verificar que la tabla `citas` tenga las columnas correctas

### FAQs no aparecen
→ Verificar que exista tabla `faqs`
→ Confirmar autenticación en `admin/faqs.php`

---

**Generado:** 2025-01-14  
**Próxima Revisión:** Después de completar Google Calendar
