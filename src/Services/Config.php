<?php

namespace RBAC\Services;

class Config {
    private static $config = null;
    private static $configFile = __DIR__ . '/../../config/rbac.php';

    // Allow users to set a custom configuration file
    public static function setFile($filePath) {
        if (file_exists($filePath)) {
            self::$configFile = $filePath;
        } else {
            throw new \Exception("Configuration file {$filePath} does not exist.");
        }
    }

    // Load configuration file, either custom or default
    private static function loadConfig() {
        if (self::$config === null) {
            if (file_exists(self::$configFile)) {
                self::$config = require self::$configFile;
            } else {
                throw new \Exception("Default configuration file does not exist at " . self::$configFile);
            }
        }
    }

    // Get a specific configuration value, with optional default
    public static function get($key, $default = null) {
        self::loadConfig();

        // Split key by dot notation (e.g., 'cache.ttl')
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                return $default; // Return default if key is not found
            }
        }

        return $value;
    }
}
