# 📚 ÍNDICE MAESTRO - SERCOLTURBOT

**Sistema:** SERCOLTURBOT Empresarial  
**Versión:** 2.0 (Phase 1 Completado)  
**Fecha:** 2025-01-14  
**Estado:** 🔴 4/8 Servicios Implementados (50%)

---

## 📋 DOCUMENTACIÓN DISPONIBLE

### 📖 Documentos Principales

| Documento | Contenido | Acceso |
|-----------|----------|--------|
| **GUIA_ACTIVACION_PHASE1.md** | Resumen de Phase 1 completado | [Ver](./GUIA_ACTIVACION_PHASE1.md) |
| **TESTING_Y_CONFIGURACION.md** | Instrucciones de setup y testing | [Ver](./TESTING_Y_CONFIGURACION.md) |
| **Este Índice** | Navegación general | [Ver](./INDICE_MAESTRO.md) |

### 📊 Documentación Técnica

Ubicación: `setup/` y `docs/`

- `setup/database.sql` - Schema completo de BD
- `setup/update_reminders_schema.sql` - Actualización para recordatorios
- `config/config_empresarial.php` - Configuración central

---

## 🎯 QUICK START (5 MINUTOS)

### Para Activar Email
1. Abrir `config/config_empresarial.php`
2. En sección `email`: Cambiar `'habilitado' => false,` a `true`
3. Agregar credenciales Gmail (obtener en https://myaccount.google.com/app-passwords)
4. Guardar

### Para Configurar Recordatorios
1. Abrir Windows Task Scheduler (`taskschd.msc`)
2. Create Basic Task
3. Trigger: Every 5 minutes
4. Action: `php.exe "c:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php"`
5. Finish

---

## 🚀 SERVICIOS IMPLEMENTADOS

### 1️⃣ EmailService ✅ COMPLETADO
**Archivo:** `services/EmailService.php`

```php
// Uso básico:
$email = new EmailService($pdo);

// Enviar confirmación de reserva
$email->enviarConfirmacionReserva($cliente, $reserva);

// Enviar recordatorio de cita
$email->enviarRecordatorioCita($cliente, $cita);

// Enviar reporte semanal
$email->enviarReporteSemanal($email_admin, $datos);

// Enviar notificación a asesor
$email->enviarNotificacionAsesor($asesor, $reserva, $cliente);
```

**Características:**
- Plantillas HTML profesionales
- Soporte para Gmail SMTP
- Logging automático en BD
- Integración con tabla email_log

**Configuración Requerida:**
```
config_empresarial.php → email → [habilitado, host, puerto, usuario, password]
```

---

### 3️⃣ ReminderService ✅ COMPLETADO
**Archivo:** `cron/send_reminders.php`

**Características:**
- Recordatorios automáticos 60 min antes
- Envío simultáneo por WhatsApp + Email
- Marca como enviado en BD
- Limpieza de citas vencidas
- Logging detallado

**Ejecutar:**
```powershell
# Manual
php cron/send_reminders.php

# Automático (Task Scheduler cada 5 min)
# Ver TESTING_Y_CONFIGURACION.md para setup
```

**Tabla de Auditoría:**
- `email_log` - Registro de envíos
- `reminder_audits` - Auditoría detallada

---

### 4️⃣ FAQs Admin Panel ✅ COMPLETADO
**Archivo:** `admin/faqs.php`

**Características:**
- CRUD completo
- Categorización
- Palabras clave de búsqueda
- Activar/Desactivar
- Interface responsiva
- Estadísticas en tiempo real

**Acceder:**
```
http://localhost/SERCOLTURBOT/admin/faqs.php
```

**Tabla Requerida:**
```sql
CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pregunta VARCHAR(255),
    respuesta LONGTEXT,
    palabras_clave VARCHAR(500),
    categoria VARCHAR(100),
    activo TINYINT DEFAULT 1,
    fecha_creacion TIMESTAMP
);
```

---

## 📁 ESTRUCTURA DE DIRECTORIOS

```
SERCOLTURBOT/
├── admin/
│   └── faqs.php              ← Panel de FAQs
├── api/
│   └── v1/
├── config/
│   ├── config_empresarial.php ← CONFIGURACIÓN PRINCIPAL
│   └── database.php
├── cron/
│   └── send_reminders.php    ← Recordatorios automáticos
├── logs/
│   └── reminders.log         ← Log de recordatorios
├── public/
│   ├── whatsapp-api.php      ← Bot WhatsApp (modificado)
│   ├── dashboard.php
│   └── dashboard-api.php
├── services/
│   ├── EmailService.php      ← Servicio de Email
│   └── WhatsAppService.php
├── setup/
│   ├── database.sql
│   └── update_reminders_schema.sql
├── GUIA_ACTIVACION_PHASE1.md
├── TESTING_Y_CONFIGURACION.md
└── INDICE_MAESTRO.md         ← Éste archivo
```

---

## 🔧 CAMBIOS REALIZADOS A ARCHIVOS EXISTENTES

### `public/whatsapp-api.php`
```diff
+ require_once(__DIR__ . '/../services/EmailService.php');

function agendarCita($phone, $fecha, $hora, $servicio, $nombre) {
    ...
+   try {
+       $emailService = new EmailService($pdo);
+       $emailService->enviarRecordatorioCita($cliente, $cita);
+   } catch (Exception $e) {
+       logBot("Nota: Email no enviado...");
+   }
}

+ function obtenerEmailCliente($pdo, $telefono) { ... }
```

---

## 📊 ESTADÍSTICAS DE CÓDIGO

| Componente | Líneas | Estado |
|-----------|--------|--------|
| EmailService.php | 362 | ✅ Completo |
| send_reminders.php | 244 | ✅ Completo |
| admin/faqs.php | 489 | ✅ Completo |
| Modificaciones existentes | ~30 | ✅ Completo |
| Documentación SQL | 35 | ✅ Completo |
| **TOTAL** | **~1,478** | **✅ PHASE 1** |

---

## ⏭️ PRÓXIMOS PASOS (PHASE 2)

### 5️⃣ Google Calendar Integration
- **Archivo:** `services/GoogleCalendarService.php`
- **Requisitos:** OAuth 2.0 credentials, google_credentials.json
- **Tiempo:** ~1 hora
- **Funcionalidad:** Sync automático de citas

### 6️⃣ Wompi Payment Processing
- **Archivo:** Expandir `services/PagoService.php`
- **Requisitos:** Credenciales Wompi, tabla `pagos`
- **Tiempo:** ~1-2 horas
- **Funcionalidad:** Procesamiento de pagos en línea

### 7️⃣ Cron Jobs Configuration
- **Requisitos:** Windows Task Scheduler setup
- **Tiempo:** ~30 minutos
- **Scripts:** Backup automático, limpieza de sesiones

### 8️⃣ Integration Testing
- **Pruebas:** End-to-end de todos los flujos
- **Tiempo:** ~1-2 horas
- **Validación:** Performance y confiabilidad

---

## 🔐 Configuración Requerida

### Email (Gmail)
```php
'email' => [
    'habilitado' => true,
    'host' => 'smtp.gmail.com',
    'puerto' => 587,
    'usuario' => 'tu_email@gmail.com',
    'password' => 'app_password_aqui',
    'from_email' => 'notificaciones@sercoltur.com',
    'from_name' => 'SERCOLTUR'
]
```

### WhatsApp, Facebook, Instagram
```php
'whatsapp' => [
    'habilitado' => true,
    'phone_number_id' => '', // ID del número de teléfono WhatsApp Business
    'access_token' => '', // Token de acceso de Meta
],
'facebook' => [
    'habilitado' => false,
    'page_access_token' => '',
],
'instagram' => [
    'habilitado' => false,
    'business_account_id' => '',
    'access_token' => '',
]

### Base de Datos
```sql
-- Tablas requeridas:
✓ citas (con recordatorio_enviado, fecha_recordatorio)
✓ faqs (para admin panel)
✓ email_log (auditoría)
✓ reminder_audits (registros detallados)
```

---

## 🧪 Testing Rápido

```powershell
# Validar sintaxis PHP
php -l services/EmailService.php
php -l cron/send_reminders.php
php -l admin/faqs.php

# Ejecutar test de email
php tests/test_email.php

# Ejecutar recordatorios
php cron/send_reminders.php

# Ver logs
Get-Content logs/reminders.log -Tail 20
```

---

## 📞 SOPORTE Y TROUBLESHOOTING

### Tabla de Problemas Comunes

| Problema | Causa | Solución |
|----------|-------|----------|
| "Email no habilitado" | Config desactivado | Cambiar `habilitado => true` |
| "SMTP Connection Failed" | Credenciales incorrectas | Verificar en myaccount.google.com |
| "Table citas doesn't exist" | BD no inicializada | Ejecutar database.sql |
| "Task Scheduler no ejecuta" | Permisos insuficientes | Ejecutar como Administrador |

**Documentación detallada:** Ver [TESTING_Y_CONFIGURACION.md](./TESTING_Y_CONFIGURACION.md)

---

## 📞 Contacto y Recursos

- **WhatsApp:** +57 302 253 1580
- **Email:** info@sercoltur.com
- **Dashboard:** http://localhost/SERCOLTURBOT/public/dashboard.php
- **Panel FAQs:** http://localhost/SERCOLTURBOT/admin/faqs.php

---

## ✅ CHECKLIST FINAL

- [ ] Leer GUIA_ACTIVACION_PHASE1.md
- [ ] Configurar Email en config_empresarial.php
- [ ] Ejecutar tests (test_email.php)
- [ ] Crear task scheduler para recordatorios
- [ ] Probar envío de FAQs
- [ ] Validar logs en logs/reminders.log
- [ ] Testing completo de reservas con notificaciones

---

**Generado:** 2025-01-14  
**Actualizado:** Phase 1 Completado  
**Próxima Revisión:** Inicio de Phase 2

