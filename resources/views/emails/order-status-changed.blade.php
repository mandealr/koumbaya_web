@component('mail::message')
    @php
        $statusLabels = [
            'paid' => 'Payée',
            'shipping' => 'En cours de livraison', 
            'fulfilled' => 'En cours de livraison',
            'cancelled' => 'Annulée'
        ];
    @endphp

    # Mise à jour de votre commande

    Bonjour **{{ $order->user->first_name ?? 'Client' }}**,

    Votre commande **{{ $order->order_number }}** a été mise à jour.

    @component('mail::panel')
        **Nouveau statut**
        
        Votre commande est maintenant : **{{ $statusLabels[$newStatus] ?? $newStatus }}**
    @endcomponent

    ## Détails de la commande

    - **Numéro :** {{ $order->order_number }}
    - **Date :** {{ $order->created_at->format('d/m/Y à H:i') }}  
    - **Montant total :** {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA
    @if($order->notes)
    - **Notes :** {{ $order->notes }}
    @endif

    ## Produits commandés

    @foreach($order->items as $item)
    - **{{ $item->product->name }}** - Quantité : {{ $item->quantity }} - {{ number_format($item->price, 0, ',', ' ') }} FCFA
    @endforeach

    @if($newStatus === 'shipping')
        @component('mail::panel') 
            **Votre commande est en route !**
            
            Votre commande a été expédiée et sera bientôt livrée.  
            Vous recevrez une notification dès qu'elle sera livrée.
        @endcomponent
    @elseif($newStatus === 'fulfilled')
        @component('mail::panel')
            **Votre commande est en cours de livraison !**
            
            Votre commande a été expédiée et sera bientôt livrée.  
            Vous recevrez une notification dès qu'elle sera livrée.
        @endcomponent
    @elseif($newStatus === 'cancelled')
        @component('mail::panel')
            **Commande annulée**
            
            Votre commande a été annulée. Si vous avez des questions, n'hésitez pas à nous contacter.  
            Le remboursement sera traité dans les plus brefs délais.
        @endcomponent
    @endif

    @component('mail::button', ['url' => config('app.frontend_url').'/customer/orders/'.$order->order_number])
        Voir ma commande
    @endcomponent

    @component('mail::panel')
        **Informations utiles**
        
        - Vous pouvez suivre l'évolution de votre commande dans votre espace client
        - En cas de question, contactez notre service client  
        - Conservez ce numéro de commande : **{{ $order->order_number }}**
    @endcomponent

    ---

    **Besoin d'aide ?** Notre équipe support est disponible à support@koumbaya.com

    Merci de votre confiance en Koumbaya Marketplace  
    **L'équipe Koumbaya Marketplace**

    @component('mail::subcopy')
        Pour suivre toutes vos commandes, rendez-vous dans votre espace client :  
        {{ config('app.frontend_url') }}/customer/orders
    @endcomponent
@endcomponent