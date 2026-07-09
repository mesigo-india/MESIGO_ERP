# MESIGO ERP - Coding Standards

## Version: 1.0
## Last Updated: 2026-07-07
## Project: MESIGO ERP Enterprise Edition

---

## 1. PHP STANDARDS

### 1.1 PHP Version Requirements
- **PHP 8.3** - Required for all features
- **No deprecated functions** - Strictly enforced
- **Strict typing** - `declare(strict_types=1)` mandatory
- **Type declarations** - Required for all functions

### 1.2 File Structure
```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Validator;
use App\Interfaces\ServiceInterface;
use PDO;
use Exception;

/**
 * Service class description
 * 
 * @package App\Services
 * @author MESIGO Development Team
 * @version 1.0
 */
class BuyerService implements ServiceInterface
{
    // Class implementation
}
```

### 1.3 Class Structure
```php
<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use DateTime;
use Exception;

/**
 * Buyer model for managing buyer data
 * 
 * @property int $id
 * @property string $buyer_code
 * @property string $company_name
 * @property DateTime $created_at
 */
class Buyer
{
    private PDO $db;
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Get all active buyers
     * 
     * @param int $limit Maximum records to return
     * @param int $offset Offset for pagination
     * @return array Array of buyer records
     * @throws Exception If database error occurs
     */
    public function getAll(int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM buyers 
            WHERE status = 1 AND deleted_at IS NULL 
            ORDER BY company_name ASC 
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

---

## 2. NAMING CONVENTIONS

### 2.1 Variables
| Type | Convention | Example |
|------|------------|---------|
| Local variables | `camelCase` | `$buyerName`, `$totalAmount` |
| Class properties | `camelCase` | `$this->buyerName` |
| Constants | `UPPER_SNAKE_CASE` | `MAX_FILE_SIZE` |
| Configuration | `kebab-case` | `$config['db-host']` |

### 2.2 Functions/Methods
| Type | Convention | Example |
|------|------------|---------|
| Public methods | `camelCase` | `getBuyer()`, `saveInvoice()` |
| Private methods | `camelCase` | `validateData()`, `formatResponse()` |
| Static methods | `camelCase` | `createInstance()`, `getConfig()` |

### 2.3 Classes
| Type | Convention | Example |
|------|------------|---------|
| Controllers | `ModuleNameController` | `BuyerCrmController` |
| Models | `ModuleName` | `Buyer`, `Invoice` |
| Services | `ModuleNameService` | `BuyerService`, `InvoiceService` |
| Helpers | `ActionHelper` | `DateHelper`, `CurrencyHelper` |
| Interfaces | `ActionInterface` | `ServiceInterface` |

### 2.4 Files
| Type | Convention | Example |
|------|------------|---------|
| PHP files | `PascalCase.php` | `BuyerCrmController.php` |
| Config files | `snake_case.php` | `database.php`, `app.php` |
| View files | `snake_case.php` | `list.php`, `edit.php` |

---

## 3. CODE ORGANIZATION

### 3.1 Controller Structure
```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Validator;
use App\Models\Buyer;
use App\Services\BuyerService;

class BuyerCrmController
{
    private BuyerService $buyerService;
    private Validator $validator;
    
    public function __construct(BuyerService $buyerService, Validator $validator)
    {
        $this->buyerService = $buyerService;
        $this->validator = $validator;
    }
    
    /**
     * Display list of buyers
     */
    public function index(): void
    {
        $this->requirePermission('view_buyers');
        
        $buyers = $this->buyerService->getAll();
        $this->render('buyer_crm/list', ['buyers' => $buyers]);
    }
    
    /**
     * Create new buyer
     */
    public function create(): void
    {
        $this->requirePermission('create_buyers');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        }
        
        $this->render('buyer_crm/create');
    }
    
    /**
     * Handle form submission for creating buyer
     */
    private function handleCreate(): void
    {
        $data = $this->getPostData();
        $errors = $this->validator->validate($data, $this->getValidationRules());
        
        if (!empty($errors)) {
            $this->render('buyer_crm/create', ['errors' => $errors, 'data' => $data]);
            return;
        }
        
        $this->buyerService->create($data);
        $this->redirect('/buyer-crm', 'Buyer created successfully');
    }
}
```

### 3.2 Model Structure
```php
<?php
declare(strict_types=1);

namespace App\Models;

use PDO;
use DateTime;
use Exception;

class Buyer
{
    private PDO $db;
    
