@extends('emails.layouts.base')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 20px; border-radius: 10px; display: inline-block;">
            <h1 style="margin: 0; font-size: 24px;">
                <span style="display: inline-block; width: 32px; height: 32px; background: rgba(255,255,255,0.2); border-radius: 50%; text-align: center; line-height: 32px; margin-right: 8px; vertical-align: middle;">$</span>
                Nouveau Paiement Reçu
            </h1>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">Notification administrateur</p>
        </div>
    </div>

    <p style="font-size: 16px; color: #374151; line-height: 1.6;">
        Bonjour <strong>Administrateur</strong>,
    </p>

    <p style="font-size: 16px; color: #374151; line-height: 1.6;">
        Un nouveau paiement a été effectué sur la plateforme Koumbaya.
    </p>

    <!-- Informations du paiement -->
    <div class="info-box" style="background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; margin: 25px 0; border-radius: 8px;">
        <h3 style="margin-top: 0; color: #059669; font-size: 18px;">
            <span style="display: inline-block; width: 24px; height: 24px; background: #10b981; color: white; border-radius: 4px; text-align: center; line-height: 24px; margin-right: 8px; font-size: 14px; vertical-align: middle;">&#9776;</span>
            Détails du paiement
        </h3>

        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #d1fae5; color: #6b7280; font-size: 14px;">
                    <strong>Référence :</strong>
                </td>
                <td style="padding: 10px 0; border-bottom: 1px solid #d1fae5; color: #111827; font-size: 14px; text-align: right;">
                    <code style="background: #e5e7eb; padding: 4px 8px; border-radius: 4px; font-family: monospace;">{{ $payment->reference }}</code>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #d1fae5; color: #6b7280; font-size: 14px;">
                    <strong>Montant :</strong>
                </td>
                <td style="padding: 10px 0; border-bottom: 1px solid #d1fae5; color: #111827; font-size: 16px; font-weight: bold; text-align: right;">
                    {{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #d1fae5; color: #6b7280; font-size: 14px;">
                    <strong>Méthode :</strong>
                </td>
                <td style="padding: 10px 0; border-bottom: 1px solid #d1fae5; color: #111827; font-size: 14px; text-align: right;">
                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; border-bottom: 1px solid #d1fae5; color: #6b7280; font-size: 14px;">
                    <strong>Statut :</strong>
                </td>
                <td style="padding: 10px 0; border-bottom: 1px solid #d1fae5; text-align: right;">
                    <span style="background: #10b981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase;">
                        {{ $payment->status }}
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 0; color: #6b7280; font-size: 14px;">
                    <strong>Date :</strong>
                </td>
                <td style="padding: 10px 0; color: #111827; font-size: 14px; text-align: right;">
                    {{ $payment->paid_at ? $payment->paid_at->format('d/m/Y à H:i') : 'En attente' }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Informations du client -->
    @if($payment->user)
    <div class="info-box" style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; margin: 25px 0; border-radius: 8px;">
        <h3 style="margin-top: 0; color: #1d4ed8; font-size: 18px;">
            <span style="display: inline-block; width: 24px; height: 24px; background: #3b82f6; color: white; border-radius: 50%; text-align: center; line-height: 24px; margin-right: 8px; font-size: 14px; vertical-align: middle;">&#9679;</span>
            Client
        </h3>

        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Nom complet :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    {{ $payment->user->full_name ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Email :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    <a href="mailto:{{ $payment->user->email }}" style="color: #3b82f6; text-decoration: none;">
                        {{ $payment->user->email }}
                    </a>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Téléphone :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    {{ $payment->user->phone ?? 'N/A' }}
                </td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Informations de la commande -->
    @if($payment->order)
    <div class="info-box" style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; margin: 25px 0; border-radius: 8px;">
        <h3 style="margin-top: 0; color: #d97706; font-size: 18px;">
            <span style="display: inline-block; width: 24px; height: 24px; background: #f59e0b; color: white; border-radius: 4px; text-align: center; line-height: 24px; margin-right: 8px; font-size: 14px; vertical-align: middle;">&#9634;</span>
            Commande
        </h3>

        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Numéro :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    <code style="background: #fef3c7; padding: 4px 8px; border-radius: 4px; font-family: monospace;">{{ $payment->order->order_number }}</code>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Type :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    @if($payment->order->type === 'lottery')
                        <span style="background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">TOMBOLA</span>
                    @else
                        <span style="background: #e0e7ff; color: #4f46e5; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">ACHAT DIRECT</span>
                    @endif
                </td>
            </tr>
            @if($payment->order->product)
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Produit :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    {{ $payment->order->product->name }}
                </td>
            </tr>
            @endif
            @if($payment->order->lottery)
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Tombola :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    {{ $payment->order->lottery->lottery_number }}
                </td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <!-- Informations du marchand -->
    @php
        $merchant = null;
        if($payment->order && $payment->order->product) {
            $merchant = $payment->order->product->merchant ?? $payment->order->product->user;
        }
    @endphp

    @if($merchant)
    <div class="info-box" style="background-color: #f3e8ff; border-left: 4px solid #9333ea; padding: 20px; margin: 25px 0; border-radius: 8px;">
        <h3 style="margin-top: 0; color: #7e22ce; font-size: 18px;">
            <span style="display: inline-block; width: 24px; height: 24px; background: #9333ea; color: white; border-radius: 4px; text-align: center; line-height: 24px; margin-right: 8px; font-size: 14px; vertical-align: middle;">&#9733;</span>
            Marchand
        </h3>

        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Nom :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    {{ $merchant->full_name ?? $merchant->name ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #6b7280; font-size: 14px;">
                    <strong>Email :</strong>
                </td>
                <td style="padding: 8px 0; color: #111827; font-size: 14px; text-align: right;">
                    <a href="mailto:{{ $merchant->email }}" style="color: #9333ea; text-decoration: none;">
                        {{ $merchant->email }}
                    </a>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Actions admin -->
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/admin/payments/{{ $payment->id }}"
           class="koumbaya-button"
           style="background-color: #0099cc; border: 8px solid #0099cc; border-radius: 8px; color: #ffffff; text-decoration: none; display: inline-block; padding: 12px 24px; font-weight: 600; font-size: 16px;">
            <span style="display: inline-block; margin-right: 6px;">&#8594;</span> Voir dans le Dashboard Admin
        </a>
    </div>

    <!-- Informations supplémentaires -->
    <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin: 25px 0;">
        <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.6;">
            <strong>
                <span style="display: inline-block; width: 18px; height: 18px; background: #6b7280; color: white; border-radius: 50%; text-align: center; line-height: 18px; margin-right: 4px; font-size: 11px; vertical-align: middle;">i</span>
                Informations :
            </strong><br>
            • Les notifications ont été envoyées au client et au marchand<br>
            • Transaction ID E-Billing : <code style="background: #e5e7eb; padding: 2px 6px; border-radius: 3px;">{{ $payment->ebilling_id ?? 'N/A' }}</code><br>
            • Cette notification est envoyée automatiquement pour tous les paiements réussis
        </p>
    </div>

    <p style="font-size: 14px; color: #6b7280; line-height: 1.6; margin-top: 30px;">
        Cordialement,<br>
        <strong style="color: #0099cc;">Le système Koumbaya</strong>
    </p>
@endsection
