@extends('emails.layouts.base')

@section('content')
<!-- Winner Icon with Animation -->
<div style="text-align: center; margin-bottom: 32px;">
    <div style="display: inline-block; padding: 24px; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); border-radius: 50%; color: white; font-size: 48px; box-shadow: 0 8px 24px rgba(251, 191, 36, 0.4); position: relative; animation: celebrate 2s ease-in-out infinite;">
        
    </div>
    <div style="margin-top: 12px; font-size: 24px;">  </div>
</div>

<!-- Winning Announcement -->
<h1 style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; color: #b45309; font-size: 32px; font-weight: bold; margin: 0 0 16px 0; text-align: center; text-shadow: 2px 2px 4px rgba(180, 83, 9, 0.1);">
     FÉLICITATIONS ! 
</h1>

<div style="background: linear-gradient(135deg, #fef3c7 0%, #fbbf24 100%); border: 3px solid #f59e0b; border-radius: 16px; padding: 24px; margin: 32px 0; text-align: center; box-shadow: 0 8px 24px rgba(251, 191, 36, 0.2);">
    <h2 style="margin: 0 0 8px 0; color: #92400e; font-size: 24px; font-weight: bold;">
        VOUS AVEZ GAGNÉ !
    </h2>
    <p style="margin: 0; color: #92400e; font-size: 18px; font-weight: 600;">
        {{ $user->first_name ?? 'Cher gagnant' }}, vous êtes notre grand gagnant ! 
    </p>
</div>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 18px; line-height: 1.6; margin: 0 0 32px 0; text-align: center; color: #2d3748; font-weight: 600;">
    Nous avons le plaisir de vous annoncer que vous êtes le gagnant de notre tombola ! 
</p>

<!-- Prize Card -->
<div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 3px solid #10b981; border-radius: 16px; padding: 32px; margin: 32px 0; text-align: center; position: relative; overflow: hidden;">
    <!-- Prize Badge -->
    <div style="position: absolute; top: -8px; right: -8px; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; transform: rotate(15deg); box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);">
        GAGNANT !
    </div>
    
    @if(isset($product))
    <div style="margin-bottom: 20px;">
        @if($product->image)
        <img src="{{ $product->image }}" alt="{{ $product->title }}" style="max-width: 250px; height: auto; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.15); border: 3px solid #10b981;">
        @endif
    </div>
    <h3 style="margin: 0 0 12px 0; color: #065f46; font-size: 24px; font-weight: bold;"> {{ $product->title }}</h3>
    <p style="margin: 0 0 20px 0; color: #059669; font-size: 16px; line-height: 1.5;">{{ $product->description }}</p>
    <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 12px 24px; border-radius: 25px; display: inline-block; font-weight: bold; font-size: 18px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
         Valeur: {{ number_format($product->price, 0, ',', ' ') }} FCFA
    </div>
    @endif
</div>

<!-- Winning Details -->
<div style="background: white; border: 2px solid #fbbf24; border-radius: 12px; padding: 24px; margin: 32px 0;">
    <h3 style="margin: 0 0 20px 0; color: #92400e; font-size: 20px; font-weight: bold; text-align: center;">
         Détails de votre gain
    </h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        @if(isset($winningTicket))
        <tr>
            <td style="padding: 12px 0; color: #4a5568; font-weight: 600;">Ticket gagnant:</td>
            <td style="padding: 12px 0; color: #dc2626; font-family: monospace; font-weight: bold; font-size: 16px;">{{ $winningTicket->ticket_number }}</td>
        </tr>
        @endif
        @if(isset($lottery))
        <tr>
            <td style="padding: 12px 0; color: #4a5568; font-weight: 600;">Tombola N°:</td>
            <td style="padding: 12px 0; color: #2d3748; font-weight: bold;">{{ $lottery->lottery_number }}</td>
        </tr>
        <tr>
            <td style="padding: 12px 0; color: #4a5568; font-weight: 600;">Date du tirage:</td>
            <td style="padding: 12px 0; color: #2d3748; font-weight: bold;">{{ isset($lottery->draw_date) ? \Carbon\Carbon::parse($lottery->draw_date)->format('d/m/Y à H:i') : 'N/A' }}</td>
        </tr>
        @endif
        <tr>
            <td style="padding: 12px 0; color: #4a5568; font-weight: 600;">Total participants:</td>
            <td style="padding: 12px 0; color: #2d3748; font-weight: bold;">{{ $totalParticipants ?? 'N/A' }} participants</td>
        </tr>
        <tr style="border-top: 2px solid #e5e7eb;">
            <td style="padding: 16px 0 12px 0; color: #065f46; font-weight: bold; font-size: 18px;"> Vous avez gagné:</td>
            <td style="padding: 16px 0 12px 0; color: #065f46; font-weight: bold; font-size: 18px;">{{ isset($product) ? $product->title : 'Prix à définir' }}</td>
        </tr>
    </table>
