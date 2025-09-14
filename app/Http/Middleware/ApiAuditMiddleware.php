<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class ApiAuditMiddleware
{
    /**
     * Routes sensibles qui nécessitent un audit automatique
     */
    protected $auditRoutes = [
        'auth/login',
        'auth/register', 
        'auth/logout',
        'orders',
        'payments',
        'tickets',
        'lotteries',
        'products',
    ];
    
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Audit seulement pour les routes API sensibles
        if ($this->shouldAudit($request)) {
            $this->logApiRequest($request, $response);
        }
        
        return $response;
    }
    
    protected function shouldAudit(Request $request): bool
    {
        // Seulement pour les routes API
        if (!$request->is('api/*')) {
            return false;
        }
        
        // Vérifier si c'est une route sensible
        $path = str_replace('api/', '', $request->path());
        
        foreach ($this->auditRoutes as $auditRoute) {
            if (str_starts_with($path, $auditRoute)) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function logApiRequest(Request $request, $response)
    {
        try {
            // Vérifier si la table audit_logs existe
            if (!\Schema::hasTable('audit_logs')) {
                return; // Skip silencieusement si la table n'existe pas
            }

            $statusCode = method_exists($response, 'getStatusCode') 
                ? $response->getStatusCode() 
                : 200;
                
            $event = $this->generateEventName($request, $statusCode);
            
            // Skip si c'est juste une requête GET de lecture
            if ($request->isMethod('GET') && $statusCode < 300) {
                return;
            }
            
            $metadata = [
                'method' => $request->method(),
                'status_code' => $statusCode,
                'platform' => $request->header('X-Platform', 'web'),
                'app_version' => $request->header('X-App-Version'),
                'endpoint' => $request->path(),
            ];
            
            // Ajouter des données contextuelles selon l'endpoint
            if (str_contains($request->path(), 'auth/')) {
                $metadata['auth_action'] = true;
            }
            
            if (str_contains($request->path(), 'payment')) {
                $metadata['payment_action'] = true;
            }
            
            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => $event,
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'tags' => ['api', 'auto'],
                'metadata' => $metadata,
            ]);
            
        } catch (\Exception $e) {
            Log::warning('Failed to create API audit log: ' . $e->getMessage());
        }
    }
    
    protected function generateEventName(Request $request, int $statusCode): string
    {
        $method = strtolower($request->method());
        $path = str_replace(['api/', '/'], ['.', '.'], $request->path());
        $path = trim($path, '.');
        
        $status = $statusCode < 300 ? 'success' : 'error';
        
        return "api.{$path}.{$method}.{$status}";
    }
}