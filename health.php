<?php
header('Content-Type: application/json');

$health = [
    'status' => 'OK',
    'php_version' => phpversion(),
    'extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'mysqli' => extension_loaded('mysqli'),
    ],
    'directory' => getcwd(),
    'files' => scandir('.'),
];

echo json_encode($health, JSON_PRETTY_PRINT);
?>
