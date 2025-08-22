<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de remboursement rejetée</title>
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
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .content {
            padding: 30px;
        }

        .rejection-card {
            background: #f8d7da;
            border: 2px solid #dc3545;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
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

        .rejection-reason {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }

        .rejection-reason h4 {
            margin-top: 0;
            color: #721c24;
        }

        .next-steps {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 20px;
            margin: 20px 0;
        }

        .next-steps h3 {
            margin-top: 0;
            color: #0c5460;
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
            background: #17a2b8;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .emoji {
            font-size: 20px;
        }

        .verification-code {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            font-weight: bold;
            background: #f8f9fa;
            padding: 6px 10px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1> DEMANDE REJETÉE</h1>
            <p>Votre demande de remboursement a été examinée</p>
        </div>

        <div class="content">
            <div class="rejection-card">
                <h2><span class="emoji">😔</span> Demande non acceptée</h2>
                <p>Malheureusement, votre demande de remboursement ne peut pas être acceptée.</p>
                <p>Référence : <span class="verification-code">{{ $refund->refund_number }}</span></p>
            </div>

            <div class="refund-details">
                <h3><span class="emoji">📋</span> Détails de la demande</h3>

                <div class="detail-row">
                    <span class="detail-label">Montant demandé :</span>
                    <span class="detail-value">{{ number_format($refund->amount, 0, ',', ' ') }} FCFA</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Date de demande :</span>
                    <span class="detail-value">{{ $refund->created_at->format('d/m/Y à H:i') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Date de rejet :</span>
                    <span class="detail-value">{{ $refund->rejected_at->format('d/m/Y à H:i') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Raison initiale :</span>
                    <span class="detail-value">
                        @switch($refund->reason)
                            @case('accidental_purchase')
                                Achat accidentel
                            @break

                            @case('changed_mind')
                                Changement d'avis
                            @break

                            @case('duplicate_purchase')
                                Achat en double
                            @break

                            @case('technical_issue')
                                Problème technique
                            @break

                            @default
                                {{ $refund->reason }}
                        @endswitch
                    </span>
                </div>
            </div>

            <div class="rejection-reason">
                <h4><span class="emoji">📝</span> Raison du rejet</h4>
                <p><strong>{{ $refund->rejection_reason }}</strong></p>

                @if ($refund->rejectedBy)
                    <p style="font-size: 14px; color: #6c757d; margin-top: 15px;">
                        Décision prise par : {{ $refund->rejectedBy->full_name }}<br>
                        Le {{ $refund->rejected_at->format('d/m/Y à H:i') }}
                    </p>
                @endif
            </div>

            @if ($refund->lottery)
                <div class="refund-details">
                    <h3><span class="emoji">🎁</span> Tombola concernée</h3>

                    <div class="detail-row">
                        <span class="detail-label">Numéro :</span>
                        <span class="detail-value">{{ $refund->lottery->lottery_number }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Produit :</span>
                        <span class="detail-value">{{ $refund->lottery->product->title }}</span>
                    </div>

                    <div class="detail-row">
                        <span class="detail-label">Statut de la tombola :</span>
                        <span class="detail-value">
                            @switch($refund->lottery->status)
                                @case('active')
                                    En cours
                                @break

                                @case('completed')
                                    Terminée
                                @break

                                @case('cancelled')
                                    Annulée
                                @break

                                @default
                                    {{ $refund->lottery->status }}
                            @endswitch
                        </span>
                    </div>
                </div>
            @endif

            <div class="next-steps">
                <h3><span class="emoji">💡</span> Que faire maintenant ?</h3>

                <ul>
                    <li><strong>Contacter le support :</strong> Si vous pensez qu'il y a une erreur, contactez notre
                        équipe de support</li>
                    <li><strong>Fournir des preuves :</strong> Vous pouvez nous envoyer des documents justificatifs si
                        disponibles</li>
                    <li><strong>Nouvelle demande :</strong> Vous pouvez faire une nouvelle demande avec plus
                        d'informations</li>
                    <li><strong>Conditions générales :</strong> Consultez nos conditions de remboursement sur notre site
                    </li>
                </ul>

                <p><strong>Note importante :</strong> Cette décision a été prise après examen attentif de votre demande
                    selon nos politiques de remboursement.</p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="mailto:support@koumbaya.cm?subject=Contestation remboursement {{ $refund->refund_number }}"
                    class="btn">
                    📧 Contacter le Support
                </a>

                <a href="{{ config('app.frontend_url') }}/customer/refunds" class="btn btn-secondary">
                    👁️ Voir mes demandes
                </a>
            </div>

            <div
                style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px; padding: 15px; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #856404;"><span class="emoji">ℹ️</span> Politique de remboursement
                </h4>
                <p style="margin-bottom: 0; font-size: 14px;">
                    Les remboursements sont accordés selon nos conditions générales. Les tombolas actives avec des
                    tickets valides ne sont généralement pas éligibles aux remboursements sauf cas exceptionnels
                    (problème technique, annulation par l'organisateur, etc.).
                </p>
            </div>

            <div style="text-align: center; color: #6c757d; margin-top: 30px;">
                <p><em>Nous nous excusons pour tout désagrément et restons à votre disposition pour toute question.</em>
                </p>
            </div>
        </div>

        <div class="footer">
            <p><strong>Koumbaya Marketplace</strong></p>
            <p>La plateforme de tombolas la plus transparente du Cameroun</p>
            <p>📧 support@koumbaya.cm | 📞 +237 123 456 789</p>

            <p style="margin-top: 15px; font-size: 12px; opacity: 0.8;">
                Référence de la demande : {{ $refund->refund_number }}<br>
                Vous pouvez faire appel de cette décision en contactant notre support
            </p>
        </div>
    </div>
</body>

</html>
