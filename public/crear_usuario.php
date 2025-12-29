<?php
/**
 * SCRIPT PARA CREAR USUARIOS
 * Ejecutar en el navegador: http://localhost/SERCOLTURBOT/public/crear_usuario.php
 * ELIMINAR DESPUÉS DE USAR
 */

require_once(__DIR__ . '/../config/database.php');

// ============================================
// CONFIGURACIÓN DE USUARIOS A CREAR
// ============================================
$usuarios = [
    [
        'username' => 'admin',
        'email' => 'admin@sercoltur.com',
        'password' => 'Admin2025!',  // CAMBIA ESTA CONTRASEÑA
        'nombre' => 'Administrador',
        'rol' => 'admin',
        'telefono' => '573001234567'
    ],
    [
        'username' => 'carlos',
        'email' => 'carlos@sercoltur.com', 
        'password' => 'Carlos2025!',  // CAMBIA ESTA CONTRASEÑA
        'nombre' => 'Carlos Mendoza',
        'rol' => 'operador',
        'telefono' => '573136761256'
    ],
    [
        'username' => 'operador',
        'email' => 'operador@sercoltur.com',
        'password' => 'Operador2025!',  // CAMBIA ESTA CONTRASEÑA
        'nombre' => 'Operador Principal',
        'rol' => 'operador',
        'telefono' => '573052100297'
    ]
];

// ============================================
// CREAR TABLAS SI NO EXISTEN
// ============================================
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            rol ENUM('admin', 'operador', 'guia', 'busero') DEFAULT 'operador',
            telefono VARCHAR(20) DEFAULT NULL,
            avatar VARCHAR(255) DEFAULT NULL,
            activo TINYINT(1) DEFAULT 1,
            ultimo_acceso DATETIME DEFAULT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT DEFAULT NULL,
            ip VARCHAR(45) NOT NULL,
            user_agent TEXT,
            exitoso TINYINT(1) DEFAULT 0,
            fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "<h2>✅ Tablas creadas correctamente</h2>";

} catch (PDOException $e) {
    echo "<h2>❌ Error creando tablas: " . $e->getMessage() . "</h2>";
}

// ============================================
// CREAR USUARIOS
// ============================================
echo "<h2>🔐 Creando usuarios...</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse:collapse; font-family:Arial;'>";
echo "<tr style='background:#333;color:#fff;'><th>Usuario</th><th>Email</th><th>Contraseña</th><th>Rol</th><th>Estado</th></tr>";

foreach ($usuarios as $user) {
    try {
        // Verificar si ya existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
        $stmt->execute([$user['username'], $user['email']]);
        
        if ($stmt->fetch()) {
            // Actualizar contraseña si existe
            $hash = password_hash($user['password'], PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE usuarios SET password = ?, nombre = ?, rol = ?, telefono = ? WHERE username = ? OR email = ?")
                ->execute([$hash, $user['nombre'], $user['rol'], $user['telefono'], $user['username'], $user['email']]);
            $estado = "🔄 Actualizado";
            $color = "#ffc107";
        } else {
            // Crear nuevo
            $hash = password_hash($user['password'], PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO usuarios (username, email, password, nombre, rol, telefono, activo) VALUES (?, ?, ?, ?, ?, ?, 1)")
                ->execute([$user['username'], $user['email'], $hash, $user['nombre'], $user['rol'], $user['telefono']]);
            $estado = "✅ Creado";
            $color = "#28a745";
        }
        
        echo "<tr>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['email']}</td>";
        echo "<td><code>{$user['password']}</code></td>";
        echo "<td>{$user['rol']}</td>";
        echo "<td style='background:$color;color:#fff;text-align:center;'>$estado</td>";
        echo "</tr>";
        
    } catch (PDOException $e) {
        echo "<tr>";
        echo "<td>{$user['username']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['password']}</td>";
        echo "<td>{$user['rol']}</td>";
        echo "<td style='background:#dc3545;color:#fff;'>❌ Error: {$e->getMessage()}</td>";
        echo "</tr>";
    }
}

echo "</table>";

// ============================================
// MOSTRAR USUARIOS ACTUALES
// ============================================
echo "<h2>📋 Usuarios en la base de datos:</h2>";
$stmt = $pdo->query("SELECT id, username, email, nombre, rol, activo, ultimo_acceso, fecha_creacion FROM usuarios ORDER BY id");
$usuariosDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse:collapse; font-family:Arial;'>";
echo "<tr style='background:#333;color:#fff;'><th>ID</th><th>Usuario</th><th>Email</th><th>Nombre</th><th>Rol</th><th>Activo</th><th>Último Acceso</th></tr>";

foreach ($usuariosDB as $u) {
    $activo = $u['activo'] ? '✅' : '❌';
    $ultimoAcceso = $u['ultimo_acceso'] ?? 'Nunca';
    echo "<tr>";
    echo "<td>{$u['id']}</td>";
    echo "<td><strong>{$u['username']}</strong></td>";
    echo "<td>{$u['email']}</td>";
    echo "<td>{$u['nombre']}</td>";
    echo "<td>{$u['rol']}</td>";
    echo "<td style='text-align:center;'>$activo</td>";
    echo "<td>$ultimoAcceso</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><br>";
echo "<div style='background:#ffeb3b;padding:20px;border-radius:10px;max-width:600px;'>";
echo "<h3>⚠️ IMPORTANTE</h3>";
echo "<p><strong>ELIMINA ESTE ARCHIVO</strong> después de crear los usuarios por seguridad.</p>";
echo "<p>Archivo a eliminar: <code>crear_usuario.php</code></p>";
echo "</div>";

echo "<br><br>";
echo "<a href='login.php' style='display:inline-block;padding:15px 30px;background:#6366f1;color:#fff;text-decoration:none;border-radius:10px;font-weight:bold;'>🔐 Ir al Login</a>";
?>