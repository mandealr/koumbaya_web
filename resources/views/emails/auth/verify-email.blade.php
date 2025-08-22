@extends('emails.layouts.base')

@section('content')
<!-- Welcome Icon -->
<div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; padding: 20px; background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); border-radius: 50%; color: white; font-size: 32px; box-shadow: 0 4px 12px rgba(0, 153, 204, 0.3);">
        ✉️
    </div>
</div>

<!-- Greeting -->
<h1 style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; color: #2d3748; font-size: 24px; font-weight: bold; margin: 0 0 16px 0; text-align: center;">
    Bienvenue sur Koumbaya !
</h1>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 0 0 24px 0; text-align: center; color: #4a5568;">
    Salut {{ $user->first_name ?? 'cher utilisateur' }} ! 👋
</p>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 0 0 32px 0; text-align: left; color: #4a5568;">
    Nous sommes ravis de vous accueillir sur <strong>Koumbaya</strong>, la plateforme qui rend l'impossible accessible ! Pour activer votre compte et commencer à participer à nos tombolas exclusives, veuillez vérifier votre adresse email.
</p>

<!-- Welcome Benefits -->
<div style="background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%); border-radius: 12px; padding: 24px; margin: 32px 0;">
    <h3 style="margin: 0 0 16px 0; color: #0c4a6e; font-size: 18px; font-weight: bold; text-align: center;">
        🎯 Avec Koumbaya, vous pouvez :
    </h3>
    <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #0c4a6e; line-height: 1.8;">
        <li><strong>Gagner des produits exceptionnels</strong> - iPhone, voitures, électroménager...</li>
        <li><strong>Participer aux tombolas</strong> - Avec des tickets à prix abordables</li>
        <li><strong>Suivre vos participations</strong> - Tableau de bord personnalisé</li>
        <li><strong>Recevoir des notifications</strong> - Ne ratez aucun tirage</li>
        <li><strong>Profiter d'offres exclusives</strong> - Réductions et promotions</li>
    </ul>
</div>

<!-- Verify Button -->
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%; margin: 40px auto; padding: 0; text-align: center; width: 100%;">
    <tr>
        <td align="center" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
                <tr>
                    <td align="center" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
                        <a href="{{ $verificationUrl }}" 
                           class="button button-primary" 
                           target="_blank" 
                           rel="noopener" 
                           style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -webkit-text-size-adjust: none; border-radius: 8px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); border: 12px solid transparent; border-image: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); border-image-slice: 1; font-size: 16px; font-weight: 600; padding: 12px 32px; box-shadow: 0 4px 12px rgba(0, 153, 204, 0.3); transition: all 0.2s ease;">
                            ✉️ Vérifier mon email
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- OTP Alternative -->
@if(isset($otpCode))
<div style="background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%); border: 2px solid #f59e0b; border-radius: 12px; padding: 24px; margin: 32px 0; text-align: center;">
    <h3 style="margin: 0 0 16px 0; color: #92400e; font-size: 18px; font-weight: bold;">
        🔢 Code de vérification
    </h3>
    <p style="margin: 0 0 16px 0; color: #92400e; font-size: 14px;">
        Alternativement, vous pouvez saisir ce code sur la page de vérification :
    </p>
    <div style="background: white; border: 2px solid #f59e0b; border-radius: 8px; padding: 16px; margin: 16px 0; font-family: monospace; font-size: 24px; font-weight: bold; color: #92400e; letter-spacing: 4px;">
        {{ $otpCode }}
    </div>
    <p style="margin: 0; color: #92400e; font-size: 12px;">
        Ce code expire dans 15 minutes
    </p>
</div>
@endif

<!-- Current Lotteries Preview -->
<div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; padding: 24px; margin: 32px 0;">
    <h3 style="margin: 0 0 16px 0; color: #065f46; font-size: 18px; font-weight: bold; text-align: center;">
        🎁 Tombolas en cours
    </h3>
    <p style="margin: 0 0 16px 0; color: #065f46; font-size: 14px; text-align: center;">
        Découvrez quelques-uns des produits incroyables que vous pouvez gagner dès maintenant :
    </p>
    <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 16px; margin-top: 16px;">
        <div style="background: white; border-radius: 8px; padding: 16px; text-align: center; min-width: 120px; border: 2px solid #10b981;">
            <div style="font-size: 24px; margin-bottom: 8px;">📱</div>
            <div style="font-size: 12px; color: #065f46; font-weight: bold;">iPhone 15 Pro</div>
            <div style="font-size: 10px; color: #065f46;">1,299,000 FCFA</div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 16px; text-align: center; min-width: 120px; border: 2px solid #10b981;">
            <div style="font-size: 24px; margin-bottom: 8px;">🚗</div>
            <div style="font-size: 12px; color: #065f46; font-weight: bold;">Toyota Corolla</div>
            <div style="font-size: 10px; color: #065f46;">15,000,000 FCFA</div>
        </div>
        <div style="background: white; border-radius: 8px; padding: 16px; text-align: center; min-width: 120px; border: 2px solid #10b981;">
            <div style="font-size: 24px; margin-bottom: 8px;">💻</div>
            <div style="font-size: 12px; color: #065f46; font-weight: bold;">MacBook Pro</div>
            <div style="font-size: 10px; color: #065f46;">2,500,000 FCFA</div>
        </div>
    </div>
