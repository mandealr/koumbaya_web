@extends('emails.layouts.base')

@section('content')
    <h2 style="color: #1f2937; margin-top: 0;">Nouveau paiement reçu</h2>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6;">
        Bonjour,
    </p>

    <p style="color: #4b5563; font-size: 16px; line-height: 1.6;">
        Vous avez reçu un nouveau paiement sur votre boutique Koumbaya. Après déduction des frais, vous recevrez <strong style="color: #10b981; font-size: 18px;">{{ number_format($payment->merchant_net_amount, 0, ',', ' ') }} {{ $payment->currency }}</strong>.
    </p>

    <div class="info-box">
        <h3 style="color: #1f2937; margin-top: 0; font-size: 18px;">Détails du paiement</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Référence :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $payment->reference }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Client :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $payment->customer_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Téléphone :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $payment->customer_phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Montant payé :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 12px 0 8px 0;">
                    <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin: 8px 0;">
                        <p style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Détail des frais :</p>
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <tr>
                                <td style="padding: 4px 0; color: #9ca3af;">Frais E-Billing ({{ $payment->ebilling_fee_percentage }}%)</td>
                                <td style="padding: 4px 0; color: #ef4444; text-align: right;">- {{ number_format($payment->ebilling_fee_amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 4px 0; color: #9ca3af;">Commission Koumbaya ({{ $payment->platform_fee_percentage }}%)</td>
                                <td style="padding: 4px 0; color: #ef4444; text-align: right;">- {{ number_format($payment->platform_fee_amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                            </tr>
                            <tr style="border-top: 2px solid #10b981;">
                                <td style="padding: 8px 0 0 0; color: #1f2937; font-weight: 700; font-size: 15px;">Montant net à recevoir</td>
                                <td style="padding: 8px 0 0 0; color: #10b981; font-weight: 700; text-align: right; font-size: 16px;">{{ number_format($payment->merchant_net_amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Méthode :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ ucfirst($payment->payment_method ?? 'Mobile Money') }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Date :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y à H:i') : 'Maintenant' }}</td>
            </tr>
        </table>
    </div>

    @if($payment->order)
        <h3 style="color: #1f2937; font-size: 18px;">Détails de la commande</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Numéro :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $payment->order->order_number }}</td>
            </tr>
            @if($payment->order->product)
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Produit :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $payment->order->product->name }}</td>
            </tr>
            @endif
            @if($payment->order->lottery)
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-weight: 600;">Tombola :</td>
                <td style="padding: 8px 0; color: #1f2937;">{{ $payment->order->lottery->title }}</td>
            </tr>
            @endif
        </table>
    @endif

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.frontend_url') }}/merchant/dashboard" class="koumbaya-button">
            Voir dans le tableau de bord
        </a>
    </div>

    <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
        L'équipe Koumbaya<br>
        <a href="{{ config('app.frontend_url') }}" style="color: #0099cc; text-decoration: none;">{{ config('app.frontend_url') }}</a>
    </p>
@endsection
