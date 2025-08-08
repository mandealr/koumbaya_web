#!/bin/bash

# Script pour démarrer l'application Koumbaya en développement

echo "🚀 Démarrage de l'application Koumbaya Marketplace..."
echo ""

# Vérifier si le port 8000 est libre
if lsof -Pi :8000 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Le port 8000 est occupé. Arrêt du processus..."
    pkill -f "artisan serve" 2>/dev/null || true
    sleep 2
fi

# Vérifier si le port 5173 est libre  
if lsof -Pi :5173 -sTCP:LISTEN -t >/dev/null ; then
    echo "⚠️  Le port 5173 est occupé. Arrêt du processus..."
    pkill -f "vite.*5173" 2>/dev/null || true
    sleep 2
fi

echo "📦 Installation/mise à jour des dépendances..."
npm install

echo "🏗️  Build des assets..."
npm run build

echo "🌐 Démarrage du serveur Laravel sur http://localhost:8000..."
php artisan serve --port=8000 &
LARAVEL_PID=$!

echo "⚡ Démarrage de Vite dev server sur http://localhost:5173..."
npm run dev &
VITE_PID=$!

echo ""
echo "✅ Application démarrée avec succès !"
echo ""
echo "📱 Frontend Vue.js : http://localhost:5173"
echo "🔗 Backend Laravel API : http://localhost:8000"
echo ""
echo "Pour arrêter l'application, appuyez sur Ctrl+C"
echo ""

# Fonction de nettoyage lors de l'arrêt
cleanup() {
    echo ""
    echo "🛑 Arrêt de l'application..."
    kill $LARAVEL_PID 2>/dev/null
    kill $VITE_PID 2>/dev/null
    pkill -f "artisan serve" 2>/dev/null || true
    pkill -f "vite" 2>/dev/null || true
    echo "✅ Application arrêtée"
    exit 0
}

# Capturer Ctrl+C
trap cleanup INT

# Attendre indéfiniment
wait