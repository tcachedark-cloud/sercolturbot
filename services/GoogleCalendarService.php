<?php
/**
 * ═══════════════════════════════════════════════════════════════
 * SERVICIO DE GOOGLE CALENDAR - SERCOLTURBOT
 * Sincronización automática de citas con Google Calendar
 * ═══════════════════════════════════════════════════════════════
 */

require_once(__DIR__ . '/../config/database.php');

class GoogleCalendarService {
    private $config;
    private $client;
    private $service;
    private $calendarId = 'primary';
    
    public function __construct() {
        $this->config = require(__DIR__ . '/../config/config_empresarial.php');
        
        if (!$this->config['google_calendar']['habilitado']) {
            return;
        }
        
        $this->inicializarCliente();
    }
    
    /**
     * Inicializar cliente de Google
     */
    private function inicializarCliente() {
        try {
            require_once(__DIR__ . '/../vendor/autoload.php');
            
            $client = new \Google_Client();
            $client->setApplicationName('SERCOLTUR Bot');
            $client->setScopes(['https://www.googleapis.com/auth/calendar']);
            
            $credentialsPath = __DIR__ . '/../config/google_credentials.json';
            if (!file_exists($credentialsPath)) {
                throw new Exception('google_credentials.json no encontrado');
            }
            
            $client->setAuthConfig($credentialsPath);
            $client->setAccessType('offline');
            $client->setPrompt('select_account');
            
            // Cargar token si existe
            $tokenPath = __DIR__ . '/../config/google_token.json';
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);
            } else {
                // Generar nuevo token
                $this->generarNuevoToken($client);
            }
            
