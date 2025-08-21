@component('mail::message')
# Bienvenue sur Koumbaya MarketPlace !

Bonjour {{ $user->first_name }} {{ $user->last_name }},

Merci de vous être inscrit sur **Koumbaya MarketPlace**, votre plateforme de tombolas et cadeaux en ligne.

Pour finaliser votre inscription et accéder à toutes nos fonctionnalités, veuillez vérifier votre adresse email en cliquant sur le bouton ci-dessous :

@component('mail::button', ['url' => $verificationUrl, 'color' => 'primary'])
Vérifier mon compte
@endcomponent

**Ou copiez ce lien dans votre navigateur :**  
{{ $verificationUrl }}

@component('mail::panel')
⏰ **Important :** Ce lien expire dans 24 heures pour votre sécurité.
@endcomponent

## Que pouvez-vous faire avec Koumbaya ?

- 🎁 Participer à des tombolas exclusives
- 🎯 Gagner des produits incroyables
- 💎 Découvrir de nouveaux cadeaux chaque semaine
- 🎊 Vivre l'excitation du jeu responsable

---

**Besoin d'aide ?** Contactez-nous à support@koumbaya.com

Cordialement,  
**L'équipe Koumbaya**

@component('mail::subcopy')
Si vous avez des difficultés à cliquer sur le bouton "Vérifier mon compte", copiez et collez l'URL suivante dans votre navigateur web : {{ $verificationUrl }}
@endcomponent
@endcomponent