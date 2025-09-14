@extends('emails.layouts.base')

@section('content')
<!-- Greeting Icon -->
<div style="text-align: center; margin-bottom: 24px;">
    <div style="display: inline-block; padding: 20px; background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); border-radius: 50%; color: white; font-size: 32px; box-shadow: 0 4px 12px rgba(0, 153, 204, 0.3);">
        
    </div>
</div>

<!-- Greeting -->
<h1 style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; color: #2d3748; font-size: 24px; font-weight: bold; margin: 0 0 16px 0; text-align: center;">
    Réinitialisation de mot de passe
</h1>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 0 0 24px 0; text-align: center; color: #4a5568;">
    Salut {{ $user->first_name ?? 'cher utilisateur' }} ! 
</p>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 0 0 32px 0; text-align: left; color: #4a5568;">
    Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte Koumbaya.
</p>

<!-- Reset Button -->
<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%; margin: 32px auto; padding: 0; text-align: center; width: 100%;">
    <tr>
        <td align="center" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
                <tr>
                    <td align="center" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
                        <a href="{{ $resetUrl }}" 
                           class="button button-primary" 
                           target="_blank" 
                           rel="noopener" 
                           style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -webkit-text-size-adjust: none; border-radius: 8px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); border: 12px solid transparent; border-image: linear-gradient(135deg, #0099cc 0%, #0088bb 100%); border-image-slice: 1; font-size: 16px; font-weight: 600; padding: 12px 32px; box-shadow: 0 4px 12px rgba(0, 153, 204, 0.3); transition: all 0.2s ease;">
                             Réinitialiser mon mot de passe
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Security Info Box -->
<div style="background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%); border-left: 4px solid #f59e0b; padding: 20px; border-radius: 8px; margin: 32px 0;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 20px; margin-right: 12px;"></div>
        <div>
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #92400e;">Important !</p>
            <p style="margin: 0; font-size: 14px; color: #92400e; line-height: 1.5;">
                Ce lien de réinitialisation expirera dans <strong>60 minutes</strong> pour votre sécurité.
            </p>
        </div>
    </div>
</div>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 24px 0; text-align: left; color: #4a5568;">
    Si vous n'avez pas demandé cette réinitialisation, aucune action n'est requise. Votre compte reste sécurisé.
</p>

<!-- Tips Box -->
<div style="background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%); border-left: 4px solid #0099cc; padding: 20px; border-radius: 8px; margin: 32px 0;">
    <div style="display: flex; align-items: flex-start;">
        <div style="font-size: 20px; margin-right: 12px;"></div>
        <div>
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #0c4a6e;">Conseils de sécurité</p>
            <ul style="margin: 0; padding-left: 16px; font-size: 14px; color: #0c4a6e; line-height: 1.5;">
                <li>Utilisez un mot de passe unique et complexe</li>
                <li>Mélangez lettres, chiffres et symboles</li>
                <li>Évitez les informations personnelles</li>
            </ul>
        </div>
    </div>
</div>

<p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; font-size: 16px; line-height: 1.6; margin: 32px 0 0 0; text-align: left; color: #4a5568;">
    Cordialement,<br>
    <strong style="color: #0099cc;">L'équipe Koumbaya</strong> 
</p>

<!-- Subcopy -->
<table class="subcopy" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; border-top: 1px solid #e2e8f0; margin-top: 32px; padding-top: 24px;">
    <tr>
        <td style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
            <p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; line-height: 1.5em; margin-top: 0; text-align: left; font-size: 14px; color: #718096;">
                Si vous avez des difficultés avec le bouton "Réinitialiser mon mot de passe", copiez et collez l'URL ci-dessous dans votre navigateur web :
            </p>
            <p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; line-height: 1.5em; margin: 8px 0 0 0; text-align: left; font-size: 12px; color: #0099cc; word-break: break-all;">
                <a href="{{ $resetUrl }}" style="color: #0099cc; text-decoration: none;">{{ $resetUrl }}</a>
            </p>
        </td>
    </tr>
</table>
@endsection