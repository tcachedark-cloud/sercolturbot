
<?php
// Lee exactamente los nombres que ya tienes en Render.
// Ojo: si el nombre tiene espacios o guiones bajos finales, colócalo tal cual.
$dbName   = getenv('BASE DE DATOS MYSQL') ?: getenv('BASE DE DATOS MYSQL_');
$dbUser   = getenv('USUARIO MYSQL');
$dbPass   = getenv('CONTRASEÑA MYSQL');
$dbHost   = getenv('HOST MYSQL');
$dbPort   = getenv('MYSQLPORT');

// Construye el DSN usando host/puerto del proxy público de Railway.
$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // En Render puede ser útil activar SSL si tu proxy lo requiere:
        // PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);

    // Prueba rápida:
    $stmt = $pdo->query('SELECT 1 as ok');
    $row  = $stmt->fetch();
    echo "Conexión OK: " . $row['ok'] . PHP_EOL;

} catch (PDOException $e) {
    // Log claro para depuración en Render
    error_log("Error de conexión MySQL: " . $e->getMessage());
    http_response_code(500);
    echo "No se pudo conectar a MySQL. Revisa HOST/PORT/USER/PASS/DB.";
}
