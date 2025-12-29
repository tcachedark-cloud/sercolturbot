#!/bin/bash
set -e

echo "🔨 Construyendo imagen Docker..."
docker build -t sercolturbot:latest .

echo "✅ Imagen construida exitosamente!"
echo ""
echo "Para ejecutar localmente:"
echo "  docker run -p 8080:8080 sercolturbot:latest"
echo ""
echo "Para probar en tu navegador:"
echo "  http://localhost:8080"
