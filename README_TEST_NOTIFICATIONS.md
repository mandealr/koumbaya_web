# 📧 Test des notifications de paiement

## 🚀 Utilisation rapide

```bash
# Test complet (client + marchand)
php artisan test:payment-notifications votre@email.com

# Test notification client uniquement
php artisan test:payment-notifications votre@email.com --type=customer

# Test notification marchand uniquement
php artisan test:payment-notifications votre@email.com --type=merchant

# Spécifier un email marchand différent
php artisan test:payment-notifications client@test.com --merchant-email=marchand@test.com

# Test scénario tombola (par défaut)
php artisan test:payment-notifications votre@email.com --scenario=lottery

# Test scénario achat direct
php artisan test:payment-notifications votre@email.com --scenario=direct
```

---

## ⚙️ Configuration requise

### **1. Vérifier la configuration mail (.env)**

```bash
# Vérifier les paramètres mail
grep "MAIL_" .env
```

**Configuration correcte** :
```env
MAIL_MAILER=smtp          # Pas "log" !
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=xxxx xxxx xxxx xxxx  # Mot de passe d'application Gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@koumbaya.com
MAIL_FROM_NAME="Koumbaya Marketplace"
```

### **2. Nettoyer le cache**

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 📊 Exemple de sortie

```
🚀 Test des notifications de paiement - Koumbaya

📨 Email de test : test@example.com
📋 Type : all
🎭 Scénario : lottery

⏳ Préparation des données de test...
📤 Envoi de la notification client...
✅ Notification client envoyée
📤 Envoi de la notification marchand...
✅ Notification marchand envoyée

🎉 Test terminé avec succès ! 2 email(s) envoyé(s)

📬 Vérifiez votre boîte mail (y compris les spams)
```

---

## 📧 Où vont les emails ?

✅ **Les emails sont envoyés aux adresses spécifiées**, pas à `MAIL_ADMIN_EMAIL`

- **Email client** → adresse fournie en argument
- **Email marchand** → `merchant@koumbaya.com` (par défaut) ou `--merchant-email`

⚠️ **Exception** : Si `MAIL_MAILER=log`, les emails vont dans `storage/logs/laravel.log`

---

## 🐛 Dépannage

### **Les emails ne sont pas reçus**

1. **Vérifier le MAIL_MAILER**
   ```bash
   grep "MAIL_MAILER" .env
   # Doit être "smtp", pas "log"
   ```

2. **Vérifier les logs**
   ```bash
   tail -f storage/logs/laravel.log | grep "TEST ::"
   ```

3. **Tester la config SMTP**
   ```bash
   php diagnose-mail.php votre@email.com
   ```

4. **Vérifier les spams**
   - Les emails peuvent arriver dans les spams la première fois

---

## 📚 Documentation complète

Voir le guide complet : `/TEST_PAYMENT_NOTIFICATIONS.md`

---

## 💡 Commandes utiles

```bash
# Aide complète
php artisan test:payment-notifications --help

# Voir les logs en temps réel
tail -f storage/logs/laravel.log | grep "TEST ::"

# Nettoyer les données de test
php artisan tinker
>>> Order::where('order_number', 'LIKE', 'ORD-TEST-%')->delete();
>>> Payment::where('reference', 'LIKE', 'PAY-TEST-%')->delete();
```

---

**🎯 Prêt à tester !**
