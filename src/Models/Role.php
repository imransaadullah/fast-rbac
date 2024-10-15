<?php

namespace RBAC\Models;

use RBAC\Services\Config;
use PDO;

class Role {
    private $id;
    private $name;
    private $slug;

    public function __construct($id, $name, $slug) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
    }

    // Retrieve all roles from the database
    // public static function all(PDO $db) {
    //     $table = Config::get('database.tables.roles', 'roles');
    //     $stmt = $db->query("SELECT * FROM {$table}");
    //     return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    // }
    public static function all(PDO $db) {
        $table = Config::get('database.tables.roles', 'roles');
        $stmt = $db->query("SELECT * FROM {$table}");
        
        // Fetch all roles as an array of associative arrays
        $rolesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Manually instantiate Role objects for each record
        $roles = [];
        foreach ($rolesData as $roleData) {
            $roles[] = new self($roleData['id'], $roleData['name'], $roleData['slug']);
        }
        
        return $roles;
    }
    

    // Find a role by its slug
    // public static function findBySlug(PDO $db, $slug) {
    //     $table = Config::get('database.tables.roles', 'roles');
    //     $stmt = $db->prepare("SELECT * FROM {$table} WHERE slug = :slug");
    //     $stmt->execute(['slug' => $slug]);
    //     return $stmt->fetchObject(self::class);
    // }

    public static function findBySlug(PDO $db, $slug) {
        $table = Config::get('database.tables.roles', 'roles');
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        
        // Fetch role data as an associative array
        $roleData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if data was found and instantiate the Role class manually
        if ($roleData) {
            return new self($roleData['id'], $roleData['name'], $roleData['slug']);
        }
    
        return null;  // Return null if no role is found
    }
    

    // Attach a permission to this role
    public function attachPermission(PDO $db, Permission $permission) {
        $rolePermissionsTable = Config::get('database.tables.role_permissions', 'role_permissions');
        $stmt = $db->prepare("INSERT INTO {$rolePermissionsTable} (role_id, permission_id) VALUES (:role_id, :permission_id)");
        $stmt->execute([
            'role_id' => $this->id,
            'permission_id' => $permission->getId()
        ]);
    }

    // Detach a permission from this role
    public function detachPermission(PDO $db, Permission $permission) {
        $rolePermissionsTable = Config::get('database.tables.role_permissions', 'role_permissions');
        $stmt = $db->prepare("DELETE FROM {$rolePermissionsTable} WHERE role_id = :role_id AND permission_id = :permission_id");
        $stmt->execute([
            'role_id' => $this->id,
            'permission_id' => $permission->getId()
        ]);
    }

    // Check if the role has a specific permission
    public function hasPermission(PDO $db, $permissionSlug) {
        $permissionsTable = Config::get('database.tables.permissions', 'permissions');
        $rolePermissionsTable = Config::get('database.tables.role_permissions', 'role_permissions');
        
        $stmt = $db->prepare("
            SELECT p.* 
            FROM {$permissionsTable} p 
            JOIN {$rolePermissionsTable} rp ON p.id = rp.permission_id 
            WHERE rp.role_id = :role_id AND p.slug = :slug
        ");
        $stmt->execute([
            'role_id' => $this->id,
            'slug' => $permissionSlug
        ]);
        
        return $stmt->fetch() !== false;
    }

    // Getter methods
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getSlug() {
        return $this->slug;
    }
}