    // Table name constant
    private const TABLE = 'buyers';
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * Find buyer by ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM " . self::TABLE . " 
            WHERE id = :id AND status = 1 AND deleted_at IS NULL
        ");
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Create new buyer
     */
    public function create(array $data): int
    {
        $this->db->beginTransaction();
        
        try {
            $stmt = $this->db->prepare("
                INSERT INTO " . self::TABLE . " 
                (buyer_code, company_name, email, phone, status, created_at) 
                VALUES (:buyer_code, :company_name, :email, :phone, 1, NOW())
            ");
            
            $stmt->execute([
                'buyer_code' => $this->generateBuyerCode(),
                'company_name' => $data['company_name'],
                'email' => $data['email'],
                'phone' => $data['phone']
            ]);
            
            $id = (int) $this->db->lastInsertId();
            $this->db->commit();
            
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
```

### 3.3 Service Structure
```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Buyer;
use App\Core\Validator;
use App\Core\Logger;
use App\Core\EventDispatcher;
use Exception;

class BuyerService
{
    private Buyer $buyerModel;
    private Validator $validator;
    private Logger $logger;
    private EventDispatcher $events;
    
    public function __construct(
        Buyer $buyerModel, 
        Validator $validator, 
        Logger $logger,
        EventDispatcher $events
    ) {
        $this->buyerModel = $buyerModel;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->events = $events;
    }
    
    /**
     * Get all buyers with pagination
     */
    public function getAll(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->buyerModel->getAll($perPage, $offset);
    }
    
    /**
     * Create new buyer with validation
     */
    public function create(array $data): int
    {
        $this->validateBuyerData($data);
        
        $id = $this->buyerModel->create($data);
        
        $this->events->dispatch('buyer.created', [
            'buyer_id' => $id,
            'user_id' => $_SESSION['user_id'] ?? null
        ]);
        
        $this->logger->info('Buyer created', ['buyer_id' => $id]);
        
        return $id;
    }
    
    /**
     * Validate buyer data
     */
    private function validateBuyerData(array $data): void
    {
        $rules = [
            'company_name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:buyers,email',
            'phone' => 'required|phone'
        ];
        
        $this->validator->validate($data, $rules);
    }
}
```

---

## 4. SECURITY STANDARDS

### 4.1 Input Validation
```php
// Always validate input
$rules = [
    'email' => 'required|email|max:255',
    'password' => 'required|min:12|strong_password',
    'phone' => 'required|regex:/^[0-9]{10}$/'
];

$validator = new Validator();
$errors = $validator->validate($_POST, $rules);

if (!empty($errors)) {
    // Handle errors
}
```

### 4.2 Output Escaping
```php
// Always escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// For HTML content (trusted only)
echo $this->escapeHtml($content);

// For JavaScript context
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP);
```

### 4.3 Password Handling
```php
// Password hashing
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Password verification
if (password_verify($password, $user['password'])) {
    // Password correct
}

// Password strength check
if (strlen($password) < 12) {
    throw new Exception('Password must be at least 12 characters');
}
```

### 4.4 CSRF Protection
```php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Validate token
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    throw new Exception('CSRF token mismatch');
}
```

---

## 5. ERROR HANDLING

### 5.1 Exception Handling
```php
try {
    $result = $this->processData($data);
    $this->logger->info('Data processed', ['result' => $result]);
} catch (ValidationException $e) {
    $this->logger->warning('Validation failed', ['errors' => $e->getErrors()]);
    $this->jsonResponse(['status' => 'error', 'errors' => $e->getErrors()]);
} catch (DatabaseException $e) {
    $this->logger->error('Database error', ['error' => $e->getMessage()]);
    $this->jsonResponse(['status' => 'error', 'message' => 'System error occurred']);
} catch (Exception $e) {
    $this->logger->error('Unexpected error', ['error' => $e->getMessage()]);
    $this->jsonResponse(['status' => 'error', 'message' => 'An error occurred']);
}
```

### 5.2 Custom Exception Classes
```php
class ValidationException extends Exception
{
    private array $errors;
    
    public function __construct(array $errors, string $message = 'Validation failed')
    {
        $this->errors = $errors;
        parent::__construct($message);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}

class DatabaseException extends Exception
{
    // Database-specific exception
}

class AuthException extends Exception
{
    // Authentication-specific exception
}
```

---

## 6. LOGGING STANDARDS

### 6.1 Log Levels
| Level | Usage | Example |
|-------|-------|---------|
| EMERGENCY | System unusable | Database connection failed |
| ALERT | Immediate action | Security breach detected |
| CRITICAL | Critical error | Payment processing failed |
| ERROR | Runtime error | Invalid input data |
| WARNING | Warning condition | Deprecated function used |
| NOTICE | Normal but significant | User password changed |
| INFO | Informational | Record created |
| DEBUG | Debug information | SQL query executed |

### 6.2 Log Format
```php
// Standard log entry
$this->logger->info('Buyer created', [
    'user_id' => $_SESSION['user_id'],
    'buyer_id' => $buyerId,
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'timestamp' => date('Y-m-d H:i:s')
]);
```

### 6.3 Log Categories
- **auth.log** - Authentication events
- **error.log** - Application errors
- **audit.log** - Data changes
- **business.log** - Business operations
- **api.log** - API requests

---

## 7. DATABASE OPERATIONS

### 7.1 PDO Best Practices
```php
// Use prepared statements always
$stmt = $pdo->prepare("SELECT * FROM buyers WHERE id = :id");
$stmt->execute(['id' => $id]);

// Use transactions for related operations
$pdo->beginTransaction();
try {
    // Multiple operations
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}

// Use proper error mode
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
```

### 7.2 Query Building
```php
// Use query builder for complex queries
$query = "SELECT b.*, c.name as country_name 
          FROM buyers b 
          LEFT JOIN countries c ON b.country_id = c.id 
          WHERE b.status = :status";

$params = ['status' => 1];

// Add optional filters
if (!empty($search)) {
    $query .= " AND (b.company_name LIKE :search OR b.email LIKE :search)";
    $params['search'] = "%{$search}%";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
```

---

## 8. API STANDARDS

### 8.1 JSON Response Format
```php
// Success response
$this->jsonResponse([
    'status' => 'success',
    'message' => 'Operation completed',
    'data' => $result,
    'meta' => [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total
    ]
], 200);

// Error response
$this->jsonResponse([
    'status' => 'error',
    'message' => 'Validation failed',
    'errors' => [
        'email' => 'Invalid email format',
        'phone' => 'Phone is required'
    ]
], 422);
```

### 8.2 HTTP Status Codes
| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET, PUT |
| 201 | Created | Successful POST |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Permission denied |
| 404 | Not Found | Resource not found |
| 409 | Conflict | Duplicate resource |
| 422 | Unprocessable | Validation error |
| 429 | Too Many | Rate limit exceeded |
| 500 | Server Error | Internal error |

---

## 9. JAVASCRIPT STANDARDS

### 9.1 jQuery Best Practices
```javascript
// Use strict mode
(function($) {
    'use strict';
    
    // Cache selectors
    const $form = $('#buyer-form');
    const $submitBtn = $('#submit-btn');
    
    // Event delegation for dynamic content
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        deleteBuyer(id);
    });
    
    // AJAX with proper error handling
    function deleteBuyer(id) {
        $.ajax({
            url: `/api/v1/buyers/${id}`,
            method: 'DELETE',
            beforeSend: function() {
                $submitBtn.prop('disabled', true);
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message);
                    $(`#row-${id}`).remove();
                }
            },
            error: function() {
                toastr.error('Failed to delete buyer');
            },
            complete: function() {
                $submitBtn.prop('disabled', false);
            }
        });
    }
})(jQuery);
```

### 9.2 AJAX Standards
```javascript
// Standard AJAX wrapper
function ajaxRequest(options) {
    const defaults = {
        method: 'GET',
        timeout: 30000,
        dataType: 'json',
        beforeSend: function() {
            showLoading();
        },
        success: function(response) {
            hideLoading();
            handleResponse(response);
        },
        error: function(xhr) {
            hideLoading();
            handleError(xhr);
        }
    };
    
    return $.ajax($.extend(defaults, options));
}
```

---

## 10. CSS STANDARDS

### 10.1 CSS Organization
```css
/* Custom CSS file structure */

