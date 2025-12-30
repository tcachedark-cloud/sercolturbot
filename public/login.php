<?php
session_start();

/* ===============================
   CONEXIÓN A BASE DE DATOS
================================ */
require_once __DIR__ . '/config/database.php';

$pdo = getDatabase();
if (!$pdo) {
    die('❌ Error: No se pudo conectar a la base de datos');
}

/* ===============================
   REDIRECCIÓN SI YA ESTÁ LOGUEADO
================================ */
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

/* ===============================
   PROCESAR LOGIN
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Por favor completa todos los campos';
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT * 
                 FROM usuarios 
                 WHERE (username = ? OR email = ?) 
                   AND activo = 1 
                 LIMIT 1"
            );
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {

                // ✅ Sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['avatar'] = $user['avatar'] ?? null;

                // Último acceso
                $pdo->prepare(
                    "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?"
                )->execute([$user['id']]);

                // Log exitoso
                $pdo->prepare(
                    "INSERT INTO login_log (usuario_id, ip, user_agent, exitoso)
                     VALUES (?, ?, ?, 1)"
                )->execute([
                    $user['id'],
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);

                header('Location: dashboard.php');
                exit;

            } else {
                $error = 'Usuario o contraseña incorrectos';

                // Log fallido
                if ($user) {
                    $pdo->prepare(
                        "INSERT INTO login_log (usuario_id, ip, user_agent, exitoso)
                         VALUES (?, ?, ?, 0)"
                    )->execute([
                        $user['id'],
                        $_SERVER['REMOTE_ADDR'] ?? '',
                        $_SERVER['HTTP_USER_AGENT'] ?? ''
                    ]);
                }
            }
        } catch (Throwable $e) {
            $error = 'Error interno del sistema';
        }
    }
}
?>
