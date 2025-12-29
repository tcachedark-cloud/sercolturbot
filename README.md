# 🗄️ SERCOLTURBOT - Base de Datos Completa

Sistema completo de gestión de reservas turísticas con bot inteligente que responde en tiempo real.

## 📋 Contenido

### Tablas de la Base de Datos

| Tabla | Descripción |
|-------|------------|
| **clientes** | Datos de clientes con contacto |
| **asesores** | Personal asesor con especialidades |
| **guias** | Guías turísticos multilingües |
| **buses** | Transporte con capacidades |
| **tours** | Paquetes turísticos disponibles |
| **reservas** | Gestión de reservas con estado |
| **asignaciones** | Vinculación guía-bus-asesor por reserva |
| **bot_conversaciones** | Historial de conversaciones del bot |
| **disponibilidad** | Disponibilidad diaria de recursos |
| **comentarios** | Calificaciones y reseñas de clientes |

## 🚀 Instalación Rápida

### Opción 1: Mediante Panel Web (Recomendado)

1. Abre en tu navegador: `http://localhost/SERCOLTURBOT/setup/`
2. Haz clic en "✅ Crear Base de Datos"
3. ¡Listo! Todas las tablas se crearán automáticamente

### Opción 2: Mediante phpMyAdmin

1. Abre `http://localhost/phpmyadmin`
2. Descarga el archivo SQL desde `http://localhost/SERCOLTURBOT/setup/`
3. En phpMyAdmin: Importar → Selecciona el archivo → Ejecutar

### Opción 3: Línea de Comandos

```bash
mysql -u root -p < setup/database.sql
```

## 🤖 Bot Inteligente en Tiempo Real

El bot responde automáticamente a consultas sobre:
- 🎫 Reservas y paquetes
- 👨‍🏫 Información de guías
- 🚌 Detalles de transporte
- 📅 Disponibilidad de fechas
- 👨‍💼 Conexión con asesores

### API del Bot

**URL Base:** `routes/bot_api.php`

#### Endpoint: Enviar Mensaje

```
POST /routes/bot_api.php?action=mensaje

Parámetros:
- cliente_id (required): ID del cliente
- mensaje (required): Mensaje del cliente
- asesor_id (optional): ID del asesor asignado
```

**Ejemplo:**
```bash
curl -X POST "http://localhost/SERCOLTURBOT/routes/bot_api.php?action=mensaje" \
  -d "cliente_id=1&mensaje=Quiero reservar un tour"
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "respuesta": "¡Hola! Me gustaría ayudarte con tu reserva...",
    "tipo": "reservas",
    "timestamp": "2025-12-25 10:30:45"
  }
}
```

#### Endpoint: Obtener Historial

```
GET /routes/bot_api.php?action=conversaciones&cliente_id=1

Parámetros:
- cliente_id (required): ID del cliente
```

#### Endpoint: Marcar como Resuelta

```
POST /routes/bot_api.php?action=resolver

Parámetros:
- conversacion_id (required): ID de la conversación
```

#### Endpoint: Estadísticas del Bot

```
GET /routes/bot_api.php?action=estadisticas
```

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "total_conversaciones": 15,
      "clientes_unicos": 5,
      "tipo_consulta": "reservas",
      "resueltas": 12
    }
  ]
}
```

## 📝 Ejemplo de Uso en PHP

```php
<?php
require_once('config/database.php');
require_once('services/BotService.php');

$botService = new BotService($pdo);

// Procesar mensaje
$respuesta = $botService->procesarMensaje(
    cliente_id: 1,
    mensaje: "¿Cuáles son los tours disponibles?",
    asesor_id: null
);