</div>

<!-- Security Notice -->
<div style="background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%); border-left: 4px solid #dc2626; padding: 20px; border-radius: 8px; margin: 32px 0;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 20px; margin-right: 12px;">🔒</div>
        <div>
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #991b1b;">Sécurité de votre compte</p>
            <p style="margin: 0; font-size: 14px; color: #991b1b; line-height: 1.5;">
                Si vous n'avez pas créé de compte sur Koumbaya, vous pouvez ignorer cet email en toute sécurité. 
                Aucun compte ne sera activé sans cette vérification.
            </p>
        </div>
    </div>
</div>

<!-- How It Works -->
<div style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border-radius: 12px; padding: 24px; margin: 32px 0;">
    <h3 style="margin: 0 0 16px 0; color: #2d3748; font-size: 18px; font-weight: bold; text-align: center;">
        🚀 Comment ça marche ?
    </h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 16px;">
        <div style="text-align: center;">
            <div style="background: #0099cc; color: white; width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 8px;">1</div>
            <p style="margin: 0; font-size: 13px; color: #4a5568; line-height: 1.4;">
                <strong>Choisissez</strong><br>un produit qui vous plaît
            </p>
        </div>
        <div style="text-align: center;">
            <div style="background: #0099cc; color: white; width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 8px;">2</div>
            <p style="margin: 0; font-size: 13px; color: #4a5568; line-height: 1.4;">
                <strong>Achetez</strong><br>vos tickets de tombola
            </p>
        </div>
        <div style="text-align: center;">
            <div style="background: #0099cc; color: white; width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 8px;">3</div>
            <p style="margin: 0; font-size: 13px; color: #4a5568; line-height: 1.4;">
                <strong>Attendez</strong><br>le tirage au sort
            </p>
        </div>
        <div style="text-align: center;">
            <div style="background: #10b981; color: white; width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-bottom: 8px;">🏆</div>
            <p style="margin: 0; font-size: 13px; color: #4a5568; line-height: 1.4;">
                <strong>Gagnez !</strong><br>Et récupérez votre prix
            </p>
        </div>
    </div>
</div>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 32px 0 0 0; text-align: left; color: #4a5568;">
    Merci de nous faire confiance pour rendre vos rêves accessibles ! 🌟<br>
    <strong style="color: #0099cc;">L'équipe Koumbaya</strong> 🎯
</p>

<!-- Welcome Gift -->
<div style="background: linear-gradient(135deg, #f3e8ff 0%, #ddd6fe 100%); border: 2px solid #8b5cf6; border-radius: 12px; padding: 20px; margin: 32px 0; text-align: center;">
    <h3 style="margin: 0 0 12px 0; color: #6b21a8; font-size: 16px; font-weight: bold;">
        🎁 Cadeau de bienvenue
    </h3>
    <p style="margin: 0; color: #7c3aed; font-size: 14px; line-height: 1.5;">
        Une fois votre email vérifié, vous recevrez <strong>10% de réduction</strong> 
        sur votre première participation ! 
    </p>
</div>

<!-- Subcopy -->
<table class="subcopy" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; border-top: 1px solid #e2e8f0; margin-top: 32px; padding-top: 24px;">
    <tr>
        <td style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
            <p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px; color: #718096;">
                Si vous avez des difficultés avec le bouton "Vérifier mon email", copiez et collez l'URL ci-dessous dans votre navigateur web :
            </p>
            <p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; line-height: 1.5em; margin: 8px 0 0 0; text-align: left; font-size: 12px; color: #0099cc; word-break: break-all;">
                <a href="{{ $verificationUrl }}" style="color: #0099cc; text-decoration: none;">{{ $verificationUrl }}</a>
            </p>
        </td>
    </tr>
</table>
@endsection