<?php

namespace RBAC\Services;

use Predis\Client;
use RBAC\Services\Config;

class CacheService {
    private $store;
    private $ttl;
    private $redis;

    public function __construct() {
        // Get cache store and TTL from configuration
        $this->store = Config::get('cache.store', 'array');  // Use configured cache store
        $this->ttl = Config::get('cache.ttl', 3600);         // Use configured TTL

        // Initialize Redis if that's the selected store
        if ($this->store === 'redis') {
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host' => '172.27.248.209',
                'port' => 6379,
            ]);
        }
    }

    // Retrieve data from Redis or array cache
    public function get($key) {
        if ($this->store === 'redis') {
            return $this->redis->get($key);
        }
        // For simplicity, we won't implement array caching in detail here
        return null;  // No cache hit
    }

    // Store data in Redis or array cache
    public function put($key, $value) {
        if ($this->store === 'redis') {
            $this->redis->set($key, $value);
            $this->redis->expire($key, $this->ttl);
        }
    }

    // Invalidate cache for a specific key
    public function forget($key) {
        if ($this->store === 'redis') {
            $this->redis->del($key);
        }
    }
}
