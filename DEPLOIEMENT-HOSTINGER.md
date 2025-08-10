# Déploiement sur Hostinger

## Préparation locale

1. **Build de production** :
   ```bash
   npm run build
   ```

## Étapes sur Hostinger

### 1. Upload des fichiers
- Uploadez **tout le projet** via FTP/SFTP vers le dossier racine
- **Important** : Pointez votre domaine vers le dossier `/public`

### 2. Configuration de l'environnement
- Copiez `.env.production` vers `.env`
- Modifiez les variables suivantes :

```env
# Votre domaine
APP_URL=https://votre-domaine.com

# Clé d'application (générez avec php artisan key:generate)
APP_KEY=base64:VOTRE_CLE_GENEREE

# Base de données MySQL Hostinger
DB_DATABASE=nom_de_votre_db
DB_USERNAME=votre_username
DB_PASSWORD=votre_password

# JWT Secret (générez un token sécurisé)
JWT_SECRET=VOTRE_JWT_SECRET_SECURISE

# Email (si vous avez configuré les emails)
MAIL_HOST=smtp.hostinger.com
MAIL_USERNAME=votre-email@domaine.com
MAIL_PASSWORD=votre-mot-de-passe-email
```

### 3. Commandes à exécuter (via SSH ou panneau Hostinger)
```bash
# Générer la clé d'application
php artisan key:generate

# Exécuter les migrations
php artisan migrate

# Créer les tables pour le cache et les sessions
php artisan make:migration create_cache_table
php artisan make:migration create_sessions_table
php artisan migrate

# Optionnel : Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Permissions
Assurez-vous que les dossiers suivants sont en écriture (755 ou 775) :
- `storage/`
- `bootstrap/cache/`

### 5. Vérification
- Testez l'accès à votre domaine
- Vérifiez que les assets se chargent correctement
- Testez l'API : `https://votre-domaine.com/api/health` (si elle existe)

## Structure finale sur Hostinger
```
public_html/ (ou dossier racine)
├── koumbaya_web/
│   ├── public/          <- Pointer le domaine ici
│   ├── app/
│   ├── resources/
│   ├── storage/
│   ├── vendor/
│   ├── .env             <- Copier depuis .env.production
│   └── ...
```

## Dépannage

### Page blanche
1. Vérifiez les logs : `storage/logs/laravel.log`
2. Activez le debug temporairement : `APP_DEBUG=true`
3. Vérifiez les permissions des dossiers

### Assets non chargés
1. Vérifiez que le domaine pointe vers `/public`
2. Vérifiez les chemins dans `.env`
3. Re-buildez si nécessaire : `npm run build`

### Base de données
1. Vérifiez les paramètres de connexion
2. Exécutez `php artisan migrate:status`
3. Vérifiez les permissions utilisateur MySQL