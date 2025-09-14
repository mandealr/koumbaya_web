@extends('emails.layouts.base')

@section('content')
<!-- Welcome Icon -->
<div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; padding: 20px; background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); border-radius: 50%; color: white; font-size: 32px; box-shadow: 0 4px 12px rgba(0, 153, 204, 0.3);">
        @
    </div>
</div>

<!-- Greeting -->
<h1 style="color: #2d3748; font-size: 24px; font-weight: bold; margin: 0 0 16px 0; text-align: center;">
    Bienvenue sur Koumbaya Marketplace !
</h1>

<p style="font-size: 16px; line-height: 1.6; margin: 0 0 24px 0; text-align: center; color: #4a5568;">
    Bonjour {{ $user->first_name ?? 'cher utilisateur' }} !
</p>

<p style="font-size: 16px; line-height: 1.6; margin: 0 0 32px 0; text-align: left; color: #4a5568;">
    Nous sommes ravis de vous accueillir sur <strong>Koumbaya Marketplace</strong>, la plateforme qui rend l'impossible accessible ! Pour activer votre compte et commencer à participer à nos tombolas exclusives, veuillez vérifier votre adresse email.
</p>

<!-- Verification Button -->
<div style="text-align: center; margin: 40px 0;">
    <a href="{{ $verificationUrl }}" style="display: inline-block; background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); color: white; text-decoration: none; padding: 16px 32px; border-radius: 8px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 12px rgba(0, 153, 204, 0.3);">
        Vérifier mon email
    </a>
</div>

<!-- Alternative Link -->
<div style="background: #f8fafc; border-radius: 8px; padding: 20px; margin: 24px 0; border-left: 4px solid #0099cc;">
    <p style="margin: 0 0 12px 0; color: #4a5568; font-size: 14px; font-weight: bold;">
        Le bouton ne fonctionne pas ?
    </p>
    <p style="margin: 0; font-size: 12px; color: #718096; word-break: break-all;">
        Copiez et collez ce lien dans votre navigateur :<br>
        <a href="{{ $verificationUrl }}" style="color: #0099cc;">{{ $verificationUrl }}</a>
    </p>
</div>

<!-- Security Notice -->
<div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 16px; margin: 24px 0;">
    <p style="margin: 0; font-size: 14px; color: #856404;">
        <strong>Important :</strong> Si vous n'avez pas créé de compte sur Koumbaya Marketplace, vous pouvez ignorer cet email en toute sécurité.
    </p>
</div>

<!-- Footer Message -->
<p style="text-align: center; margin: 32px 0 0 0; color: #718096; font-size: 14px;">
    Merci de rejoindre notre communauté !<br>
    <strong>L'équipe Koumbaya Marketplace</strong>
</p>
@endsection