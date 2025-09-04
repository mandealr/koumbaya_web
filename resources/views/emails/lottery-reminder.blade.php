@component('mail::message')
    @if($reminderType === '1h')
        # ⏰ Dernière heure - Tirage dans 1 heure !
    @else
        # ⏰ Rappel - Tirage demain sur Koumbaya  
    @endif

    Bonjour **{{ $user->first_name }}**,

    @if($reminderType === '1h')
        🚨 **DERNIÈRE HEURE !** Le tirage de la tombola aura lieu dans **1 heure**.
        Ne laissez pas passer cette opportunité unique !
    @else  
        N'oubliez pas que le tirage de votre tombola aura lieu **demain**.
    @endif

    @component('mail::panel')
        🎁 **{{ $lottery->product->title }}**
        
        **Valeur du prix :** {{ number_format($lottery->product->price, 0, ',', ' ') }} FCFA  
        **Numéro de tombola :** {{ $lottery->lottery_number }}  
        **Date de tirage :** {{ $lottery->end_date->format('d/m/Y à H:i') }}
        
        @php
            $participationRate = $lottery->total_tickets > 0 ? ($lottery->sold_tickets / $lottery->total_tickets) * 100 : 0;
        @endphp
        
        **Progression :** {{ round($participationRate, 1) }}% des tickets vendus
    @endcomponent

    ## 📊 Statistiques de la tombola

    - **{{ $lottery->sold_tickets }}** tickets vendus sur **{{ $lottery->total_tickets }}** au total
    - **Vos chances :** 1 sur {{ $lottery->sold_tickets ?: 1 }}
    - **Prix du ticket :** {{ number_format($lottery->ticket_price, 0) }} FCFA

    @if($reminderType === '1h')
        @component('mail::button', ['url' => config('app.frontend_url').'/lotteries/'.$lottery->id, 'color' => 'error'])
            💨 Participer maintenant - Dernière chance !
        @endcomponent
    @else
        @component('mail::button', ['url' => config('app.frontend_url').'/lotteries/'.$lottery->id, 'color' => 'primary'])
            🎯 Voir la tombola
        @endcomponent
    @endif

    @component('mail::panel')
        ℹ️ **Informations importantes**
        
        - Le tirage aura lieu automatiquement à la date prévue
        - Vous serez notifié du résultat par email et SMS  
        - Les résultats sont vérifiables publiquement
        @if($reminderType !== '1h')
        - Vous pouvez encore acheter des tickets jusqu'au tirage
        @endif
    @endcomponent

    ---

    **Besoin d'aide ?** Notre équipe support est disponible à support@koumbaya.cm

    Bonne chance {{ $user->first_name }} ! 🍀🎊  
    **L'équipe Koumbaya MarketPlace** 💙

    @component('mail::subcopy')
        Si vous ne souhaitez plus recevoir ces rappels, vous pouvez vous désabonner en nous contactant à support@koumbaya.cm
    @endcomponent
@endcomponent