<?php

namespace App\Services;

use App\Models\Order;
use App\Enums\OrderStatus;
use App\Services\MetricsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class InvoiceService
{
    protected MetricsService $metricsService;

    public function __construct(MetricsService $metricsService)
    {
        $this->metricsService = $metricsService;
    }
    /**
     * Generate PDF invoice for an order
     * 
     * @param Order $order
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function make(Order $order): Response
    {
        $startTime = microtime(true);

        // Verify order is paid
        if (!in_array($order->status, [OrderStatus::PAID->value, OrderStatus::FULFILLED->value])) {
            throw new \Exception('Invoice can only be generated for paid orders');
        }

        // Load necessary relationships
        $order->load([
            'user',
            'product', 
            'lottery.product',
            'lottery.lotteryTickets' => function($query) use ($order) {
                $query->where('user_id', $order->user_id);
            },
            'payments' => function($query) {
                $query->where('status', 'paid')->orderBy('paid_at', 'desc');
            }
        ]);

        // Prepare invoice data
        $invoiceData = $this->prepareInvoiceData($order);

        // Generate PDF
        $pdf = Pdf::loadView('invoices.order-invoice', $invoiceData)
            ->setPaper('a4')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => false,
            ]);

        $fileName = "facture-{$order->order_number}.pdf";
        
        // Calculate generation time
        $endTime = microtime(true);
        $generationTimeMs = ($endTime - $startTime) * 1000;

        // Track invoice generation metrics
        $this->metricsService->invoiceGenerated($order, $generationTimeMs);
        
        return $pdf->download($fileName);
    }

    /**
     * Prepare data for invoice template
     * 
     * @param Order $order
     * @return array
     */
    private function prepareInvoiceData(Order $order): array
    {
        $lineItems = $this->generateLineItems($order);
        
        return [
            'order' => $order,
            'user' => $order->user,
            'company' => [
                'name' => config('app.name', 'Koumbaya'),
                'address' => 'Abidjan, Côte d\'Ivoire',
                'phone' => '+225 XX XX XX XX',
                'email' => 'contact@koumbaya.com',
            ],
            'invoice_number' => 'INV-' . $order->order_number,
            'invoice_date' => $order->paid_at ?? $order->created_at,
            'line_items' => $lineItems,
            'subtotal' => $order->total_amount,
            'total' => $order->total_amount,
            'currency' => $order->currency,
            'generated_at' => now(),
        ];
    }

    /**
     * Generate line items for the invoice
     * 
     * @param Order $order
     * @return array
     */
    private function generateLineItems(Order $order): array
    {
        $lineItems = [];

        if ($order->type === Order::TYPE_LOTTERY && $order->lottery) {
            // Lottery tickets
            $ticketCount = $order->lottery->tickets ? $order->lottery->tickets->count() : 0;
            $unitPrice = $order->lottery->ticket_price ?? ($ticketCount > 0 ? $order->total_amount / $ticketCount : 0);

            if ($ticketCount > 0) {
                $lineItems[] = [
                    'description' => "Billets de loterie - {$order->lottery->title}",
                    'quantity' => $ticketCount,
                    'unit_price' => $unitPrice,
                    'total' => $order->total_amount,
                    'details' => $order->lottery->lotteryTickets->pluck('ticket_number')->toArray()
                ];
            } else {
                // Fallback if no tickets loaded
                $lineItems[] = [
                    'description' => "Achat loterie - {$order->lottery->title}",
                    'quantity' => 1,
                    'unit_price' => $order->total_amount,
                    'total' => $order->total_amount,
                    'details' => []
                ];
            }
        } elseif ($order->type === Order::TYPE_DIRECT && $order->product) {
            // Direct product purchase
            $quantity = 1; // Assuming 1 product for direct purchases
            
            $lineItems[] = [
                'description' => $order->product->name,
                'quantity' => $quantity,
                'unit_price' => $order->total_amount,
                'total' => $order->total_amount,
                'details' => []
            ];
        } else {
            // Fallback for unknown order types
            $lineItems[] = [
                'description' => 'Commande - ' . ucfirst($order->type),
                'quantity' => 1,
                'unit_price' => $order->total_amount,
                'total' => $order->total_amount,
                'details' => []
            ];
        }

        return $lineItems;
    }

    /**
     * Get formatted currency amount
     * 
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public function formatCurrency(float $amount, string $currency = 'XAF'): string
    {
        return number_format($amount, 0, ',', ' ') . ' FCFA';
    }
}