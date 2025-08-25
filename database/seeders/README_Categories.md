# 📂 CategorySeeder - Koumbaya Marketplace

## Description

Ce seeder crée **25 catégories de produits** complètes pour le marketplace Koumbaya, couvrant tous les types de produits susceptibles d'être vendus dans des tombolas.

## Catégories incluses

### 🔌 **Électronique (5 catégories)**
- Smartphones & Tablettes
- Ordinateurs & Informatique  
- Audio & Vidéo
- Gaming
- Accessoires Tech

### 🏠 **Maison & Jardin (4 catégories)**
- Électroménager
- Mobilier
- Décoration
- Jardin & Extérieur

### 👕 **Mode & Accessoires (4 catégories)**
- Mode Homme
- Mode Femme
- Montres & Bijoux
- Maroquinerie

### 🚗 **Automobile (3 catégories)**
- Voitures
- Motos & Scooters
- Accessoires Auto

### ⚽ **Sport & Loisirs (3 catégories)**
- Sport & Fitness
- Voyage & Bagages
- Instruments de Musique

### 💄 **Beauté & Santé (2 catégories)**
- Beauté & Cosmétiques
- Santé & Bien-être

### 💎 **Premium & Luxe (2 catégories)**
- Articles de Luxe
- Art & Collection

### 🎁 **Bons & Services (2 catégories)**
- Bons d'Achat
- Expériences & Loisirs

## Utilisation

### Exécuter le seeder
```bash
# Seeder seul
php artisan db:seed --class=CategorySeeder

# Avec tous les seeders
php artisan db:seed
```

### Vérifier les catégories créées
```bash
php artisan tinker
>>> App\Models\Category::count()
>>> App\Models\Category::pluck('name')
```

## Structure des données

Chaque catégorie contient :
- `name` : Nom de la catégorie
- `slug` : Slug URL-friendly
- `description` : Description des produits inclus
- `is_active` : Statut actif (true par défaut)
- `parent_id` : Parent (null pour toutes - pas de hiérarchie)

## Notes techniques

- ✅ **Truncate automatique** : Le seeder vide la table avant insertion
- ✅ **Safe re-run** : Peut être exécuté plusieurs fois sans problème
- ✅ **Slugs uniques** : Chaque catégorie a un slug unique pour les URLs
- ✅ **Descriptions détaillées** : Aide les marchands à choisir la bonne catégorie

## Intégration frontend

Les catégories sont automatiquement chargées dans :
- Page création de produit marchand (`/merchant/products/create`)
- Filtres de recherche
- Catalogues publics

---

*Généré pour Koumbaya Marketplace - 25 catégories complètes*