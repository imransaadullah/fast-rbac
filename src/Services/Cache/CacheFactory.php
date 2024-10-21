<?php
namespace RBAC\Services\Cache;

use RBAC\Services\Config;

class CacheFactory {
    public static function create() {
        // Get cache store and TTL from configuration
        $store = Config::get('cache.store', 'array');  // 'redis', 'array', etc.
        $ttl = Config::get('cache.ttl', 3600);         // Use configured TTL
        
        // Based on store type, return the appropriate cache implementation
        switch ($store) {
            case 'redis':
                $payload = [
                    'scheme' => Config::get('cache.scheme', 'tcp'),
                    'host' => Config::get('cache.host', '127.0.0.1'),
                    'port' => Config::get('cache.port', 6379),
                ];
                return new RedisCache($ttl, $payload);
            case 'array':
            default:
                return new ArrayCache($ttl);  // Default to array cache if not configured
        }
    }
}