/* 1. Variables */
:root {
    --agri-primary: #198754;
    --agri-secondary: #28a745;
}

/* 2. Base styles */
body {
    font-family: system-ui, sans-serif;
}

/* 3. Layout components */
.sidebar {
    /* Sidebar styles */
}

/* 4. Page components */
.page-header {
    /* Page header styles */
}

/* 5. Utility classes */
.text-truncate {
    /* Truncate text */
}

/* 6. Print styles */
@media print {
    /* Print-specific styles */
}
```

### 10.2 CSS Naming
- **BEM methodology** - Block__Element--Modifier
- **No ID selectors** - Use classes only
- **No !important** - Use specificity instead
- **Mobile first** - Min-width media queries

---

## 11. FILE UPLOAD STANDARDS

### 11.1 Upload Validation
```php
// Validate file upload
$allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowedTypes)) {
    throw new ValidationException(['file' => 'Invalid file type']);
}

if ($file['size'] > $maxSize) {
    throw new ValidationException(['file' => 'File too large']);
}

// Verify file content
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($detectedType !== $file['type']) {
    throw new ValidationException(['file' => 'File type mismatch']);
}
```

### 11.2 Secure File Storage
```php
// Generate secure filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = bin2hex(random_bytes(16)) . '.' . $extension;

