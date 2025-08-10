<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TicketPriceCalculator;

class TicketPriceCalculatorTest extends TestCase
{
    /**
     * Test basique de calcul de prix de ticket
     */
    public function test_basic_ticket_price_calculation()
    {
        // iPhone 15 Pro à 750,000 FCFA avec 1000 tickets
        $productPrice = 750000;
        $numberOfTickets = 1000;
        
        $ticketPrice = TicketPriceCalculator::calculateTicketPrice($productPrice, $numberOfTickets);
        
        // Calcul attendu:
        // Commission (10%): 75,000 FCFA
        // Marge (15%): 112,500 FCFA
        // Total: 750,000 + 75,000 + 112,500 = 937,500 FCFA
        // Prix par ticket: 937,500 / 1000 = 937.5 → arrondi à 938 FCFA
        
        $this->assertEquals(938, $ticketPrice);
    }

    /**
     * Test avec différents nombres de tickets
     */
    public function test_different_ticket_counts()
    {
        $productPrice = 500000; // 500k FCFA
        
        // Avec 500 tickets
        $ticketPrice500 = TicketPriceCalculator::calculateTicketPrice($productPrice, 500);
        
        // Avec 2000 tickets
        $ticketPrice2000 = TicketPriceCalculator::calculateTicketPrice($productPrice, 2000);
        
        // Plus il y a de tickets, moins cher est le ticket individuel
        $this->assertGreaterThan($ticketPrice2000, $ticketPrice500);
    }

    /**
     * Test des détails de calcul complets
     */
    public function test_calculation_details()
    {
        $productPrice = 1000000; // 1M FCFA
        $numberOfTickets = 1000;
        
        $details = TicketPriceCalculator::getCalculationDetails($productPrice, $numberOfTickets);
        
        $this->assertArrayHasKey('product_price', $details);
        $this->assertArrayHasKey('commission_amount', $details);
        $this->assertArrayHasKey('margin_amount', $details);
        $this->assertArrayHasKey('total_amount', $details);
        $this->assertArrayHasKey('final_ticket_price', $details);
        $this->assertArrayHasKey('koumbaya_profit', $details);
        $this->assertArrayHasKey('merchant_revenue', $details);
        
        // Vérifier les calculs
        $this->assertEquals(1000000, $details['product_price']);
        $this->assertEquals(100000, $details['commission_amount']); // 10% de 1M
        $this->assertEquals(150000, $details['margin_amount']); // 15% de 1M
        $this->assertEquals(1250000, $details['total_amount']); // 1M + 100k + 150k
        $this->assertEquals(250000, $details['koumbaya_profit']); // Commission + Marge
        $this->assertEquals(1000000, $details['merchant_revenue']); // Prix original
    }

    /**
     * Test des suggestions de prix
     */
    public function test_price_suggestions()
    {
        $productPrice = 300000; // 300k FCFA
        $currentTicketPrice = 375; // Prix actuel calculé
        
        $suggestions = TicketPriceCalculator::getSuggestions($productPrice, $currentTicketPrice);
        
        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        
        // Vérifier que chaque suggestion a les bonnes clés
        foreach ($suggestions as $suggestion) {
            $this->assertArrayHasKey('tickets', $suggestion);
            $this->assertArrayHasKey('price', $suggestion);
            $this->assertArrayHasKey('total_revenue', $suggestion);
            $this->assertArrayHasKey('is_recommended', $suggestion);
        }
    }

    /**
     * Test de validation des prix
     */
    public function test_price_validation()
    {
        // Prix très bas
        $validation = TicketPriceCalculator::validateTicketPrice(50);
        $this->assertFalse($validation['is_valid']);
        $this->assertNotEmpty($validation['warnings']);
        
        // Prix normal
        $validation = TicketPriceCalculator::validateTicketPrice(1000);
        $this->assertTrue($validation['is_valid']);
        $this->assertEmpty($validation['warnings']);
        
        // Prix très élevé
        $validation = TicketPriceCalculator::validateTicketPrice(60000);
        $this->assertFalse($validation['is_valid']);
        $this->assertNotEmpty($validation['warnings']);
    }

    /**
     * Test avec des taux personnalisés
     */
    public function test_custom_rates()
    {
        $productPrice = 200000;
        $numberOfTickets = 800;
        $commissionRate = 0.05; // 5%
        $marginRate = 0.10; // 10%
        
        $details = TicketPriceCalculator::getCalculationDetails(
            $productPrice,
            $numberOfTickets,
            $commissionRate,
            $marginRate
        );
        
        $this->assertEquals(10000, $details['commission_amount']); // 5% de 200k
        $this->assertEquals(20000, $details['margin_amount']); // 10% de 200k
        $this->assertEquals(230000, $details['total_amount']); // 200k + 10k + 20k
    }

    /**
     * Test de gestion des erreurs
     */
    public function test_error_handling()
    {
        $this->expectException(\InvalidArgumentException::class);
        TicketPriceCalculator::calculateTicketPrice(0, 1000); // Prix zéro
        
        $this->expectException(\InvalidArgumentException::class);
        TicketPriceCalculator::calculateTicketPrice(1000, 0); // Nombre de tickets zéro
        
        $this->expectException(\InvalidArgumentException::class);
        TicketPriceCalculator::calculateTicketPrice(1000, 500, -0.1); // Taux négatif
    }

    /**
     * Test de cas réels avec des produits populaires
     */
    public function test_real_world_examples()
    {
        // iPhone 15 Pro 256GB - 750k FCFA
        $iphone = TicketPriceCalculator::calculateTicketPrice(750000, 1000);
        $this->assertEquals(938, $iphone);

        // MacBook Pro M3 - 1.2M FCFA
        $macbook = TicketPriceCalculator::calculateTicketPrice(1200000, 1500);
        $this->assertEquals(1000, $macbook);

        // PlayStation 5 - 400k FCFA
        $ps5 = TicketPriceCalculator::calculateTicketPrice(400000, 800);
        $this->assertEquals(625, $ps5);

        // Samsung Galaxy S24 Ultra - 600k FCFA
        $samsung = TicketPriceCalculator::calculateTicketPrice(600000, 1200);
        $this->assertEquals(625, $samsung);
    }
}