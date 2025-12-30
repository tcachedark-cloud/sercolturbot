<?php
// Cargar configuración de base de datos
require_once __DIR__ . '/config/database.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SERCOLTURBOT - Sistema de Reservas de Tours</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 { color: #333; }
        .status { margin: 20px 0; padding: 10px; border-radius: 4px; }
        .status.ok { background: #d4edda; color: #155724; }
        .status.error { background: #f8d7da; color: #721c24; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌍 SERCOLTURBOT</h1>
        <h2>Sistema de Reservas de Tours - Medellín</h2>
        
        <div class="status ok">
            ✅ PHP funcionando correctamente
        </div>
        
        <h3>🔗 Enlaces útiles:</h3>
        <ul>
            <li><a href="/env-check.php">Verificar variables de entorno</a></li>
            <li><a href="/status.php">Estado de conexión a BD</a></li>
            <li><a href="/scripts/export-database.php">Exportar base de datos</a></li>
        </ul>
        
        <hr>
        <p><small>Despliegue: Render | Base de datos: Railway MySQL</small></p>
    </div>
</body>
</html>