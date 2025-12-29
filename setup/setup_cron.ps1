# SERCOLTURBOT - Windows Task Scheduler Automatic Setup
# Run as ADMINISTRATOR in PowerShell
# Ejecutar como ADMINISTRADOR en PowerShell

param(
    [switch]$Remove = $false,
    [switch]$Verbose = $false
)

$ErrorActionPreference = "Stop"

# Colors
$Green = "Green"
$Red = "Red"
$Yellow = "Yellow"
$Cyan = "Cyan"

# Paths
$BasePath = "C:\xampp\htdocs\SERCOLTURBOT"
$PhpPath = "C:\xampp\php\php.exe"
$LogPath = "$BasePath\logs\cron"

# Verificar que PHP existe
if (!(Test-Path $PhpPath)) {
    Write-Host "✗ Error: PHP no encontrado en $PhpPath" -ForegroundColor $Red
    exit 1
}

# Crear directorio de logs
Write-Host "Creando directorio de logs..." -ForegroundColor $Cyan
New-Item -ItemType Directory -Path $LogPath -Force | Out-Null

function Create-CronTask {
    param(
        [string]$TaskName,
        [string]$ScriptPath,
        [string]$TriggerType = "Interval",
        [int]$Minutes = 5,
        [string]$AtTime = "02:00"
    )
    
    # Validar que el script existe
    if (!(Test-Path "$BasePath\cron\$ScriptPath")) {
        if ($Verbose) {
            Write-Host "  [WARN] Script no existe: $BasePath\cron\$ScriptPath (se creará después)" -ForegroundColor $Yellow
        }
    }
    
    try {
        # Crear action
        $action = New-ScheduledTaskAction `
            -Execute $PhpPath `
            -Argument "$BasePath\cron\$ScriptPath" `
            -WorkingDirectory "$BasePath\cron"
        
        # Crear trigger
        if ($TriggerType -eq "Interval") {
            $trigger = New-ScheduledTaskTrigger `
                -RepetitionInterval (New-TimeSpan -Minutes $Minutes) `
                -RepeatIndefinitely
        } else {
            $trigger = New-ScheduledTaskTrigger -Daily -At $AtTime
        }
        
        # Crear configuración
        $settings = New-ScheduledTaskSettingsSet `
            -AllowStartIfOnBatteries `
            -DontStopIfGoingOnBatteries `
            -MultipleInstances IgnoreNew `
            -ExecutionTimeLimit (New-TimeSpan -Hours 1)
        
        # Registrar tarea
        Register-ScheduledTask `
            -TaskName $TaskName `
            -Action $action `
            -Trigger $trigger `
            -Settings $settings `
            -RunLevel Highest `
            -Force | Out-Null
        
        Write-Host "  [OK] Tarea creada: $TaskName" -ForegroundColor $Green
        return $true
    } catch {
        Write-Host "  [ERROR] Error creando $TaskName : $_" -ForegroundColor $Red
        return $false
    }
}

function Remove-CronTask {
    param([string]$TaskName)
    
    try {
        Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false -ErrorAction SilentlyContinue
        Write-Host "  [OK] Tarea eliminada: $TaskName" -ForegroundColor $Green
        return $true
    } catch {
        Write-Host "  [ERROR] Error eliminando $TaskName : $_" -ForegroundColor $Red
        return $false
    }
}

# ═══════════════════════════════════════════════════════════════

Write-Host ""
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor $Cyan
Write-Host "SERCOLTURBOT - Windows Task Scheduler Configuration" -ForegroundColor $Cyan
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor $Cyan
Write-Host ""

if ($Remove) {
    Write-Host "Eliminando tareas..." -ForegroundColor $Yellow
    Write-Host ""
    
    $tasks = @(
        "SERCOLTURBOT-Reminders",
        "SERCOLTURBOT-CleanupSessions",
        "SERCOLTURBOT-Backup",
        "SERCOLTURBOT-GoogleSync",
        "SERCOLTURBOT-CheckPayments",
        "SERCOLTURBOT-SyncReminders",
        "SERCOLTURBOT-ValidationTasks"
    )
    
    foreach ($task in $tasks) {
        Remove-CronTask $task
    }
    
    Write-Host ""
    Write-Host "✓ Tareas eliminadas" -ForegroundColor $Green
    exit 0
}

# ═══════════════════════════════════════════════════════════════

Write-Host "Creando tareas programadas..." -ForegroundColor $Cyan
Write-Host ""

$tasksCreated = 0
$tasksFailed = 0

# Tarea 1: Enviar recordatorios (cada 5 minutos)
if (Create-CronTask "SERCOLTURBOT-Reminders" "send_reminders.php" "Interval" 5) {
    $tasksCreated++
} else {
    $tasksFailed++
}

# Tarea 2: Limpiar sesiones (diariamente a las 3 AM)
if (Create-CronTask "SERCOLTURBOT-CleanupSessions" "cleanup_sessions.php" "Daily" 0 "03:00") {
    $tasksCreated++
} else {
    $tasksFailed++
}

# Tarea 3: Backup de BD (diariamente a las 2 AM)
if (Create-CronTask "SERCOLTURBOT-Backup" "backup_database.php" "Daily" 0 "02:00") {
    $tasksCreated++
} else {
    $tasksFailed++
}

# Tarea 4: Sincronizar Google Calendar (cada 15 minutos)
if (Create-CronTask "SERCOLTURBOT-GoogleSync" "sync_google_calendar.php" "Interval" 15) {
    $tasksCreated++
} else {
    $tasksFailed++
}

# Tarea 5: Verificar pagos vencidos (cada 10 minutos)
if (Create-CronTask "SERCOLTURBOT-CheckPayments" "check_expired_payments.php" "Interval" 10) {
    $tasksCreated++
} else {
    $tasksFailed++
}

# Tarea 6: Sincronizar recordatorios (cada 30 minutos)
if (Create-CronTask "SERCOLTURBOT-SyncReminders" "sync_reminders.php" "Interval" 30) {
    $tasksCreated++
} else {
    $tasksFailed++
}

# Tarea 7: Tareas de validación (cada 6 horas)
if (Create-CronTask "SERCOLTURBOT-ValidationTasks" "validation_tasks.php" "Daily" 0 "06:00") {
    $tasksCreated++
} else {
    $tasksFailed++
}

# ═══════════════════════════════════════════════════════════════

Write-Host ""
Write-Host "═════════════════════════════════════════════════════════════" -ForegroundColor $Cyan

if ($tasksFailed -eq 0) {
    Write-Host "[SUCCESS] Todas las tareas fueron configuradas exitosamente" -ForegroundColor $Green
} else {
    Write-Host "[WARN] $tasksCreated tareas creadas, $tasksFailed fallaron" -ForegroundColor $Yellow
}

Write-Host ""
Write-Host "Tareas Configuradas:" -ForegroundColor $Yellow
Write-Host ""

Get-ScheduledTask | Where-Object {$_.TaskName -like "*SERCOLTURBOT*"} | ForEach-Object {
    $taskName = $_.TaskName
    $readyStatus = if ($_.State -eq "Ready") { "[ENABLED]" } else { "[" + $_.State + "]" }
    $nextRun = $_.NextRunTime
    Write-Host "  $readyStatus | $taskName" -ForegroundColor $Green
}

Write-Host ""
Write-Host "Logs disponibles en: $LogPath" -ForegroundColor $Cyan
Write-Host ""
Write-Host "Para ver el estado de una tarea:" -ForegroundColor $Yellow
Write-Host "  Get-ScheduledTask | Where-Object {`$_.TaskName -like '*SERCOLTURBOT*'}" -ForegroundColor $Cyan
Write-Host ""
Write-Host "Para ver historial:" -ForegroundColor $Yellow
Write-Host "  Get-EventLog -LogName System | Where-Object {`$_.Source -like '*SERCOLTURBOT*'}" -ForegroundColor $Cyan
Write-Host ""
Write-Host "Para eliminar las tareas:" -ForegroundColor $Yellow
Write-Host "  .\setup_cron.ps1 -Remove" -ForegroundColor $Cyan
Write-Host ""
