<?php

/**
 * Dashboard Cache Service
 *
 * Session-based cache for dashboard metrics with TTL and event-driven invalidation.
 * Each cache entry: ['data' => mixed, 'stored_at' => int (unix timestamp)]
 *
 * @package ProyectoBase
 * @subpackage App\Services
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Services;

class DashboardCache
{
    private const SESSION_KEY = 'dashboard_cache';

    /**
     * Retrieve a cached value. Returns null if missing or expired.
     */
    public static function get(string $key): ?array
    {
        $store = $_SESSION[self::SESSION_KEY] ?? [];

        if (!isset($store[$key])) {
            return null;
        }

        $entry = $store[$key];

        if ((time() - $entry['stored_at']) > self::ttl()) {
            self::forget($key);
            return null;
        }

        return $entry['data'];
    }

    /**
     * Store a value in the cache.
     */
    public static function put(string $key, array $data): void
    {
        $_SESSION[self::SESSION_KEY][$key] = [
            'data'      => $data,
            'stored_at' => time(),
        ];
    }

    /**
     * Get a cached value or compute and store it.
     *
     * @param callable(): array $loader
     */
    public static function remember(string $key, callable $loader): array
    {
        $cached = self::get($key);

        if ($cached !== null) {
            return $cached;
        }

        $data = $loader();
        self::put($key, $data);

        return $data;
    }

    /**
     * Remove a single cache key.
     */
    public static function forget(string $key): void
    {
        unset($_SESSION[self::SESSION_KEY][$key]);
    }

    /**
     * Clear all dashboard cache entries.
     */
    public static function flush(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    /**
     * TTL in seconds. Reads DASHBOARD_CACHE_TTL from .env (default 300 s).
     */
    public static function ttl(): int
    {
        return (int) env('DASHBOARD_CACHE_TTL', 300);
    }
}
