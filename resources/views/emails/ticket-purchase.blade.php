<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ Confirmation d'achat de ticket</title>
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
            background: linear-gradient(135deg, #17a2b8, #007bff);
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
        .success-card {
            background: linear-gradient(135deg, #e6f7ff, #cce7f0);
            border: 2px solid #0099cc;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .product-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            margin: 10px auto;
            display: block;
        }
        .product-title {
            font-size: 20px;
            font-weight: bold;
            color: #0099cc;
            margin: 15px 0;
        }
        .ticket-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .ticket-info h3 {
            margin-top: 0;
            color: #495057;
        }
        .ticket-number {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin: 10px 0;
        }
        .lottery-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .lottery-info h3 {
            margin-top: 0;
            color: #856404;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
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
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .emoji {
            font-size: 20px;
        }
        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            background: #0099cc;
            height: 20px;
            text-align: center;
            color: white;
            font-size: 12px;
            line-height: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span class="emoji">✅</span> ACHAT CONFIRMÉ</h1>
            <p>Votre ticket de tombola a été acheté avec succès !</p>
        </div>

        <div class="content">
            <div class="success-card">
                <h2><span class="emoji">🎫</span> Ticket acheté</h2>
                <p>Félicitations ! Vous participez maintenant à cette tombola.</p>
            </div>

            <div class="ticket-info">
                <h3><span class="emoji">🎟️</span> Vos informations de ticket</h3>
                @foreach($tickets as $ticket)
                <div class="ticket-number">{{ $ticket->ticket_number }}</div>
                @endforeach
                <p><strong>Nombre de tickets :</strong> {{ count($tickets) }}</p>
                <p><strong>Prix unitaire :</strong> {{ number_format($lottery->ticket_price, 0, ',', ' ') }} FCFA</p>
                <p><strong>Total payé :</strong> {{ number_format($totalAmount, 0, ',', ' ') }} FCFA</p>
                <p><strong>Date d'achat :</strong> {{ now()->format('d/m/Y à H:i') }}</p>
            </div>

            <div class="lottery-info">
                <h3><span class="emoji">🎁</span> Tombola</h3>
                
                @if($lottery->product->image_url)
                    <img src="{{ $lottery->product->image_url }}" alt="{{ $lottery->product->title }}" class="product-image">
                @endif
                
                <div class="product-title">{{ $lottery->product->title }}</div>
                <p><strong>Valeur du prix :</strong> {{ number_format($lottery->product->price, 0, ',', ' ') }} FCFA</p>
                <p><strong>Numéro de tombola :</strong> {{ $lottery->lottery_number }}</p>
                <p><strong>Date de fin :</strong> {{ $lottery->end_date->format('d/m/Y à H:i') }}</p>
                
                @php
                    $remainingDays = $lottery->end_date->diffInDays(now());
                    $participationRate = $lottery->total_tickets > 0 ? ($lottery->sold_tickets / $lottery->total_tickets) * 100 : 0;
                @endphp
                
                <p><strong>Temps restant :</strong> {{ $remainingDays }} jour(s)</p>
                
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $participationRate }}%">
                        {{ round($participationRate, 1) }}% vendu
                    </div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $lottery->sold_tickets }}</div>
                    <div class="stat-label">Tickets vendus</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $lottery->total_tickets }}</div>
                    <div class="stat-label">Total tickets</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">1/{{ $lottery->sold_tickets }}</div>
                    <div class="stat-label">Vos chances</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $lottery->ticket_price }}</div>
                    <div class="stat-label">Prix ticket (FCFA)</div>
                </div>
            </div>

            <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 20px; margin: 20px 0;">
                <h3><span class="emoji">ℹ️</span> Informations importantes</h3>
                <ul>
                    <li>Conservez cet email comme preuve d'achat</li>
                    <li>Le tirage aura lieu automatiquement à la date prévue</li>
                    <li>Vous serez notifié du résultat par email et SMS</li>
                    <li>Les résultats sont vérifiables publiquement</li>
                    <li>En cas de gain, vous avez 30 jours pour récupérer votre prix</li>
                </ul>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.frontend_url') }}/customer/tickets" class="btn">
                    Voir mes tickets
                </a>
                <a href="{{ config('app.frontend_url') }}/lotteries/{{ $lottery->id }}" class="btn" style="background: #0099cc;">
                    Voir la tombola
                </a>
            </div>

            <div style="text-align: center; color: #6c757d;">
                <p><span class="emoji">🤞</span> <strong>Bonne chance pour le tirage !</strong> <span class="emoji">🍀</span></p>
                <p><em>Merci de votre confiance en Koumbaya Marketplace</em></p>
            </div>
        </div>

        <div class="footer">
            <p><strong>Koumbaya Marketplace</strong></p>
            <p>La plateforme de tombolas la plus transparente du Cameroun</p>
            <p>📧 support@koumbaya.cm | 📞 +237 123 456 789</p>
        </div>
    </div>
</body>
</html>