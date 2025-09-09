#!/bin/bash

# Script de scan de sécurité automatisé pour Koumbaya
# Ce script lance plusieurs outils de sécurité et génère un rapport consolidé

set -e

echo "🛡️  Démarrage du scan de sécurité Koumbaya"
echo "=================================================="

# Couleurs pour l'output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Répertoire de base
BASE_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPORTS_DIR="$BASE_DIR/security-reports"

# Créer le répertoire des rapports
mkdir -p "$REPORTS_DIR"

# Date du scan
SCAN_DATE=$(date +"%Y-%m-%d_%H-%M-%S")
REPORT_FILE="$REPORTS_DIR/security-report-$SCAN_DATE.txt"

echo "📁 Répertoire des rapports: $REPORTS_DIR"
echo "📄 Rapport principal: $REPORT_FILE"
echo ""

# Fonction pour logger
log() {
    echo -e "$1" | tee -a "$REPORT_FILE"
}

# Header du rapport
{
    echo "RAPPORT DE SÉCURITÉ KOUMBAYA"
    echo "============================"
    echo "Date: $(date)"
    echo "Environnement: $(php artisan env)"
    echo "Version PHP: $(php --version | head -n1)"
    echo "Version Node: $(node --version)"
    echo ""
} > "$REPORT_FILE"

# 1. Tests PHP avec PHPUnit
log "${BLUE}1. 🧪 Tests de sécurité PHP...${NC}"
if command -v php >/dev/null 2>&1; then
    if [ -f "$BASE_DIR/vendor/bin/phpunit" ]; then
        php "$BASE_DIR/vendor/bin/phpunit" \
            --testsuite=Feature \
            --filter=Security \
            --verbose \
            >> "$REPORT_FILE" 2>&1
        
        if [ $? -eq 0 ]; then
            log "${GREEN}✅ Tests PHP de sécurité réussis${NC}"
        else
            log "${RED}❌ Échec des tests PHP de sécurité${NC}"
        fi
    else
        log "${YELLOW}⚠️  PHPUnit non trouvé - installation des dépendances...${NC}"
        composer install --no-dev --optimize-autoloader
    fi
else
    log "${RED}❌ PHP non trouvé${NC}"
fi

echo ""

# 2. Tests JavaScript avec Vitest
log "${BLUE}2. 🧪 Tests de sécurité JavaScript...${NC}"
if command -v npm >/dev/null 2>&1; then
    if [ -f "$BASE_DIR/package.json" ]; then
        cd "$BASE_DIR"
        npm test 2>&1 | tee -a "$REPORT_FILE"
        
        if [ ${PIPESTATUS[0]} -eq 0 ]; then
            log "${GREEN}✅ Tests JavaScript de sécurité réussis${NC}"
        else
            log "${YELLOW}⚠️  Avertissements dans les tests JavaScript${NC}"
        fi
    fi
else
    log "${RED}❌ NPM non trouvé${NC}"
fi

echo ""

# 3. Analyse des dépendances PHP
log "${BLUE}3. 📦 Analyse des dépendances PHP...${NC}"
if [ -f "$BASE_DIR/composer.json" ]; then
    composer audit --format=table >> "$REPORT_FILE" 2>&1
    
    if [ $? -eq 0 ]; then
        log "${GREEN}✅ Aucune vulnérabilité trouvée dans les dépendances PHP${NC}"
    else
        log "${YELLOW}⚠️  Vulnérabilités trouvées dans les dépendances PHP${NC}"
    fi
fi

echo ""

# 4. Analyse des dépendances JavaScript
log "${BLUE}4. 📦 Analyse des dépendances JavaScript...${NC}"
if [ -f "$BASE_DIR/package.json" ]; then
    cd "$BASE_DIR"
    npm audit --audit-level=moderate >> "$REPORT_FILE" 2>&1
    
    if [ $? -eq 0 ]; then
        log "${GREEN}✅ Aucune vulnérabilité trouvée dans les dépendances JavaScript${NC}"
    else
        log "${YELLOW}⚠️  Vulnérabilités trouvées dans les dépendances JavaScript${NC}"
    fi
fi

echo ""

# 5. Analyse ESLint pour la sécurité
log "${BLUE}5. 🔍 Analyse ESLint sécurité...${NC}"
if [ -f "$BASE_DIR/.eslintrc.js" ] || [ -f "$BASE_DIR/eslint.config.js" ]; then
    cd "$BASE_DIR"
    npm run lint 2>&1 | tee -a "$REPORT_FILE"
    
    if [ ${PIPESTATUS[0]} -eq 0 ]; then
        log "${GREEN}✅ Aucun problème de sécurité détecté par ESLint${NC}"
    else
        log "${YELLOW}⚠️  Problèmes détectés par ESLint${NC}"
    fi
fi

echo ""

# 6. Vérification des permissions de fichiers
log "${BLUE}6. 🔒 Vérification des permissions...${NC}"

# Fichiers qui ne doivent pas être exécutables
SHOULD_NOT_BE_EXECUTABLE=(
    ".env*"
    "*.php"
    "*.js"
    "*.vue"
    "*.json"
    "*.md"
)

# Fichiers sensibles qui ne doivent pas être accessibles en lecture par tous
SENSITIVE_FILES=(
    ".env"
    ".env.local"
    ".env.production"
    "config/database.php"
    "storage/logs/*"
)

permission_issues=0

for pattern in "${SHOULD_NOT_BE_EXECUTABLE[@]}"; do
    find "$BASE_DIR" -name "$pattern" -type f -executable 2>/dev/null | while read -r file; do
        log "${YELLOW}⚠️  Fichier exécutable suspect: $file${NC}"
        permission_issues=$((permission_issues + 1))
    done
done

