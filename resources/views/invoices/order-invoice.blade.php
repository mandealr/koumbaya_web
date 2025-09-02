<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture {{ $invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 20px;
        }
        
        .company-info {
            display: table-cell;
            vertical-align: top;
            width: 50%;
        }
        
        .invoice-info {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            text-align: right;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        
        .client-section {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
        }
        
        .order-details {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .order-left, .order-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .order-right {
            text-align: right;
        }
        
        .line-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .line-items th {
            background-color: #34495e;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        .line-items td {
            padding: 10px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .line-items tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-section {
            width: 300px;
            margin-left: auto;
            background-color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
        }
        
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .total-label {
            display: table-cell;
            font-weight: bold;
        }
        
        .total-amount {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }
        
        .final-total {
            font-size: 16px;
            color: #27ae60;
            border-top: 2px solid #27ae60;
            padding-top: 8px;
        }
        
        .status-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #d5f4e6;
            border: 1px solid #27ae60;
            border-radius: 5px;
            text-align: center;
        }
        
        .status-paid {
            color: #27ae60;
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            color: #7f8c8d;
            font-size: 10px;
        }
        
        .ticket-details {
            font-size: 10px;
            color: #7f8c8d;
            margin-top: 5px;
        }
        
        .generated-at {
            margin-top: 30px;
            text-align: right;
            color: #7f8c8d;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ $company['name'] }}</div>
                <div>{{ $company['address'] }}</div>
                <div>Tél: {{ $company['phone'] }}</div>
                <div>Email: {{ $company['email'] }}</div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">FACTURE</div>
                <div><strong>N°:</strong> {{ $invoice_number }}</div>
                <div><strong>Date:</strong> {{ $invoice_date->format('d/m/Y') }}</div>
                <div><strong>Commande:</strong> {{ $order->order_number }}</div>
            </div>
        </div>

        <!-- Client Information -->
        <div class="client-section">
            <div class="section-title">INFORMATIONS CLIENT</div>
            <div><strong>Nom:</strong> {{ $user->name }}</div>
            <div><strong>Email:</strong> {{ $user->email }}</div>
            @if($user->phone)
            <div><strong>Téléphone:</strong> {{ $user->phone }}</div>
            @endif
        </div>

        <!-- Order Details -->
        <div class="order-details">
            <div class="order-left">
                <div><strong>Type de commande:</strong> {{ ucfirst($order->type) }}</div>
                @if($order->lottery)
                <div><strong>Tombola:</strong> {{ $order->lottery->title }}</div>
                @endif
                @if($order->product)
                <div><strong>Produit:</strong> {{ $order->product->name }}</div>
                @endif
            </div>
            <div class="order-right">
                <div><strong>Date de commande:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</div>
                <div><strong>Date de paiement:</strong> {{ $order->paid_at?->format('d/m/Y H:i') ?? 'N/A' }}</div>
            </div>
        </div>

        <!-- Line Items -->
        <table class="line-items">
            <thead>
                <tr>
                    <th style="width: 50%;">Description</th>
                    <th class="text-center" style="width: 15%;">Quantité</th>
                    <th class="text-right" style="width: 17.5%;">Prix unitaire</th>
                    <th class="text-right" style="width: 17.5%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($line_items as $item)
                <tr>
                    <td>
                        {{ $item['description'] }}
                        @if(count($item['details']) > 0)
                        <div class="ticket-details">
                            Billets: {{ implode(', ', array_slice($item['details'], 0, 5)) }}
                            @if(count($item['details']) > 5)
                                <br>... et {{ count($item['details']) - 5 }} autres
                            @endif
                        </div>
                        @endif
                    </td>
                    <td class="text-center">{{ $item['quantity'] }}</td>
                    <td class="text-right">{{ number_format($item['unit_price'], 0, ',', ' ') }} FCFA</td>
                    <td class="text-right">{{ number_format($item['total'], 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row">
                <div class="total-label">Sous-total:</div>
                <div class="total-amount">{{ number_format($subtotal, 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="total-row final-total">
                <div class="total-label">Total:</div>
                <div class="total-amount">{{ number_format($total, 0, ',', ' ') }} FCFA</div>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="status-section">
            <div class="status-paid">✓ FACTURE PAYÉE</div>
            <div>Statut de la commande: {{ $order->status_enum->label() }}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Merci de votre confiance !</div>
            <div>Cette facture a été générée électroniquement et n'a pas besoin de signature.</div>
        </div>

        <!-- Generation timestamp -->
        <div class="generated-at">
            Généré le {{ $generated_at->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>