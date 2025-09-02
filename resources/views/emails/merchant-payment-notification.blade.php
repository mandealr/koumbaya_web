@extends('emails.layouts.base')

@section('content')
    <h2>Nouveau paiement reçu</h2>
    
    <p>Bonjour,</p>
    
    <p>Vous avez reçu un nouveau paiement de <strong>{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</strong> sur votre boutique Koumbaya.</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3>Détails du paiement</h3>
        <ul style="list-style: none; padding: 0;">
            <li><strong>Référence :</strong> {{ $payment->reference }}</li>
            <li><strong>Client :</strong> {{ $payment->customer_name ?? 'N/A' }}</li>
            <li><strong>Téléphone :</strong> {{ $payment->customer_phone ?? 'N/A' }}</li>
            <li><strong>Montant :</strong> {{ number_format($payment->amount, 0, ',', ' ') }} FCFA</li>
            <li><strong>Méthode :</strong> {{ ucfirst($payment->payment_method ?? 'Mobile Money') }}</li>
            <li><strong>Date :</strong> {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y à H:i') : 'Maintenant' }}</li>
        </ul>
    </div>
    
    @if($payment->order)
        <h3>Détails de la commande</h3>
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
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.frontend_url') }}/merchant/dashboard" 
           style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
            Voir dans le tableau de bord
        </a>
    </div>
    
    <p style="margin-top: 30px;">
        L'équipe Koumbaya<br>
        <a href="{{ config('app.frontend_url') }}">{{ config('app.frontend_url') }}</a>
    </p>
@endsection