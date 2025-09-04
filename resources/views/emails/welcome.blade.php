@component('mail::message')
    # 🎉 Bienvenue sur Koumbaya MarketPlace !

    Bonjour **{{ $user->first_name }}** {{ $user->last_name }},

    Félicitations ! Votre compte sur **Koumbaya MarketPlace** a été créé avec succès. 
    Vous faites maintenant partie de la famille Koumbaya ! 🎊

    @component('mail::panel')
        👋 **Bienvenue {{ $user->first_name }} !**
        
        Vous êtes maintenant prêt(e) à découvrir la plateforme de tombolas la plus transparente du Cameroun !
    @endcomponent

    ## ✨ Que pouvez-vous faire sur Koumbaya ?

    - **🎫 Participer aux tombolas** - Tentez votre chance pour gagner des produits incroyables
    - **🛒 Acheter des produits** - Achat direct auprès de nos marchands vérifiés  
    - **📊 Transparence totale** - Tous les tirages sont publics et vérifiables
    - **💰 Paiements sécurisés** - Airtel Money et Moov Money intégrés
    - **📱 Interface moderne** - Application web et mobile optimisées
    @if($user->is_merchant)
    - **🏪 Vendre vos produits** - Interface marchand complète pour gérer votre boutique
    @endif

    ## 🚀 Prêt(e) à commencer ?

    Explorez nos tombolas en cours et tentez votre chance dès maintenant !

    @component('mail::button', ['url' => config('app.frontend_url').'/lotteries', 'color' => 'primary'])
        Voir les tombolas
    @endcomponent

    @component('mail::button', ['url' => config('app.frontend_url').'/products', 'color' => 'success'])
        Parcourir les produits
    @endcomponent

    @component('mail::panel')
        💡 **Conseils pour bien commencer**
        
        - Complétez votre profil pour une meilleure expérience
        - Consultez nos conditions d'utilisation  
        - Suivez-nous sur les réseaux sociaux
        - N'hésitez pas à contacter notre support en cas de question
    @endcomponent

    ## 📊 En quelques chiffres

    - **100+** produits disponibles
    - **50+** tombolas actives  
    - **Transparence 100%** sur tous les tirages

    ---

    **Besoin d'aide ?** Notre équipe support est disponible à support@koumbaya.cm

    Bienvenue dans l'aventure Koumbaya ! 🎊  
    **L'équipe Koumbaya MarketPlace** 💙

    @component('mail::subcopy')
        Vous recevez cet email car vous venez de créer un compte sur Koumbaya MarketPlace.  
        Si vous n'êtes pas à l'origine de cette inscription, veuillez nous contacter à support@koumbaya.cm
    @endcomponent
@endcomponent