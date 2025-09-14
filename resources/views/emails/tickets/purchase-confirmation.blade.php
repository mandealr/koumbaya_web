@extends('emails.layouts.base')

@section('content')
<!-- Success Icon -->
<div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; padding: 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 50%; color: white; font-size: 32px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
        
    </div>
</div>

<!-- Greeting -->
<h1 style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; color: #2d3748; font-size: 24px; font-weight: bold; margin: 0 0 16px 0; text-align: center;">
    Achat confirmé !
</h1>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 0 0 24px 0; text-align: center; color: #4a5568;">
    Félicitations {{ $user->first_name ?? 'cher participant' }} ! 
</p>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 0 0 32px 0; text-align: left; color: #4a5568;">
    Votre achat de tickets a été confirmé avec succès. Vous participez maintenant à la tombola pour gagner ce magnifique produit !
</p>

<!-- Product Card -->
<div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 2px solid #0099cc; border-radius: 12px; padding: 24px; margin: 32px 0; text-align: center;">
    @if(isset($product))
    <div style="margin-bottom: 16px;">
        @if($product->image)
        <img src="{{ $product->image }}" alt="{{ $product->title }}" style="max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        @endif
    </div>
    <h3 style="margin: 0 0 8px 0; color: #2d3748; font-size: 20px; font-weight: bold;">{{ $product->title }}</h3>
    <p style="margin: 0 0 16px 0; color: #4a5568; font-size: 14px;">{{ $product->description }}</p>
    <div style="background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); color: white; padding: 8px 16px; border-radius: 20px; display: inline-block; font-weight: bold;">
         Valeur: {{ number_format($product->price, 0, ',', ' ') }} FCFA
    </div>
    @endif
</div>

<!-- Ticket Details -->
<div style="background: white; border: 2px solid #10b981; border-radius: 12px; padding: 24px; margin: 32px 0;">
    <h3 style="margin: 0 0 16px 0; color: #065f46; font-size: 18px; font-weight: bold; text-align: center;">
         Détails de votre participation
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; color: #4a5568; font-weight: 600;">Numéro de transaction:</td>
            <td style="padding: 8px 0; color: #2d3748; font-family: monospace; font-weight: bold;">{{ $transaction->transaction_id ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #4a5568; font-weight: 600;">Nombre de tickets:</td>
            <td style="padding: 8px 0; color: #2d3748; font-weight: bold;">{{ $transaction->quantity ?? 1 }} ticket{{ ($transaction->quantity ?? 1) > 1 ? 's' : '' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #4a5568; font-weight: 600;">Prix par ticket:</td>
            <td style="padding: 8px 0; color: #2d3748; font-weight: bold;">{{ isset($ticketPrice) ? number_format($ticketPrice, 0, ',', ' ') : 'N/A' }} FCFA</td>
        </tr>
        <tr style="border-top: 2px solid #e5e7eb;">
            <td style="padding: 12px 0 8px 0; color: #065f46; font-weight: bold; font-size: 16px;">Total payé:</td>
            <td style="padding: 12px 0 8px 0; color: #065f46; font-weight: bold; font-size: 16px;">{{ isset($transaction->amount) ? number_format($transaction->amount, 0, ',', ' ') : 'N/A' }} FCFA</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #4a5568; font-weight: 600;">Date d'achat:</td>
            <td style="padding: 8px 0; color: #2d3748; font-weight: bold;">{{ isset($transaction->created_at) ? $transaction->created_at->format('d/m/Y à H:i') : date('d/m/Y à H:i') }}</td>
        </tr>
        @if(isset($lottery->draw_date))
        <tr>
            <td style="padding: 8px 0; color: #4a5568; font-weight: 600;">Date du tirage:</td>
            <td style="padding: 8px 0; color: #dc2626; font-weight: bold;">{{ \Carbon\Carbon::parse($lottery->draw_date)->format('d/m/Y à H:i') }}</td>
        </tr>
        @endif
    </table>
</div>

<!-- Ticket Numbers -->
@if(isset($tickets) && count($tickets) > 0)
<div style="background: linear-gradient(135deg, #fef3c7 0%, #fbbf24 100%); border-radius: 12px; padding: 24px; margin: 32px 0; text-align: center;">
    <h3 style="margin: 0 0 16px 0; color: #92400e; font-size: 18px; font-weight: bold;">
        🎟️ Vos numéros de tickets
    </h3>
    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px;">
        @foreach($tickets as $ticket)
        <span style="background: white; color: #92400e; padding: 8px 12px; border-radius: 8px; font-family: monospace; font-weight: bold; border: 2px solid #f59e0b; display: inline-block;">
            {{ $ticket->ticket_number }}
        </span>
        @endforeach
    </div>
</div>
@endif

<!-- Action Button -->
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 32px auto;">
    <tr>
        <td align="center">
            <a href="{{ config('app.url') }}/customer/tickets" 
               target="_blank" 
               style="background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); color: white; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-weight: 600; display: inline-block; box-shadow: 0 4px 12px rgba(0, 153, 204, 0.3);">
                 Voir mes tickets
            </a>
        </td>
    </tr>
</table>

<!-- Progress Info -->
@if(isset($lottery))
<div style="background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%); border-left: 4px solid #0099cc; padding: 20px; border-radius: 8px; margin: 32px 0;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 20px; margin-right: 12px;"></div>
        <div>
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #0c4a6e;">Progression de la tombola</p>
            <p style="margin: 0; font-size: 14px; color: #0c4a6e; line-height: 1.5;">
                {{ $lottery->sold_tickets ?? 0 }} tickets vendus sur {{ $lottery->total_tickets ?? 0 }} 
                ({{ $lottery->total_tickets > 0 ? round(($lottery->sold_tickets / $lottery->total_tickets) * 100, 1) : 0 }}%)
            </p>
            @if(isset($lottery->draw_date))
            <p style="margin: 8px 0 0 0; font-size: 14px; color: #0c4a6e;">
                <strong>Tirage prévu:</strong> {{ \Carbon\Carbon::parse($lottery->draw_date)->format('d/m/Y à H:i') }}
            </p>
            @endif
        </div>
    </div>
</div>
@endif

<!-- What's Next -->
<div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-left: 4px solid #10b981; padding: 20px; border-radius: 8px; margin: 32px 0;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 20px; margin-right: 12px;"></div>
        <div>
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #065f46;">Et maintenant ?</p>
            <ul style="margin: 0; padding-left: 16px; font-size: 14px; color: #065f46; line-height: 1.5;">
                <li>Vous recevrez une notification par email et SMS si vous gagnez</li>
                <li>Le tirage aura lieu à la date indiquée</li>
                <li>Consultez vos tickets dans votre espace personnel</li>
                <li>Participez à d'autres tombolas pour maximiser vos chances !</li>
            </ul>
        </div>
    </div>
</div>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 32px 0 0 0; text-align: left; color: #4a5568;">
    Merci de votre confiance et bonne chance ! <br>
    <strong style="color: #0099cc;">L'équipe Koumbaya</strong> 
</p>

<!-- Support -->
<div style="background: #f7fafc; border-radius: 8px; padding: 16px; margin: 32px 0; text-align: center;">
    <p style="margin: 0; font-size: 14px; color: #718096;">
        Une question ? Contactez notre support à 
        <a href="mailto:support@koumbaya.com" style="color: #0099cc; text-decoration: none; font-weight: 600;">support@koumbaya.com</a>
        ou au <strong>+241 01 XX XX XX</strong>
    </p>
</div>
@endsection