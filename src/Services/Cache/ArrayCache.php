<?php
namespace RBAC\Services\Cache;

class ArrayCache implements CacheInterface {
    private $cache = [];
    private $ttl;

    public function __construct($ttl) {
        $this->ttl = $ttl;
    }

    public function get($key) {
        return isset($this->cache[$key]) ? $this->cache[$key] : null;
    }

    public function put($key, $value) {
        $this->cache[$key] = $value;
        // Simulate TTL by removing it after time (can be improved)
    }

    public function forget($key) {
        unset($this->cache[$key]);
    }
}
