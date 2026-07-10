<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Session management class
 */
class Session
{
    private static bool $started = false;
    
    /**
     * Start session with secure configuration
     */
    public static function start(array $config = []): void
    {
        if (self::$started) {
            return;
        }
        
        $name = $config['name'] ?? 'MESIGO_SESSID';
        $lifetime = $config['lifetime'] ?? 10800;
        $cookieLifetime = $config['cookie_lifetime'] ?? 0;
        $cookiePath = $config['cookie_path'] ?? '/';
        $cookieSecure = $config['cookie_secure'] ?? true;
        $cookieHttpOnly = $config['cookie_httponly'] ?? true;
        $cookieSameSite = $config['cookie_samesite'] ?? 'Strict';
        $useStrictMode = $config['use_strict_mode'] ?? true;
        
        session_name($name);
        ini_set('session.cookie_lifetime', (string) $cookieLifetime);
        ini_set('session.gc_maxlifetime', (string) $lifetime);
        ini_set('session.cookie_secure', $cookieSecure ? '1' : '0');
        ini_set('session.cookie_httponly', $cookieHttpOnly ? '1' : '0');
        ini_set('session.cookie_samesite', $cookieSameSite);
        
        if ($useStrictMode) {
            ini_set('session.use_strict_mode', '1');
        }
        
        session_start();
        self::$started = true;
        
        // Regenerate session ID periodically
        if (!isset($_SESSION['_last_regeneration'])) {
            $_SESSION['_last_regeneration'] = time();
        } elseif (time() - $_SESSION['_last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = time();
        }
    }
    
    /**
     * Set session value
     */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session has key
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Regenerate session ID to prevent session fixation
     */
    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = time();
        }
    }
    
    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        self::$started = false;
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Set a flash message
     */
    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
}