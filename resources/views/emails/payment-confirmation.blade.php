@extends('emails.layouts.base')

@section('content')
    <h2>Confirmation de paiement</h2>
    
    <p>Bonjour {{ $payment->customer_name ?? 'Client' }},</p>
    
    <p>Nous confirmons la réception de votre paiement de <strong>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</strong>.</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3>Détails du paiement</h3>
        <ul style="list-style: none; padding: 0;">
            <li><strong>Référence :</strong> {{ $payment->reference }}</li>
            <li><strong>Montant :</strong> {{ number_format($payment->amount, 0, ',', ' ') }} FCFA</li>
            <li><strong>Méthode :</strong> {{ ucfirst($payment->payment_method ?? 'Mobile Money') }}</li>
            <li><strong>Date :</strong> {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y à H:i') : 'Maintenant' }}</li>
        </ul>
    </div>
    
    @if($payment->order)
        <h3>Informations de la commande</h3>
        <ul style="list-style: none; padding: 0;">
            <li><strong>Numéro :</strong> {{ $payment->order->order_number }}</li>
            @if($payment->order->product)
                <li><strong>Produit :</strong> {{ $payment->order->product->name }}</li>
            @endif
            @if($payment->order->lottery)
                <li><strong>Tombola :</strong> {{ $payment->order->lottery->title }}</li>
            @endif
        </ul>
    @endif
    
    <p>Merci de votre confiance !</p>
    
    <p style="margin-top: 30px;">
        L'équipe Koumbaya<br>
        <a href="{{ config('app.frontend_url') }}">{{ config('app.frontend_url') }}</a>
    </p>
@endsection