echo $respuesta['respuesta'];
// Output: ¡Hola! 👋 Bienvenido a SERCOLTURBOT...
?>
```

## 🔌 Ejemplo de Uso en JavaScript

```javascript
async function enviarMensajeAlBot(clienteId, mensaje) {
    const response = await fetch('routes/bot_api.php?action=mensaje', {
        method: 'POST',
        body: new URLSearchParams({
            cliente_id: clienteId,
            mensaje: mensaje
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        console.log('Respuesta:', data.data.respuesta);
        console.log('Tipo:', data.data.tipo);
    }
}

// Uso
enviarMensajeAlBot(1, 'Quiero info sobre tours a Cartagena');
```

## 🎯 Ejemplos de Consultas del Bot

El bot identifica automáticamente el tipo de consulta:

### Sobre Reservas
- "Quiero hacer una reserva"
- "¿Cuánto cuesta el tour a Cartagena?"
- "¿Hay disponibilidad para 5 personas?"

**Respuesta automática:** Información sobre tours y precios

### Sobre Guías
- "¿Hablan francés los guías?"
- "¿Quién será mi acompañante?"
- "¿Cuál es la experiencia de los guías?"

**Respuesta automática:** Detalles de guías especializados

### Sobre Transporte
- "¿Cómo es el bus?"
- "¿Qué capacidad tiene el transporte?"
- "¿Qué tipo de vehículo utilizan?"

**Respuesta automática:** Especificaciones de buses

### Solicitud de Asesor
- "Necesito hablar con un asesor"
- "¿Puedo hablar con alguien?"
- "Quiero más información personalizada"

**Respuesta automática:** Conecta con un asesor disponible

## 📊 Datos de Prueba Incluidos

### Clientes
- Juan Pérez (juan@email.com)
- María García (maria@email.com)
- Carlos López (carlos@email.com)

### Asesores
- Roberto Silva (Tours Nacionales)
- Ana Martínez (Tours Internacionales)
- Pedro Gómez (Grupos y Eventos)

### Guías
- Santiago Ruiz (8 años experiencia, habla 3 idiomas)
- Laura Díaz (5 años experiencia)
- Miguel Ángel (10 años experiencia)

### Buses
- Transportes Colombia (45 pasajeros)
- Viajes Seguros (50 pasajeros)
- Rutas del País (35 pasajeros)

### Tours
- Cartagena Clásica ($450 - 3 días)
- Santa Marta y Tayrona ($650 - 4 días)
- Bogotá Imperial ($350 - 2 días)

## 🔐 Configuración

**Archivo:** `config/database.php`

```php
$host = "localhost";
$db = "sercolturbot";
$user = "root";
$pass = "C121672@c";
```

## 📁 Estructura de Carpetas

```
SERCOLTURBOT/
├── config/
│   └── database.php           # Conexión a BD
├── controllers/
│   └── DashboardController.php
├── models/
│   ├── Bus.php
│   ├── Cliente.php
│   ├── Guia.php
│   └── Reserva.php
├── public/
│   └── index.php
├── routes/
│   ├── api.php
│   └── bot_api.php           # API del Bot
├── services/
│   └── BotService.php        # Lógica del Bot
├── setup/
│   ├── index.php             # Panel de instalación
│   ├── database_setup.php    # Script de creación
│   └── database.sql          # SQL completo
└── logs/
    └── bot.log              # Registro de conversaciones
```

## ✅ Funcionalidades Implementadas

- ✓ Gestión de clientes
- ✓ Gestión de asesores
- ✓ Gestión de guías turísticos
- ✓ Gestión de buses
- ✓ Sistema de reservas
- ✓ Asignación de recursos (guía, bus, asesor)
- ✓ Bot inteligente con respuestas en tiempo real
- ✓ Historial de conversaciones
- ✓ Disponibilidad diaria
- ✓ Sistema de comentarios y calificaciones
- ✓ API REST completa
- ✓ Estadísticas del bot
- ✓ Registro de logs

## 🔧 Mantenimiento

### Ver Log del Bot
```
cat logs/bot.log
```

### Limpiar Conversaciones Antiguas
```sql
DELETE FROM bot_conversaciones 
WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### Reestablecer BD
```bash
mysql -u root -p sercolturbot < setup/database.sql
```

## 📞 Soporte

Para más información o problemas con la instalación, consulta el panel en:
`http://localhost/SERCOLTURBOT/setup/`

## 📄 Licencia

Este sistema está diseñado para SERCOLTURBOT

---

**Versión:** 1.0  
**Última actualización:** 25 de Diciembre de 2025
