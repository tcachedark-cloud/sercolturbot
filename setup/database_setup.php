<?php
$pdo = new PDO("mysql:host=localhost", "root", "C121672@c", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS sercolturbot");
    $pdo->exec("USE sercolturbot");
    
    // Tabla de Clientes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE,
            telefono VARCHAR(20) NOT NULL,
            documento VARCHAR(20) UNIQUE,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Tabla de Asesores
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS asesores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            especialidad VARCHAR(100),
            disponible BOOLEAN DEFAULT TRUE,
            fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Tabla de Guías
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS guias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            idiomas VARCHAR(200),
            experiencia INT,
            disponible BOOLEAN DEFAULT TRUE,
            calificacion DECIMAL(3,2),
            fecha_contratacion DATE,
            telefono VARCHAR(20)
        )
    ");
    
    // Tabla de Buses
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS buses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_busero VARCHAR(100) NOT NULL,
            placa VARCHAR(20) UNIQUE NOT NULL,
            capacidad INT NOT NULL,
            disponible BOOLEAN DEFAULT TRUE,
            estado VARCHAR(30),
            telefono VARCHAR(20),
            marca VARCHAR(50),
            modelo VARCHAR(50),
            año INT
        )
    ");
    
    // Tabla de Tours/Paquetes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tours (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            precio DECIMAL(10,2) NOT NULL,
            duracion_dias INT,
            destino VARCHAR(100),
            capacidad_maxima INT,
            activo BOOLEAN DEFAULT TRUE
        )
    ");
    
    // Tabla de Reservas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reservas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            tour_id INT,
            fecha_reserva TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_inicio DATE NOT NULL,
            fecha_fin DATE,
            cantidad_personas INT NOT NULL,
            precio_total DECIMAL(10,2),
            estado VARCHAR(30) DEFAULT 'pendiente',
            notas TEXT,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            FOREIGN KEY (tour_id) REFERENCES tours(id)
        )
    ");
    
    // Tabla de Asignaciones (Reserva-Guía-Bus)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS asignaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reserva_id INT NOT NULL,
            guia_id INT,
            bus_id INT,
            asesor_id INT,
            fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
            FOREIGN KEY (guia_id) REFERENCES guias(id),
            FOREIGN KEY (bus_id) REFERENCES buses(id),
            FOREIGN KEY (asesor_id) REFERENCES asesores(id)
        )
    ");
    
    // Tabla para Historial del Bot (Respuestas en Tiempo Real)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS bot_conversaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT,
            asesor_id INT,
            mensaje_cliente TEXT NOT NULL,
            respuesta_bot TEXT,
            tipo_consulta VARCHAR(50),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            resuelta BOOLEAN DEFAULT FALSE
        )
    ");
    
    // Tabla de Disponibilidad Diaria
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS disponibilidad (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tipo VARCHAR(30) NOT NULL,
            referencia_id INT NOT NULL,
            fecha DATE NOT NULL,
            disponible BOOLEAN DEFAULT TRUE,
            UNIQUE KEY unique_disponibilidad (tipo, referencia_id, fecha)
        )
    ");
    
    // Tabla de Comentarios/Calificaciones
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS comentarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            reserva_id INT,
            calificacion INT,
            comentario TEXT,
            fecha_comentario TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id),
            FOREIGN KEY (reserva_id) REFERENCES reservas(id)
        )
    ");
    
    echo "<div style='background: #4CAF50; color: white; padding: 20px; border-radius: 5px; text-align: center;'>";
    echo "<h2>✓ Base de datos creada exitosamente</h2>";
    echo "<p>Todas las tablas han sido inicializadas correctamente</p>";
    echo "<ul style='text-align: left;'>";
    echo "<li>✓ Tabla clientes</li>";
    echo "<li>✓ Tabla asesores</li>";
    echo "<li>✓ Tabla guías</li>";
    echo "<li>✓ Tabla buses</li>";
    echo "<li>✓ Tabla tours</li>";
    echo "<li>✓ Tabla reservas</li>";
    echo "<li>✓ Tabla asignaciones</li>";
    echo "<li>✓ Tabla bot_conversaciones (Bot tiempo real)</li>";
    echo "<li>✓ Tabla disponibilidad</li>";
    echo "<li>✓ Tabla comentarios</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f44336; color: white; padding: 20px; border-radius: 5px;'>";
    echo "<h2>✗ Error en la creación de la BD</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>