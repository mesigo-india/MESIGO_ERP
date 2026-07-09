<?php
declare(strict_types=1);

namespace App\Core;

class ProductController extends Controller
{
    private Product $products;
    private Validator $validator;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/Product.php';
        $this->products = new Product(Database::getInstance());
        $this->validator = new Validator();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('products.view');

        $search = trim((string) ($_GET['search'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));

        $this->render('products/index', [
            'title' => 'Products',
            'products' => $this->products->getAll($search, $status),
            'search' => $search,
            'status' => $status,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('products.create');

        $this->renderForm('Add Product', null, [], [[]], '/products');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('products.create');

        if (!$this->validateCsrf()) {
            Response::redirect('/products/create', 'Invalid security token.');
        }

        $data = $this->productDataFromRequest();
        $data['created_by'] = $this->currentUserId();

        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            Response::redirect('/products/create', $this->formatValidationErrors($errors));
        }

        if ($this->products->findByCode($data['product_code'])) {
            Response::redirect('/products/create', 'Product code already exists.');
        }

        $id = $this->products->create($data);
        $this->logger->info('Product created', ['product_id' => $id]);
        Response::redirect('/products', 'Product created successfully.');
    }

    public function edit(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('products.update');

        $product = $this->products->findById((int) $id);
        if (!$product) {
            Response::redirect('/products', 'Product not found.');
        }

        $this->renderForm(
            'Edit Product',
            $product,
            $this->products->decodeMeta($product['description'] ?? null),
            $this->products->getPackaging((int) $id) ?: [[]],
            '/products/' . (int) $id
        );
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('products.update');

        if (!$this->validateCsrf()) {
            Response::redirect('/products/' . (int) $id . '/edit', 'Invalid security token.');
        }

        $product = $this->products->findById((int) $id);
        if (!$product) {
            Response::redirect('/products', 'Product not found.');
        }

        $data = $this->productDataFromRequest();
        $data['updated_by'] = $this->currentUserId();

        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            Response::redirect('/products/' . (int) $id . '/edit', $this->formatValidationErrors($errors));
        }

        $existing = $this->products->findByCode($data['product_code']);
        if ($existing && (int) $existing['id'] !== (int) $id) {
            Response::redirect('/products/' . (int) $id . '/edit', 'Product code already exists.');
        }

        $this->products->update((int) $id, $data);
        $this->logger->info('Product updated', ['product_id' => (int) $id]);
        Response::redirect('/products', 'Product updated successfully.');
    }

    public function delete(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('products.delete');

        if (!$this->validateCsrf()) {
            Response::redirect('/products', 'Invalid security token.');
        }

        $this->products->delete((int) $id, $this->currentUserId());
        $this->logger->warning('Product disabled', ['product_id' => (int) $id]);
        Response::redirect('/products', 'Product disabled successfully.');
    }

    private function renderForm(string $title, ?array $product, array $meta, array $packaging, string $action): void
    {
        $this->render('products/form', [
            'title' => $title,
            'product' => $product,
            'meta' => $meta,
            'packaging' => $packaging,
            'categories' => $this->products->productCategories(),
            'grades' => $this->products->productGrades(),
            'origins' => $this->products->productOrigins(),
            'hsCodes' => $this->products->hsCodes(),
            'packingTypes' => $this->products->packingTypes(),
            'units' => $this->products->units(),
            'action' => $action,
        ]);
    }

    private function productDataFromRequest(): array
    {
        return [
            'product_code' => trim((string) ($_POST['product_code'] ?? '')),
            'name' => trim((string) ($_POST['name'] ?? '')),
            'category_id' => (int) ($_POST['category_id'] ?? 0),
            'hsn_code' => trim((string) ($_POST['hsn_code'] ?? '')),
            'unit_id' => (int) ($_POST['unit_id'] ?? 0),
            'packing_type_id' => (int) ($_POST['packing_type_id'] ?? 0),
            'status' => (int) ($_POST['status'] ?? 1),
            'meta' => [
                'description_text' => trim((string) ($_POST['description_text'] ?? '')),
                'grade_id' => (int) ($_POST['grade_id'] ?? 0),
                'origin_id' => (int) ($_POST['origin_id'] ?? 0),
                'gst_rate' => trim((string) ($_POST['gst_rate'] ?? '')),
                'gst_type' => trim((string) ($_POST['gst_type'] ?? '')),
            ],
            'packaging' => $this->packagingFromRequest(),
        ];
    }

    private function packagingFromRequest(): array
    {
        $rows = [];
        foreach ($_POST['package_packing_type_id'] ?? [] as $index => $packingTypeId) {
            $rows[] = [
                'packing_type_id' => (int) $packingTypeId,
                'unit_id' => (int) ($_POST['package_unit_id'][$index] ?? 0),
                'quantity_per_pack' => trim((string) ($_POST['quantity_per_pack'][$index] ?? '0')),
            ];
        }

        return $rows;
    }

    private function validateProduct(array $data): array
    {
        return $this->validator->validate($data, [
            'product_code' => 'required|max:50',
            'name' => 'required|max:255',
            'hsn_code' => 'max:20',
        ]);
    }

    private function formatValidationErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $messages[] = $error;
            }
        }

        return implode(' ', $messages);
    }

    private function currentUserId(): ?int
    {
        $user = $this->auth->user();
        return $user ? (int) $user['id'] : null;
    }
}