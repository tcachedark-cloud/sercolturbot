# Gestión de Base de Datos

## Exportar Base de Datos Local

1. Accede a: `http://localhost/SERCOLTURBOT/public/scripts/export-database.php`
2. Se descargará un archivo `backup_YYYY-MM-DD_HH-MM-SS.sql`

## Importar en Railway

### Opción 1: Usar Railway CLI
```bash
railway db push < backup_YYYY-MM-DD_HH-MM-SS.sql
```

### Opción 2: Usar interfaz web de Railway
1. Ve a tu proyecto en Railway
2. Selecciona el plugin MySQL
3. Usa "Run MySQL Query" y pega el contenido del SQL

### Opción 3: Usar script PHP (si tienes acceso)
1. Sube el archivo `backup_YYYY-MM-DD_HH-MM-SS.sql`
2. Accede a: `https://sercolturbot-kwhr.onrender.com/scripts/import-database.php`
3. Selecciona el archivo y envía (POST)

## Variables de Entorno Requeridas

Configura en Railway:
- `DB_HOST` - Host MySQL
- `DB_PORT` - Puerto (3306)
- `DB_DATABASE` - Nombre BD
- `DB_USERNAME` - Usuario
- `DB_PASSWORD` - Contraseña

O simplemente: `DATABASE_URL` (formato: `mysql://user:pass@host:port/db`)

## Estructura de Archivos

```
public/
├── config/
│   └── database.php (conexión a BD)
├── scripts/
│   ├── export-database.php (descargar SQL)
│   └── import-database.php (cargar SQL)
└── ...
```
