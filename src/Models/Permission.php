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

    // Retrieve all permissions from the database
    public static function all(PDO $db) {
        $table = Config::get('database.tables.permissions', 'permissions');
        $stmt = $db->query("SELECT * FROM {$table}");
        return $stmt->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    // Find a permission by its slug
    public static function findBySlug(PDO $db, $slug) {
        $table = Config::get('database.tables.permissions', 'permissions');
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE slug = :slug");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetchObject(self::class);
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
