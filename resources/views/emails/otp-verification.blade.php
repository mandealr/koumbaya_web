@component('mail::message')
    # Code de vérification Koumbaya

    Bonjour,

    @if ($purpose === 'registration')
        Bienvenue sur **Koumbaya MarketPlace** !
    @elseif($purpose === 'login')
        Connexion sécurisée à votre compte **Koumbaya**.
    @elseif($purpose === 'payment')
        Confirmation de votre paiement sur **Koumbaya**.
    @else
        Voici votre code de vérification pour **Koumbaya**.
    @endif

    Voici votre code de vérification à usage unique :

    @component('mail::panel')
        # {{ $code }}

        **Ce code expire dans 5 minutes.**
    @endcomponent

    ## Comment utiliser ce code ?

    1. **Retournez sur la page Koumbaya**
    2. **Saisissez ce code : {{ $code }}**
    3. **Validez pour continuer**

    @component('mail::panel')
        🔒 **Sécurité :** Si vous n'avez pas demandé ce code, ignorez cet email. Votre compte reste sécurisé.
    @endcomponent

    ---

    **Besoin d'aide ?** Contactez-nous à support@koumbaya.com

    Cordialement,
    **L'équipe Koumbaya**
@endcomponent
