<?php
require "./vendor/autoload.php";

use Dotenv\Dotenv;

$env = Dotenv::createImmutable('../');
$env->safeLoad();

return [
    'database' => [
        'tables' => [
            'roles' => $_ENV['ROLES_TABLE'] ?? 'roles',
            'permissions' => $_ENV['PERMISSIONS_TABLE'] ?? 'permissions',
            'role_permissions' => $_ENV['ROLE_PERMISSIONS_TABLE'] ?? 'role_permissions',
            'user_roles' => $_ENV['USER_ROLES_TABLE'] ?? 'user_roles',
        ],
    ],
    'cache' => [
        'enabled' => filter_var($_ENV['CACHE_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN),  // Improved casting
        'store' => $_ENV['CACHE_STORE'] ?? 'array',  // Fixed typo from 'CACHEC_STORE'
        'ttl' => $_ENV['CACHE_TTL'] ?? 3600,
        'host' => $_ENV['REDIS_IP_ADDRESS'] ?? 'localhost',
        'port' => $_ENV['REDIS_PORT'] ?? 6379,
        'scheme' => $_ENV['REDIS_SCHEME'] ?? 'tcp'
    ],
    'user_model' => \RBAC\Models\User::class,
];
