<?php

return [
    'database' => [
        'tables' => [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'role_permissions' => 'role_permissions',
            'user_roles' => 'user_roles',
        ],
    ],
    'cache' => [
        'enabled' => true,
        'store' => 'redis',
        'ttl' => 3600,
    ],
    'user_model' => \RBAC\Models\User::class,
];
