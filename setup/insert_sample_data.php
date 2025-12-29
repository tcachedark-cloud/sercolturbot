<?php
/**
 * INSERTAR DATOS DE EJEMPLO
 * Ejecuta este script para llenar la BD con datos de prueba
 * Acceso: http://localhost/SERCOLTURBOT/setup/insert_sample_data.php
 */

require_once(__DIR__ . '/../config/database.php');

try {
    // Limpiar tablas (opcional - comentar si no quieres perder datos)
    // $pdo->exec("TRUNCATE TABLE asignaciones");
    // $pdo->exec("TRUNCATE TABLE comentarios");
    // $pdo->exec("TRUNCATE TABLE disponibilidad");
    // $pdo->exec("TRUNCATE TABLE reservas");
    // $pdo->exec("TRUNCATE TABLE tours");
    // $pdo->exec("TRUNCATE TABLE buses");
    // $pdo->exec("TRUNCATE TABLE guias");
    // $pdo->exec("TRUNCATE TABLE asesores");
    // $pdo->exec("TRUNCATE TABLE clientes");

    echo "<h2>📝 Insertando datos de ejemplo...</h2>";

    // 1. ASESORES
    echo "<h3>1️⃣ Asesores</h3>";
    $asesores = [
        ['nombre' => 'Carlos Mendoza', 'especialidad' => 'Tours de aventura', 'telefono' => '+51987654321', 'email' => 'carlos@sercoltur.com'],
        ['nombre' => 'María López', 'especialidad' => 'Tours culturales', 'telefono' => '+51987654322', 'email' => 'maria@sercoltur.com'],
        ['nombre' => 'Juan García', 'especialidad' => 'Tours gastronómicos', 'telefono' => '+51987654323', 'email' => 'juan@sercoltur.com'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO asesores (nombre, especialidad, telefono, email) VALUES (?, ?, ?, ?)");
    foreach ($asesores as $asesor) {
        $stmt->execute([$asesor['nombre'], $asesor['especialidad'], $asesor['telefono'], $asesor['email']]);
        echo "✓ {$asesor['nombre']}<br>";
    }

    // 2. GUÍAS
    echo "<h3>2️⃣ Guías Turísticos</h3>";
    $guias = [
        ['nombre' => 'Roberto Inca', 'idiomas' => 'Español, Inglés, Quechua', 'experiencia' => 15, 'calificacion' => 4.9, 'disponible' => 1, 'telefono' => '+51912345671'],
        ['nombre' => 'Andres Quipucs', 'idiomas' => 'Español, Inglés', 'experiencia' => 12, 'calificacion' => 4.8, 'disponible' => 1, 'telefono' => '+51912345672'],
        ['nombre' => 'Patricia Huaylas', 'idiomas' => 'Español, Francés', 'experiencia' => 10, 'calificacion' => 4.7, 'disponible' => 0, 'telefono' => '+51912345673'],
        ['nombre' => 'Diego Puma', 'idiomas' => 'Español, Inglés, Alemán', 'experiencia' => 20, 'calificacion' => 5.0, 'disponible' => 1, 'telefono' => '+51912345674'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO guias (nombre, idiomas, experiencia, calificacion, disponible, telefono) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($guias as $guia) {
        $stmt->execute([$guia['nombre'], $guia['idiomas'], $guia['experiencia'], $guia['calificacion'], $guia['disponible'], $guia['telefono']]);
        echo "✓ {$guia['nombre']}<br>";
    }

    // 3. BUSES
    echo "<h3>3️⃣ Buses</h3>";
    $buses = [
        ['placa' => 'ABC-123', 'capacidad' => 50, 'marca' => 'Mercedes', 'modelo' => 'Sprinter', 'nombre_busero' => 'Transportes del Sur', 'telefono' => '+51998765431', 'estado' => 'activo', 'disponible' => 1],
        ['placa' => 'XYZ-789', 'capacidad' => 40, 'marca' => 'Volvo', 'modelo' => '8700', 'nombre_busero' => 'Buses del Perú', 'telefono' => '+51998765432', 'estado' => 'activo', 'disponible' => 1],
        ['placa' => 'DEF-456', 'capacidad' => 35, 'marca' => 'Hyundai', 'modelo' => 'County', 'nombre_busero' => 'Transportes Andinos', 'telefono' => '+51998765433', 'estado' => 'mantenimiento', 'disponible' => 0],
        ['placa' => 'GHI-012', 'capacidad' => 45, 'marca' => 'Scania', 'modelo' => 'K360', 'nombre_busero' => 'Turismo Cusco', 'telefono' => '+51998765434', 'estado' => 'activo', 'disponible' => 1],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO buses (placa, capacidad, marca, modelo, nombre_busero, telefono, estado, disponible) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($buses as $bus) {
        $stmt->execute([$bus['placa'], $bus['capacidad'], $bus['marca'], $bus['modelo'], $bus['nombre_busero'], $bus['telefono'], $bus['estado'], $bus['disponible']]);
        echo "✓ {$bus['nombre_busero']} - {$bus['placa']}<br>";
    }

    // 4. TOURS - MEDELLÍN Y ANTIOQUIA
    echo "<h3>4️⃣ Tours</h3>";
    $tours = [
        [
            'nombre' => 'Tour a Guatapé',
            'descripcion' => 'Visita a la Piedra del Peñol, paseo en barco por la represa, municipio de Guatapé, Guarne y Marinilla. Incluye: Transporte, desayuno, almuerzo, barco rumbero y guía acompañante',
            'destino' => 'Guatapé',
            'duracion_dias' => 1,
            'precio' => 109000,
            'capacidad_maxima' => 45,
            'activo' => 1
        ],
        [
            'nombre' => 'City Tour Comuna 13',
            'descripcion' => 'Visita: Parque del poblado, Pueblito paisa, Pies Descalzos, Plaza Botero, Parques del Río, graffitis de Medellín y escaleras eléctricas Comuna 13. Incluye: Transporte, almuerzo, metro cable y guía',
            'destino' => 'Medellín - Comuna 13',
            'duracion_dias' => 1,
            'precio' => 99000,
            'capacidad_maxima' => 35,
            'activo' => 1
        ],
        [
            'nombre' => 'Tour Navideño',
            'descripcion' => 'Recorrido panorámico por la ciudad, caminata por Parques del Río y municipio cercano para ver alumbrados. Incluye: Transporte, degustación de licor, música y ambiente familiar. Disponible hasta enero 2026',
            'destino' => 'Medellín',
            'duracion_dias' => 1,
            'precio' => 65000,
            'capacidad_maxima' => 40,
            'activo' => 1
        ],
        [
            'nombre' => 'Tour Parque Hacienda Nápoles + Santorini',
            'descripcion' => 'Visita a plaza Santorini, lago hipopótamos, amazon safari, sabana africana, museos, mariposario y más. Pasaporte básico o safari completo. Incluye: Transporte y desayuno',
            'destino' => 'Hacienda Nápoles',
            'duracion_dias' => 1,
            'precio' => 228000,
            'capacidad_maxima' => 50,
            'activo' => 1
        ],
        [
            'nombre' => 'Paquete Vibrante - Medellín y Guatapé',
            'descripcion' => 'Un recorrido inolvidable combinando lo mejor de Medellín y Guatapé. Incluye: Transporte IDA/REGRESO, desayuno, almuerzo, guía acompañante y tarjeta de asistencia médica',
            'destino' => 'Medellín - Guatapé',
            'duracion_dias' => 1,
            'precio' => 195000,
            'capacidad_maxima' => 40,
            'activo' => 1
        ],
        [
            'nombre' => 'Chiva Rumbera',
            'descripcion' => 'Recorrido panorámico nocturno en chiva: Avenida 70, Puente de la 4 Sur, Parque El Poblado, Parque Lleras, Provenza y Milla de Oro. Incluye: Transporte y degustación de cerveza y bebidas',
            'destino' => 'Medellín',
            'duracion_dias' => 1,
            'precio' => 65000,
            'capacidad_maxima' => 35,
            'activo' => 1
        ],
        [
            'nombre' => 'City Tour Medellín',
            'descripcion' => 'Visita: Parque del poblado, Plaza Botero, Parque de los deseos, Pies Descalzos, Pueblito Paisa (Cerro Nutibara). Incluye: Transporte, guía acompañante y asistencia médica',
            'destino' => 'Medellín',
            'duracion_dias' => 1,
            'precio' => 65000,
            'capacidad_maxima' => 35,
            'activo' => 1
        ],
        [
            'nombre' => 'Solo Comuna 13',
            'descripcion' => 'Enfoque en Comuna 13: Graffitis Medellín, escaleras eléctricas y recorrido artístico. Incluye: Transporte, ingreso Metro y Metro Cable, guía bilingüe disponible',
            'destino' => 'Medellín - Comuna 13',
            'duracion_dias' => 1,
            'precio' => 70000,
            'capacidad_maxima' => 30,
            'activo' => 1
        ],
        [
            'nombre' => 'Tour a Jardín Antioquia',
            'descripcion' => 'Visita municipio de Hispania, Andes, Basílica de la Inmaculada Concepción, Casa de los dulces. Recorrido en Chiva. Incluye: Transporte, desayuno, almuerzo, refrigerio y guía',
            'destino' => 'Jardín, Antioquia',
            'duracion_dias' => 1,
            'precio' => 130000,
            'capacidad_maxima' => 40,
            'activo' => 1
        ],
        [
            'nombre' => 'Tour a Río Claro',
            'descripcion' => 'Aventura extrema: Rafting, body rafting, hidro senderismo y espeleología en Caverna del Cóndor. Incluye: Transporte, desayuno, almuerzo, guía local y asistencia médica',
            'destino' => 'Río Claro',
            'duracion_dias' => 1,
            'precio' => 220000,
            'capacidad_maxima' => 25,
            'activo' => 1
        ],
        [
            'nombre' => 'Tour a Santa Fe de Antioquia',
            'descripcion' => 'Visita: Túnel y Puente de Occidente, Plazuela Santa Bárbara, Catedral, Museo Juan del Corral, artesanías y dulces. Incluye: Transporte, almuerzo y guía acompañante',
            'destino' => 'Santa Fe de Antioquia',
            'duracion_dias' => 1,
            'precio' => 120000,
            'capacidad_maxima' => 40,
            'activo' => 1
        ],
        [
            'nombre' => 'Tour de Café',
            'descripcion' => 'Experiencia cafetera: Kit de café blanqueado, degustación de café especial, catación de cafés especiales tipo miel. Incluye: Transporte, almuerzo tipo fiambre paisa y bebidas refrescantes',
            'destino' => 'Región Cafetera',
            'duracion_dias' => 1,
            'precio' => 220000,
            'capacidad_maxima' => 35,
            'activo' => 1
        ],
        [
            'nombre' => 'Tour del Parapente',
            'descripcion' => 'Vuela en parapente tándem con piloto experimentado por 15-20 minutos. Incluye: Fotos/videos full HD, equipo de seguridad homologado, hidratación de bienvenida y asistencia médica',
            'destino' => 'Medellín',
            'duracion_dias' => 1,
            'precio' => 350000,
            'capacidad_maxima' => 15,
            'activo' => 1
        ],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO tours (nombre, descripcion, destino, duracion_dias, precio, capacidad_maxima, activo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($tours as $tour) {
        $stmt->execute([
            $tour['nombre'],
            $tour['descripcion'],
            $tour['destino'],
            $tour['duracion_dias'],
            $tour['precio'],
            $tour['capacidad_maxima'],
            $tour['activo']
        ]);
        echo "✓ {$tour['nombre']}<br>";
    }

    // 5. CLIENTES
    echo "<h3>5️⃣ Clientes</h3>";
    $clientes = [
        ['nombre' => 'Jorge Rivera', 'email' => 'jorge@example.com', 'telefono' => '+51987123456', 'documento' => '12345678'],
        ['nombre' => 'Ana Martínez', 'email' => 'ana@example.com', 'telefono' => '+51987123457', 'documento' => '87654321'],
        ['nombre' => 'Michael Johnson', 'email' => 'michael@example.com', 'telefono' => '+1 555 123 4567', 'documento' => 'US123456789'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO clientes (nombre, email, telefono, documento) VALUES (?, ?, ?, ?)");
    foreach ($clientes as $cliente) {
        $stmt->execute([
            $cliente['nombre'],
            $cliente['email'],
            $cliente['telefono'],
            $cliente['documento']
        ]);
        echo "✓ {$cliente['nombre']}<br>";
    }

    // 6. RESERVAS
    echo "<h3>6️⃣ Reservas</h3>";
    $reservas = [
        ['cliente_id' => 1, 'tour_id' => 1, 'fecha_inicio' => '2024-03-15', 'cantidad_personas' => 4, 'precio_total' => 4800, 'estado' => 'confirmada'],
        ['cliente_id' => 2, 'tour_id' => 2, 'fecha_inicio' => '2024-02-20', 'cantidad_personas' => 2, 'precio_total' => 900, 'estado' => 'pendiente'],
        ['cliente_id' => 3, 'tour_id' => 4, 'fecha_inicio' => '2024-02-25', 'cantidad_personas' => 6, 'precio_total' => 1500, 'estado' => 'confirmada'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO reservas (cliente_id, tour_id, fecha_inicio, cantidad_personas, precio_total, estado) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($reservas as $reserva) {
        $stmt->execute([
            $reserva['cliente_id'],
            $reserva['tour_id'],
            $reserva['fecha_inicio'],
            $reserva['cantidad_personas'],
            $reserva['precio_total'],
            $reserva['estado']
        ]);
        echo "✓ Reserva para cliente {$reserva['cliente_id']}<br>";
    }

    // 7. ASIGNACIONES
    echo "<h3>7️⃣ Asignaciones</h3>";
    $asignaciones = [
        ['reserva_id' => 1, 'guia_id' => 1, 'bus_id' => 1],
        ['reserva_id' => 2, 'guia_id' => 2, 'bus_id' => 2],
        ['reserva_id' => 3, 'guia_id' => 4, 'bus_id' => 4],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO asignaciones (reserva_id, guia_id, bus_id) VALUES (?, ?, ?)");
    foreach ($asignaciones as $asignacion) {
        $stmt->execute([
            $asignacion['reserva_id'],
            $asignacion['guia_id'],
            $asignacion['bus_id']
        ]);
        echo "✓ Asignación para reserva {$asignacion['reserva_id']}<br>";
    }

    echo "<hr>";
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<h3>✅ ¡Datos insertados exitosamente!</h3>";
    echo "<p>Ahora puedes ver el dashboard en: <a href='../public/dashboard.php'>Dashboard</a></p>";
    echo "<p>O el chat en: <a href='../public/chat_demo.php'>Chat Web</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Insertar Datos</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        h2, h3 { color: #667eea; }
    </style>
</head>
<body>
<div class='container'>
    <!-- El contenido se inserta con echo arriba -->
</div>
</body>
</html>
