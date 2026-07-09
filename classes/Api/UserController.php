<?php
declare(strict_types=1);

namespace App\Core\Api;

use App\Core\Database;
use App\Core\Auth;
use App\Core\Response;
use PDO;

class UserController
{
    private PDO $db;
    private Auth $auth;

    public function __construct()
    {
        require_once APP_ROOT . '/classes/Database.php';
        require_once APP_ROOT . '/classes/Auth.php';
        require_once APP_ROOT . '/classes/Response.php';
        $this->db   = Database::getInstance();
        $this->auth = new Auth($this->db);
    }

    public function index(): void
    {
        $this->requireApiAuth();

        $stmt = $this->db->query("
            SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.phone,
                   u.status, u.last_login_at, u.created_at, r.display_name AS role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.created_at DESC
        ");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->jsonResponse(['data' => $users, 'total' => count($users)]);
    }

    public function show(string $id): void
    {
        $this->requireApiAuth();

        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.phone,
                   u.role_id, u.status, u.last_login_at, u.created_at, r.display_name AS role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id AND u.deleted_at IS NULL
        ");
        $stmt->execute([':id' => (int) $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            $this->jsonResponse(['error' => 'User not found'], 404);
            return;
        }

        $this->jsonResponse(['data' => $user]);
    }

    public function store(): void
    {
        $this->requireApiAuth();

        $body = $this->getJsonBody();
        $username   = trim((string) ($body['username'] ?? ''));
        $email      = trim((string) ($body['email'] ?? ''));
        $password   = (string) ($body['password'] ?? '');
        $roleId     = (int) ($body['role_id'] ?? 0);
        $firstName  = trim((string) ($body['first_name'] ?? ''));
        $lastName   = trim((string) ($body['last_name'] ?? ''));
        $phone      = trim((string) ($body['phone'] ?? ''));
        $status     = (int) ($body['status'] ?? 1);

        if ($username === '' || $email === '' || $password === '') {
            $this->jsonResponse(['error' => 'username, email and password are required'], 422);
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO users (role_id, username, email, password, first_name, last_name, phone, status, created_at, updated_at)
            VALUES (:role_id, :username, :email, :password, :first_name, :last_name, :phone, :status, NOW(), NOW())
        ");
        $stmt->execute([
            ':role_id'    => $roleId ?: null,
            ':username'   => $username,
            ':email'      => $email,
            ':password'   => password_hash($password, PASSWORD_BCRYPT),
            ':first_name' => $firstName,
            ':last_name'  => $lastName,
            ':phone'      => $phone,
            ':status'     => $status,
        ]);

        $this->jsonResponse(['message' => 'User created', 'id' => (int) $this->db->lastInsertId()], 201);
    }

    public function update(string $id): void
    {
        $this->requireApiAuth();

        $stmt = $this->db->prepare("SELECT id FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => (int) $id]);
        if (!$stmt->fetch()) {
            $this->jsonResponse(['error' => 'User not found'], 404);
            return;
        }

        $body       = $this->getJsonBody();
        $username   = trim((string) ($body['username'] ?? ''));
        $email      = trim((string) ($body['email'] ?? ''));
        $password   = (string) ($body['password'] ?? '');
        $roleId     = isset($body['role_id']) ? (int) $body['role_id'] : null;
        $firstName  = trim((string) ($body['first_name'] ?? ''));
        $lastName   = trim((string) ($body['last_name'] ?? ''));
        $phone      = trim((string) ($body['phone'] ?? ''));
        $status     = isset($body['status']) ? (int) $body['status'] : null;

        $setClauses = [];
        $params     = [':id' => (int) $id];

        if ($username !== '')         { $setClauses[] = 'username = :username';     $params[':username']   = $username; }
        if ($email !== '')            { $setClauses[] = 'email = :email';           $params[':email']      = $email; }
        if ($password !== '')         { $setClauses[] = 'password = :password';     $params[':password']   = password_hash($password, PASSWORD_BCRYPT); }
        if ($roleId !== null)         { $setClauses[] = 'role_id = :role_id';       $params[':role_id']    = $roleId ?: null; }
        if ($firstName !== '')        { $setClauses[] = 'first_name = :first_name'; $params[':first_name'] = $firstName; }
        if ($lastName !== '')         { $setClauses[] = 'last_name = :last_name';   $params[':last_name']  = $lastName; }
        if ($phone !== '')            { $setClauses[] = 'phone = :phone';           $params[':phone']      = $phone; }
        if ($status !== null)         { $setClauses[] = 'status = :status';         $params[':status']     = $status; }

        if (empty($setClauses)) {
            $this->jsonResponse(['message' => 'Nothing to update']);
            return;
        }

        $setClauses[] = 'updated_at = NOW()';
        $sql = 'UPDATE users SET ' . implode(', ', $setClauses) . ' WHERE id = :id AND deleted_at IS NULL';
        $this->db->prepare($sql)->execute($params);

        $this->jsonResponse(['message' => 'User updated']);
    }

    public function delete(string $id): void
    {
        $this->requireApiAuth();

        $stmt = $this->db->prepare("UPDATE users SET deleted_at = NOW(), status = 0 WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => (int) $id]);

        $this->jsonResponse(['message' => 'User deleted']);
    }

    private function requireApiAuth(): void
    {
        if (!$this->auth->isLoggedIn()) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }

    private function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw ?: '{}', true) ?: [];
    }

    private function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
