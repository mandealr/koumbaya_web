<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // SafeRateLimiter force Cache::store('file') (ignore le cache array des
        // tests) → les compteurs de throttle persistent sur disque entre les
        // tests/runs et provoquent des 429 parasites. On repart propre.
        Cache::store('file')->flush();
    }
}
