<?php
declare(strict_types=1);

namespace App\Core;

class RoleController extends Controller
{
    private Role $roles;
    private Permission $permissions;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/Role.php';
        require_once APP_ROOT . '/classes/Permission.php';
        $db = Database::getInstance();
        $this->roles = new Role($db);
        $this->permissions = new Permission($db);
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('roles.view');
        $this->render('roles/index', [
            'title' => 'Roles',
            'roles' => $this->roles->getAll(),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('roles.create');
        $this->render('roles/form', [
            'title' => 'Create Role',
            'role' => null,
            'permissions' => $this->permissions->getAll(),
            'selectedPermissions' => [],
            'action' => '/roles',
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('roles.create');

        if (!$this->validateCsrf()) {
            Response::redirect('/roles/create', 'Invalid security token.');
        }

        $data = $this->roleDataFromRequest();
        if ($data['name'] === '') {
            Response::redirect('/roles/create', 'Role name is required.');
        }

        $this->roles->create($data);
        $this->logger->info('Role created', ['name' => $data['name']]);
        Response::redirect('/roles', 'Role created successfully.');
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('roles.update');

        $role = $this->roles->findById((int) $id);
        if (!$role) {
            Response::redirect('/roles', 'Role not found.');
        }

        $this->render('roles/form', [
            'title' => 'Edit Role',
            'role' => $role,
            'permissions' => $this->permissions->getAll(),
            'selectedPermissions' => json_decode($role['permissions'] ?? '[]', true) ?: [],
            'action' => '/roles/' . (int) $id,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('roles.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/roles/' . (int) $id . '/edit', 'Invalid security token.');
        }

        $data = $this->roleDataFromRequest();
        if ($data['name'] === '') {
            Response::redirect('/roles/' . (int) $id . '/edit', 'Role name is required.');
        }

        $this->roles->update((int) $id, $data);
        $this->logger->info('Role updated', ['role_id' => (int) $id]);
        Response::redirect('/roles', 'Role updated successfully.');
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('roles.delete');

        if (!$this->validateCsrf()) {
            Response::redirect('/roles', 'Invalid security token.');
        }

        $this->roles->delete((int) $id);
        $this->logger->warning('Role disabled', ['role_id' => (int) $id]);
        Response::redirect('/roles', 'Role disabled successfully.');
    }

    private function roleDataFromRequest(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'display_name' => trim((string) ($_POST['display_name'] ?? '')),
            'permissions' => $_POST['permissions'] ?? [],
            'status' => (int) ($_POST['status'] ?? 1),
        ];
    }
}