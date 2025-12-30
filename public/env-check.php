
<?php
header('Content-Type: text/plain; charset=utf-8');

$keys = [
  'DB_HOST','DB_NAME','DB_USER','DB_PASS','DB_PORT',
  'DB_DATABASE','DB_USERNAME','DB_PASSWORD',
  'DATABASE_URL','RAILWAY_DATABASE_URL'
];

foreach ($keys as $k) {
  $v = getenv($k);
  printf("%s = %s\n", $k, ($v !== false && $v !== '' ? $v : '(no definida)'));
}