// Store outside web root
$uploadPath = '/var/www/uploads/' . $filename;
move_uploaded_file($file['tmp_name'], $uploadPath);

// Store only filename in database
$this->db->insert('documents', [
    'filename' => $filename,
    'original_name' => $file['name'],
    'mime_type' => $file['type'],
    'size' => $file['size']
]);
```

---

## 12. SESSION MANAGEMENT

### 12.1 Session Configuration
```php
// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // HTTPS only
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_name('MESIGO_SESSID');
session_start();

// Regenerate session ID on login
session_regenerate_id(true);
```

### 12.2 Session Data
```php
// Store minimal data in session
$_SESSION['user_id'] = $user['id'];
$_SESSION['role_id'] = $user['role_id'];
$_SESSION['permissions'] = $user['permissions']; // JSON array
$_SESSION['last_activity'] = time();

// Never store sensitive data
// ❌ $_SESSION['password'] = $password;
// ❌ $_SESSION['credit_card'] = $cardNumber;
```

---

## 13. CONFIGURATION STANDARDS

### 13.1 Config File Structure
```php
<?php
// config/app.php

return [
    'name' => 'MESIGO ERP',
    'version' => '1.0.0',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    
    'timezone' => 'Asia/Kolkata',
    'date_format' => 'd-m-Y',
    'datetime_format' => 'd-m-Y H:i:s',
    
    'pagination' => [
        'per_page' => 15,
        'max_per_page' => 100
    ],
    
    'upload' => [
        'max_size' => 10485760, // 10MB
        'allowed_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png']
    ]
];
```

### 13.2 Environment Variables
```
# .env file
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:random_key_here

DB_HOST=localhost
DB_PORT=3306
DB_NAME=mesigo_erp
DB_USER=app_user
DB_PASS=secure_password

MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USER=username
MAIL_PASS=password
```

---

## 14. TESTING STANDARDS

### 14.1 Unit Test Structure
```php
// tests/Unit/BuyerServiceTest.php
use PHPUnit\Framework\TestCase;

class BuyerServiceTest extends TestCase
{
    private BuyerService $service;
    private PDO $mockDb;
    
    protected function setUp(): void
    {
        $this->mockDb = $this->createMock(PDO::class);
        $this->service = new BuyerService($this->mockDb);
    }
    
    public function testCreateBuyerWithValidData(): void
    {
        $data = [
            'company_name' => 'Test Company',
            'email' => 'test@example.com',
            'phone' => '9876543210'
        ];
        
        $result = $this->service->create($data);
        
        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }
    
    public function testCreateBuyerWithInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        
        $data = [
            'company_name' => 'Test Company',
            'email' => 'invalid-email',
            'phone' => '9876543210'
        ];
        
        $this->service->create($data);
    }
}
```

---

## 15. DOCUMENTATION STANDARDS

### 15.1 PHPDoc Format
```php
/**
 * Create a new buyer record
 * 
 * Validates input data and creates buyer in database.
 * Also triggers buyer.created event and logs the action.
 * 
 * @param array $data Buyer data with keys: company_name, email, phone, address
 * @return int The ID of the created buyer
 * @throws ValidationException If validation fails
 * @throws DatabaseException If database error occurs
 * 
 * @example
 * $buyerId = $service->create([
 *     'company_name' => 'ABC Exports',
 *     'email' => 'contact@abcexports.com',
 *     'phone' => '9876543210'
 * ]);
 */
```

### 15.2 Inline Comments
```php
// Calculate total with GST
$subtotal = $quantity * $rate;
$gst = $subtotal * 0.18; // 18% GST
$total = $subtotal + $gst;

// Generate unique buyer code
// Format: BUY-YYYYMMDD-XXXX
$buyerCode = 'BUY-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
```

---

## 16. VERSION CONTROL STANDARDS

### 16.1 Git Commit Messages
```
feat: Add buyer creation functionality
fix: Resolve invoice calculation error
docs: Update API documentation
style: Format code according to standards
refactor: Extract validation to separate class
test: Add unit tests for buyer service
chore: Update dependencies
```

### 16.2 Branch Naming
- `feature/buyer-crm` - New feature
- `fix/invoice-bug` - Bug fix
- `hotfix/critical-security` - Critical fix
- `release/v1.0.0` - Release branch
- `develop` - Development branch
- `main` - Production branch

---

*This document defines the coding standards for MESIGO ERP. All code must comply with these rules.*