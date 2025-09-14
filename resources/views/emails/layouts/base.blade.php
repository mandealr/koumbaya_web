<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{{ $subject ?? 'Koumbaya' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }
            .footer {
                width: 100% !important;
            }
        }
        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
        .koumbaya-gradient {
            background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%);
        }
        .koumbaya-primary {
            color: #0099cc;
        }
        .koumbaya-button {
            background-color: #0099cc;
            border: 8px solid #0099cc;
            border-radius: 8px;
        }
        .koumbaya-button:hover {
            background-color: #0088bb;
            border-color: #0088bb;
        }
    </style>
</head>
<body style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -webkit-text-size-adjust: none; background-color: #f8fafc; color: #718096; height: 100%; line-height: 1.4; margin: 0; padding: 0; width: 100% !important;">

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%; background-color: #f8fafc; margin: 0; padding: 0; width: 100%;">
    <tr>
        <td align="center" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
            <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%; margin: 0; padding: 0; width: 100%;">
                
                <!-- Header -->
                <tr>
                    <td class="header koumbaya-gradient" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; padding: 25px 0; text-align: center; background: linear-gradient(135deg, #0099cc 0%, #0088bb 100%);">
                        <a href="{{ config('app.url') }}" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; color: #ffffff; font-size: 24px; font-weight: bold; text-decoration: none; display: inline-block; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            KOUMBAYA MARKETPLACE
                        </a>
                        <p style="margin: 8px 0 0 0; color: rgba(255,255,255,0.9); font-size: 14px;">Rendre l'impossible accessible</p>
                    </td>
                </tr>

                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%; background-color: #f8fafc; border-bottom: 1px solid #f8fafc; border-top: 1px solid #f8fafc; margin: 0; padding: 0; width: 100%; border: hidden !important;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); margin: 32px auto; padding: 0; width: 570px; overflow: hidden;">
                            
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; max-width: 100vw; padding: 40px;">
                                    @yield('content')
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative;">
                        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 570px; margin: 0 auto; padding: 0; text-align: center; width: 570px;">
                            <tr>
                                <td class="content-cell" align="center" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; max-width: 100vw; padding: 32px;">
                                    
                                    <!-- Social Links -->
                                    <div style="margin-bottom: 20px;">
                                        <a href="#" style="display: inline-block; margin: 0 8px; padding: 8px; background-color: #0099cc; color: white; text-decoration: none; border-radius: 50%; width: 40px; height: 40px; text-align: center; line-height: 24px;">FB</a>
                                        <a href="#" style="display: inline-block; margin: 0 8px; padding: 8px; background-color: #0099cc; color: white; text-decoration: none; border-radius: 50%; width: 40px; height: 40px; text-align: center; line-height: 24px;">IG</a>
                                        <a href="#" style="display: inline-block; margin: 0 8px; padding: 8px; background-color: #0099cc; color: white; text-decoration: none; border-radius: 50%; width: 40px; height: 40px; text-align: center; line-height: 24px;">@</a>
                                    </div>

                                    <!-- Contact Info -->
                                    <p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; line-height: 1.5em; margin: 0 0 16px 0; color: #718096; font-size: 14px; text-align: center;">
                                        <strong>Koumbaya Marketplace</strong><br>
                                        Libreville, Gabon<br>
                                        +241 01 XX XX XX<br>
                                        support@koumbaya.com
                                    </p>

                                    <!-- Copyright -->
                                    <p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; line-height: 1.5em; margin-top: 0; color: #a0aec0; font-size: 12px; text-align: center;">
                                        © {{ date('Y') }} Koumbaya. Tous droits réservés.<br>
                                        Plateforme de tombolas et ventes au Gabon
                                    </p>

                                    <!-- Unsubscribe -->
                                    @isset($unsubscribeUrl)
                                    <p style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'; position: relative; line-height: 1.5em; margin-top: 16px; color: #a0aec0; font-size: 11px; text-align: center;">
                                        <a href="{{ $unsubscribeUrl }}" style="color: #0099cc; text-decoration: none;">Se désabonner</a> | 
                                        <a href="{{ config('app.url') }}/privacy" style="color: #0099cc; text-decoration: none;">Confidentialité</a> | 
                                        <a href="{{ config('app.url') }}/terms" style="color: #0099cc; text-decoration: none;">CGU</a>
                                    </p>
                                    @endisset
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>