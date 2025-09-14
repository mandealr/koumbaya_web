@component('mail::message')
    # Réinitialisation de votre mot de passe

    Bonjour {{ $user->first_name }} {{ $user->last_name }},

    Vous avez demandé la réinitialisation de votre mot de passe sur **Koumbaya Marketplace**.

    Voici votre code de vérification à usage unique :

    @component('mail::panel')
        # {{ $otp }}

        **Ce code expire dans 5 minutes.**
    @endcomponent

    ## Comment procéder ?

    1. **Retournez sur la page de réinitialisation**
    2. **Saisissez ce code de vérification : {{ $otp }}**
    3. **Créez votre nouveau mot de passe sécurisé**

    @component('mail::button', ['url' => $resetUrl, 'color' => 'primary'])
        Continuer la réinitialisation
    @endcomponent

    @component('mail::panel')
         **Sécurité :** Si vous n'avez pas demandé cette réinitialisation, ignorez cet email. Votre compte reste sécurisé et
        aucune action n'est requise.
    @endcomponent

    ## Conseils pour un mot de passe sécurisé :
    - Au moins 8 caractères
    - Combinaison de lettres, chiffres et symboles
    - Évitez les informations personnelles

    ---

    **Besoin d'aide ?** Notre équipe support est disponible à support@koumbaya.com

    Cordialement,
    **L'équipe Koumbaya** 

    @component('mail::subcopy')
        Si vous avez des difficultés à cliquer sur le bouton, copiez et collez l'URL suivante dans votre navigateur web :
        {{ $resetUrl }}
    @endcomponent
@endcomponent