for pattern in "${SENSITIVE_FILES[@]}"; do
    find "$BASE_DIR" -name "$pattern" -type f -perm /004 2>/dev/null | while read -r file; do
        log "${YELLOW}⚠️  Fichier sensible lisible par tous: $file${NC}"
        permission_issues=$((permission_issues + 1))
    done
done

if [ $permission_issues -eq 0 ]; then
    log "${GREEN}✅ Permissions des fichiers correctes${NC}"
fi

echo ""

# 7. Vérification de la configuration de sécurité
log "${BLUE}7. ⚙️  Vérification de la configuration...${NC}"

config_issues=0

# Vérifier que debug est désactivé en production
if [ -f "$BASE_DIR/.env" ]; then
    if grep -q "APP_DEBUG=true" "$BASE_DIR/.env" && grep -q "APP_ENV=production" "$BASE_DIR/.env"; then
        log "${RED}❌ DEBUG activé en production${NC}"
        config_issues=$((config_issues + 1))
    fi
    
    # Vérifier la présence d'une clé d'application
    if ! grep -q "APP_KEY=base64:" "$BASE_DIR/.env"; then
        log "${YELLOW}⚠️  Clé d'application manquante ou mal formatée${NC}"
        config_issues=$((config_issues + 1))
    fi
    
    # Vérifier HTTPS en production
    if grep -q "APP_ENV=production" "$BASE_DIR/.env" && ! grep -q "FORCE_HTTPS=true" "$BASE_DIR/.env"; then
        log "${YELLOW}⚠️  HTTPS non forcé en production${NC}"
        config_issues=$((config_issues + 1))
    fi
fi

if [ $config_issues -eq 0 ]; then
    log "${GREEN}✅ Configuration de sécurité correcte${NC}"
fi

echo ""

# 8. Recherche de secrets dans le code
log "${BLUE}8. 🔐 Recherche de secrets exposés...${NC}"

secret_patterns=(
    "password\s*=\s*['\"][^'\"]*['\"]"
    "secret\s*=\s*['\"][^'\"]*['\"]"
    "api_key\s*=\s*['\"][^'\"]*['\"]"
    "token\s*=\s*['\"][^'\"]*['\"]"
    "-----BEGIN.*PRIVATE.*KEY-----"
    "AKIA[0-9A-Z]{16}"  # AWS Access Key
)

secrets_found=0

for pattern in "${secret_patterns[@]}"; do
    grep -r -i -E "$pattern" "$BASE_DIR" \
        --exclude-dir=node_modules \
        --exclude-dir=vendor \
        --exclude-dir=.git \
        --exclude="*.log" \
        --exclude="security-scan.sh" \
        2>/dev/null | while read -r match; do
            log "${RED}❌ Secret potentiel trouvé: $match${NC}"
            secrets_found=$((secrets_found + 1))
        done
done

if [ $secrets_found -eq 0 ]; then
    log "${GREEN}✅ Aucun secret exposé détecté${NC}"
fi

echo ""

# 9. Test des endpoints de sécurité
log "${BLUE}9. 🌐 Test des endpoints de sécurité...${NC}"

if command -v curl >/dev/null 2>&1; then
    # Tester la présence des headers de sécurité
    BASE_URL="http://localhost:8000"  # Ajuster selon la configuration
    
    SECURITY_HEADERS=(
        "X-Content-Type-Options"
        "X-Frame-Options" 
        "X-XSS-Protection"
        "Content-Security-Policy"
    )
    
    for header in "${SECURITY_HEADERS[@]}"; do
        if curl -s -I "$BASE_URL" | grep -i "$header" > /dev/null; then
            log "${GREEN}✅ Header $header présent${NC}"
        else
            log "${YELLOW}⚠️  Header $header manquant${NC}"
        fi
    done
else
    log "${YELLOW}⚠️  cURL non disponible - impossible de tester les endpoints${NC}"
fi

echo ""

# 10. Génération du résumé
log "${BLUE}10. 📊 Génération du résumé...${NC}"

{
    echo ""
    echo "RÉSUMÉ DU SCAN DE SÉCURITÉ"
    echo "========================="
    echo "Scan terminé le: $(date)"
    echo ""
    
    # Compter les différents types d'issues
    error_count=$(grep -c "❌" "$REPORT_FILE" || echo "0")
    warning_count=$(grep -c "⚠️" "$REPORT_FILE" || echo "0")
    success_count=$(grep -c "✅" "$REPORT_FILE" || echo "0")
    
    echo "Résultats:"
    echo "  ✅ Succès: $success_count"
    echo "  ⚠️  Avertissements: $warning_count"
    echo "  ❌ Erreurs: $error_count"
    echo ""
    
    if [ $error_count -gt 0 ]; then
        echo "🚨 ATTENTION: Des vulnérabilités critiques ont été détectées!"
        echo "   Veuillez corriger les erreurs avant le déploiement."
    elif [ $warning_count -gt 0 ]; then
        echo "⚠️  Des améliorations de sécurité sont recommandées."
    else
        echo "✅ Aucun problème de sécurité critique détecté!"
    fi
    
    echo ""
    echo "Rapport complet sauvegardé dans: $REPORT_FILE"
    
} >> "$REPORT_FILE"

# Afficher le résumé à l'écran aussi
tail -n 20 "$REPORT_FILE"

# Code de sortie basé sur les erreurs
if [ $error_count -gt 0 ]; then
    echo ""
    log "${RED}🚨 Scan terminé avec des erreurs critiques!${NC}"
    exit 1
elif [ $warning_count -gt 0 ]; then
    echo ""
    log "${YELLOW}⚠️  Scan terminé avec des avertissements.${NC}"
    exit 2
else
    echo ""
    log "${GREEN}✅ Scan de sécurité réussi!${NC}"
    exit 0
fi