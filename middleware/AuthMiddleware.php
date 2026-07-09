<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Response;

/**
 * Authentication Middleware
 */
class AuthMiddleware
{
    private Auth $auth;
    
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    
    /**
     * Handle authentication check
     */
    public function handle(): void
    {
        if (!$this->auth->isLoggedIn()) {
            if ($this->isApiRequest()) {
                Response::error('Authentication required', [], 401);
            }
            
            header('Location: /login');
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