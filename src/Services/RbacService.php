<?php

namespace RBAC\Services;

use RBAC\Models\User;
use RBAC\Models\Role;
use RBAC\Models\Permission;
use PDO;
use RBAC\Services\CacheService;

class RbacService {
    private $cacheService;

    public function __construct(CacheService $cacheService) {
        $this->cacheService = $cacheService;
    }

    // Check if a user has a specific permission
    public function checkPermission(PDO $db, User $user, $permissionSlug) {
        return $user->hasPermission($db, $permissionSlug);
    }

    // Assign a role to a user
    public function assignRoleToUser(PDO $db, User $user, Role $role) {
        $user->assignRole($db, $role);
        // Invalidate cache since roles have changed
        $this->cacheService->forget("user_{$user->getId()}_permissions");
    }

    // Grant a permission to a role and invalidate cache for all users with this role
    public function grantPermissionToRole(PDO $db, Role $role, Permission $permission) {
        $role->attachPermission($db, $permission);
        // Invalidate cache for all users with this role
        $this->invalidateUserPermissionsCacheForRole($db, $role);
    }

    // Remove a permission from a role and invalidate cache for all users with this role
    public function revokePermissionFromRole(PDO $db, Role $role, Permission $permission) {
        $role->detachPermission($db, $permission);
        // Invalidate cache for all users with this role
        $this->invalidateUserPermissionsCacheForRole($db, $role);
    }

    // Invalidate cache for all users with a specific role
    private function invalidateUserPermissionsCacheForRole(PDO $db, Role $role) {
        $userRolesTable = Config::get('database.tables.user_roles', 'user_roles');

        // Get all users with this role
        $stmt = $db->prepare("SELECT user_id FROM {$userRolesTable} WHERE role_id = :role_id");
        $stmt->execute(['role_id' => $role->getId()]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Invalidate cache for each user associated with the role
        foreach ($users as $user) {
            $cacheKey = "user_{$user['user_id']}_permissions";
            $this->cacheService->forget($cacheKey);
        }
    }
}