</div>

<!-- Next Steps -->
<div style="background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%); border-left: 4px solid #f59e0b; padding: 24px; border-radius: 8px; margin: 32px 0;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 24px; margin-right: 12px;"></div>
        <div>
            <p style="margin: 0 0 12px 0; font-weight: bold; color: #92400e; font-size: 18px;">Prochaines étapes</p>
            <ol style="margin: 0; padding-left: 20px; font-size: 15px; color: #92400e; line-height: 1.6;">
                <li><strong>Vérification d'identité</strong> - Nous vous contactons dans les 24h</li>
                <li><strong>Confirmation du gain</strong> - Validation de votre ticket gagnant</li>
                <li><strong>Remise du prix</strong> - Récupération ou livraison de votre gain</li>
                <li><strong>Certificat de gain</strong> - Document officiel de votre victoire</li>
            </ol>
        </div>
    </div>
</div>

<!-- Contact Information -->
<div style="background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%); border: 2px solid #0099cc; border-radius: 12px; padding: 24px; margin: 32px 0; text-align: center;">
    <h3 style="margin: 0 0 16px 0; color: #0c4a6e; font-size: 18px; font-weight: bold;">
         Notre équipe vous contacte bientôt !
    </h3>
    <p style="margin: 0 0 16px 0; color: #0c4a6e; font-size: 15px; line-height: 1.5;">
        Un membre de notre équipe vous appellera au <strong>{{ $user->phone ?? 'numéro enregistré' }}</strong> 
        dans les prochaines 24 heures pour organiser la remise de votre prix.
    </p>
    <div style="background: white; border-radius: 8px; padding: 16px; margin: 16px 0;">
        <p style="margin: 0; color: #374151; font-size: 14px;">
            <strong>Contact urgent:</strong><br>
            @ gagnants@koumbaya.com<br>
             +241 01 XX XX XX<br>
            🕒 Lundi - Vendredi : 8h - 18h
        </p>
    </div>
</div>

<!-- Action Button -->
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 32px auto;">
    <tr>
        <td align="center">
            <a href="{{ config('app.url') }}/customer/tickets" 
               target="_blank" 
               style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #92400e; text-decoration: none; padding: 16px 40px; border-radius: 12px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 6px 16px rgba(251, 191, 36, 0.4); border: 2px solid #f59e0b;">
                 Voir mon ticket gagnant
            </a>
        </td>
    </tr>
</table>

<!-- Important Notice -->
<div style="background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%); border-left: 4px solid #dc2626; padding: 20px; border-radius: 8px; margin: 32px 0;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 20px; margin-right: 12px;"></div>
        <div>
            <p style="margin: 0 0 8px 0; font-weight: bold; color: #991b1b;">Important !</p>
            <ul style="margin: 0; padding-left: 16px; font-size: 14px; color: #991b1b; line-height: 1.5;">
                <li>Vous avez <strong>30 jours</strong> pour réclamer votre prix</li>
                <li>Une pièce d'identité sera requise pour la remise</li>
                <li>Méfiez-vous des tentatives d'arnaque - nous ne demandons jamais d'argent</li>
                <li>Ce gain est non-transférable et personnel</li>
            </ul>
        </div>
    </div>
</div>

<!-- Share Your Joy -->
<div style="background: linear-gradient(135deg, #f3e8ff 0%, #ddd6fe 100%); border-radius: 12px; padding: 24px; margin: 32px 0; text-align: center;">
    <h3 style="margin: 0 0 16px 0; color: #6b21a8; font-size: 18px; font-weight: bold;">
         Partagez votre joie !
    </h3>
    <p style="margin: 0 0 16px 0; color: #7c3aed; font-size: 14px;">
        N'hésitez pas à partager cette grande nouvelle avec vos proches !
    </p>
    <div style="margin-top: 16px;">
        <span style="font-size: 24px; margin: 0 4px;"></span>
        <span style="font-size: 24px; margin: 0 4px;"></span>
        <span style="font-size: 24px; margin: 0 4px;"></span>
        <span style="font-size: 24px; margin: 0 4px;"></span>
        <span style="font-size: 24px; margin: 0 4px;"></span>
    </div>
</div>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 18px; line-height: 1.6; margin: 40px 0 0 0; text-align: center; color: #2d3748; font-weight: 600;">
    Encore une fois, toutes nos félicitations ! <br>
    <strong style="color: #fbbf24;">L'équipe Koumbaya</strong> <br>
    <em style="color: #6b7280; font-size: 14px;">"Rendre l'impossible accessible"</em>
</p>

<!-- Winner Badge -->
<div style="text-align: center; margin-top: 32px;">
    <div style="display: inline-block; background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #92400e; padding: 12px 24px; border-radius: 25px; font-weight: bold; font-size: 14px; border: 2px solid #f59e0b; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);">
         GAGNANT OFFICIEL Koumbaya Marketplace 
    </div>
</div>
@endsection