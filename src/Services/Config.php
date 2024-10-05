<?php

namespace RBAC\Services;

class Config {
    private static $config;
    private static $customConfigFile;

    // Allow users to set a custom configuration file
    public static function setCustomConfigFile($filePath) {
        self::$customConfigFile = $filePath;
    }

    // Load configuration file, either custom or default
    private static function loadConfig() {
        if (!self::$config) {
            if (self::$customConfigFile && file_exists(self::$customConfigFile)) {
                // Load the custom configuration file if it exists
                self::$config = require self::$customConfigFile;
            } else {
                // Load the default configuration
                self::$config = require __DIR__ . '/../../config/rbac.php';
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
