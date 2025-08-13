<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💸 Remboursement traité</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #0099cc, #007799);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 30px;
        }
        .refund-card {
            background: linear-gradient(135deg, #e6f7ff, #cce7f0);
            border: 2px solid #0099cc;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .amount {
            font-size: 32px;
            font-weight: bold;
            color: #0099cc;
            margin: 15px 0;
        }
        .refund-details {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .refund-details h3 {
            margin-top: 0;
            color: #495057;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 4px 0;
        }
        .detail-label {
            color: #6c757d;
            font-weight: 500;
        }
        .detail-value {
            font-weight: 600;
            text-align: right;
        }
        .reason-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .reason-box h4 {
            margin-top: 0;
            color: #856404;
        }
        .timeline {
            background: #e9ecef;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .timeline h4 {
            margin-top: 0;
            color: #495057;
        }
        .timeline-item {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
        }
        .next-steps {
            background: #e6f7ff;
            border-left: 4px solid #0099cc;
            padding: 20px;
            margin: 20px 0;
        }
        .next-steps h3 {
            margin-top: 0;
            color: #007799;
        }
        .footer {
            background: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #0099cc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .emoji {
            font-size: 24px;
        }
        .verification-code {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: bold;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span class="emoji">💸</span> REMBOURSEMENT TRAITÉ</h1>
            <p>Votre remboursement a été traité avec succès !</p>
        </div>

        <div class="content">
            <div class="refund-card">
                <h2><span class="emoji">✅</span> Remboursement effectué</h2>
                <div class="amount">{{ number_format($refund->amount, 0, ',', ' ') }} FCFA</div>
                <p>Référence : <span class="verification-code">{{ $refund->refund_number }}</span></p>
            </div>

            <div class="refund-details">
                <h3><span class="emoji">📋</span> Détails du remboursement</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Montant remboursé :</span>
                    <span class="detail-value">{{ number_format($refund->amount, 0, ',', ' ') }} FCFA</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Méthode de remboursement :</span>
                    <span class="detail-value">{{ $refund->refund_method === 'mobile_money' ? 'Mobile Money' : ($refund->refund_method === 'wallet_credit' ? 'Crédit portefeuille' : 'Virement bancaire') }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Type :</span>
                    <span class="detail-value">{{ $refund->auto_processed ? 'Automatique' : 'Manuel' }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Date de traitement :</span>
                    <span class="detail-value">{{ $refund->processed_at->format('d/m/Y à H:i') }}</span>
                </div>
                
                @if($refund->external_refund_id)
                <div class="detail-row">
                    <span class="detail-label">Référence externe :</span>
                    <span class="detail-value">{{ $refund->external_refund_id }}</span>
                </div>
                @endif
            </div>

            <div class="reason-box">
                <h4><span class="emoji">❓</span> Raison du remboursement</h4>
                <p>
                    @switch($refund->reason)
                        @case('insufficient_participants')
                            <strong>Participants insuffisants</strong> - La tombola n'a pas atteint le nombre minimum de participants requis.
                            @break
                        @case('lottery_cancelled')
                            <strong>Tombola annulée</strong> - La tombola a été annulée par l'organisateur.
                            @break
                        @case('accidental_purchase')
                            <strong>Achat accidentel</strong> - Achat effectué par erreur.
                            @break
                        @case('technical_issue')
                            <strong>Problème technique</strong> - Un problème technique a nécessité ce remboursement.
                            @break
                        @default
                            {{ $refund->reason }}
                    @endswitch
                </p>
            </div>

            @if($refund->lottery)
            <div class="refund-details">
                <h3><span class="emoji">🎁</span> Tombola concernée</h3>
                
                <div class="detail-row">
                    <span class="detail-label">Numéro de tombola :</span>
                    <span class="detail-value">{{ $refund->lottery->lottery_number }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Produit :</span>
                    <span class="detail-value">{{ $refund->lottery->product->title }}</span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Valeur du produit :</span>
                    <span class="detail-value">{{ number_format($refund->lottery->product->price, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
            @endif

            <div class="timeline">
                <h4><span class="emoji">📅</span> Chronologie</h4>
                
                <div class="timeline-item">
                    <span>Demande créée</span>
                    <span>{{ $refund->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                
                @if($refund->approved_at)
                <div class="timeline-item">
                    <span>Approuvé</span>
                    <span>{{ $refund->approved_at->format('d/m/Y à H:i') }}</span>
                </div>
                @endif
                
                <div class="timeline-item">
                    <span>Traité</span>
                    <span>{{ $refund->processed_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>

            <div class="next-steps">
                <h3><span class="emoji">💡</span> Que se passe-t-il maintenant ?</h3>
                
                @if($refund->refund_method === 'mobile_money')
                <ul>
                    <li>Le montant a été crédité sur votre compte Mobile Money</li>
                    <li>Vous devriez recevoir un SMS de confirmation de votre opérateur</li>
                    <li>Le remboursement peut prendre quelques minutes à apparaître</li>
                </ul>
                @elseif($refund->refund_method === 'wallet_credit')
                <ul>
                    <li>Le montant a été ajouté à votre portefeuille Koumbaya</li>
                    <li>Vous pouvez l'utiliser pour vos prochains achats de tickets</li>
                    <li>Consultez votre portefeuille dans l'application</li>
                </ul>
                @else
                <ul>
                    <li>Le remboursement sera effectué selon la méthode spécifiée</li>
                    <li>Vous recevrez une confirmation une fois terminé</li>
                    <li>Contactez notre support en cas de problème</li>
                </ul>
                @endif
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.frontend_url') }}/customer/refunds" class="btn">
                    Voir mes remboursements
                </a>
            </div>

            @if($refund->notes)
            <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #1565c0;">📝 Notes</h4>
                <p style="margin-bottom: 0;">{{ $refund->notes }}</p>
            </div>
            @endif

            <div style="text-align: center; color: #6c757d; margin-top: 30px;">
                <p><em>Merci de votre compréhension et de votre confiance en Koumbaya Marketplace</em></p>
            </div>
        </div>

        <div class="footer">
            <p><strong>Koumbaya Marketplace</strong></p>
            <p>La plateforme de tombolas la plus transparente du Cameroun</p>
            <p>📧 support@koumbaya.cm | 📞 +237 123 456 789</p>
            
            <p style="margin-top: 15px; font-size: 12px; opacity: 0.8;">
                Référence de remboursement : {{ $refund->refund_number }}<br>
                Conservez cet email comme preuve de remboursement
            </p>
        </div>
    </div>
</body>
</html>