<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductImageController extends Controller
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Upload une image de produit
     */
    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,webp',
                    'max:5120' // 5MB max
                ],
                'index' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadedFile = $request->file('image');
            $index = $request->get('index', 0);

            // Générer un nom unique pour le fichier
            $filename = $this->generateUniqueFilename($uploadedFile->getClientOriginalExtension());
            
            // Créer le dossier s'il n'existe pas
            $uploadPath = 'products/' . date('Y/m');
            
            // Traiter et optimiser l'image
            $processedImage = $this->processImage($uploadedFile);
            
            // Sauvegarder l'image dans le storage
            $path = Storage::disk('public')->put($uploadPath . '/' . $filename, $processedImage);
            
            if (!$path) {
                throw new \Exception('Erreur lors de la sauvegarde du fichier');
            }

            // Construire l'URL publique
            $url = Storage::disk('public')->url($path);
            
            // Informations sur le fichier
            $fileInfo = [
                'filename' => $filename,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'path' => $path,
                'url' => $url,
                'size' => Storage::disk('public')->size($path),
                'mime_type' => $uploadedFile->getMimeType(),
                'index' => $index,
                'uploaded_at' => now()->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Image uploadée avec succès',
                'data' => $fileInfo
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur upload image produit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload multiple images en une fois
     */
    public function uploadMultiple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'images' => 'required|array|max:10',
                'images.*' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,webp',
                    'max:5120' // 5MB max par image
                ]
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadedImages = [];
            $errors = [];

            foreach ($request->file('images') as $index => $uploadedFile) {
                try {
                    // Générer un nom unique pour le fichier
                    $filename = $this->generateUniqueFilename($uploadedFile->getClientOriginalExtension());
                    
                    // Créer le dossier s'il n'existe pas
                    $uploadPath = 'products/' . date('Y/m');
                    
                    // Traiter et optimiser l'image
                    $processedImage = $this->processImage($uploadedFile);
                    
                    // Sauvegarder l'image dans le storage
                    $path = Storage::disk('public')->put($uploadPath . '/' . $filename, $processedImage);
                    
                    if ($path) {
                        $uploadedImages[] = [
                            'index' => $index,
                            'filename' => $filename,
                            'original_name' => $uploadedFile->getClientOriginalName(),
                            'path' => $path,
                            'url' => Storage::disk('public')->url($path),
                            'size' => Storage::disk('public')->size($path),
                            'mime_type' => $uploadedFile->getMimeType()
                        ];
                    }

                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'filename' => $uploadedFile->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                }
            }

            $response = [
                'success' => count($uploadedImages) > 0,
                'message' => count($uploadedImages) . ' image(s) uploadée(s) avec succès',
                'data' => [
                    'uploaded' => $uploadedImages,
                    'errors' => $errors,
                    'total_uploaded' => count($uploadedImages),
                    'total_errors' => count($errors)
                ]
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Erreur upload multiple images produits', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une image de produit
     */
    public function delete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chemin de fichier requis',
                    'errors' => $validator->errors()
                ], 422);
            }

            $path = $request->get('path');

            // Vérifier que le fichier existe
            if (!Storage::disk('public')->exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Fichier introuvable'
                ], 404);
            }

            // Supprimer le fichier
            $deleted = Storage::disk('public')->delete($path);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Image supprimée avec succès'
                ]);
            } else {
                throw new \Exception('Erreur lors de la suppression du fichier');
            }

        } catch (\Exception $e) {
            \Log::error('Erreur suppression image produit', [
                'error' => $e->getMessage(),
                'path' => $request->get('path'),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer un nom de fichier unique
     */
    private function generateUniqueFilename(string $extension): string
    {
        return Str::uuid() . '_' . time() . '.' . strtolower($extension);
    }

    /**
     * Traiter et optimiser l'image
     */
    private function processImage($uploadedFile): string
    {
        try {
            // Lire l'image
            $image = $this->imageManager->read($uploadedFile->getRealPath());
            
            // Redimensionner si trop grande (max 1200px de largeur)
            if ($image->width() > 1200) {
                $image->scaleDown(width: 1200);
            }
            
            // Optimiser la qualité selon le format
            $extension = strtolower($uploadedFile->getClientOriginalExtension());
            
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    return $image->toJpeg(quality: 85);
                case 'png':
                    return $image->toPng();
                case 'webp':
                    return $image->toWebp(quality: 85);
                default:
                    return $image->toJpeg(quality: 85);
            }
            
        } catch (\Exception $e) {
            // Si le traitement échoue, retourner le fichier original
            \Log::warning('Erreur traitement image, utilisation fichier original', [
                'error' => $e->getMessage(),
                'file' => $uploadedFile->getClientOriginalName()
            ]);
            
            return file_get_contents($uploadedFile->getRealPath());
        }
    }
}