<?php

namespace RBAC\Services;

use RBAC\Services\Cache\CacheFactory;

class CacheService {
    private $cache;

    public function __construct() {
        // Use the factory to create the appropriate cache store
        $this->cache = CacheFactory::create();
    }

    // Retrieve data from the cache
    public function get($key) {
        return $this->cache->get($key);
    }

    // Store data in the cache
    public function put($key, $value) {
        $this->cache->put($key, $value);
    }

    // Invalidate cache for a specific key
    public function forget($key) {
        $this->cache->forget($key);
    }
}