@extends('emails.layouts.base')

@section('content')
    @php
        $statusLabels = [
            'paid' => 'Payée',
            'shipping' => 'En cours de livraison',
            'fulfilled' => 'Livrée',
            'cancelled' => 'Annulée'
        ];
        $statusColors = [
            'paid' => '#10b981',
            'shipping' => '#0099cc',
            'fulfilled' => '#10b981',
            'cancelled' => '#ef4444'
        ];
    @endphp

    <h2 style="color: #1f2937; margin-top: 0;">Mise à jour de votre commande</h2>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6;">
        Bonjour <strong>{{ $order->user->first_name ?? 'Client' }}</strong>,
    </p>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6;">
        Votre commande <strong>{{ $order->order_number }}</strong> a été mise à jour.
    </p>

    <div class="info-box" style="background-color: {{ $statusColors[$newStatus] ?? '#0099cc' }}15; border-left-color: {{ $statusColors[$newStatus] ?? '#0099cc' }};">
        <h3 style="color: #1f2937; margin-top: 0; font-size: 18px;">Nouveau statut</h3>
        <p style="font-size: 20px; font-weight: 700; color: {{ $statusColors[$newStatus] ?? '#0099cc' }}; margin: 0;">
            {{ $statusLabels[$newStatus] ?? $newStatus }}
        </p>
    </div>

    <h3 style="color: #1f2937; font-size: 18px;">Détails de la commande</h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Numéro :</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $order->order_number }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Date :</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $order->created_at->format('d/m/Y à H:i') }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Montant total :</td>
            <td style="padding: 8px 0; color: #1f2937; font-weight: 700;">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
        @if($order->notes)
        <tr>
            <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Notes :</td>
            <td style="padding: 8px 0; color: #1f2937;">{{ $order->notes }}</td>
        </tr>
        @endif
    </table>

    @if($order->product)
    <h3 style="color: #1f2937; font-size: 18px; margin-top: 24px;">Produit commandé</h3>
    <div style="background-color: #f9fafb; padding: 16px; border-radius: 8px;">
        <p style="margin: 0; color: #1f2937; font-weight: 600;">{{ $order->product->name }}</p>
        @if($order->lottery)
        <p style="margin: 8px 0 0 0; color: #6b7280; font-size: 14px;">
            Tombola : {{ $order->lottery->title }}
        </p>
        @endif
    </div>
    @endif

    @if($order->tickets && $order->tickets->count() > 0)
    <h3 style="color: #1f2937; font-size: 18px; margin-top: 24px;">
        Vos tickets de tombola ({{ $order->tickets->count() }})
    </h3>
    <div style="background-color: #faf5ff; padding: 16px; border-radius: 8px; border-left: 4px solid #9333ea;">
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @foreach($order->tickets as $ticket)
            <span style="display: inline-block; background-color: #9333ea; color: #ffffff; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 14px; margin: 4px;">
                {{ $ticket->ticket_number }}
            </span>
            @endforeach
        </div>
        <p style="margin: 12px 0 0 0; color: #6b7280; font-size: 13px;">
            Conservez ces numéros précieusement. Ils seront utilisés lors du tirage au sort.
        </p>
    </div>
    @endif

    @if($newStatus === 'paid')
    <div class="info-box" style="margin-top: 24px;">
        <h4 style="color: #1f2937; margin-top: 0; font-size: 16px;">Paiement confirmé !</h4>
        <p style="color: #4b5563; font-size: 14px; margin: 0;">
            Votre paiement a été reçu avec succès. Le vendeur a été notifié et prépare votre commande.
        </p>
    </div>
    @elseif($newStatus === 'shipping' || $newStatus === 'fulfilled')
    <div class="info-box" style="margin-top: 24px;">
        <h4 style="color: #1f2937; margin-top: 0; font-size: 16px;">Votre commande est en route !</h4>
        <p style="color: #4b5563; font-size: 14px; margin: 0;">
            Votre commande a été expédiée et sera bientôt livrée.
            Vous recevrez une notification dès qu'elle sera livrée.
        </p>
    </div>
    @elseif($newStatus === 'cancelled')
    <div class="info-box" style="margin-top: 24px; background-color: #fef2f2; border-left-color: #ef4444;">
        <h4 style="color: #1f2937; margin-top: 0; font-size: 16px;">Commande annulée</h4>
        <p style="color: #4b5563; font-size: 14px; margin: 0;">
            Votre commande a été annulée. Si vous avez des questions, n'hésitez pas à nous contacter.
            Le remboursement sera traité dans les plus brefs délais.
        </p>
    </div>
    @endif

    <div style="text-align: center; margin: 32px 0;">
        <a href="{{ config('app.frontend_url') }}/customer/orders/{{ $order->order_number }}" class="koumbaya-button" style="background-color: #0099cc; border: 8px solid #0099cc; border-radius: 8px; color: #ffffff; text-decoration: none; display: inline-block; padding: 12px 24px; font-weight: 600;">
            Voir ma commande
        </a>
    </div>

    <div style="background-color: #f0f9ff; border-radius: 8px; padding: 16px; margin-top: 24px;">
        <h4 style="color: #1f2937; margin-top: 0; font-size: 14px;">Informations utiles</h4>
        <ul style="color: #4b5563; font-size: 14px; margin: 0; padding-left: 20px;">
            <li>Vous pouvez suivre l'évolution de votre commande dans votre espace client</li>
            <li>En cas de question, contactez notre service client</li>
            <li>Conservez ce numéro de commande : <strong>{{ $order->order_number }}</strong></li>
        </ul>
    </div>

    <p style="color: #6b7280; font-size: 14px; margin-top: 32px; border-top: 1px solid #e5e7eb; padding-top: 16px;">
        <strong>Besoin d'aide ?</strong> Notre équipe support est disponible à
        <a href="mailto:support@koumbaya.com" style="color: #0099cc; text-decoration: none;">support@koumbaya.com</a>
    </p>

    <p style="color: #4b5563; font-size: 14px; margin-top: 24px;">
        Merci de votre confiance !<br>
        <strong>L'équipe Koumbaya Marketplace</strong>
    </p>
@endsection
