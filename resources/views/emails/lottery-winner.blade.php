<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Félicitations ! Vous avez gagné !</title>
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
            background: linear-gradient(135deg, #0099cc, #007bff);
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

        .winner-card {
            background: linear-gradient(135deg, #e6f7ff, #cce7f0);
            border: 2px solid #0099cc;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .product-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin: 10px auto;
            display: block;
        }

        .product-title {
            font-size: 24px;
            font-weight: bold;
            color: #0099cc;
            margin: 15px 0;
        }

        .product-value {
            font-size: 20px;
            color: #007bff;
            font-weight: bold;
        }

        .verification-code {
            background: #f8f9fa;
            border: 2px dashed #007bff;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 5px;
        }

        .verification-code strong {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            color: #007bff;
        }

        .details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .details h3 {
            margin-top: 0;
            color: #495057;
        }

        .details p {
            margin: 5px 0;
        }

        .next-steps {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            margin: 20px 0;
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
            font-size: 24px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1><span class="emoji"></span> FÉLICITATIONS !</h1>
            <p>Vous êtes le grand gagnant de notre tombola Koumbaya !</p>
        </div>

        <div class="content">
            <div class="winner-card">
                <h2><span class="emoji"></span> Vous avez gagné !</h2>

                @if ($lottery->product->image_url)
                    <img src="{{ $lottery->product->image_url }}" alt="{{ $lottery->product->title }}"
                        class="product-image">
                @endif

                <div class="product-title">{{ $lottery->product->title }}</div>
                <div class="product-value">{{ number_format($lottery->product->price, 0, ',', ' ') }} FCFA</div>
            </div>

            <div class="details">
                <h3><span class="emoji"></span> Détails du gain</h3>
                <p><strong>Tombola :</strong> {{ $lottery->lottery_number }}</p>
                <p><strong>Ticket gagnant :</strong> {{ $winningTicket->ticket_number }}</p>
                <p><strong>Date du tirage :</strong> {{ $lottery->draw_date->format('d/m/Y à H:i') }}</p>
                <p><strong>Participants :</strong> {{ $lottery->sold_tickets }} sur {{ $lottery->total_tickets }}
                    tickets</p>
            </div>

            <div class="verification-code">
                <h4>Code de vérification</h4>
                <p>Votre code de vérification unique :</p>
                <strong>{{ $verificationCode }}</strong>
                <p><small>Conservez ce code pour vérifier votre gain publiquement</small></p>
            </div>

            <div class="next-steps">
                <h3><span class="emoji"></span> Prochaines étapes</h3>
                <p><strong>Pour récupérer votre prix :</strong></p>
                <ol>
                    <li>Contactez notre service client au <strong>+241 123 456 789</strong></li>
                    <li>Munissez-vous de votre code de vérification : <strong>{{ $verificationCode }}</strong></li>
                    <li>Présentez une pièce d'identité valide</li>
                    <li>Le retrait se fait dans nos locaux à Libreville</li>
                </ol>
                <p><em>Vous avez 30 jours pour récupérer votre prix.</em></p>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ config('app.frontend_url') }}/results?verify={{ $verificationCode }}" class="btn">
                    Vérifier mon gain en ligne
                </a>
            </div>

            <div style="text-align: center; color: #6c757d;">
                <p><em>Merci de votre participation à Koumbaya Marketplace !</em></p>
                <p><span class="emoji"></span> <strong>Félicitations encore une fois !</strong> <span
                        class="emoji"></span></p>
            </div>
        </div>

        <div class="footer">
            <p><strong>Koumbaya Marketplace</strong></p>
            <p>La plateforme de tombolas la plus transparente du Gabon</p>
            <p>@ support@koumbaya.com |  +241 123 456 789</p>
        </div>
    </div>
</body>

</html>
