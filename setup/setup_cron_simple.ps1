# SERCOLTURBOT - Windows Task Scheduler Setup
# Script simplificado para crear tareas programadas
# Ejecutar como ADMINISTRADOR

$BasePath = "C:\xampp\htdocs\SERCOLTURBOT"
$PhpPath = "C:\xampp\php\php.exe"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SERCOLTURBOT - Configurando Tareas" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Crear directorio de logs
New-Item -ItemType Directory -Path "$BasePath\logs\cron" -Force | Out-Null
Write-Host "[*] Directorio de logs creado" -ForegroundColor Yellow

$tareas = @(
    @{
        Nombre = "SERCOLTURBOT-Reminders"
        Script = "send_reminders.php"
        Tipo = "Cada 5 minutos"
        Minutos = 5
    },
    @{
        Nombre = "SERCOLTURBOT-CleanupSessions"
        Script = "cleanup_sessions.php"
        Tipo = "Diariamente a 3:00 AM"
        Hora = "03:00"
    },
    @{
        Nombre = "SERCOLTURBOT-Backup"
        Script = "backup_database.php"
        Tipo = "Diariamente a 2:00 AM"
        Hora = "02:00"
    },
    @{
        Nombre = "SERCOLTURBOT-GoogleSync"
        Script = "sync_google_calendar.php"
        Tipo = "Cada 15 minutos"
        Minutos = 15
    },
    @{
        Nombre = "SERCOLTURBOT-CheckPayments"
        Script = "check_expired_payments.php"
        Tipo = "Cada 10 minutos"
        Minutos = 10
    },
    @{
        Nombre = "SERCOLTURBOT-SyncReminders"
        Script = "sync_reminders.php"
        Tipo = "Cada 30 minutos"
        Minutos = 30
    },
    @{
        Nombre = "SERCOLTURBOT-ValidationTasks"
        Script = "validation_tasks.php"
        Tipo = "Cada 6 horas"
        Hora = "06:00"
    }
)

Write-Host ""
Write-Host "Creando tareas programadas..." -ForegroundColor Yellow
Write-Host ""

$exito = 0
$errores = 0

foreach ($tarea in $tareas) {
    try {
        $scriptCompleto = "$BasePath\cron\$($tarea.Script)"
        $action = New-ScheduledTaskAction -Execute $PhpPath -Argument $scriptCompleto -WorkingDirectory "$BasePath\cron"
        
        if ($tarea.Minutos) {
            $trigger = New-ScheduledTaskTrigger -RepetitionInterval (New-TimeSpan -Minutes $tarea.Minutos) -At (Get-Date) -Daily
        } else {
            $trigger = New-ScheduledTaskTrigger -Daily -At $tarea.Hora
        }
        
        $settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -MultipleInstances IgnoreNew
        
        Register-ScheduledTask -TaskName $tarea.Nombre -Action $action -Trigger $trigger -Settings $settings -RunLevel Highest -Force | Out-Null
        
        Write-Host "[OK] $($tarea.Nombre) - $($tarea.Tipo)" -ForegroundColor Green
        $exito++
    } catch {
        Write-Host "[ERROR] $($tarea.Nombre): $_" -ForegroundColor Red
        $errores++
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Resultado: $exito creadas, $errores errores" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Tareas configuradas:" -ForegroundColor Yellow
Get-ScheduledTask | Where-Object {$_.TaskName -like "*SERCOLTURBOT*"} | Select-Object TaskName, State | Format-Table

Write-Host "Para eliminar todas las tareas, ejecuta:" -ForegroundColor Yellow
Write-Host "  Get-ScheduledTask | Where-Object {`$_.TaskName -like '*SERCOLTURBOT*'} | Unregister-ScheduledTask -Confirm:`$false" -ForegroundColor Cyan
Write-Host ""
