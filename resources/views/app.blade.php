<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="color-scheme: light !important;">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#0099cc">

    <title>{{ config('app.name', 'Koumbaya Marketplace') }}</title>
    <meta name="description" content="Koumbaya - La marketplace de tombolas et d'achats directs au Gabon. Gagnez des produits de qualité à prix réduit !">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ config('app.name', 'Koumbaya Marketplace') }}">
    <meta property="og:description" content="Koumbaya - La marketplace de tombolas et d'achats directs au Gabon. Gagnez des produits de qualité à prix réduit !">
    <meta property="og:image" content="{{ asset('logo.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Koumbaya">
    <meta property="og:locale" content="fr_FR">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="{{ config('app.name', 'Koumbaya Marketplace') }}">
    <meta property="twitter:description" content="Koumbaya - La marketplace de tombolas et d'achats directs au Gabon. Gagnez des produits de qualité à prix réduit !">
    <meta property="twitter:image" content="{{ asset('logo.png') }}">

    <!-- Additional SEO -->
    <meta name="robots" content="index, follow">
    <meta name="author" content="Koumbaya">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/icon.png">
    <link rel="shortcut icon" type="image/png" href="/icon.png">
    <link rel="apple-touch-icon" href="/icon.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased" style="color-scheme: light !important;">
    <div id="app"></div>
</body>
</html>