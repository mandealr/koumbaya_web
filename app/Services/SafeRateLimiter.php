<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SafeRateLimiter
{
    /**
     * Check if too many attempts have been made.
     */
    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        try {
            $attempts = Cache::store('file')->get($key, 0);
            return $attempts >= $maxAttempts;
        } catch (\Exception $e) {
            // En cas d'erreur, on considère qu'il n'y a pas trop de tentatives
            return false;
        }
    }

    /**
     * Increment the number of attempts.
     */
    public function hit(string $key, int $decaySeconds = 60): int
    {
        try {
            $attempts = Cache::store('file')->get($key, 0);
            $attempts++;
            
            Cache::store('file')->put($key, $attempts, $decaySeconds);
            
            return $attempts;
        } catch (\Exception $e) {
            // En cas d'erreur, on retourne 1
            return 1;
        }
    }

    /**
     * Get the number of attempts.
     */
    public function attempts(string $key): int
    {
        try {
            return Cache::store('file')->get($key, 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get the number of seconds until the attempts are available.
     */
    public function availableIn(string $key): int
    {
        try {
            $ttl = Cache::store('file')->getStore()->getPrefix() . $key;
            // Pour les caches fichier, on retourne une estimation
            return 60; // 1 minute par défaut
        } catch (\Exception $e) {
            return 60;
        }
    }

    /**
     * Clear the attempts for the given key.
     */
    public function clear(string $key): bool
    {
        try {
            return Cache::store('file')->forget($key);
        } catch (\Exception $e) {
            return false;
        }
    }
}