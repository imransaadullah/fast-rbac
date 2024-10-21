<?php
namespace RBAC\Services\Cache;

use Predis\Client;

class RedisCache implements CacheInterface {
    private $redis;
    private $ttl;

    public function __construct($ttl, array $payload = null) {
        $this->redis = new Client($payload);
        $this->ttl = $ttl;
    }

    public function get($key) {
        return $this->redis->get($key);
    }

    public function put($key, $value) {
        $this->redis->set($key, $value);
        $this->redis->expire($key, $this->ttl);
    }

    public function forget($key) {
        $this->redis->del($key);
    }
}
