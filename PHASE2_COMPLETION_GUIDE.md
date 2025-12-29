# 🚀 PHASE 2 - INSTRUCCIONES DE COMPLETACIÓN

**Documento de Implementación Final - SERCOLTURBOT**

---

## 📋 RESUMEN ACTUAL

✅ **Completado:**
- Servicio de Pagos Wompi (PagoService.php actualizado)
- Windows Task Scheduler configurado (setup_cron.ps1)
- 5 nuevos cron jobs creados
- GoogleCalendarService.php creado (pero no integrado)

⏳ **Pendiente:**
- Integración final de Google Calendar
- Integration Testing
- Documentación de testing

---

## 🔧 PASOS PARA COMPLETAR PHASE 2

### PASO 1: Configurar Windows Task Scheduler
**Tiempo estimado:** 5 minutos

1. Abrir PowerShell **COMO ADMINISTRADOR**
2. Navegar a:
   ```powershell
   cd C:\xampp\htdocs\SERCOLTURBOT\setup
   ```

3. Permitir ejecución de scripts:
   ```powershell
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser -Force
   ```

4. Ejecutar el script:
   ```powershell
   .\setup_cron.ps1
   ```

5. Verificar que todas las tareas se crearon:
   ```powershell
   Get-ScheduledTask | Where-Object {$_.TaskName -like "*SERCOLTURBOT*"} | Format-Table TaskName, State
   ```

**Resultado esperado:**
```
SERCOLTURBOT-Reminders              Ready
SERCOLTURBOT-CleanupSessions        Ready
SERCOLTURBOT-Backup                 Ready
SERCOLTURBOT-GoogleSync             Ready
SERCOLTURBOT-CheckPayments          Ready
SERCOLTURBOT-SyncReminders          Ready
SERCOLTURBOT-ValidationTasks        Ready
```

---

### PASO 2: Configurar Google Calendar (Pendiente)
**Tiempo estimado:** 30 minutos

1. **Crear Google Cloud Project**
   - Ir a: https://console.cloud.google.com
   - Crear nuevo proyecto: "SERCOLTURBOT"
   - Habilitar API: Google Calendar API

2. **Crear OAuth 2.0 Credentials**
   - Ir a: APIs & Services > Credentials
   - Crear "OAuth 2.0 Client ID" (Desktop app)
   - Descargar JSON

3. **Guardar credenciales**
   ```
   C:\xampp\htdocs\SERCOLTURBOT\config\google_credentials.json
   ```

4. **Actualizar config_empresarial.php**
   ```php
   'google_calendar' => [
       'habilitado' => true,
       'credentials_file' => __DIR__ . '/google_credentials.json',
       'token_file' => __DIR__ . '/google_token.json',
       'calendar_id' => 'primary',
       'timezone' => 'America/Bogota',
   ]
   ```

5. **Ejecutar autenticación inicial**
   ```php
   // Crear archivo: public/setup_google_calendar.php
   <?php
   require_once __DIR__ . '/../services/GoogleCalendarService.php';
   $gcal = new GoogleCalendarService();
   $gcal->inicializarCliente();
   echo "Token generado en config/google_token.json";
   ?>
   ```
   - Visitar: `http://localhost/SERCOLTURBOT/public/setup_google_calendar.php`

---

### PASO 3: Integrar Google Calendar con WhatsApp API
**Tiempo estimado:** 15 minutos

1. Abrir: `public/whatsapp-api.php`

2. Buscar la función `agendarCita()` (línea ~200)

