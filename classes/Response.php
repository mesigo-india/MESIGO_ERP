<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Response class for handling HTTP responses
 */
class Response
{
    /**
     * Send JSON response
     */
    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send success JSON response
     */
    public static function success(string $message = '', array $data = [], array $meta = []): void
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];
        
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        
        self::json($response, 200);
    }
    
    /**
     * Send error JSON response
     */
    public static function error(string $message, array $errors = [], int $statusCode = 400): void
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ];
        
        self::json($response, $statusCode);
    }
    
    /**
     * Redirect to URL
     */
    public static function redirect(string $url, string $message = ''): void
    {
        if ($message) {
            $_SESSION['flash_message'] = $message;
        }
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Set flash message
     */
    public static function flash(string $message, string $type = 'success'): void
    {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    /**
     * Get flash message
     */
    public static function getFlash(): ?array
    {
        if (!isset($_SESSION['flash_message'])) {
            return null;
        }
        
        $flash = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'success',
        ];
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        return $flash;
    }
}