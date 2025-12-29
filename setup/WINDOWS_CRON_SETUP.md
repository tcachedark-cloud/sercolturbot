# Windows Task Scheduler - Configuración SERCOLTURBOT

## Descripción
Script para agendar tareas automáticas en Windows usando PowerShell.
Ejecutar COMO ADMINISTRADOR en PowerShell.

## Instalación

### 1. Crear carpeta de logs
```powershell
New-Item -ItemType Directory -Path "C:\xampp\htdocs\SERCOLTURBOT\logs\cron" -Force
```

### 2. Crear tareas programadas

#### Tarea 1: Enviar recordatorios (cada 5 minutos)
```powershell
$trigger = New-JobTrigger -RepetitionInterval (New-TimeSpan -Minutes 5) -RepeatIndefinitely
$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php"
Register-ScheduledTask -TaskName "SERCOLTURBOT-Reminders" -Action $action -Trigger $trigger -RunLevel Highest
```

#### Tarea 2: Limpiar sesiones (diariamente a las 3 AM)
```powershell
$trigger = New-JobTrigger -Daily -At 03:00
$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\SERCOLTURBOT\cron\cleanup_sessions.php"
Register-ScheduledTask -TaskName "SERCOLTURBOT-CleanupSessions" -Action $action -Trigger $trigger -RunLevel Highest
```

#### Tarea 3: Backup de base de datos (diariamente a las 2 AM)
```powershell
$trigger = New-JobTrigger -Daily -At 02:00
$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\SERCOLTURBOT\cron\backup_database.php"
Register-ScheduledTask -TaskName "SERCOLTURBOT-Backup" -Action $action -Trigger $trigger -RunLevel Highest
```

#### Tarea 4: Sincronizar Google Calendar (cada 15 minutos)
```powershell
$trigger = New-JobTrigger -RepetitionInterval (New-TimeSpan -Minutes 15) -RepeatIndefinitely
$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\SERCOLTURBOT\cron\sync_google_calendar.php"
Register-ScheduledTask -TaskName "SERCOLTURBOT-GoogleSync" -Action $action -Trigger $trigger -RunLevel Highest
```

#### Tarea 5: Validar y marcar pagos vencidos (cada 10 minutos)
```powershell
$trigger = New-JobTrigger -RepetitionInterval (New-TimeSpan -Minutes 10) -RepeatIndefinitely
$action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument "C:\xampp\htdocs\SERCOLTURBOT\cron\check_expired_payments.php"
Register-ScheduledTask -TaskName "SERCOLTURBOT-CheckPayments" -Action $action -Trigger $trigger -RunLevel Highest
```

## Ver tareas programadas

```powershell
# Listar todas las tareas SERCOLTURBOT
Get-ScheduledTask | Where-Object {$_.TaskName -like "*SERCOLTURBOT*"} | Select-Object TaskName, State, Triggers

# Ver historial de una tarea
Get-EventLog -LogName System | Where-Object {$_.Source -like "*SERCOLTURBOT*"}
```

## Editar una tarea

```powershell
$trigger = New-JobTrigger -Daily -At 04:00
Set-ScheduledTask -TaskName "SERCOLTURBOT-Reminders" -Trigger $trigger
```

## Eliminar una tarea

```powershell
Unregister-ScheduledTask -TaskName "SERCOLTURBOT-Reminders" -Confirm:$false
```

## Script para crear todas las tareas de una vez

Crear archivo `setup_cron.ps1`:

```powershell
# SERCOLTURBOT - Windows Task Scheduler Setup
# Ejecutar como ADMINISTRADOR

$ErrorActionPreference = "Stop"

function Create-Task {
    param(
        [string]$TaskName,
        [string]$PhpScript,
        [string]$TriggerTime = "Daily",
        [int]$RepetitionMinutes = 0
    )
    
    $action = New-ScheduledTaskAction -Execute "C:\xampp\php\php.exe" -Argument $PhpScript
    
    if ($RepetitionMinutes -gt 0) {
        $trigger = New-JobTrigger -RepetitionInterval (New-TimeSpan -Minutes $RepetitionMinutes) -RepeatIndefinitely
    } else {
        $trigger = New-JobTrigger -Daily -At $TriggerTime
    }
    
    try {
        Register-ScheduledTask -TaskName $TaskName -Action $action -Trigger $trigger -RunLevel Highest -Force
        Write-Host "✓ Tarea creada: $TaskName" -ForegroundColor Green
    } catch {
        Write-Host "✗ Error creando $TaskName : $_" -ForegroundColor Red
    }
}

Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "SERCOLTURBOT - Configuración de Tareas Programadas" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════" -ForegroundColor Cyan

# Crear directorio de logs
New-Item -ItemType Directory -Path "C:\xampp\htdocs\SERCOLTURBOT\logs\cron" -Force | Out-Null

# Crear tareas
Create-Task "SERCOLTURBOT-Reminders" "C:\xampp\htdocs\SERCOLTURBOT\cron\send_reminders.php" -RepetitionMinutes 5
Create-Task "SERCOLTURBOT-CleanupSessions" "C:\xampp\htdocs\SERCOLTURBOT\cron\cleanup_sessions.php" -TriggerTime "03:00"
Create-Task "SERCOLTURBOT-Backup" "C:\xampp\htdocs\SERCOLTURBOT\cron\backup_database.php" -TriggerTime "02:00"
Create-Task "SERCOLTURBOT-GoogleSync" "C:\xampp\htdocs\SERCOLTURBOT\cron\sync_google_calendar.php" -RepetitionMinutes 15
Create-Task "SERCOLTURBOT-CheckPayments" "C:\xampp\htdocs\SERCOLTURBOT\cron\check_expired_payments.php" -RepetitionMinutes 10

Write-Host ""
Write-Host "✓ Todas las tareas se han configurado correctamente" -ForegroundColor Green
Write-Host ""
Write-Host "Tareas creadas:" -ForegroundColor Yellow
Get-ScheduledTask | Where-Object {$_.TaskName -like "*SERCOLTURBOT*"} | Format-Table TaskName, State
```

## Ejecutar script

```powershell
# Cambiar directorio
cd C:\xampp\htdocs\SERCOLTURBOT\setup

# Permitir ejecución de scripts (si es necesario)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser -Force

# Ejecutar
.\setup_cron.ps1
```

## Verificación

1. Abrir "Programador de tareas" (Task Scheduler)
2. Buscar "SERCOLTURBOT" en la lista
3. Verificar que todas las tareas estén presentes
4. Ver logs en: `C:\xampp\htdocs\SERCOLTURBOT\logs\cron\`

## Logs

Todos los cron jobs escriben logs en:
- `logs/cron/reminders_YYYY-MM-DD.log`
- `logs/cron/cleanup_YYYY-MM-DD.log`
- `logs/cron/backup_YYYY-MM-DD.log`
- `logs/cron/google_sync_YYYY-MM-DD.log`
- `logs/cron/payments_YYYY-MM-DD.log`
