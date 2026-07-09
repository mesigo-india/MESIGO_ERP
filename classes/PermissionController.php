<?php
declare(strict_types=1);

namespace App\Core;

class PermissionController extends Controller
{
    private Permission $permissions;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/Permission.php';
        $this->permissions = new Permission(Database::getInstance());
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('permissions.view');
        $this->render('permissions/index', [
            'title' => 'Permissions',
            'permissions' => $this->permissions->getAll(),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('permissions.create');
        $this->render('permissions/form', [
            'title' => 'Create Permission',
            'permission' => null,
            'action' => '/permissions',
        ]);
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('permissions.create');

        if (!$this->validateCsrf()) {
            Response::redirect('/permissions/create', 'Invalid security token.');
        }

        $data = $this->permissionDataFromRequest();
        if ($data['name'] === '') {
            Response::redirect('/permissions/create', 'Permission name is required.');
        }

        $this->permissions->create($data);
        $this->logger->info('Permission created', ['name' => $data['name']]);
        Response::redirect('/permissions', 'Permission created successfully.');
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('permissions.update');

        $permission = $this->permissions->findById((int) $id);
        if (!$permission) {
            Response::redirect('/permissions', 'Permission not found.');
        }

        $this->render('permissions/form', [
            'title' => 'Edit Permission',
            'permission' => $permission,
            'action' => '/permissions/' . (int) $id,
        ]);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('permissions.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/permissions/' . (int) $id . '/edit', 'Invalid security token.');
        }

        $data = $this->permissionDataFromRequest();
        if ($data['name'] === '') {
            Response::redirect('/permissions/' . (int) $id . '/edit', 'Permission name is required.');
        }

        $this->permissions->update((int) $id, $data);
        $this->logger->info('Permission updated', ['permission_id' => (int) $id]);
        Response::redirect('/permissions', 'Permission updated successfully.');
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('permissions.delete');

        if (!$this->validateCsrf()) {
            Response::redirect('/permissions', 'Invalid security token.');
        }

        $this->permissions->delete((int) $id);
        $this->logger->warning('Permission disabled', ['permission_id' => (int) $id]);
        Response::redirect('/permissions', 'Permission disabled successfully.');
    }

    private function permissionDataFromRequest(): array
    {
        return [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'display_name' => trim((string) ($_POST['display_name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'module' => trim((string) ($_POST['module'] ?? '')),
            'status' => (int) ($_POST['status'] ?? 1),
        ];
    }
}