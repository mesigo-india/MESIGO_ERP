<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Exception;

/**
 * Authentication class
 */
class Auth
{
    private PDO $db;
    private string $table = 'users';
    private int $maxLoginAttempts = 5;
    private int $lockoutSeconds = 900;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Authenticate user
     */
    public function authenticate(string $username, string $password): ?array
    {
        if ($this->isRateLimited()) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name, r.permissions 
            FROM {$this->table} u 
            LEFT JOIN roles r ON u.role_id = r.id 
            WHERE (u.username = :username OR u.email = :email) 
            AND u.status = 1 
            AND u.deleted_at IS NULL
        ");
        
        $stmt->execute(['username' => $username, 'email' => $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $this->logFailedLogin(null);
            return null;
        }
        
        if (!password_verify($password, $user['password'])) {
            $this->logFailedLogin($user['id']);
            return null;
        }
        
        $this->logSuccessfulLogin($user['id']);
        
        return [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role_id' => $user['role_id'],
            'role_name' => $user['role_name'],
            'permissions' => json_decode($user['permissions'] ?? '[]', true) ?: [],
        ];
    }

    /**
     * Check if the current IP is rate limited for login attempts
     */
    public function isRateLimited(): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM login_logs
            WHERE status = 'failed'
            AND ip_address = :ip
            AND created_at >= :lockout_time
        ");

        $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? '', PDO::PARAM_STR);
        $lockoutTime = date('Y-m-d H:i:s', time() - $this->lockoutSeconds);
        $stmt->bindValue(':lockout_time', $lockoutTime, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn() >= $this->maxLoginAttempts;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn(): bool
    {
        return Session::has('user_id') && Session::has('authenticated');
    }
    
    /**
     * Get current user
     */
    public function user(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => Session::get('user_id'),
            'username' => Session::get('username'),
            'email' => Session::get('email'),
            'role_id' => Session::get('role_id'),
            'permissions' => Session::get('permissions', []),
        ];
    }
    
    /**
     * Get current user ID
     */
    public function id(): ?int
    {
        return $this->isLoggedIn() ? (int)Session::get('user_id') : null;
    }
    
    /**
     * Login user
     */
    public function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        Session::set('email', $user['email']);
        Session::set('role_id', $user['role_id']);
        Session::set('permissions', $user['permissions']);
        Session::set('authenticated', true);
    }
    
    /**
     * Logout user
     */
    public function logout(): void
    {
        Session::remove('user_id');
        Session::remove('username');
        Session::remove('email');
        Session::remove('role_id');
        Session::remove('permissions');
        Session::remove('authenticated');
        Session::destroy();
    }
    
    /**
     * Check permission
     */
    public function can(string $permission): bool
    {
        $permissions = Session::get('permissions', []);
        return in_array('all', $permissions, true) || in_array($permission, $permissions, true);
    }
    
    /**
     * Log successful login
     */
    private function logSuccessfulLogin(int $userId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO login_logs (user_id, status, ip_address, user_agent, created_at) 
            VALUES (:user_id, 'success', :ip, :agent, NOW())
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
    }
    
    /**
     * Log failed login
     */
    private function logFailedLogin(?int $userId): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO login_logs (user_id, status, ip_address, user_agent, created_at) 
            VALUES (:user_id, 'failed', :ip, :agent, NOW())
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
    }
}