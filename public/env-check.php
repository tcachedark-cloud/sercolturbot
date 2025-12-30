<?php
header('Content-Type: text/plain; charset=utf-8');

$appUrl = 'https://sercolturbot-kwhr.onrender.com';

echo "=== SERCOLTURBOT - Verificación de Configuración ===\n";
echo "URL de la aplicación: $appUrl\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$keys = [
  'DB_HOST','DB_NAME','DB_USER','DB_PASS','DB_PORT',
  'DB_DATABASE','DB_USERNAME','DB_PASSWORD',
  'DATABASE_URL','RAILWAY_DATABASE_URL'
];

foreach ($keys as $k) {
  $v = getenv($k);
  $status = ($v !== false && $v !== '') ? '✅' : '❌';
  printf("%s %s = %s\n", $status, $k, ($v !== false && $v !== '' ? substr($v, 0, 50) : '(no definida)'));
}

echo "\n=== Instrucciones ===\n";
echo "1. Exportar BD local: $appUrl/scripts/export-database.php\n";
echo "2. Importar en Railway: Usar el archivo SQL descargado\n";
echo "3. Verificar conexión: $appUrl/public/status.php\n";
?>
