<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * PANEL ADMINISTRATIVO DE FAQs - SERCOLTURBOT
 * ═══════════════════════════════════════════════════════════════
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

// Validar autenticación
if (!isset($_SESSION['admin_loggedin'])) {
    header('Location: ../public/login.php');
    exit;
}

require_once(__DIR__ . '/../config/database.php');

$pdo = getDatabase();
if (!$pdo) {
    die('Error de conexión a base de datos');
}

// Variables iniciales
$mensaje = '';
$tipo_mensaje = '';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ═══════════════════════════════════════════════════════════════
// PROCESAR FORMULARIOS
// ═══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($action) {
            case 'crear':
                $pregunta = trim($_POST['pregunta'] ?? '');
                $respuesta = trim($_POST['respuesta'] ?? '');
                $palabras_clave = trim($_POST['palabras_clave'] ?? '');
                $categoria = trim($_POST['categoria'] ?? 'General');
                
                if (empty($pregunta) || empty($respuesta)) {
                    throw new Exception('La pregunta y respuesta son requeridas');
                }
                
                $pdo->prepare("
                    INSERT INTO faqs (pregunta, respuesta, palabras_clave, categoria, activo)
                    VALUES (?, ?, ?, ?, 1)
                ")->execute([$pregunta, $respuesta, $palabras_clave, $categoria]);
                
                $mensaje = "✅ FAQ creada exitosamente";
                $tipo_mensaje = "success";
                break;
                
            case 'editar':
                $id = intval($_POST['id'] ?? 0);
                $pregunta = trim($_POST['pregunta'] ?? '');
                $respuesta = trim($_POST['respuesta'] ?? '');
                $palabras_clave = trim($_POST['palabras_clave'] ?? '');
                $categoria = trim($_POST['categoria'] ?? 'General');
                
                if (empty($id) || empty($pregunta) || empty($respuesta)) {
                    throw new Exception('Datos inválidos');
                }
                
                $pdo->prepare("
                    UPDATE faqs 
                    SET pregunta = ?, respuesta = ?, palabras_clave = ?, categoria = ?
                    WHERE id = ?
                ")->execute([$pregunta, $respuesta, $palabras_clave, $categoria, $id]);
                
                $mensaje = "✅ FAQ actualizada exitosamente";
                $tipo_mensaje = "success";
                break;
                
            case 'eliminar':
                $id = intval($_POST['id'] ?? 0);
                if (empty($id)) {
                    throw new Exception('ID inválido');
                }
                
                $pdo->prepare("DELETE FROM faqs WHERE id = ?")->execute([$id]);
                
                $mensaje = "✅ FAQ eliminada";
                $tipo_mensaje = "success";
                break;
                
            case 'toggle':
                $id = intval($_POST['id'] ?? 0);
                if (empty($id)) {
                    throw new Exception('ID inválido');
                }
                
                $faq = $pdo->prepare("SELECT activo FROM faqs WHERE id = ?")->fetchColumn($id);
                $nuevo_estado = $faq ? 0 : 1;
                
                $pdo->prepare("UPDATE faqs SET activo = ? WHERE id = ?")->execute([$nuevo_estado, $id]);
                
                $mensaje = "✅ Estado actualizado";
                $tipo_mensaje = "success";
                break;
        }
    } catch (Exception $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// Obtener FAQs
$faqs = $pdo->query("
    SELECT * FROM faqs 
    ORDER BY categoria, id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías únicas
$categorias = $pdo->query("
    SELECT DISTINCT categoria FROM faqs ORDER BY categoria
")->fetchAll(PDO::FETCH_ASSOC);

// FAQs para editar
$faq_editar = null;
if ($action === 'editar_form') {
    $id = intval($_GET['id'] ?? 0);
    $faq_editar = $pdo->prepare("SELECT * FROM faqs WHERE id = ?")->fetch(PDO::FETCH_ASSOC);
    if (!$faq_editar) {
        $faq_editar = null;
    }
}

// Estadísticas
$stats = [
    'total' => count($faqs),
    'activas' => count(array_filter($faqs, fn($f) => $f['activo'] == 1)),
    'inactivas' => count(array_filter($faqs, fn($f) => $f['activo'] == 0))
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de FAQs - SERCOLTUR</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255,255,255,0.95);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .stat-card h3 {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        
        .mensaje {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        
        .mensaje.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }
        
        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }
        
        .main {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .card {
            background: rgba(255,255,255,0.95);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
        }
        
        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .faq-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .faq-item.inactive {
            opacity: 0.6;
        }
        
        .faq-item h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .faq-item .categoria {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            margin-bottom: 10px;
        }
        
        .faq-item p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .faq-item .keywords {
            color: #999;
            font-size: 12px;
            margin-bottom: 10px;
        }
        
        .faq-item .keywords strong {
            color: #667eea;
        }
        
        .faq-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .faq-actions a,
        .faq-actions button {
            padding: 8px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .faq-actions .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .faq-actions .btn-edit:hover {
            background: #138496;
        }
        
        .faq-actions .btn-toggle {
            background: #ffc107;
            color: #333;
        }
        
        .faq-actions .btn-toggle:hover {
            background: #e0a800;
        }
        
        .faq-actions .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .faq-actions .btn-delete:hover {
            background: #c82333;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            color: #333;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        
        @media (max-width: 768px) {
            .main {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📚 Panel de FAQs</h1>
            <p>Gestiona las preguntas frecuentes del bot</p>
            <div class="stats">
                <div class="stat-card">
                    <h3>Total</h3>
                    <div class="value"><?php echo $stats['total']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Activas</h3>
                    <div class="value"><?php echo $stats['activas']; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Inactivas</h3>
                    <div class="value"><?php echo $stats['inactivas']; ?></div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="main">
            <!-- FORMULARIO DE CREACIÓN/EDICIÓN -->
            <div class="card">
                <h2><?php echo $faq_editar ? '✏️ Editar FAQ' : '➕ Nueva FAQ'; ?></h2>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $faq_editar ? 'editar' : 'crear'; ?>">
                    
                    <?php if ($faq_editar): ?>
                        <input type="hidden" name="id" value="<?php echo $faq_editar['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>📋 Categoría</label>
                        <input type="text" name="categoria" placeholder="Ej: Reservas, Tours, Pagos..."
                            value="<?php echo htmlspecialchars($faq_editar['categoria'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>❓ Pregunta</label>
                        <input type="text" name="pregunta" placeholder="¿Cuál es tu pregunta?"
                            value="<?php echo htmlspecialchars($faq_editar['pregunta'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>💬 Respuesta</label>
                        <textarea name="respuesta" placeholder="Escriba la respuesta completa..."
                            required><?php echo htmlspecialchars($faq_editar['respuesta'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>🔑 Palabras Clave (separadas por comas)</label>
                        <input type="text" name="palabras_clave" placeholder="Ej: reserva, horario, contacto..."
                            value="<?php echo htmlspecialchars($faq_editar['palabras_clave'] ?? ''); ?>">
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn">
                            <?php echo $faq_editar ? '✅ Actualizar' : '➕ Crear'; ?>
                        </button>
                        <?php if ($faq_editar): ?>
                            <a href="?action=" class="btn btn-secondary">Cancelar</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- LISTA DE FAQs -->
            <div class="card">
                <h2>📚 FAQs Existentes (<?php echo $stats['total']; ?>)</h2>
                
                <div class="faq-list">
                    <?php if (empty($faqs)): ?>
                        <p style="text-align: center; color: #999; padding: 40px 0;">
                            No hay FAQs creadas. ¡Crea la primera!
                        </p>
                    <?php else: ?>
                        <?php foreach ($faqs as $faq): ?>
                            <div class="faq-item <?php echo $faq['activo'] ? '' : 'inactive'; ?>">
                                <div class="categoria"><?php echo htmlspecialchars($faq['categoria']); ?></div>
                                <h3><?php echo htmlspecialchars($faq['pregunta']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($faq['respuesta'], 0, 150)); ?>...</p>
                                
                                <?php if (!empty($faq['palabras_clave'])): ?>
                                    <div class="keywords">
                                        <strong>Palabras:</strong> <?php echo htmlspecialchars($faq['palabras_clave']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="faq-actions">
                                    <a href="?action=editar_form&id=<?php echo $faq['id']; ?>" class="btn-edit">✏️ Editar</a>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">
                                        <button type="submit" class="btn-toggle">
                                            <?php echo $faq['activo'] ? '👁️ Desactivar' : '🚫 Activar'; ?>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" style="display: inline;" 
                                        onsubmit="return confirm('¿Eliminar esta FAQ?');">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">
                                        <button type="submit" class="btn-delete">🗑️ Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
