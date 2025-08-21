@component('mail::message')
# Réinitialisation de votre mot de passe

Bonjour {{ $user->first_name }} {{ $user->last_name }},

Vous avez demandé la réinitialisation de votre mot de passe sur **Koumbaya MarketPlace**.

Voici votre code de vérification à usage unique :

@component('mail::panel')
# {{ $otp }}

**Ce code expire dans 15 minutes.**
@endcomponent

## Comment procéder ?

1. **Retournez sur la page de réinitialisation**
2. **Saisissez ce code de vérification : {{ $otp }}**
3. **Créez votre nouveau mot de passe**

@component('mail::button', ['url' => $resetUrl, 'color' => 'primary'])
Continuer la réinitialisation
@endcomponent

@component('mail::panel')
🔒 **Sécurité :** Si vous n'avez pas demandé cette réinitialisation, ignorez cet email. Votre compte reste sécurisé.
@endcomponent

---

**Besoin d'aide ?** Contactez-nous à support@koumbaya.com

Cordialement,  
**L'équipe Koumbaya**

@component('mail::subcopy')
Si vous avez des difficultés à cliquer sur le bouton, copiez et collez l'URL suivante dans votre navigateur web : {{ $resetUrl }}
@endcomponent
@endcomponent