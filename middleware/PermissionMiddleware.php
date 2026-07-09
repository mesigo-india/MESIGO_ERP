<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Response;

/**
 * Permission Middleware
 */
class PermissionMiddleware
{
    private Auth $auth;
    
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    
    /**
     * Handle permission check
     */
    public function handle(string $permission): void
    {
        if (!$this->auth->can($permission)) {
            if ($this->isApiRequest()) {
                Response::error('Permission denied', [], 403);
            }
            
            http_response_code(403);
            require_once APP_ROOT . '/403.php';
            exit;
        }
    }
    
    /**
     * Check if request is API
     */
    private function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
    }
}