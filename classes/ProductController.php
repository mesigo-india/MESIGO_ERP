<?php
declare(strict_types=1);

namespace App\Core;

class ProductController extends Controller
{
    private Product $products;

    public function __construct()
    {
        parent::__construct();
        require_once APP_ROOT . '/classes/Product.php';
        $this->products = new Product(Database::getInstance());
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->requirePermission('products.view');

        $search = trim((string)($_GET['search'] ?? ''));
        $status = trim((string)($_GET['status'] ?? ''));
        $filters = [
            'category_id' => $_GET['category_id'] ?? '',
            'country_of_origin' => $_GET['country_of_origin'] ?? ''
        ];

        $sort        = $_GET['sort']        ?? 'name';
        $dir         = strtoupper($_GET['dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $page        = max(1, (int)($_GET['page'] ?? 1));
        $limit       = 50;
        $offset      = ($page - 1) * $limit;

        // ── Export: CSV / PDF / Print ───────────────────────────────────────────────
        $exportType = $_GET['export'] ?? '';
        if (in_array($exportType, ['csv', 'pdf', 'print'])) {
            $exportProducts = $this->products->getAll($search, $status, 10000, 0, $filters);
            $this->handleExportData($exportType, 'Product Master', 'Products', $exportProducts, [
                'Product Code'      => 'product_code',
                'Name'              => 'name',
                'HS Code'           => 'hsn_code',
                'Category'          => 'category_name',
                'Origin'            => 'country_name', // Updated to country_name in next step
                'Purchase Price'    => 'purchase_price',
                'Selling Price'     => 'selling_price',
                'Currency'          => 'default_currency',
                'Opening Stock'     => 'opening_stock',
                'Status'            => function($p) { return ($p['status'] ?? 1) == 1 ? 'Active' : 'Inactive'; }
            ]);
        }

        $list = $this->products->getAll($search, $status, $limit, $offset, $filters);
        $total = $this->products->getTotalCount($search, $status, $filters);
        $totalPages = ceil($total / $limit) ?: 1;

        $stats = $this->products->getDashboardStats();

        $this->render('products/index', [
            'title'      => 'Export Product Master',
            'products'   => $list,
            'search'     => $search,
            'status'     => $status,
            'filters'    => $filters,
            'stats'      => $stats,
            'sort'       => $sort,
            'dir'        => $dir,
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalRows'  => $total,
            'categories' => $this->products->productCategories(),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->requirePermission('products.create');
        Session::generateCsrfToken();

        $product = [];
        if (!empty($_GET['duplicate_from'])) {
            $src = $this->products->findById((int)$_GET['duplicate_from']);
            if ($src) {
                $product = $src;
                $product['product_code'] = 'COPY-' . $src['product_code'];
            }
        } else {
            $product['product_code'] = $this->products->getNextProductCode();
        }

        $this->renderForm(empty($_GET['duplicate_from']) ? 'Add Product' : 'Duplicate Product', $product, [], '/products');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->requirePermission('products.create');

        if (isset($_POST['ajax_action'])) {
            if ($_POST['ajax_action'] === 'add_city') {
                $this->ajaxAddCity();
            } elseif ($_POST['ajax_action'] === 'get_cities') {
                $this->ajaxGetCities();
            }
            return;
        }

        if (!$this->validateCsrf()) {
            $_SESSION['product_form_data'] = $_POST;
            Response::redirect('/products/create', 'Invalid security token.');
        }

        $data = $this->productDataFromRequest();
        $data['created_by'] = $this->currentUserId();

        $errors = $this->validateProduct($data);
        if (!empty($errors)) {
            $_SESSION['product_form_data'] = $_POST;
            $_SESSION['product_form_errors'] = $errors;
            Response::redirect('/products/create', 'Please correct the highlighted errors.');
        }

        if ($this->products->existsByCode($data['product_code'])) {
            $_SESSION['product_form_data'] = $_POST;
            $_SESSION['product_form_errors'] = ['product_code' => 'Product code already exists.'];
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
        Session::generateCsrfToken();

        $product = $this->products->findById((int) $id);
        if (!$product) {
            Response::redirect('/products', 'Product not found.');
        }

        $errors = $_SESSION['product_form_errors'] ?? [];
        unset($_SESSION['product_form_errors']);

        if (isset($_SESSION['product_form_data'])) {
            $product = array_merge($product, $_SESSION['product_form_data']);
            unset($_SESSION['product_form_data']);
        }

        $this->renderForm('Edit Product', $product, $errors, '/products/' . (int) $id);
    }

    public function update(string $id): void
    {
        $this->requireLogin();
        $this->requirePermission('products.update');

        if (isset($_POST['ajax_action'])) {
            if ($_POST['ajax_action'] === 'add_city') {
                $this->ajaxAddCity();
            } elseif ($_POST['ajax_action'] === 'get_cities') {
                $this->ajaxGetCities();
            }
            return;
        }

        if (!$this->validateCsrf()) {
            $_SESSION['product_form_data'] = $_POST;
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
            $_SESSION['product_form_data'] = $_POST;
            $_SESSION['product_form_errors'] = $errors;
            Response::redirect('/products/' . (int) $id . '/edit', 'Please correct the highlighted errors.');
        }

        if ($this->products->existsByCode($data['product_code'], (int)$id)) {
            $_SESSION['product_form_data'] = $_POST;
            $_SESSION['product_form_errors'] = ['product_code' => 'Product code already exists.'];
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
        Response::redirect('/products', 'Product deleted successfully.');
    }

    private function renderForm(string $title, array $product, array $errors, string $action): void
    {
        $db = Database::getInstance();
        $this->render('products/form', [
            'title' => $title,
            'product' => $product,
            'errors' => $errors,
            'action' => $action,
            'categories' => $this->products->productCategories(),
            'origins' => $this->products->productOrigins(),
            'packingTypes' => $this->products->packingTypes(),
            'units' => $this->products->units(),
            'currencies' => $this->getMasterData('currencies', 'code'),
            'incoterms' => $this->getMasterData('incoterms', 'code'),
            'countries' => $db->query('SELECT id, name FROM countries ORDER BY name ASC')->fetchAll(\PDO::FETCH_ASSOC),
            'states'    => $db->query('SELECT id, country_id, name FROM states ORDER BY name ASC')->fetchAll(\PDO::FETCH_ASSOC),
            'cities'    => $db->query('SELECT id, name FROM cities ORDER BY name ASC')->fetchAll(\PDO::FETCH_ASSOC),
        ]);
    }

    private function getMasterData(string $table, string $orderBy): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM {$table} WHERE status != 0 ORDER BY {$orderBy} ASC");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function productDataFromRequest(): array
    {
        $d = $_POST;
        foreach (['product_code', 'name', 'category_id', 'hsn_code', 'unit_id', 'packing_type_id', 'country_of_origin', 'country_id', 'state_id', 'city_id'] as $k) {
            $d[$k] = trim((string)($d[$k] ?? ''));
        }
        return $d;
    }

    private function validateProduct(array $data): array
    {
        $errors = [];
        
        if (empty($data['product_code'])) $errors['product_code'] = 'Product Code is required.';
        if (empty($data['name'])) $errors['name'] = 'Product Name is required.';
        if (empty($data['category_id'])) $errors['category_id'] = 'Category is required.';
        if (empty($data['hsn_code'])) $errors['hsn_code'] = 'HS Code is required.';
        if (empty($data['unit_id'])) $errors['unit_id'] = 'Primary Unit is required.';
        if (empty($data['country_of_origin'])) $errors['country_of_origin'] = 'Origin is required.';

        if (!empty($data['purchase_price']) && !empty($data['selling_price'])) {
            if ((float)$data['purchase_price'] > (float)$data['selling_price']) {
                $errors['purchase_price'] = 'Purchase Price cannot exceed Selling Price.';
            }
        }
        
        if (!empty($data['opening_stock']) && (float)$data['opening_stock'] < 0) {
            $errors['opening_stock'] = 'Opening Stock cannot be negative.';
        }

        return $errors;
    }




    private function ajaxAddCity(): void
    {
        header('Content-Type: application/json');
        
        $stateId = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : 0;
        $cityName = !empty($_POST['city_name']) ? trim($_POST['city_name']) : '';
        
        if (empty($stateId) || empty($cityName)) {
            echo json_encode(['success' => false, 'message' => 'State ID and City Name are required.']);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT id FROM cities WHERE state_id = ? AND name = ?');
            $stmt->execute([$stateId, $cityName]);
            $existing = $stmt->fetchColumn();
            
            if ($existing) {
                echo json_encode([
                    'success' => true,
                    'city' => [
                        'id' => (int)$existing,
                        'state_id' => $stateId,
                        'name' => $cityName
                    ]
                ]);
                return;
            }
            
            $stmt = $db->prepare('INSERT INTO cities (state_id, name, status) VALUES (?, ?, 1)');
            $stmt->execute([$stateId, $cityName]);
            $cityId = (int)$db->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'city' => [
                    'id' => $cityId,
                    'state_id' => $stateId,
                    'name' => $cityName
                ]
            ]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function ajaxGetCities(): void
    {
        header('Content-Type: application/json');
        
        $stateId = !empty($_POST['state_id']) ? (int)$_POST['state_id'] : 0;
        
        if (empty($stateId)) {
            echo json_encode(['success' => true, 'cities' => []]);
            return;
        }
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare('SELECT id, name FROM cities WHERE state_id = ? ORDER BY name ASC');
            $stmt->execute([$stateId]);
            $cities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'cities' => $cities]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}