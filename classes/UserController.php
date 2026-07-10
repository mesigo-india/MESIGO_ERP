<?php
declare(strict_types=1);

namespace App\Core;

class UserController extends Controller
{
    private \PDO $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('users.view');

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (int) $_GET['status'] : null;

        $where = ['u.deleted_at IS NULL'];
        $params = [];

        if ($search !== '') {
            $where[] = '(u.username LIKE :search OR u.email LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }
        if ($status !== null) {
            $where[] = 'u.status = :status';
            $params[':status'] = $status;
        }

        $whereClause = implode(' AND ', $where);
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.phone,
                   u.status, u.last_login_at, u.created_at, r.display_name AS role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE {$whereClause}
            ORDER BY u.created_at DESC
        ");
        $stmt->execute($params);
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('users/index', [
            'title'  => 'User Management',
            'users'  => $users,
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function show(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('users.view');

        $user = $this->findUserById((int) $id);
        if (!$user) {
            Response::redirect('/users', 'User not found.');
        }

        $this->render('users/index', [
            'title' => 'User Detail',
            'users' => [$user],
            'search' => '',
            'status' => null,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('users.create');

        $roles = $this->getRoles();
        $this->render('users/form', [
            'title'  => 'Add User',
            'user'   => null,
            'roles'  => $roles,
            'action' => '/users',
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('users.create');

        if (!$this->validateCsrf()) {
            Response::redirect('/users/create', 'Invalid security token.');
        }

        $data = $this->userDataFromRequest();

        if ($data['username'] === '' || $data['email'] === '' || $data['password'] === '') {
            Response::redirect('/users/create', 'Username, email and password are required.');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::redirect('/users/create', 'Invalid email address.');
        }
        if ($this->usernameExists($data['username'])) {
            Response::redirect('/users/create', 'Username already exists.');
        }
        if ($this->emailExists($data['email'])) {
            Response::redirect('/users/create', 'Email already exists.');
        }

        $stmt = $this->db->prepare("
            INSERT INTO users (role_id, username, email, password, first_name, last_name, phone, status, created_at, updated_at)
            VALUES (:role_id, :username, :email, :password, :first_name, :last_name, :phone, :status, NOW(), NOW())
        ");
        $stmt->execute([
            ':role_id'    => $data['role_id'] ?: null,
            ':username'   => $data['username'],
            ':email'      => $data['email'],
            ':password'   => password_hash($data['password'], PASSWORD_BCRYPT),
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':phone'      => $data['phone'],
            ':status'     => $data['status'],
        ]);

        $this->logger->info('User created', ['username' => $data['username']]);
        Response::redirect('/users', 'User created successfully.');
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('users.update');

        $user = $this->findUserById((int) $id);
        if (!$user) {
            Response::redirect('/users', 'User not found.');
        }

        $this->render('users/form', [
            'title'  => 'Edit User',
            'user'   => $user,
            'roles'  => $this->getRoles(),
            'action' => '/users/' . (int) $id,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('users.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/users/' . (int) $id . '/edit', 'Invalid security token.');
        }

        $user = $this->findUserById((int) $id);
        if (!$user) {
            Response::redirect('/users', 'User not found.');
        }

        $data = $this->userDataFromRequest();

        if ($data['username'] === '' || $data['email'] === '') {
            Response::redirect('/users/' . (int) $id . '/edit', 'Username and email are required.');
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::redirect('/users/' . (int) $id . '/edit', 'Invalid email address.');
        }

        $existingUsername = $this->usernameExists($data['username'], (int) $id);
        if ($existingUsername) {
            Response::redirect('/users/' . (int) $id . '/edit', 'Username already taken.');
        }
        $existingEmail = $this->emailExists($data['email'], (int) $id);
        if ($existingEmail) {
            Response::redirect('/users/' . (int) $id . '/edit', 'Email already taken.');
        }

        $setClause = 'role_id = :role_id, username = :username, email = :email, first_name = :first_name, last_name = :last_name, phone = :phone, status = :status, updated_at = NOW()';
        $params = [
            ':role_id'    => $data['role_id'] ?: null,
            ':username'   => $data['username'],
            ':email'      => $data['email'],
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':phone'      => $data['phone'],
            ':status'     => $data['status'],
            ':id'         => (int) $id,
        ];

        if ($data['password'] !== '') {
            $setClause .= ', password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $stmt = $this->db->prepare("UPDATE users SET {$setClause} WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute($params);

        $this->logger->info('User updated', ['user_id' => (int) $id]);
        Response::redirect('/users', 'User updated successfully.');
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('users.delete');

        if (!$this->validateCsrf()) {
            Response::redirect('/users', 'Invalid security token.');
        }

        // Prevent self-deletion
        $currentUser = $this->auth->user();
        if ($currentUser && (int) $currentUser['id'] === (int) $id) {
            Response::redirect('/users', 'You cannot delete your own account.');
        }

        $stmt = $this->db->prepare("UPDATE users SET deleted_at = NOW(), status = 0 WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => (int) $id]);

        $this->logger->warning('User deleted', ['user_id' => (int) $id]);
        Response::redirect('/users', 'User deleted successfully.');
    }

    private function findUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.phone,
                   u.role_id, u.status, u.last_login_at, u.created_at, r.display_name AS role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = :id AND u.deleted_at IS NULL
        ");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function getRoles(): array
    {
        $stmt = $this->db->query("SELECT id, name, display_name FROM roles WHERE status = 1 ORDER BY display_name");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function usernameExists(string $username, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':username' => $username, ':id' => $excludeId]);
        return (bool) $stmt->fetch();
    }

    private function emailExists(string $email, int $excludeId = 0): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email AND id != :id AND deleted_at IS NULL");
        $stmt->execute([':email' => $email, ':id' => $excludeId]);
        return (bool) $stmt->fetch();
    }

    private function userDataFromRequest(): array
    {
        return [
            'role_id'    => (int) ($_POST['role_id'] ?? 0),
            'username'   => trim((string) ($_POST['username'] ?? '')),
            'email'      => trim((string) ($_POST['email'] ?? '')),
            'password'   => (string) ($_POST['password'] ?? ''),
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name'  => trim((string) ($_POST['last_name'] ?? '')),
            'phone'      => trim((string) ($_POST['phone'] ?? '')),
            'status'     => (int) ($_POST['status'] ?? 1),
        ];
    }

    public function showProfile(): void
    {
        $this->requireLogin();
        $userId = $this->auth->id();

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            Response::redirect('/dashboard', 'User profile not found.');
        }

        $this->render('auth/profile', [
            'title' => 'My Profile',
            'user'  => $user,
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireLogin();
        if (!$this->validateCsrf()) {
            Response::redirect('/profile', 'Invalid security token.');
        }

        $userId = $this->auth->id();
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$userId]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$existing) {
            Response::redirect('/dashboard', 'User profile not found.');
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $first_name = trim((string) ($_POST['first_name'] ?? ''));
        $last_name = trim((string) ($_POST['last_name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $language = trim((string) ($_POST['language'] ?? 'en'));
        $timezone = trim((string) ($_POST['timezone'] ?? 'Asia/Kolkata'));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $first_name === '' || $last_name === '') {
            Response::redirect('/profile', 'First name, last name, and email are required.');
        }

        if ($this->emailExists($email, $userId)) {
            Response::redirect('/profile', 'This email is already in use by another account.');
        }

        // Upload files
        $photoPath = $this->handleProfileUpload('photo_file', 'avatar', $existing['photo_path'] ?? null);
        $signaturePath = $this->handleProfileUpload('signature_file', 'user_sig', $existing['signature_path'] ?? null);

        $params = [
            ':email' => $email,
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':phone' => $phone,
            ':language' => $language,
            ':timezone' => $timezone,
            ':photo_path' => $photoPath,
            ':signature_path' => $signaturePath,
            ':id' => $userId,
        ];

        $passSql = "";
        if ($password !== '') {
            if (strlen($password) < 8) {
                Response::redirect('/profile', 'Password must be at least 8 characters long.');
            }
            $passSql = ", password = :password";
            $params[':password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $updateStmt = $this->db->prepare("
            UPDATE users 
            SET email = :email, first_name = :first_name, last_name = :last_name,
                phone = :phone, language = :language, timezone = :timezone,
                photo_path = :photo_path, signature_path = :signature_path
                {$passSql}
            WHERE id = :id
        ");

        if ($updateStmt->execute($params)) {
            Session::set('email', $email);
            $this->logger->info('User updated profile', ['user_id' => $userId]);
            Response::redirect('/profile', 'Profile updated successfully.');
        } else {
            Response::redirect('/profile', 'Failed to update profile.');
        }
    }

    private function handleProfileUpload(string $fieldName, string $prefix, ?string $existingPath = null): ?string
    {
        if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
            $uploadDir = APP_ROOT . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $originalName = basename($_FILES[$fieldName]['name']);
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = $prefix . '_' . uniqid() . '.' . $extension;
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetPath)) {
                if ($existingPath && file_exists($uploadDir . $existingPath)) {
                    @unlink($uploadDir . $existingPath);
                }
                return $fileName;
            }
        }
        return $existingPath;
    }
}
