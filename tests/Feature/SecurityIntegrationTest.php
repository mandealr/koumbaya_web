<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class SecurityIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Désactiver temporairement les logs pour les tests
        Log::shouldReceive('warning')->andReturn(true);
        Log::shouldReceive('critical')->andReturn(true);
    }

    /**
     * Test des headers de sécurité
     */
    public function test_security_headers_are_present()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
    }

    /**
     * Test CSP pour les requêtes web
     */
    public function test_csp_header_for_web_requests()
    {
        $response = $this->get('/', ['Accept' => 'text/html']);
        
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContains("default-src 'self'", $csp);
        $this->assertStringContains("object-src 'none'", $csp);
        $this->assertStringContains("frame-ancestors 'none'", $csp);
    }

    /**
     * Test CSP strict pour les requêtes API
     */
    public function test_strict_csp_for_api_requests()
    {
        $response = $this->getJson('/api/products');
        
        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContains("default-src 'none'", $csp);
        $this->assertStringContains("frame-ancestors 'none'", $csp);
    }

    /**
     * Test de détection XSS dans les paramètres GET
     */
    public function test_xss_detection_in_get_parameters()
    {
        $xssPayload = '<script>alert("XSS")</script>';
        
        $response = $this->get('/api/products?search=' . urlencode($xssPayload));
        
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Request blocked for security reasons'
        ]);
    }

    /**
     * Test de détection XSS dans les données POST
     */
    public function test_xss_detection_in_post_data()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => 'Product Name',
            'description' => '<script>alert("XSS")</script>',
        ]);
        
        $response->assertStatus(400);
        $response->assertJsonStructure([
            'error',
            'message',
            'code'
        ]);
    }

    /**
     * Test de détection d'injection SQL
     */
    public function test_sql_injection_detection()
    {
        $user = User::factory()->create();
        $sqlPayload = "'; DROP TABLE users; --";
        
        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => $sqlPayload,
            'description' => 'Normal description',
        ]);
        
        $response->assertStatus(400);
    }

    /**
     * Test des entrées légitimes ne sont pas bloquées
     */
    public function test_legitimate_inputs_are_not_blocked()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => 'iPhone 14 Pro Max',
            'description' => 'Smartphone haut de gamme avec caméra 48MP & écran 6.7"',
            'price' => 850000,
        ]);
        
        // Ne devrait pas être bloqué par le middleware de sécurité
        $response->assertStatus(422); // Erreur de validation Laravel, pas de sécurité
    }

    /**
     * Test de validation des emails
     */
    public function test_email_validation()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'invalid-email',
            'password' => 'password123',
            'name' => 'Test User',
        ]);
        
        // Devrait passer le middleware de sécurité mais échouer à la validation
        $response->assertStatus(422);
    }

    /**
     * Test de validation des numéros de téléphone
     */
    public function test_phone_validation()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->putJson('/api/profile', [
            'phone' => 'abc123xyz', // Format invalide
        ]);
        
        // Devrait générer un warning mais pas bloquer complètement
        $response->assertStatus(422);
    }

    /**
     * Test de validation des prix
     */
    public function test_price_validation()
    {
        $user = User::factory()->create();
        
        // Prix négatif
        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => -1000,
        ]);
        
        $response->assertStatus(422);
        
        // Prix excessivement élevé
        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 999999999,
        ]);
        
        $response->assertStatus(422);
    }

    /**
     * Test de détection d'activité suspecte dans les headers
     */
    public function test_suspicious_user_agent_detection()
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 <script>alert("XSS")</script>',
        ])->get('/api/products');
        
        // Devrait logger l'activité suspecte mais permettre la requête
        $response->assertStatus(200);
    }

    /**
     * Test du rate limiting
     */
    public function test_rate_limiting_headers()
    {
        $response = $this->getJson('/api/products');
        
        $this->assertNotNull($response->headers->get('X-RateLimit-Remaining'));
    }

    /**
     * Test de suppression des headers sensibles
     */
    public function test_sensitive_headers_are_removed()
    {
        $response = $this->get('/');
        
        $this->assertNull($response->headers->get('X-Powered-By'));
        $this->assertNull($response->headers->get('Server'));
        $this->assertNull($response->headers->get('X-Laravel-Session'));
    }

    /**
     * Test HSTS en HTTPS
     */
    public function test_hsts_header_in_https()
    {
        // Simuler une requête HTTPS
        $response = $this->call('GET', '/', [], [], [], [
            'HTTPS' => 'on',
            'SERVER_PORT' => 443,
        ]);
        
        $this->assertNotNull($response->headers->get('Strict-Transport-Security'));
    }

    /**
     * Test de validation des URLs
     */
    public function test_dangerous_url_blocking()
    {
        $user = User::factory()->create();
        
        $dangerousUrls = [
            'javascript:alert("XSS")',
            'data:text/html,<script>alert("XSS")</script>',
            'vbscript:msgbox("XSS")',
        ];
        
        foreach ($dangerousUrls as $url) {
            $response = $this->actingAs($user)->postJson('/api/products', [
                'name' => 'Test Product',
                'website' => $url,
            ]);
            
            $response->assertStatus(400);
        }
    }

    /**
     * Test de caractères dangereux dans les formulaires
     */
    public function test_dangerous_characters_in_forms()
    {
        $user = User::factory()->create();
        
        $dangerousInputs = [
            '<iframe src="javascript:alert()">',
            '<object data="javascript:alert()">',
            '<embed src="javascript:alert()">',
            '<form action="javascript:alert()">',
        ];
        
        foreach ($dangerousInputs as $input) {
            $response = $this->actingAs($user)->postJson('/api/products', [
                'name' => 'Test Product',
                'description' => $input,
            ]);
            
            $response->assertStatus(400);
        }
    }

    /**
     * Test de validation récursive des données imbriquées
     */
    public function test_nested_data_validation()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => 'Test Product',
            'metadata' => [
                'description' => '<script>alert("XSS")</script>',
                'features' => [
                    'color' => 'Red',
                    'size' => '<img onerror=alert("XSS")>',
                ]
            ]
        ]);
        
        $response->assertStatus(400);
    }

    /**
     * Test de performance avec de gros volumes de données
     */
    public function test_performance_with_large_datasets()
    {
        $user = User::factory()->create();
        
        // Créer une grande quantité de données
        $largeData = [];
        for ($i = 0; $i < 1000; $i++) {
            $largeData["field_$i"] = "value_$i";
        }
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)->postJson('/api/products', $largeData);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        // Le middleware de sécurité ne devrait pas ajouter plus de 1 seconde
        $this->assertLessThan(1.0, $executionTime);
    }

    /**
     * Test de gestion des encodages multiples
     */
    public function test_multiple_encoding_attacks()
    {
        $user = User::factory()->create();
        
        $encodedXss = [
            '%3Cscript%3Ealert%28%22XSS%22%29%3C%2Fscript%3E', // URL encoded
            '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', // HTML encoded
            '\x3Cscript\x3Ealert(\x22XSS\x22)\x3C/script\x3E', // Hex encoded
        ];
        
        foreach ($encodedXss as $payload) {
            $response = $this->actingAs($user)->postJson('/api/products', [
                'name' => $payload,
            ]);
            
            // Certains encodages pourraient passer, mais pas les scripts décodés
            if ($response->status() !== 400) {
                // Vérifier que le contenu n'est pas dangereux après traitement
                $this->assertStringNotContains('<script>', $response->getContent());
            }
        }
    }

    /**
     * Test de bypass de sécurité
     */
    public function test_security_bypass_attempts()
    {
        $user = User::factory()->create();
        
        $bypassAttempts = [
            '<ScRiPt>alert("XSS")</ScRiPt>', // Casse mixte
            '< script >alert("XSS")< /script >', // Espaces
            '<script\x20type="text/javascript">alert("XSS")</script>', // Caractères de contrôle
            'javascript\x3Aalert("XSS")', // Caractère de contrôle dans le protocole
        ];
        
        foreach ($bypassAttempts as $payload) {
            $response = $this->actingAs($user)->postJson('/api/products', [
                'name' => 'Test Product',
                'description' => $payload,
            ]);
            
            $response->assertStatus(400);
        }
    }
}