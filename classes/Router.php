<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Router class for URL routing
 */
class Router
{
    private array $routes = [];
    private array $params = [];
    private bool $routeFound = false;
    
    /**
     * Add GET route
     */
    public function get(string $path, string $controller, string $method = 'index'): void
    {
        $this->addRoute('GET', $path, $controller, $method);
    }
    
    /**
     * Add POST route
     */
    public function post(string $path, string $controller, string $method = 'index'): void
    {
        $this->addRoute('POST', $path, $controller, $method);
    }
    
    /**
     * Add PUT route
     */
    public function put(string $path, string $controller, string $method = 'index'): void
    {
        $this->addRoute('PUT', $path, $controller, $method);
    }
    
    /**
     * Add DELETE route
     */
    public function delete(string $path, string $controller, string $method = 'index'): void
    {
        $this->addRoute('DELETE', $path, $controller, $method);
    }
    
    /**
     * Add route
     */
    private function addRoute(string $method, string $path, string $controller, string $action): void
    {
        if (str_contains($controller, '@')) {
            [$controller, $action] = explode('@', $controller, 2);
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
        ];
    }
    
    /**
     * Dispatch request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = $this->convertToRegex($route['path']);
            
            if (preg_match($pattern, $uri, $matches)) {
                $this->routeFound = true;
                $this->params = $this->extractParams($route['path'], $matches);
                
                $this->callController($route['controller'], $route['action']);
                return;
            }
        }
    }
    
    /**
     * Convert path to regex pattern
     */
    private function convertToRegex(string $path): string
    {
        $path = preg_replace('/\//', '\\/', $path);
        $path = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $path);
        return '/^' . $path . '$/i';
    }
    
    /**
     * Extract parameters from matches
     */
    private function extractParams(string $path, array $matches): array
    {
        preg_match_all('/\{([a-z]+)\}/', $path, $paramNames);
        $params = [];
        
        foreach ($paramNames[1] as $name) {
            $params[$name] = $matches[$name] ?? null;
        }
        
        return $params;
    }
    
    /**
     * Call controller method
     */
    private function callController(string $controller, string $action): void
    {
        $controllerPath = str_replace('\\', '/', $controller);
        $controllerFile = APP_ROOT . '/classes/' . $controllerPath . '.php';
        
        if (!file_exists($controllerFile)) {
            http_response_code(404);
            require_once APP_ROOT . '/404.php';
            exit;
        }
        
        require_once $controllerFile;
        
        $className = "App\\Core\\{$controller}";
        
        if (!class_exists($className)) {
            http_response_code(404);
            require_once APP_ROOT . '/404.php';
            exit;
        }
        
        $controllerInstance = new $className();
        
        if (!method_exists($controllerInstance, $action)) {
            http_response_code(404);
            require_once APP_ROOT . '/404.php';
            exit;
        }
        
        $reflection = new \ReflectionMethod($controllerInstance, $action);

        if ($reflection->getNumberOfParameters() > 0) {
            $controllerInstance->$action(...array_values($this->params));
            return;
        }

        $controllerInstance->$action();
    }
    
    /**
     * Check if route was found
     */
    public function hasRoute(): bool
    {
        return $this->routeFound;
    }
    
    /**
     * Get route parameters
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
