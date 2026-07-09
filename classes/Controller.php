<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Base Controller class
 */
abstract class Controller
{
    protected Auth $auth;
    protected Logger $logger;
    
    public function __construct()
    {
        $this->auth = new Auth(Database::getInstance());
        $this->logger = new Logger();
    }
    
    /**
     * Render view
     */
    protected function render(string $view, array $data = []): void
    {
        $viewFile = APP_ROOT . '/templates/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            http_response_code(404);
            require_once APP_ROOT . '/404.php';
            exit;
        }
        
        extract($data);
        require APP_ROOT . '/includes/header.php';
        require $viewFile;
        require APP_ROOT . '/includes/footer.php';
    }
    
    /**
     * Redirect
     */
    protected function redirect(string $url, string $message = ''): void
    {
        Response::redirect($url, $message);
    }
    
    /**
     * Require permission
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->auth->can($permission)) {
            http_response_code(403);
            require_once APP_ROOT . '/403.php';
            exit;
        }
    }
    
    /**
     * Require login
     */
    protected function requireLogin(): void
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Get POST data
     */
    protected function getPostData(): array
    {
        return $_POST;
    }
    
    /**
     * Get GET data
     */
    protected function getGetData(): array
    {
        return $_GET;
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? '';
        return Session::validateCsrfToken($token);
    }
}