3. Agregar después de guardar en BD:
   ```php
   // Crear evento en Google Calendar si está habilitado
   if (!empty($GLOBALS['config']['google_calendar']['habilitado'])) {
       require_once __DIR__ . '/../services/GoogleCalendarService.php';
       $gcalService = new GoogleCalendarService($pdo);
       
       $datosEvento = [
           'summary' => "Cita: {$cliente['nombre']}",
           'description' => "Tour de " . $datos['tipo_tour'],
           'start' => [
               'dateTime' => $fecha_cita,
               'timeZone' => 'America/Bogota'
           ],
           'end' => [
               'dateTime' => date('c', strtotime($fecha_cita) + 3600),
               'timeZone' => 'America/Bogota'
           ],
           'attendees' => [
               ['email' => $cliente['email'] ?? 'cliente@sercoltur.com']
           ],
           'reminders' => [
               'useDefault' => false,
               'overrides' => [
                   ['method' => 'email', 'minutes' => 60]
               ]
           ]
       ];
       
       $eventoGoogle = $gcalService->crearEvento($datosEvento);
       if (!empty($eventoGoogle['id'])) {
           $pdo->prepare("
               UPDATE citas 
               SET google_event_id = ? 
               WHERE id = ?
           ")->execute([$eventoGoogle['id'], $cita_id]);
       }
   }
   ```

---

### PASO 4: Crear tabla para Google Calendar Events
**Tiempo estimado:** 5 minutos

1. Abrir MySQL/MariaDB
   ```
   Hostname: localhost
   Username: root
   Password: C121672@c
   Database: sercolturbot
   ```

2. Ejecutar SQL:
   ```sql
   -- Agregar columna a citas
   ALTER TABLE citas 
   ADD COLUMN IF NOT EXISTS google_event_id VARCHAR(255),
   ADD COLUMN IF NOT EXISTS pagado TINYINT(1) DEFAULT 0,
   ADD INDEX idx_google_event (google_event_id);

   -- Crear tabla de auditoría
   CREATE TABLE IF NOT EXISTS google_calendar_events (
       id INT AUTO_INCREMENT PRIMARY KEY,
       cita_id INT,
       google_event_id VARCHAR(255),
       accion VARCHAR(50),
       fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (cita_id) REFERENCES citas(id),
       INDEX idx_google_event (google_event_id)
   ) ENGINE=InnoDB CHARSET=utf8mb4;
   ```

---

### PASO 5: Crear Test file para Google Calendar
**Tiempo estimado:** 10 minutos

Crear archivo: `tests/test_google_calendar.php`

```php
<?php
define('BASE_PATH', __DIR__ . '/..');
header('Content-Type: application/json');

require_once BASE_PATH . '/config/config_empresarial.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/services/GoogleCalendarService.php';

// DB connection
$pdo = new PDO(
    "mysql:host=localhost;dbname=sercolturbot",
    "root",
    "C121672@c"
);

$gcalService = new GoogleCalendarService($pdo);
$accion = $_GET['accion'] ?? 'info';

switch ($accion) {
    case 'info':
        echo json_encode([
            'servicio' => 'Google Calendar',
            'configurado' => $gcalService->estaConfigurado(),
            'calendarios' => $gcalService->obtenerCalendarios(),
        ]);
        break;
        
    case 'crear_evento':
        $datos = [
            'summary' => 'Test Event',
            'description' => 'Evento de prueba',
            'start' => ['dateTime' => date('c')],
            'end' => ['dateTime' => date('c', time() + 3600)]
        ];
        $resultado = $gcalService->crearEvento($datos);
        echo json_encode($resultado);
        break;
}
?>
```

Probar: `http://localhost/SERCOLTURBOT/tests/test_google_calendar.php?accion=info`

---

### PASO 6: Testing de Wompi Payments
**Tiempo estimado:** 20 minutos

1. **Obtener credenciales Wompi**
   - Ir a: https://dashboard.wompi.co
   - Crear cuenta (sandbox primero)
   - Copiar Public Key y Private Key

2. **Actualizar config_empresarial.php**
   ```php
   'wompi' => [
       'habilitado' => true,
       'ambiente' => 'sandbox',
       'public_key' => 'tu_public_key_aqui',
       'private_key' => 'tu_private_key_aqui',
   ]
   ```

3. **Probar servicio**
   - Acceder a: `http://localhost/SERCOLTURBOT/tests/test_wompi.php`
   - Hacer clic en "crear_pago"
   - Verificar respuesta

