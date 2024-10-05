<?php

namespace RBAC\Models;

use RBAC\Services\Config;
use RBAC\Services\CacheService;
use PDO;
use RBAC\Models\Role;

class User {
    private $id;
    private $roles = [];
    private $cacheService;

    public function __construct($id, CacheService $cacheService = null) {
        $this->id = $id;
        $this->cacheService = $cacheService;  // Optional CacheService instance
    }

    // Assign a role to the user
    public function assignRole(PDO $db, Role $role) {
        $userRolesTable = Config::get('database.tables.user_roles', 'user_roles');
        $stmt = $db->prepare("INSERT INTO {$userRolesTable} (user_id, role_id) VALUES (:user_id, :role_id)");
        $stmt->execute([
            'user_id' => $this->id,
            'role_id' => $role->getId()
        ]);
    }

    // Check if the user has a specific role
    public function hasRole(PDO $db, $roleSlug) {
        $rolesTable = Config::get('database.tables.roles', 'roles');
        $userRolesTable = Config::get('database.tables.user_roles', 'user_roles');
        
        $stmt = $db->prepare("
            SELECT r.* 
            FROM {$rolesTable} r 
            JOIN {$userRolesTable} ur ON r.id = ur.role_id 
            WHERE ur.user_id = :user_id AND r.slug = :slug
        ");
        $stmt->execute([
            'user_id' => $this->id,
            'slug' => $roleSlug
        ]);

        return $stmt->fetch() !== false;
    }

    // Check if the user has a specific permission
    public function hasPermission(PDO $db, $permissionSlug) {
        $cacheKey = "user_{$this->id}_permissions";

        // Check if permissions are cached
        if ($this->cacheService) {
            $cachedPermissions = $this->cacheService->get($cacheKey);
            if ($cachedPermissions) {
                $permissions = json_decode($cachedPermissions, true);
                return in_array($permissionSlug, $permissions);
            }
        }

        // Otherwise, fetch from DB and cache the result
        $roles = $this->getRoles($db);
        $permissions = [];

        foreach ($roles as $role) {
            if ($role->hasPermission($db, $permissionSlug)) {
                $permissions[] = $permissionSlug;
            }
        }

        // Cache permissions for this user, if CacheService is available
        if ($this->cacheService) {
            $this->cacheService->put($cacheKey, json_encode($permissions));
        }

        return in_array($permissionSlug, $permissions);
    }

    // Get all roles assigned to the user
    public function getRoles(PDO $db) {
        if (empty($this->roles)) {
            $rolesTable = Config::get('database.tables.roles', 'roles');
            $userRolesTable = Config::get('database.tables.user_roles', 'user_roles');
            
            $stmt = $db->prepare("
                SELECT r.* 
                FROM {$rolesTable} r 
                JOIN {$userRolesTable} ur ON r.id = ur.role_id 
                WHERE ur.user_id = :user_id
            ");
            $stmt->execute(['user_id' => $this->id]);
            $this->roles = $stmt->fetchAll(PDO::FETCH_CLASS, Role::class);
        }

        return $this->roles;
    }

    public function getId() {
        return $this->id;
    }
}
