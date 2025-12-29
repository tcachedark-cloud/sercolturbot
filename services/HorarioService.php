<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * SERVICIO DE HORARIOS DE ATENCIÓN
 * Agregar a tu carpeta services/
 * ═══════════════════════════════════════════════════════════════
 */

class HorarioService {
    private $config;
    
    public function __construct() {
        $configPath = __DIR__ . '/../config/config_empresarial.php';
        $this->config = file_exists($configPath) ? require $configPath : [];
    }
    
    /**
     * Verificar si está dentro del horario de atención
     */
    public function dentroDeHorario(): bool {
        if (empty($this->config['horarios']['habilitado'])) {
            return true; // Si no está configurado, siempre disponible
        }
        
        date_default_timezone_set($this->config['horarios']['zona_horaria'] ?? 'America/Bogota');
        
        $diaSemana = (int)date('w'); // 0=domingo, 1=lunes...
        $horaActual = date('H:i');
        
        $diaConfig = $this->config['horarios']['dias'][$diaSemana] ?? null;
        
        if (!$diaConfig || !$diaConfig['activo']) {
            return false;
        }
        
        return $horaActual >= $diaConfig['inicio'] && $horaActual <= $diaConfig['fin'];
    }
    
    /**
     * Obtener mensaje de fuera de horario
     */
    public function getMensajeFueraHorario(): string {
        return $this->config['horarios']['mensaje_fuera_horario'] ?? 
            "⏰ Estamos fuera de horario. Te responderemos pronto.\n\n🚌 SERCOLTUR";
    }
    
    /**
     * Obtener horario del día actual
     */
    public function getHorarioHoy(): ?array {
        date_default_timezone_set($this->config['horarios']['zona_horaria'] ?? 'America/Bogota');
        $diaSemana = (int)date('w');
        return $this->config['horarios']['dias'][$diaSemana] ?? null;
    }
    
    /**
     * Obtener todos los horarios
     */
    public function getTodosLosHorarios(): array {
        return $this->config['horarios']['dias'] ?? [];
    }
    
    /**
     * Generar mensaje con horarios
     */
    public function generarMensajeHorarios(): string {
        $dias = $this->config['horarios']['dias'] ?? [];
        
        $mensaje = "📅 *HORARIOS DE ATENCIÓN*\n\n";
        
        $diasNombres = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        
        foreach ($dias as $num => $dia) {
            if ($dia['activo']) {
                $mensaje .= "• {$diasNombres[$num]}: {$dia['inicio']} - {$dia['fin']}\n";
            } else {
                $mensaje .= "• {$diasNombres[$num]}: Cerrado\n";
            }
        }
        
        $mensaje .= "\n🚌 *SERCOLTUR*";
        
        return $mensaje;
    }
}