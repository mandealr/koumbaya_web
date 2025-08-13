# Instructions pour corriger l'erreur de rôle CUSTOMER

## Problème
La colonne `role` dans la table `users` est définie comme un ENUM qui ne contient pas 'CUSTOMER', ce qui cause une erreur SQL lors de l'inscription.

## Solution
1. **Exécuter la migration** (sur le serveur de production) :
```bash
cd /path/to/koumbaya_web
php artisan migrate --force
```

2. **Après la migration**, modifier le AuthController pour remettre la logique correcte :
```php
// Dans app/Http/Controllers/Api/AuthController.php ligne 89
$role = ($request->account_type === 'business' || $request->can_sell) ? 'MERCHANT' : 'CUSTOMER';
```

## Migration créée
- **Fichier** : `database/migrations/2025_08_13_131200_add_customer_role_to_users.php`
- **Action** : Ajoute 'CUSTOMER' aux valeurs autorisées de l'enum role

## Modifications temporaires appliquées
- AuthController : Utilise 'MERCHANT' pour tous les utilisateurs temporairement
- ApiService (mobile) : Ne spécifie plus le rôle explicitement
- Register.vue (web) : Ne spécifie plus le rôle explicitement

Le serveur détermine automatiquement le rôle basé sur account_type.