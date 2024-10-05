# FAST RBAC Package

This package provides a flexible and easy-to-integrate **Role-Based Access Control (RBAC)** system for PHP applications. It supports role/permission assignment, permission checks, caching with Redis, and framework integration.

## Features

- Define roles (e.g., Admin, Editor) and permissions (e.g., Create Post, Edit Post).
- Assign roles to users and check user permissions.
- Support for caching user permissions to improve performance.
- Automatic database migrations to create necessary tables.

## Installation

### Step 1: Install via Composer

```bash
composer require yourname/rbac-php
```

### Step 2: Set up the Database
Run the following SQL migrations to create the required tables:

```SQL
-- SQL Migration Script
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE user_roles (
    user_id INT,
    role_id INT,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

## Database Migrations

To ensure that the necessary tables for roles, permissions, and user-role assignments are created, the RBAC package provides an automatic migration feature.

### 1. Migration File

The migration SQL file (`migrations.sql`) is located in the `src/Database/` directory. It contains the SQL statements for creating the following tables:

- `roles`: Stores the available roles.
- `permissions`: Stores the available permissions.
- `role_permissions`: A pivot table linking roles and permissions.
- `user_roles`: A pivot table linking users and roles.

### 2. Running Migrations

Before starting the application, ensure that the required tables are created by running the `migrate` method. The system will automatically create the tables if they do not exist.

#### Usage:

```php
use RBAC\Setup\DatabaseSetup;
use PDO;

// Setup database connection
$pdo = new PDO('mysql:host=localhost;dbname=rbac_db', 'username', 'password');

// Initialize the database setup
$dbSetup = new DatabaseSetup();

// Run migrations if tables don't exist
$migrationFilePath = __DIR__ . '/src/Database/migrations.sql';
$dbSetup->migrate($pdo, $migrationFilePath);
```

## Configuration

To customize the RBAC system, modify the `config/rbac.php` file. Here’s a breakdown of the configuration options:

```php
return [
    'database' => [
        'tables' => [
            'roles' => 'roles',                  // Table for storing roles
            'permissions' => 'permissions',      // Table for storing permissions
            'role_permissions' => 'role_permissions',  // Role-to-permission pivot table
            'user_roles' => 'user_roles',        // User-to-role pivot table
        ],
    ],
    'cache' => [
        'enabled' => true,         // Enable or disable caching
        'store' => 'redis',        // Cache store: redis or array
        'ttl' => 3600,             // Cache Time-to-Live in seconds
    ],
    'user_model' => \App\Models\User::class, // Fully qualified name of the user model
];
```

## Core Functionality

### Role and Permission Management

To create roles and assign permissions:

```php
use RBAC\Models\Role;
use RBAC\Models\Permission;
use PDO;

// Setup a database connection
$pdo = new PDO('mysql:host=localhost;dbname=rbac_db', 'username', 'password');

// Create a new role
$adminRole = new Role(null, 'Admin', 'admin');
$pdo->exec("INSERT INTO roles (name, slug) VALUES ('Admin', 'admin')");

// Create a permission and attach it to the role
$createPostPermission =

 new Permission(null, 'Create Post', 'create_post');
$pdo->exec("INSERT INTO permissions (name, slug) VALUES ('Create Post', 'create_post')");

// Attach permission to role
$adminRole = Role::findBySlug($pdo, 'admin');
$createPostPermission = Permission::findBySlug($pdo, 'create_post');
$adminRole->attachPermission($pdo, $createPostPermission);
```

### Assigning Roles to Users

```php
use RBAC\Models\User;
use RBAC\Services\CacheService;
use RBAC\Services\RbacService;
use PDO;

// Setup a database connection
$pdo = new PDO('mysql:host=localhost;dbname=rbac_db', 'username', 'password');

// Initialize CacheService and RbacService
$cacheService = new CacheService();
$rbacService = new RbacService($cacheService);

// Create a new user
$user = new User(1, $cacheService);

// Assign a role to the user
$adminRole = Role::findBySlug($pdo, 'admin');
$rbacService->assignRoleToUser($pdo, $user, $adminRole);
```

### Checking User Permissions

```php
// Check if the user has permission to create a post
if ($rbacService->checkPermission($pdo, $user, 'create_post')) {
    echo "User can create posts.";
} else {
    echo "User cannot create posts.";
}
```

## Middleware Integration
#### Laravel Integration:
1. Register middleware in app/Http/Kernel.php:

```php
'rbac' => \RBAC\Middleware\RbacMiddleware::class,
```
2. Use in routes:
```php
Route::get('/admin/create-post', function () {
    // Logic here
})->middleware('rbac:create_post');
```

#### Slim Framework Integration:
In Slim, middleware is added directly to the application or specific routes. Here’s an example of integrating RBAC middleware in Slim:

```php
$app->add(new \RBAC\Middleware\RbacMiddleware($rbacService, $pdo));

// Alternatively, for specific routes
$app->get('/admin/create-post', function ($request, $response, $args) {
    // Your route logic here
})->add(new \RBAC\Middleware\RbacMiddleware($rbacService, $pdo));
```

## Testing

To run the tests:

```bash
./vendor/bin/phpunit
```


## Database Table Customization
You can customize the table names for storing roles, permissions, and role-user assignments by changing the values under the database.tables section.

## Caching
To improve performance, the RBAC system supports caching. You can enable/disable caching and choose between Redis or an in-memory array as the cache store.

## Custom User Model
By default, the RBAC system uses the User model. If you have a custom user model, specify its fully qualified name under the user_model setting.
