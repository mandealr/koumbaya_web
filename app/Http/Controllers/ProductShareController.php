<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
            // Produit non trouvé, rediriger vers la page d'accueil
            return redirect('/');
        }

        // Détecter si c'est un bot de réseau social
        $userAgent = $request->header('User-Agent', '');
        $isBot = $this->isSocialMediaBot($userAgent);

        // Si c'est un humain, rediriger vers la SPA
        if (!$isBot) {
            return redirect("/products/{$id}");
        }

        // Construire l'URL de l'image
        $imageUrl = $this->getProductImageUrl($product);

        // Construire la description
        $description = $this->buildDescription($product);

        // Retourner la vue avec les meta tags dynamiques
        return view('share.product', [
            'product' => $product,
            'imageUrl' => $imageUrl,
            'description' => $description,
            'canonicalUrl' => url("/products/{$id}"),
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
