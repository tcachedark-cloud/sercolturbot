Write-Host '🔨 Construyendo imagen Docker...' -ForegroundColor Cyan
docker build -t sercolturbot:latest .

Write-Host '✅ Imagen construida exitosamente!' -ForegroundColor Green
Write-Host ''
Write-Host 'Para ejecutar localmente:' -ForegroundColor Yellow
Write-Host '  docker run -p 8080:8080 sercolturbot:latest'
Write-Host ''
Write-Host 'Para probar en tu navegador:' -ForegroundColor Yellow
Write-Host '  http://localhost:8080'
