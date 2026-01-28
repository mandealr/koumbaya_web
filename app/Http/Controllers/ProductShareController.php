<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Lottery;
use Illuminate\Http\Request;

class ProductShareController extends Controller
{
    /**
     * Affiche une page de partage avec les meta tags Open Graph dynamiques
     * pour les robots des réseaux sociaux, et redirige les humains vers la SPA.
     */
    public function show(Request $request, $id)
    {
        // Récupérer le produit avec ses relations
        $product = Product::with(['category', 'merchant', 'activeLottery'])->find($id);

        if (!$product) {
            return redirect('/');
        }

        // Détecter si c'est un bot de réseau social
        $userAgent = $request->header('User-Agent', '');
        $isBot = $this->isSocialMediaBot($userAgent);

        // Si c'est un humain, rediriger vers la SPA
        if (!$isBot) {
            return redirect("/customer/products/{$product->slug}");
        }

        return $this->renderProductShare($product);
    }

    /**
     * Affiche la page produit par slug avec détection des bots
     */
    public function showBySlug(Request $request, $slug)
    {
        // Récupérer le produit par son slug
        $product = Product::with(['category', 'merchant', 'activeLottery'])
            ->where('slug', $slug)
            ->first();

        if (!$product) {
            // Essayer de trouver par ID si le slug ressemble à un nombre
            if (is_numeric($slug)) {
                $product = Product::with(['category', 'merchant', 'activeLottery'])->find($slug);
            }
        }

        if (!$product) {
            return view('app');
        }

        // Détecter si c'est un bot de réseau social
        $userAgent = $request->header('User-Agent', '');
        $isBot = $this->isSocialMediaBot($userAgent);

        // Si c'est un humain, servir la SPA normale
        if (!$isBot) {
            return view('app');
        }

        return $this->renderProductShare($product);
    }

    /**
     * Affiche la page tombola avec détection des bots
     */
    public function showLottery(Request $request, $id)
    {
        // Récupérer la tombola avec son produit
        $lottery = Lottery::with(['product.category', 'product.merchant'])->find($id);

        if (!$lottery || !$lottery->product) {
            return view('app');
        }

        // Détecter si c'est un bot de réseau social
        $userAgent = $request->header('User-Agent', '');
        $isBot = $this->isSocialMediaBot($userAgent);

        // Si c'est un humain, servir la SPA normale
        if (!$isBot) {
            return view('app');
        }

        return $this->renderLotteryShare($lottery);
    }

    /**
     * Render le partage produit
     */
    private function renderProductShare(Product $product)
    {
        $imageUrl = $this->getProductImageUrl($product);
        $description = $this->buildDescription($product);

        return view('share.product', [
            'product' => $product,
            'imageUrl' => $imageUrl,
            'description' => $description,
            'canonicalUrl' => url("/customer/products/{$product->slug}"),
        ]);
    }

    /**
     * Render le partage tombola
     */
    private function renderLotteryShare(Lottery $lottery)
    {
        $product = $lottery->product;
        $imageUrl = $this->getProductImageUrl($product);

        // Description spécifique tombola
        $ticketPrice = number_format($lottery->ticket_price, 0, ',', ' ');
        $productPrice = number_format($product->price, 0, ',', ' ');
        $ticketsSold = $lottery->tickets()->where('status', 'paid')->count();
        $maxTickets = $lottery->max_tickets;

        $description = "Tentez de gagner ce produit d'une valeur de {$productPrice} FCFA pour seulement {$ticketPrice} FCFA ! ";
        $description .= "{$ticketsSold}/{$maxTickets} tickets vendus. ";
        $description .= strip_tags(substr($product->description ?? '', 0, 100));

        return view('share.product', [
            'product' => $product,
            'lottery' => $lottery,
            'imageUrl' => $imageUrl,
            'description' => $description,
            'canonicalUrl' => url("/lotteries/{$lottery->id}"),
            'title' => "Tombola : {$product->name}",
        ]);
    }

    /**
     * Vérifie si le User-Agent correspond à un bot de réseau social
     */
    private function isSocialMediaBot(string $userAgent): bool
    {
        $botPatterns = [
            'facebookexternalhit',
            'Facebot',
            'Twitterbot',
            'Pinterest',
            'LinkedInBot',
            'WhatsApp',
            'Slackbot',
            'TelegramBot',
            'Discordbot',
            'Googlebot',
            'bingbot',
            'Baiduspider',
            'YandexBot',
            'Applebot',
            'curl',
            'wget',
        ];

        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Récupère l'URL de l'image principale du produit
     */
    private function getProductImageUrl(Product $product): string
    {
        // Priorité : image_url > main_image > image > images[0] > logo par défaut
        if (!empty($product->image_url)) {
            return $this->ensureAbsoluteUrl($product->image_url);
        }

        if (!empty($product->main_image)) {
            return $this->ensureAbsoluteUrl($product->main_image);
        }

        if (!empty($product->image)) {
            return $this->ensureAbsoluteUrl($product->image);
        }

        // Si images est un tableau JSON
        $images = $product->images;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        if (is_array($images) && count($images) > 0) {
            return $this->ensureAbsoluteUrl($images[0]);
        }

        // Image par défaut
        return asset('logo.png');
    }

    /**
     * S'assure que l'URL est absolue
     */
    private function ensureAbsoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return url('/' . $url);
    }

    /**
     * Construit une description pour les meta tags
     */
    private function buildDescription(Product $product): string
    {
        $description = strip_tags($product->description ?? '');

        // Ajouter le prix
        $price = number_format($product->price, 0, ',', ' ');

        // Ajouter le prix du ticket si c'est une tombola
        if ($product->sale_mode === 'lottery' && $product->activeLottery) {
            $ticketPrice = number_format($product->activeLottery->ticket_price, 0, ',', ' ');
            $prefix = "Tentez de gagner pour seulement {$ticketPrice} FCFA ! Valeur : {$price} FCFA. ";
        } else {
            $prefix = "Prix : {$price} FCFA. ";
        }

        // Limiter la longueur totale à 200 caractères
        $maxDescLength = 200 - strlen($prefix);
        if (strlen($description) > $maxDescLength) {
            $description = substr($description, 0, $maxDescLength - 3) . '...';
        }

        return $prefix . $description;
    }
}
