# Registro de Cambios

## [1.1.0] - 2024

### Agregado
- Script `status.php` para diagnóstico de conexión a BD
- Script `env-check.php` para listar variables de entorno
- Validación de credenciales por defecto en `database.php`
- Soporte para `DATABASE_URL` y `RAILWAY_DATABASE_URL`

### Modificado
- `public/config/database.php`: Mejorado parseo de `DATABASE_URL`
- `.gitignore`: Excluir scripts de depuración

### Notas
- Railway proporciona `DATABASE_URL` automáticamente
- Verifica en Railway que las variables estén configuradas correctamente

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
