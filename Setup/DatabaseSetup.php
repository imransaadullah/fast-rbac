<?php

namespace RBAC\Setup;

use PDO;
use Exception;

class DatabaseSetup {

    // Function to check if a table exists
    private function tableExists(PDO $db, $tableName) {
        $stmt = $db->prepare("SHOW TABLES LIKE :table");
        $stmt->execute(['table' => $tableName]);
        return $stmt->fetch() !== false;
    }

    // Function to execute the SQL file for migrations
    private function executeSqlFile(PDO $db, $filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("SQL file not found: " . $filePath);
        }

        // Read the SQL file
        $sql = file_get_contents($filePath);

        // Execute the SQL statements
        try {
            $db->exec($sql);
            echo "Executed migration from: $filePath\n";
        } catch (Exception $e) {
            throw new Exception("Error executing SQL migration: " . $e->getMessage());
        }
    }

    // Main method to run migrations if tables don't exist
    public function migrate(PDO $db, $migrationFilePath) {
        // Check if any of the necessary tables are missing
        if (!$this->tableExists($db, 'roles') ||
            !$this->tableExists($db, 'permissions') ||
            !$this->tableExists($db, 'role_permissions') ||
            !$this->tableExists($db, 'user_roles')) {

            echo "One or more required tables are missing. Running migrations...\n";
            // Run the migration SQL file
            $this->executeSqlFile($db, $migrationFilePath);
        } else {
            echo "All required tables already exist. No migration needed.\n";
        }
    }
}
