<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AvatarController extends Controller
{
    /**
     * Serve avatar image
     */
    public function show($filename)
    {
        try {
            $path = 'avatars/' . $filename;
            
            // Vérifier si le fichier existe
            if (!Storage::disk('public')->exists($path)) {
                return $this->serveDefaultAvatar();
            }
            
            // Obtenir le contenu du fichier
            $file = Storage::disk('public')->get($path);
            $mimeType = Storage::disk('public')->mimeType($path);
            
            // Retourner l'image avec les headers appropriés
            return response($file, 200)
                ->header('Content-Type', $mimeType)
                ->header('Cache-Control', 'public, max-age=2592000'); // Cache 30 jours
                
        } catch (\Exception $e) {
            // Log silencieux si pas dans le contexte Laravel
            if (class_exists('\Illuminate\Support\Facades\Log')) {
                \Illuminate\Support\Facades\Log::error('Error serving avatar', [
                    'filename' => $filename,
                    'error' => $e->getMessage()
                ]);
            }
            
            return $this->serveDefaultAvatar();
        }
    }
    
    /**
     * Serve default avatar placeholder
     */
    protected function serveDefaultAvatar()
    {
        // Créer un SVG placeholder
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
        <svg width="128" height="128" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
            <rect width="128" height="128" fill="#e5e7eb"/>
            <circle cx="64" cy="48" r="24" fill="#9ca3af"/>
            <path d="M 32 96 Q 32 72, 64 72 Q 96 72, 96 96" fill="#9ca3af"/>
        </svg>';
        
        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400'); // Cache 1 jour
    }
}