#!/bin/bash

echo "🚀 Démarrage du serveur de développement Koumbaya..."

# Configuration pour développement local
export APP_URL="http://localhost:5173"
export VITE_APP_URL="http://localhost:5173"
export VITE_API_URL="http://localhost:8888/koumbaya/koumbaya_web/public/api"

# Démarrage du serveur Vite en mode développement
echo "📦 Démarrage de Vite dev server..."
npm run dev