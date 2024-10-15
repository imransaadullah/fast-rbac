<?php

namespace RBAC\Models;

use RBAC\Services\Config;
use PDO;

class Permission {
    private $id;
    private $name;
    private $slug;

    public function __construct($id, $name, $slug) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
    }

    // Find a permission by its slug
    public static function findBySlug(PDO $db, $slug) {
        $table = Config::get('database.tables.permissions', 'permissions');
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);

        // Fetch the permission data as an associative array
        $permissionData = $stmt->fetch(PDO::FETCH_ASSOC);

        // If permission is found, manually instantiate the Permission class
        if ($permissionData) {
            return new self($permissionData['id'], $permissionData['name'], $permissionData['slug']);
        }

        return null; // Return null if no permission is found
    }

    // Retrieve all permissions from the database
    public static function all(PDO $db) {
        $table = Config::get('database.tables.permissions', 'permissions');
        $stmt = $db->query("SELECT * FROM {$table}");

        // Fetch all permissions as an associative array
        $permissionsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Create an array to hold Permission objects
        $permissions = [];

        // Manually instantiate Permission objects for each row
        foreach ($permissionsData as $permissionData) {
            $permissions[] = new self($permissionData['id'], $permissionData['name'], $permissionData['slug']);
        }

        return $permissions;
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
