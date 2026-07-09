<?php
declare(strict_types=1);

namespace App\Core;

use Exception;

/**
 * Logger class for application logging
 */
class Logger
{
    private string $logPath;
    private string $dateFormat = 'Y-m-d H:i:s';
    
    public function __construct(string $logPath = null)
    {
        $this->logPath = $logPath ?? APP_ROOT . '/logs';
    }
    
    /**
     * Log info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Log error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }
    
    /**
     * Log debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * Write log entry
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date($this->dateFormat);
        $userId = $_SESSION['user_id'] ?? 'system';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $logEntry = sprintf(
            "[%s] [%s] [User: %s] [IP: %s] %s %s",
            $timestamp,
            $level,
            $userId,
            $ipAddress,
            $message,
            !empty($context) ? json_encode($context) : ''
        );
        
        $logFile = $this->logPath . '/' . strtolower($level) . '.log';
        file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log to specific file
     */
    public function logTo(string $file, string $message, array $context = []): void
    {
        $timestamp = date($this->dateFormat);
        $logEntry = sprintf("[%s] %s %s", $timestamp, $message, json_encode($context));
        
        $logFile = $this->logPath . '/' . $file;
        file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}