4. **Verificar pagos en BD**
   ```sql
   SELECT * FROM pagos ORDER BY fecha_creacion DESC LIMIT 5;
   ```

---

### PASO 7: Verificar Cron Jobs
**Tiempo estimado:** 10 minutos

1. **Ver si están ejecutándose**
   ```powershell
   Get-ScheduledTask | Where-Object {$_.TaskName -like "*SERCOLTURBOT*"} | Select-Object TaskName, LastRunTime, NextRunTime
   ```

2. **Ver logs**
   ```powershell
   Get-ChildItem C:\xampp\htdocs\SERCOLTURBOT\logs\cron\ -Filter *.log | Sort-Object LastWriteTime -Descending | Select-Object -First 5
   ```

3. **Leer un log**
   ```powershell
   Get-Content "C:\xampp\htdocs\SERCOLTURBOT\logs\cron\check_expired_payments_2024-12-29.log" -Tail 20
   ```

---

## 📊 CHECKLIST DE COMPLETACIÓN

### Wompi Payments
- [ ] Actualizar `config_empresarial.php` con credenciales
- [ ] Crear tabla `pagos` ejecutando `setup/update_payments_schema.sql`
- [ ] Probar en `tests/test_wompi.php`
- [ ] Verificar logs en `logs/pagos.log`

### Windows Cron Jobs
- [ ] Ejecutar `setup_cron.ps1` como ADMIN
- [ ] Verificar que aparecen 7 tareas en Task Scheduler
- [ ] Esperar a que ejecuten y revisar logs
- [ ] Verificar que BD se actualiza correctamente

### Google Calendar Integration
- [ ] Crear Google Cloud Project
- [ ] Descargar OAuth credentials
- [ ] Guardar en `config/google_credentials.json`
- [ ] Integrar en `public/whatsapp-api.php`
- [ ] Ejecutar `setup_google_calendar.php`
- [ ] Crear `tests/test_google_calendar.php`
- [ ] Probar creación de eventos
- [ ] Verificar sincronización

### Testing General
- [ ] Crear reserva y verificar que se crea en Google Calendar
- [ ] Crear reserva y verificar que genera link de Wompi
- [ ] Esperar recordatorio automático (5 minutos después)
- [ ] Verificar que cron jobs están generando logs
- [ ] Revisar BD para ver que datos se actualizan correctamente

---

## 🐛 Troubleshooting

### PowerShell Script no ejecuta
```powershell
# Permitir scripts
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser -Force

# Ejecutar con ruta completa
& "C:\xampp\htdocs\SERCOLTURBOT\setup\setup_cron.ps1"
```

### Google Calendar - Token no se genera
- Verificar que `google_credentials.json` existe
- Verificar que Google Calendar API está habilitada
- Revisar permisos en carpeta `config/`

### Cron Jobs no se ejecutan
- Verificar que Task Scheduler muestra estado "Ready"
- Ver logs: `C:\xampp\htdocs\SERCOLTURBOT\logs\cron\`
- Ejecutar manualmente en PowerShell para debug:
  ```powershell
  C:\xampp\php\php.exe C:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php
  ```

### Wompi - 401 Unauthorized
- Verificar credenciales en `config_empresarial.php`
- Verificar que ambiente coincide (sandbox/production)
- Revisar logs en `logs/pagos_errores.log`

---

## 📚 Documentación de Referencia

- [Wompi API Docs](https://docs.wompi.co)
- [Google Calendar API](https://developers.google.com/calendar)
- [Windows Task Scheduler](https://docs.microsoft.com/windows/win32/taskschd/task-scheduler-start-page)
- Local: `setup/WINDOWS_CRON_SETUP.md`
- Local: `PHASE2_STATUS.md`

---

## ✨ PRÓXIMO: Phase 3?

Después de completar Phase 2, posibles mejoras:
- SMS con Twilio
- Notificaciones push
- Dashboard avanzado
- Reportes analíticos
- Integración con Salesforce

---

**Última actualización:** 2024-12-16
**Autor:** GitHub Copilot - SERCOLTURBOT Development Team
