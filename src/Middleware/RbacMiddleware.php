<?php

namespace RBAC\Middleware;

use RBAC\Services\RbacService;
use RBAC\Models\User;
use PDO;

class RbacMiddleware {
    private $rbacService;
    private $db;

    public function __construct(RbacService $rbacService, PDO $db) {
        $this->rbacService = $rbacService;
        $this->db = $db;
    }

    // Middleware handle function
    public function handle($request, $next, $requiredPermission) {
        // Extract the authenticated user (assumes a method to get user from request/session)
        $user = $this->getAuthenticatedUser($request);

        // Check if user has the required permission
        if (!$this->rbacService->checkPermission($this->db, $user, $requiredPermission)) {
            return $this->denyAccess();
        }

        // Proceed with the request if permission is granted
        return $next($request);
    }

    // Mock method to extract user from request/session (this would depend on your application)
    private function getAuthenticatedUser($request) {
        // Example: extract user ID from session or request and fetch the User
        $userId = $_SESSION['user_id'] ?? null;  // Assuming user_id is stored in the session
        return new User($userId);
    }

    // Respond with a 403 Forbidden error if the user lacks permission
    private function denyAccess() {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'Forbidden: You do not have permission to access this resource.']);
        exit;
    }
}
