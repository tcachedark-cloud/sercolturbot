# 📱 OBTENER CREDENCIALES WHATSAPP DE META

## Paso 1: Acceder a Meta Business Suite

### 1.1 Ve a Facebook Developers
```
https://developers.facebook.com/
```

### 1.2 Inicia sesión con tu cuenta Facebook
- Si no tienes cuenta: Crea una en facebook.com
- Asegúrate que tu página de Facebook esté vinculada a tu cuenta

---

## Paso 2: Crear o Usar una App de Meta

### 2.1 Si NO tienes app aún:
1. En https://developers.facebook.com/apps/
2. Click **"Crear una aplicación"**
3. Selecciona: **"Otra" → Siguiente**
4. Nombre: `SERCOLTURBOT-Production`
5. Email de contacto: Tu email
6. Click **"Crear aplicación"**

### 2.2 Si YA tienes app:
1. Ve a https://developers.facebook.com/apps/
2. Selecciona tu app
3. Ve a **Configuración → Información de la aplicación**

---

## Paso 3: Agregar Producto WhatsApp

### 3.1 En tu App → Agregar Producto
1. Click **"+ Agregar producto"**
2. Busca **"WhatsApp"**
3. Click **"Agregar"**

### 3.2 Esperar a que WhatsApp se agregue
- Tardará unos segundos
- Verás una nueva sección "WhatsApp" en el menú izquierdo

---

## Paso 4: Obtener Phone ID (Número de Teléfono ID)

### 4.1 Ve a WhatsApp → Configuración
1. En el menú izquierdo: **WhatsApp → Configuración**
2. Verás una tabla con tu(s) número(s) de WhatsApp Business

### 4.2 Busca el Phone Number ID
```
┌─────────────────────────────────────────┐
│ Número de Teléfono    │ Nombre         │ ID de Número    │
├─────────────────────────────────────────┤
│ +573011773292         │ SERCOLTUR Bot  │ 123456789012345 │
└─────────────────────────────────────────┘
```

**Copia el ID de Número** (número largo en la columna derecha)
→ Este es tu `WHATSAPP_PHONE_ID`

### 4.3 Si NO ves tu número:
1. Ve a **Configuración → Números de Teléfono**
2. Click **"Agregar número"**
3. Sigue el flujo:
   - Verifica el número con código SMS
   - Elige nombre para el bot
   - Acepta términos

---

## Paso 5: Obtener Access Token

### 5.1 Ve a Configuración → Credenciales de la Aplicación
1. En el menú: **Configuración → Credenciales de la Aplicación**
2. Verás dos tokens:
   - **Token de Usuario** (temporal, caduca en 60 días)
   - **Token de Sistema** (permanente, mejor para producción)

### 5.2 Usar Token de Sistema (RECOMENDADO)
1. Busca la sección **"Token del Sistema"**
   ```
   Número de teléfono     │ Token
   ──────────────────────────────────────
   +573011773292          │ EAAxxxxxx... (token de 100+ caracteres)
   ```

2. Click en el ícono **copiar** al lado del token
   → Este es tu `WHATSAPP_ACCESS_TOKEN`

### 5.3 Si NO ves Token del Sistema:
1. Ve a **WhatsApp → Configuración → Números de Teléfono**
2. Selecciona tu número
3. Click en **"Gestionar Token"**
4. Sigue instrucciones para generar token permanente

---

## Paso 6: Verificar que tu Número está Vinculado

### 6.1 En Meta Business Suite
1. Ve a https://business.facebook.com/
2. Configuración → Números de Teléfono
3. Deberías ver tu número (+573011773292 o similar)
4. Estado: "✅ Verificado"

### 6.2 Si NO aparece:
1. Ve a Meta Business Suite → Configuración
2. Click **"Vincular números"**
3. Sigue el flujo de verificación SMS

---

## Paso 7: Probar el Token en Local (Antes de subir a Railway)

### 7.1 Prueba en XAMPP
```php
<?php
$phone_id = "123456789012345"; // Tu Phone ID
$token = "EAAxxxxxx..."; // Tu Access Token
$numero = "573011773292"; // Número receptor
$mensaje = "Prueba desde SERCOLTUR Bot";

$url = "https://graph.facebook.com/v18.0/$phone_id/messages";

$data = json_encode([
    'messaging_product' => 'whatsapp',
    'to' => $numero,
    'type' => 'text',
    'text' => ['body' => $mensaje]
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_POST, true);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "Error: $error\n";
} else {
    echo "Respuesta: $response\n";
}
?>
```

