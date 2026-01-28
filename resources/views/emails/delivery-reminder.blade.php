@component('mail::message')
# Rappel : Confirmez la réception de votre commande

Bonjour **{{ $order->user->first_name ?? 'Client' }}**,

Le vendeur **{{ $merchantName }}** souhaite vous rappeler que votre commande **{{ $order->order_number }}** a été livrée.

@component('mail::panel')
**Action requise**

Pour finaliser la transaction, veuillez confirmer que vous avez bien reçu votre commande.
@endcomponent

## Informations de la commande

- **Numéro :** {{ $order->order_number }}
- **Date de commande :** {{ $order->created_at->format('d/m/Y à H:i') }}
- **Montant :** {{ number_format($order->total_amount, 0, ',', ' ') }} FCFA

@component('mail::button', ['url' => config('app.frontend_url').'/customer/orders/'.$order->order_number, 'color' => 'success'])
Confirmer la réception
@endcomponent

@component('mail::panel')
**Pourquoi confirmer la réception ?**

- Cela permet au vendeur de savoir que vous avez bien reçu votre produit
- La transaction sera finalisée et le paiement sera libéré au vendeur
- En cas de problème avec votre commande, vous pouvez nous contacter avant de confirmer
@endcomponent

---

**Un problème avec votre commande ?** Contactez notre service client à support@koumbaya.com avant de confirmer la réception.

Merci de votre confiance,
**L'équipe Koumbaya Marketplace**

@component('mail::subcopy')
Si vous avez déjà confirmé la réception de cette commande, vous pouvez ignorer cet email.
@endcomponent
@endcomponent
