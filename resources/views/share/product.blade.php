<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">

    <title>{{ $title ?? $product->name }} - Koumbaya Marketplace</title>
    <meta name="description" content="{{ $description }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $title ?? $product->name }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $imageUrl }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $product->name }}">
    <meta property="og:site_name" content="Koumbaya Marketplace">
    <meta property="og:locale" content="fr_FR">

    <!-- Product specific Open Graph -->
    <meta property="product:price:amount" content="{{ $product->price }}">
    <meta property="product:price:currency" content="XAF">
    @if($product->category)
    <meta property="product:category" content="{{ $product->category->name }}">
    @endif

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $canonicalUrl }}">
    <meta name="twitter:title" content="{{ $product->name }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $imageUrl }}">
    <meta name="twitter:image:alt" content="{{ $product->name }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/icon.png">

    <!-- Redirection automatique pour les utilisateurs humains qui arrivent ici -->
    <meta http-equiv="refresh" content="0;url={{ $canonicalUrl }}">

    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #0099cc 0%, #006699 100%);
            color: white;
            text-align: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        p {
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        a {
            color: white;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>{{ $product->name }}</h1>
        <p>Redirection vers Koumbaya Marketplace...</p>
        <p><a href="{{ $canonicalUrl }}">Cliquez ici si vous n'êtes pas redirigé automatiquement</a></p>
    </div>

    <script>
        // Redirection JavaScript en backup
        window.location.href = "{{ $canonicalUrl }}";
    </script>
</body>
</html>