### 7.2 Ejecutar desde terminal
```powershell
php -r "
\$token = 'Tu_Token_Aqui';
\$phone_id = 'Tu_Phone_ID';
\$numero = '573011773292';

\$ch = curl_init('https://graph.facebook.com/v18.0/' . \$phone_id . '/messages');
curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . \$token]);
curl_setopt(\$ch, CURLOPT_POST, true);
curl_setopt(\$ch, CURLOPT_POSTFIELDS, json_encode([
    'messaging_product' => 'whatsapp',
    'to' => \$numero,
    'type' => 'text',
    'text' => ['body' => 'Test']
]));

\$response = curl_exec(\$ch);
echo \$response;
"
```

---

## 🔐 Seguridad: Proteger tus Tokens

### ❌ NO HAGAS:
- ❌ Escribir el token en código (`$token = "EAAxxxx"`)
- ❌ Subir a GitHub sin .gitignore
- ❌ Compartir tokens en chat/email
- ❌ Usar el mismo token en múltiples apps

### ✅ SÍ HAZ:
- ✅ Guardar en variables de entorno (.env)
- ✅ En producción (Railway): Usar el Dashboard → Variables
- ✅ Rotar tokens periódicamente (ej: cada 3 meses)
- ✅ Si lo expones, revocar inmediatamente:
  - Meta Business Suite → Configuración → Tokens
  - Click en el token → "Revocar"

---

## 📋 Resumen: Credenciales que Necesitas

| Credencial | Dónde obtenerla | Forma |
|-----------|-----------------|-------|
| **Phone ID** | Meta Business → WhatsApp → Configuración → Columna "ID de Número" | Copiar directamente |
| **Access Token** | Meta Business → WhatsApp → Configuración → "Token del Sistema" | Copiar directamente |

---

## ✅ Checklist Final

- [ ] ✅ Tengo cuenta en Meta/Facebook
- [ ] ✅ Mi número WhatsApp está vinculado
- [ ] ✅ Copié el Phone ID
- [ ] ✅ Copié el Access Token
- [ ] ✅ Probé el token en local (envié mensaje de prueba)
- [ ] ✅ Guardé las credenciales en lugar seguro
- [ ] ✅ Estoy listo para agregar a Railway

---

## 🆘 Problemas Comunes

### "No veo mi número WhatsApp"
**Solución:**
1. Verifica que tu número está registrado en WhatsApp Business
2. En Meta Business Suite → Configuración → Agregar número
3. Verifica con SMS
4. Espera 15 minutos

### "El token dice 'inválido' o 'expirado'"
**Solución:**
1. Si es token de Usuario → Caduca en 60 días
   - Genera nuevo: Meta Business → Configuración → Tokens
2. Si es token de Sistema → Debería ser permanente
   - Verifica en Meta → Configuración → Números → Gestionar Token

### "Error 400: Invalid phone number"
**Solución:**
1. Asegúrate que el número receptor incluye país: `57` (Colombia)
2. Formato: `573011773292` (sin + ni espacios)
3. El número debe estar en la lista de contactos permitidos (meta lo auto-permite al primer envío)

### "Error 403: Unauthorized"
**Solución:**
1. Revisa que el token es correcto (sin espacios)
2. El número debe estar verificado en Meta
3. La app debe tener permisos de WhatsApp activados

---

## 📞 Enlaces Útiles

| Recurso | URL |
|---------|-----|
| Meta Developers | https://developers.facebook.com/ |
| Meta Business Suite | https://business.facebook.com/ |
| WhatsApp Cloud API Docs | https://developers.facebook.com/docs/whatsapp/cloud-api |
| Generar Access Token | https://developers.facebook.com/apps/ |
| Ver mis Apps | https://developers.facebook.com/apps/ |
| Soporte Meta | https://developers.facebook.com/community/ |

---

**Una vez tengas Phone ID y Access Token, estás listo para:**
1. Agregar a tu `.env` local
2. Probar en XAMPP
3. Subirlos a Railway como variables
4. ¡Ir a producción! 🚀
