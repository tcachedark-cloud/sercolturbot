# Registro de Cambios

## [1.0.0] - 2024

### Agregado
- Soporte para múltiples formatos de variables de entorno (Railway, estándar)
- Parseo de `DATABASE_URL` para compatibilidad con Railway
- Script de diagnóstico `debug-env.php` para validar configuración

### Modificado
- `public/config/database.php`: Mejorado manejo de variables de entorno con fallbacks
- `Dockerfile`: Corregida configuración de DocumentRoot para Apache

### Eliminado
- Dependencia de variables de entorno únicas
