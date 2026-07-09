<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use Exception;

/**
 * Database class for PDO connection management
 */
class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];
    
    private function __construct()
    {
        // Private constructor for singleton
    }
    
    private function __clone()
    {
        // Prevent cloning
    }
    
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
    
    /**
     * Get database instance
     */
    public static function getInstance(array $config = []): PDO
    {
        if (self::$instance === null) {
            self::$config = $config;
            self::$instance = self::connect();
        }
        
        return self::$instance;
    }
    
    /**
     * Create database connection
     */
    private static function connect(): PDO
    {
        $host = self::$config['host'] ?? 'localhost';
        $port = self::$config['port'] ?? 3306;
        $dbname = self::$config['name'] ?? self::$config['database'];
        $user = self::$config['user'] ?? self::$config['username'];
        $pass = self::$config['pass'] ?? self::$config['password'];
        $charset = self::$config['charset'] ?? 'utf8mb4';
        $options = self::$config['options'] ?? [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            $pdo->exec("SET time_zone = '+05:30'");
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool
    {
        return self::$instance->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public static function commit(): bool
    {
        return self::$instance->commit();
    }
    
    /**
     * Rollback transaction
     */
    public static function rollBack(): bool
    {
        return self::$instance->rollBack();
    }
    
    /**
     * Get last insert ID
     */
    public static function lastInsertId(): string
    {
        return self::$instance->lastInsertId();
    }
}