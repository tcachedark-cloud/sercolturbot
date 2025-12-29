# 🚀 GUÍA DE INTEGRACIÓN META WHATSAPP

## 📋 Requisitos Previos

1. **Cuenta Meta Business** - Acceso a [https://developers.facebook.com/](https://developers.facebook.com/)
2. **Número de teléfono verificado** en Meta Business
3. **Servidor con HTTPS** - Webhook debe estar en HTTPS
4. **PHP 7.4+** con extensión cURL

## 🔧 Pasos de Configuración

### Paso 1: Obtener Credenciales Meta

1. Ve a [Meta Developers Console](https://developers.facebook.com/apps)
2. Crea una nueva app (selecciona "Business" como tipo)
3. Agrega el producto "WhatsApp"
4. Ve a **WhatsApp > API Setup**
5. Selecciona tu número de teléfono de prueba o registra el tuyo

### Paso 2: Copiar Credenciales

Necesitarás:
- **Phone Number ID** - Encontrado en WhatsApp > API Setup
- **Business Account ID** - En Settings > Business Information
- **Access Token** - Temporalmente disponible en WhatsApp > API Setup
- **Webhook Token** - Lo creas tú mismo (cualquier string)

### Paso 3: Configurar el Archivo de Credenciales

Edita `config/whatsapp_config.php` y reemplaza:

```php
// REEMPLAZA ESTOS VALORES CON TUS CREDENCIALES META
define('META_PHONE_NUMBER_ID', 'TU_PHONE_NUMBER_ID');
define('META_BUSINESS_ACCOUNT_ID', 'TU_BUSINESS_ACCOUNT_ID');
define('META_ACCESS_TOKEN', 'TU_ACCESS_TOKEN');
define('META_WEBHOOK_TOKEN', 'TU_WEBHOOK_TOKEN_SECRETO');
```

**Ejemplo:**
```php
define('META_PHONE_NUMBER_ID', '120335794857649');
define('META_BUSINESS_ACCOUNT_ID', '8374329847328947');
define('META_ACCESS_TOKEN', 'EAABsZA...');
define('META_WEBHOOK_TOKEN', 'mi_token_secreto_12345');
```

### Paso 4: Configurar Webhook en Meta

1. En **Meta Developers** → Tu app → **WhatsApp** → **Configuration**
2. Busca **Webhooks**
3. Haz clic en **Edit Callbacks**
4. Completa:
   - **Callback URL**: `https://tu-dominio.com/routes/whatsapp_webhook.php`
   - **Verify Token**: El mismo que `META_WEBHOOK_TOKEN` en tu código
5. Selecciona los eventos: `messages`, `message_template_status_update`
6. Guarda los cambios

### Paso 5: Crear la Base de Datos para WhatsApp

Ejecuta en MySQL:

```bash
mysql -u root -p sercolturbot < setup/whatsapp_tables.sql
```

O manualmente en phpMyAdmin:

```sql
CREATE TABLE whatsapp_conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    phone_number VARCHAR(20) UNIQUE NOT NULL,
    user_name VARCHAR(100),
    state VARCHAR(50) DEFAULT 'initial',
    selected_tour_id INT,
    selected_date DATE,
    num_people INT,
    full_name VARCHAR(100),
    email VARCHAR(100),
    reservation_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_phone (phone_number),
    KEY idx_state (state),
    KEY idx_reservation (reservation_id)
);

CREATE TABLE whatsapp_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    message_type VARCHAR(20),
    message_content LONGTEXT,
    is_incoming BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_conversation (conversation_id),
    KEY idx_created (created_at)
);
```

### Paso 6: Probar el Webhook

En **Meta Developers** → WhatsApp → Configuration → Webhooks:

1. Haz clic en **Send Test Event**
2. Deberías ver un ✅ si la configuración es correcta
3. Revisa `logs/whatsapp.log` para confirmar los logs

## 🤖 Cómo Funciona el Bot

### Flujo de Conversación

```
Cliente envía: "Hola"
    ↓
Bot: "Bienvenido a SERCOLTURBOT! 👋"
     [Mostrar lista de tours disponibles]
    ↓
Cliente selecciona: Tour (Ej: Machu Picchu)
    ↓
Bot: "¿Qué fecha deseas viajar? (YYYY-MM-DD)"
    ↓
Cliente: "2024-03-15"
    ↓
Bot: "¿Cuántas personas viajarán?"
    ↓
Cliente: "4"
    ↓
Bot: "¿Cuál es tu nombre completo?"
    ↓
Cliente: "Juan Pérez"
    ↓
Bot: "¿Tu correo electrónico?"
    ↓
Cliente: "juan@example.com"
    ↓
Bot: "✅ Reserva confirmada #12345"
     "Guía asignado: Carlos"
     "Bus: ABC-123 (4 pax)"
```

### Estados del Bot

- `initial` - Esperando selección de tour
- `selecting_tour` - Mostrar lista de tours
- `selecting_date` - Pedir fecha
- `entering_people` - Pedir cantidad de personas
- `entering_name` - Pedir nombre
- `entering_email` - Pedir email
- `confirming_reservation` - Confirmación final
- `completed` - Reserva completada

## 📱 Enviar Mensajes Manualmente

Puedes enviar mensajes manualmente usando cURL:

```bash
curl -X POST "https://graph.instagram.com/v18.0/123456789/messages" \
  -H "Authorization: Bearer TU_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "messaging_product": "whatsapp",
    "to": "1234567890",
    "type": "text",
    "text": {
      "body": "¡Hola! 👋"
    }
  }'
```

## 🛠️ Solución de Problemas

### El webhook no recibe mensajes

1. Verifica que el URL sea HTTPS y público
2. Confirma el Verify Token es correcto
3. Revisa `logs/whatsapp.log`
4. En Meta Developers, ve a Webhooks > View Recent Requests

### El bot no responde

1. Verifica `logs/whatsapp.log`
2. Asegúrate de que la BD tiene los tours:
   ```sql
   SELECT * FROM tours WHERE activo = TRUE;
   ```
3. Verifica que hay guías y buses disponibles:
   ```sql
   SELECT * FROM guias WHERE disponible = TRUE;
   SELECT * FROM buses WHERE disponible = TRUE;
   ```

### Error: "Invalid webhook token"

1. Asegúrate que `META_WEBHOOK_TOKEN` en `config/whatsapp_config.php` coincide con el Verify Token en Meta
2. Ambos deben ser exactamente iguales

## 📊 Dashboard

Una vez configurado, accede al dashboard:

```
http://localhost/public/dashboard.php
```

Verás:
- ✅ Reservas confirmadas
- ⏳ Reservas pendientes
- 💰 Ingresos totales
- 📱 Chats WhatsApp activos
- 👨‍🏫 Guías asignados
- 🚌 Buses asignados

## 🔐 Seguridad

1. **Nunca compartas tus credenciales** en públicos
2. **Regenera tokens regularmente** en Meta
3. **Usa HTTPS** para el webhook
4. **Valida los Webhook Tokens** en la configuración
5. **Logs se guardan en**: `logs/whatsapp.log`

## 📞 API Endpoints

### Dashboard API
- `GET /public/dashboard-api.php?action=stats` - Estadísticas
- `GET /public/dashboard-api.php?action=reservations` - Reservas
- `GET /public/dashboard-api.php?action=assignments` - Asignaciones
- `GET /public/dashboard-api.php?action=guides` - Guías
- `GET /public/dashboard-api.php?action=buses` - Buses
- `GET /public/dashboard-api.php?action=whatsapp` - Chats WhatsApp
- `POST /public/dashboard-api.php?action=update-reservation` - Actualizar reserva

### WhatsApp Webhook
- `GET /routes/whatsapp_webhook.php` - Verificación Meta
- `POST /routes/whatsapp_webhook.php` - Recibir mensajes

### Bot Web
- `POST /routes/bot_api.php` - Chat web

## 🎯 Próximos Pasos

1. ✅ Obtener credenciales Meta
2. ✅ Configurar webhook
3. ✅ Crear tablas WhatsApp
4. ✅ Probar bot con un mensaje
5. ✅ Monitorear en dashboard
6. (Opcional) Integrar pagos

---

**Última actualización**: 2024
**Estado**: ✅ Listo para producción