            // Refrescar token si es necesario
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                }
            }
            
            $this->client = $client;
            $this->service = new \Google_Service_Calendar($client);
            
        } catch (Exception $e) {
            error_log("Error inicializando Google Calendar: " . $e->getMessage());
        }
    }
    
    /**
     * Generar nuevo token de autenticación
     */
    private function generarNuevoToken($client) {
        try {
            // Este código se ejecutaría una sola vez en setup
            $authUrl = $client->createAuthUrl();
            echo "Por favor visite este URL para autorizar:\n$authUrl\n";
            echo "Pegue el código de autorización aquí: ";
            
            $authCode = trim(fgets(STDIN));
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $tokenPath = __DIR__ . '/../config/google_token.json';
            
            if (!is_dir(__DIR__ . '/../config')) {
                mkdir(__DIR__ . '/../config', 0755, true);
            }
            
            file_put_contents($tokenPath, json_encode($accessToken));
            echo "Token guardado en $tokenPath\n";
            
        } catch (Exception $e) {
            throw new Exception("Error al generar token: " . $e->getMessage());
        }
    }
    
    /**
     * Crear evento en Google Calendar
     */
    public function crearEvento($datos) {
        if (!$this->service) {
            return ['success' => false, 'error' => 'Google Calendar no configurado'];
        }
        
        try {
            $event = new \Google_Service_Calendar_Event();
            
            // Información básica
            $event->setSummary($datos['titulo'] ?? 'Cita SERCOLTUR');
            $event->setDescription($datos['descripcion'] ?? 'Cita agendada en SERCOLTUR');
            
            // Fecha y hora
            $fecha_hora = new DateTime($datos['fecha_hora']);
            $startTime = new \Google_Service_Calendar_EventDateTime();
            $startTime->setDateTime($fecha_hora->format(DateTime::RFC3339));
            $startTime->setTimeZone('America/Bogota');
            $event->setStart($startTime);
            
            // Calcular duración
            $duracion = $datos['duracion'] ?? 30; // minutos
            $endTime = clone $fecha_hora;
            $endTime->add(new DateInterval('PT' . $duracion . 'M'));
            
            $end = new \Google_Service_Calendar_EventDateTime();
            $end->setDateTime($endTime->format(DateTime::RFC3339));
            $end->setTimeZone('America/Bogota');
            $event->setEnd($end);
            
            // Agregar asistente (cliente)
            if (!empty($datos['cliente_email'])) {
                $attendee = new \Google_Service_Calendar_EventAttendee();
                $attendee->setEmail($datos['cliente_email']);
                $attendee->setDisplayName($datos['cliente_nombre'] ?? 'Cliente');
                $event->setAttendees([$attendee]);
            }
            
            // Crear recordatorio automático
            $reminder = new \Google_Service_Calendar_EventReminder();
            $reminder->setMethod('email');
            $reminder->setMinutes(60); // Recordatorio 60 minutos antes
            
            $event->setReminders(new \Google_Service_Calendar_Event_Reminders([
                'useDefault' => false,
                'overrides' => [$reminder]
            ]));
            
            // Información adicional
            $event->setLocation($datos['ubicacion'] ?? 'SERCOLTUR');
            
            // Guardar evento
            $createdEvent = $this->service->events->insert($this->calendarId, $event);
            
            $this->registrarEvento($datos['cita_id'] ?? null, $createdEvent->getId(), 'creado');
            
            return [
                'success' => true,
                'event_id' => $createdEvent->getId(),
                'event_link' => $createdEvent->getHtmlLink(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Error creando evento en Google Calendar: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Actualizar evento en Google Calendar
     */
    public function actualizarEvento($eventId, $datos) {
        if (!$this->service) {
            return ['success' => false, 'error' => 'Google Calendar no configurado'];
        }
        
        try {
            $event = $this->service->events->get($this->calendarId, $eventId);
            
            if (!empty($datos['titulo'])) {
                $event->setSummary($datos['titulo']);
            }
            
            if (!empty($datos['fecha_hora'])) {
                $fecha_hora = new DateTime($datos['fecha_hora']);
                $startTime = new \Google_Service_Calendar_EventDateTime();
                $startTime->setDateTime($fecha_hora->format(DateTime::RFC3339));
                $startTime->setTimeZone('America/Bogota');
                $event->setStart($startTime);
                
                $duracion = $datos['duracion'] ?? 30;
                $endTime = clone $fecha_hora;
                $endTime->add(new DateInterval('PT' . $duracion . 'M'));
                
                $end = new \Google_Service_Calendar_EventDateTime();
                $end->setDateTime($endTime->format(DateTime::RFC3339));
                $end->setTimeZone('America/Bogota');
                $event->setEnd($end);
            }
            
            if (!empty($datos['descripcion'])) {
                $event->setDescription($datos['descripcion']);
            }
            
            $updatedEvent = $this->service->events->update($this->calendarId, $eventId, $event);
            
            $this->registrarEvento($datos['cita_id'] ?? null, $eventId, 'actualizado');
            
            return ['success' => true, 'event_id' => $updatedEvent->getId()];
            
        } catch (Exception $e) {
            error_log("Error actualizando evento: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Eliminar evento de Google Calendar
     */
    public function eliminarEvento($eventId) {
        if (!$this->service) {
            return ['success' => false, 'error' => 'Google Calendar no configurado'];
        }
        
        try {
            $this->service->events->delete($this->calendarId, $eventId);
            $this->registrarEvento(null, $eventId, 'eliminado');
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Error eliminando evento: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Obtener evento de Google Calendar
     */
    public function obtenerEvento($eventId) {
        if (!$this->service) {
            return null;
        }
        
        try {
            return $this->service->events->get($this->calendarId, $eventId);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Sincronizar citas de Google Calendar con BD
     */
    public function sincronizarDesdeGoogle() {
        if (!$this->service) {
            return ['success' => false, 'error' => 'Google Calendar no configurado'];
        }
        
        try {
            $pdo = getDatabase();
            if (!$pdo) {
                return ['success' => false, 'error' => 'Sin conexión a BD'];
            }
            
            // Obtener eventos de los próximos 30 días
            $params = [
                'maxResults' => 100,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => date('c'),
                'timeMax' => date('c', strtotime('+30 days'))
            ];
            
            $events = $this->service->events->listEvents($this->calendarId, $params);
            
            $sincronizados = 0;
            
            foreach ($events->getItems() as $event) {
                // Buscar en BD si existe
                $sql = "SELECT id FROM citas WHERE google_event_id = ?";
                $existe = $pdo->prepare($sql)->fetch(PDO::FETCH_ASSOC);
                
                if (!$existe && strpos($event->getSummary(), 'SERCOLTUR') !== false) {
                    // Crear cita en BD
                    $pdo->prepare("
                        INSERT INTO citas (
                            nombre, fecha_hora, servicio, codigo, estado, google_event_id
                        ) VALUES (?, ?, ?, ?, ?, ?)
                    ")->execute([
                        $event->getSummary(),
                        $event->getStart()->getDateTime(),
                        'Sincronizada de Google',
                        'SYNC-' . $event->getId(),
                        'confirmada',
                        $event->getId()
                    ]);
                    
                    $sincronizados++;
                }
            }
            
            return [
                'success' => true,
                'sincronizadas' => $sincronizados,
                'total' => count($events->getItems())
            ];
            
        } catch (Exception $e) {
            error_log("Error sincronizando desde Google: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Registrar evento en tabla de auditoría
     */
    private function registrarEvento($citaId, $googleEventId, $accion) {
        try {
            $pdo = getDatabase();
            if (!$pdo) return;
            
            $pdo->prepare("
                INSERT INTO google_calendar_events (cita_id, google_event_id, accion, fecha_registro)
                VALUES (?, ?, ?, NOW())
            ")->execute([$citaId, $googleEventId, $accion]);
            
        } catch (Exception $e) {
            // Silenciar si tabla no existe
        }
    }
    
    /**
     * Verificar si Google Calendar está configurado
     */
    public function estaConfigurado() {
        return $this->service !== null && $this->config['google_calendar']['habilitado'];
    }
    
    /**
     * Obtener lista de calendarios disponibles
     */
    public function obtenerCalendarios() {
        if (!$this->service) {
            return [];
        }
        
        try {
            $calendarList = $this->service->calendarList->listCalendarList();
            return $calendarList->getItems();
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
