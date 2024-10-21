<?php

namespace RBAC\Services\Cache;

interface CacheInterface {
    public function get($key);
    public function put($key, $value);
    public function forget($key);
}
