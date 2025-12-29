@echo off
REM SERCOLTURBOT - Windows Task Scheduler Setup
REM Ejecutar como ADMINISTRADOR

setlocal enabledelayedexpansion

set "PHP_PATH=C:\xampp\php\php.exe"
set "BASE_PATH=C:\xampp\htdocs\SERCOLTURBOT"

echo.
echo ========================================
echo SERCOLTURBOT - Configurando Tareas
echo ========================================
echo.

REM Crear directorio de logs
if not exist "%BASE_PATH%\logs\cron" mkdir "%BASE_PATH%\logs\cron"
echo [*] Directorio de logs creado
echo.

echo Creando tareas programadas...
echo.

REM Tarea 1: Recordatorios (Cada 5 minutos)
schtasks /create /tn "SERCOLTURBOT-Reminders" /tr "%PHP_PATH% %BASE_PATH%\cron\send_reminders.php" /sc minute /mo 5 /f >nul 2>&1
if errorlevel 0 (
    echo [OK] SERCOLTURBOT-Reminders - Cada 5 minutos
) else (
    echo [ERROR] SERCOLTURBOT-Reminders
)

REM Tarea 2: Limpiar sesiones (3:00 AM)
schtasks /create /tn "SERCOLTURBOT-CleanupSessions" /tr "%PHP_PATH% %BASE_PATH%\cron\cleanup_sessions.php" /sc daily /st 03:00 /f >nul 2>&1
if errorlevel 0 (
    echo [OK] SERCOLTURBOT-CleanupSessions - Diariamente a 3:00 AM
) else (
    echo [ERROR] SERCOLTURBOT-CleanupSessions
)

REM Tarea 3: Backup (2:00 AM)
schtasks /create /tn "SERCOLTURBOT-Backup" /tr "%PHP_PATH% %BASE_PATH%\cron\backup_database.php" /sc daily /st 02:00 /f >nul 2>&1
if errorlevel 0 (
    echo [OK] SERCOLTURBOT-Backup - Diariamente a 2:00 AM
) else (
    echo [ERROR] SERCOLTURBOT-Backup
)

REM Tarea 4: Sincronizar Google Calendar (Cada 15 minutos)
schtasks /create /tn "SERCOLTURBOT-GoogleSync" /tr "%PHP_PATH% %BASE_PATH%\cron\sync_google_calendar.php" /sc minute /mo 15 /f >nul 2>&1
if errorlevel 0 (
    echo [OK] SERCOLTURBOT-GoogleSync - Cada 15 minutos
) else (
    echo [ERROR] SERCOLTURBOT-GoogleSync
)

REM Tarea 5: Verificar pagos vencidos (Cada 10 minutos)
schtasks /create /tn "SERCOLTURBOT-CheckPayments" /tr "%PHP_PATH% %BASE_PATH%\cron\check_expired_payments.php" /sc minute /mo 10 /f >nul 2>&1
if errorlevel 0 (
    echo [OK] SERCOLTURBOT-CheckPayments - Cada 10 minutos
) else (
    echo [ERROR] SERCOLTURBOT-CheckPayments
)

REM Tarea 6: Sincronizar recordatorios (Cada 30 minutos)
schtasks /create /tn "SERCOLTURBOT-SyncReminders" /tr "%PHP_PATH% %BASE_PATH%\cron\sync_reminders.php" /sc minute /mo 30 /f >nul 2>&1
if errorlevel 0 (
    echo [OK] SERCOLTURBOT-SyncReminders - Cada 30 minutos
) else (
    echo [ERROR] SERCOLTURBOT-SyncReminders
)

REM Tarea 7: Validaciones (Cada 6 horas a las 6:00 AM)
schtasks /create /tn "SERCOLTURBOT-ValidationTasks" /tr "%PHP_PATH% %BASE_PATH%\cron\validation_tasks.php" /sc daily /st 06:00 /f >nul 2>&1
if errorlevel 0 (
    echo [OK] SERCOLTURBOT-ValidationTasks - Cada 6 horas
) else (
    echo [ERROR] SERCOLTURBOT-ValidationTasks
)

echo.
echo ========================================
echo Tareas configuradas:
echo ========================================
echo.
schtasks /query /tn "SERCOLTURBOT*" /v | findstr "SERCOLTURBOT"

echo.
echo Para eliminar todas las tareas, ejecuta:
echo   schtasks /delete /tn "SERCOLTURBOT*" /f
echo.

pause
