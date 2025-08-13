<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Résultats de la Tombola</title>
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
            background: linear-gradient(135deg, #6c757d, #495057);
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
        .result-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
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
            color: #495057;
            margin: 15px 0;
        }
        .winner-info {
            background: linear-gradient(135deg, #e6f7ff, #cce7f0);
            border: 1px solid #0099cc;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .stats {
            background: #e9ecef;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .stats h3 {
            margin-top: 0;
            color: #495057;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 15px;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background: white;
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
        .not-winner {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><span class="emoji">📊</span> RÉSULTATS DE LA TOMBOLA</h1>
            <p>{{ $lottery->lottery_number }}</p>
        </div>

        <div class="content">
            <div class="result-card">
                <h2><span class="emoji">🎁</span> Produit en jeu</h2>
                
                @if($lottery->product->image_url)
                    <img src="{{ $lottery->product->image_url }}" alt="{{ $lottery->product->title }}" class="product-image">
                @endif
                
                <div class="product-title">{{ $lottery->product->title }}</div>
                <p><strong>Valeur :</strong> {{ number_format($lottery->product->price, 0, ',', ' ') }} FCFA</p>
            </div>

            <div class="winner-info">
                <h3><span class="emoji">🏆</span> GAGNANT OFFICIEL</h3>
                <p><strong>Nom :</strong> {{ substr($winner->first_name, 0, 1) }}**** {{ substr($winner->last_name, 0, 1) }}****</p>
                <p><strong>Ville :</strong> {{ $winner->city ?? 'Non spécifiée' }}</p>
                <p><strong>Ticket gagnant :</strong> {{ $lottery->winner_ticket_number }}</p>
                <p><strong>Date du tirage :</strong> {{ $lottery->draw_date->format('d/m/Y à H:i') }}</p>
            </div>

            <div class="stats">
                <h3><span class="emoji">📈</span> Statistiques de la tombola</h3>
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
                        <div class="stat-value">{{ round(($lottery->sold_tickets / $lottery->total_tickets) * 100, 1) }}%</div>
                        <div class="stat-label">Taux participation</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{{ number_format($lottery->sold_tickets * $lottery->ticket_price, 0, ',', ' ') }}</div>
                        <div class="stat-label">Revenus (FCFA)</div>
                    </div>
                </div>
            </div>

            @if($isWinner)
                <div style="background: #e6f7ff; border: 1px solid #0099cc; padding: 20px; border-radius: 5px; text-align: center;">
                    <h3><span class="emoji">🎉</span> FÉLICITATIONS !</h3>
                    <p>Vous êtes le grand gagnant de cette tombola !</p>
                    <p>Un email spécial avec toutes les instructions vous a été envoyé.</p>
                </div>
            @else
                <div class="not-winner">
                    <h3><span class="emoji">😔</span> Pas de chance cette fois</h3>
                    <p>Votre ticket n'a pas été tiré au sort, mais ne vous découragez pas !</p>
                    <p>De nouvelles tombolas sont régulièrement ajoutées sur notre plateforme.</p>
                </div>
            @endif

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.frontend_url') }}/results" class="btn">
                    Voir tous les résultats
                </a>
            </div>

            <div style="text-align: center; color: #6c757d; margin-top: 30px;">
                <p><em>Tous nos tirages sont effectués de manière transparente et équitable.</em></p>
                <p><strong>Merci de votre participation !</strong></p>
            </div>
        </div>

        <div class="footer">
            <p><strong>Koumbaya Marketplace</strong></p>
            <p>La plateforme de tombolas la plus transparente du Cameroun</p>
            <p>📧 support@koumbaya.cm | 📞 +237 123 456 789</p>
            <p><a href="{{ config('app.frontend_url') }}/results" style="color: #17a2b8;">Vérifiez tous les résultats en ligne</a></p>
        </div>
    </div>
</body>
</html>