
<?php
/**
 * Conector robusto para Render ⇄ Railway
 * - Lee primero DB_* (tu esquema actual)
 * - Si faltan, intenta DB_DATABASE/DB_USERNAME/DB_PASSWORD (esquema estándar)
 * - Si aún faltan, parsea DATABASE_URL (mysql://user:pass@host:port/db)
 */

function dbConfigFromEnv(): array {
    // Intento 1: tu esquema actual
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT');
    $db   = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');

    // Intento 2: esquema estándar
    if (!$db || !$user || !$pass) {
        $db   = $db   ?: getenv('DB_DATABASE');
        $user = $user ?: getenv('DB_USERNAME');
        $pass = $pass ?: getenv('DB_PASSWORD');
    }

    // Intento 3: parsear DATABASE_URL (si existe)
    $url = getenv('DATABASE_URL') ?: getenv('RAILWAY_DATABASE_URL');
    if ((!$host || !$port || !$db || !$user || !$pass) && $url) {
        $parts = parse_url($url);
        if ($parts !== false) {
            $host = $host ?: ($parts['host'] ?? null);
            $port = $port ?: ($parts['port'] ?? 3306);
            if (!$db && isset($parts['path'])) {
                $db = ltrim($parts['path'], '/');
            }
            $user = $user ?: ($parts['user'] ?? null);
            $pass = $pass ?: ($parts['pass'] ?? null);
        }
    }

    // Defaults de seguridad
    $host = $host ?: 'localhost';
    $port = $port ?: 3306;

    return compact('host', 'port', 'db', 'user', 'pass');
}

$config = dbConfigFromEnv();

// Diagnóstico mínimo en logs (temporal)
error_log('DB_HOST=' . ($config['host'] ?? '(vacío)'));
error_log('DB_NAME=' . ($config['db']   ?? '(vacío)'));
error_log('DB_USER=' . ($config['user'] ?? '(vacío)'));
error_log('DB_PORT=' . ($config['port'] ?? '(vacío)'));

try {
    if (!$config['host'] || !$config['db'] || !$config['user']) {
        throw new RuntimeException('Variables MySQL no configuradas (host/db/user faltan).');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['port'],
        $config['db']
    );

    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    die('❌ Error de conexión MySQL: ' . $e->getMessage());
}
