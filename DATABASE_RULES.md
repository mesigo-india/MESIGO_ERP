# MESIGO ERP - Database Rules and Standards

## Version: 1.0
## Last Updated: 2026-07-07
## Project: MESIGO ERP Enterprise Edition

---

## 1. DATABASE OVERVIEW

### 1.1 Database Engine
- **MySQL 8.0** - Required for JSON support, window functions, and CTEs
- **Character Set**: `utf8mb4` (supports all Unicode characters including emojis)
- **Collation**: `utf8mb4_unicode_ci` for case-insensitive comparisons
- **Storage Engine**: InnoDB (for transaction support and foreign keys)

### 1.2 Connection Standards
- **PDO with prepared statements** - Mandatory for all database operations
- **Connection pooling** - Implemented via persistent connections
- **Error mode**: `PDO::ERRMODE_EXCEPTION`
- **Default fetch mode**: `PDO::FETCH_ASSOC`
- **Transaction isolation**: `REPEATABLE READ`

---

## 2. NAMING CONVENTIONS

### 2.1 Table Naming
| Entity Type | Naming Pattern | Example |
|-------------|--------------|---------|
| Main tables | `module_name` (plural, snake_case) | `buyers`, `products`, `invoices` |
| Pivot tables | `{table1}_{table2}` (alphabetical) | `buyer_products`, `invoice_items` |
| Audit tables | `{table_name}_audit` | `invoices_audit` |
| Log tables | `{module}_log` | `auth_log`, `business_log` |
| Configuration | `config_*` | `config_settings`, `config_email` |

### 2.2 Column Naming
| Column Type | Naming Pattern | Example |
|-------------|--------------|---------|
| Primary Key | `id` | `id` |
| Foreign Key | `{table_name}_id` | `buyer_id`, `product_id` |
| Created timestamp | `created_at` | `created_at` |
| Updated timestamp | `updated_at` | `updated_at` |
| Deleted timestamp | `deleted_at` | `deleted_at` |
| Status | `status` | `status` |
| Code/Number | `{entity}_code` or `{entity}_number` | `buyer_code`, `invoice_number` |

### 2.3 Index Naming
| Index Type | Naming Pattern | Example |
|------------|--------------|---------|
| Primary Key | `PRIMARY` (auto-generated) | - |
| Foreign Key | `fk_{table}_{column}` | `fk_buyers_buyer_id` |
| Unique | `uk_{table}_{column}` | `uk_users_email` |
| Index | `idx_{table}_{column}` | `idx_products_category` |
| Composite | `idx_{table}_{col1}_{col2}` | `idx_invoices_buyer_status` |

---

## 3. TABLE STRUCTURE STANDARDS

### 3.1 Base Table Template
```sql
CREATE TABLE table_name (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    -- Business columns here
    status TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    PRIMARY KEY (id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_unicode_ci;
```

### 3.2 Status Values
| Status Value | Meaning | Usage |
|--------------|---------|-------|
| 0 | Inactive/Deleted | Soft delete |
| 1 | Active | Default active state |
| 2 | Draft | Work in progress |
| 3 | Pending | Awaiting action |
| 4 | Approved | Approved for next step |
| 5 | Rejected | Rejected/Invalid |
| 6 | Completed | Process completed |
| 7 | Cancelled | Cancelled by user |

---

## 4. DATA TYPES AND CONSTRAINTS

### 4.1 String Types
| Data Type | Usage | Max Length |
|-----------|-------|------------|
| `VARCHAR(n)` | Short text, names, codes | n = 255 max |
| `CHAR(n)` | Fixed length codes | n = 36 for UUIDs |
| `TEXT` | Long descriptions | 65,535 bytes |
| `MEDIUMTEXT` | Large text content | 16MB |
| `JSON` | Structured data | - |

### 4.2 Numeric Types
| Data Type | Usage | Range |
|-----------|-------|-------|
| `TINYINT` | Status, small enums | -128 to 127 |
| `SMALLINT` | Small counts, years | -32,768 to 32,767 |
| `MEDIUMINT` | Medium counts | -8,388,608 to 8,388,607 |
| `INT` | Standard IDs, counts | -2.1B to 2.1B |
| `BIGINT` | Large IDs, amounts | -9.2E18 to 9.2E18 |
| `DECIMAL(p,s)` | Monetary values | p=15, s=2 for currency |
| `FLOAT` | Non-precise decimals | - |

### 4.3 Date/Time Types
| Data Type | Usage | Format |
|-----------|-------|--------|
| `DATE` | Date only | YYYY-MM-DD |
| `TIME` | Time only | HH:MM:SS |
| `DATETIME` | Date and time | YYYY-MM-DD HH:MM:SS |
| `TIMESTAMP` | Audit timestamps | Auto-managed |

### 4.4 Binary Types
| Data Type | Usage |
|-----------|-------|
| `BLOB` | Binary data < 64KB |
| `MEDIUMBLOB` | Binary data < 16MB |
| `LONGBLOB` | Binary data < 4GB |

---

## 5. RELATIONSHIP RULES

### 5.1 One-to-Many Relationships
```sql
-- Parent table
CREATE TABLE buyers (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    -- ... other columns
    PRIMARY KEY (id)
);

-- Child table
CREATE TABLE inquiries (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    buyer_id BIGINT UNSIGNED NOT NULL,
    -- ... other columns
    PRIMARY KEY (id),
    CONSTRAINT fk_inquiries_buyer 
        FOREIGN KEY (buyer_id) 
        REFERENCES buyers(id) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
);
```

### 5.2 Many-to-Many Relationships
```sql
-- Pivot table
CREATE TABLE buyer_products (
    buyer_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (buyer_id, product_id),
    CONSTRAINT fk_buyer_products_buyer 
        FOREIGN KEY (buyer_id) 
        REFERENCES buyers(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_buyer_products_product 
        FOREIGN KEY (product_id) 
        REFERENCES products(id) 
        ON DELETE CASCADE
);
```

### 5.3 Self-Referencing Relationships
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    manager_id BIGINT UNSIGNED NULL,
    -- ... other columns
    PRIMARY KEY (id),
    CONSTRAINT fk_users_manager 
        FOREIGN KEY (manager_id) 
        REFERENCES users(id) 
        ON DELETE SET NULL
);
```

---

## 6. INDEXING STRATEGY

### 6.1 Required Indexes
Every table must have:
1. **Primary key index** (auto-created)
2. **Status index** for soft delete queries
3. **Created_at index** for date-based queries
4. **Foreign key indexes** for all FK columns

### 6.2 Performance Indexes
| Index Type | When to Use | Example |
|------------|-------------|---------|
| Single column | Frequently filtered column | `idx_products_category` |
| Composite | Multi-column filters | `idx_invoices_buyer_date` |
| Unique | Business unique constraints | `uk_buyers_gst` |
| Fulltext | Search operations | `ft_products_name` |

### 6.3 Index Optimization Rules
- **Avoid over-indexing** - Each index slows INSERT/UPDATE
- **Composite index order** - Most selective column first
- **Covering indexes** - Include all needed columns
- **Prefix indexes** - For long VARCHAR columns (max 1000 bytes)

---

## 7. QUERY STANDARDS

### 7.1 SELECT Queries
```php
// ✅ Correct - Using prepared statements
$stmt = $pdo->prepare("SELECT * FROM buyers WHERE status = :status AND deleted_at IS NULL");
$stmt->execute(['status' => 1]);
$buyers = $stmt->fetchAll();

// ❌ Incorrect - Raw SQL
$buyers = $pdo->query("SELECT * FROM buyers WHERE status = 1")->fetchAll();
```

### 7.2 JOIN Queries
```sql
-- Use explicit JOIN syntax
SELECT b.company_name, p.name, i.quantity
FROM inquiries i
INNER JOIN buyers b ON i.buyer_id = b.id
INNER JOIN products p ON i.product_id = p.id
WHERE i.status = 1 
  AND i.created_at >= :start_date
  AND i.created_at <= :end_date;
```

### 7.3 INSERT Queries
```php
// Always use transactions for related inserts
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("
        INSERT INTO invoices (buyer_id, invoice_date, total_amount, created_at) 
        VALUES (:buyer_id, :invoice_date, :total_amount, NOW())
    ");
    $stmt->execute([
        'buyer_id' => $buyerId,
        'invoice_date' => $date,
        'total_amount' => $total
    ]);
    $invoiceId = $pdo->lastInsertId();
    
    // Insert related items
    foreach ($items as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO invoice_items (invoice_id, product_id, quantity, rate) 
            VALUES (:invoice_id, :product_id, :quantity, :rate)
        ");
        $stmt->execute([
            'invoice_id' => $invoiceId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'rate' => $item['rate']
        ]);
    }
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

### 7.4 UPDATE Queries
```php
// Always update timestamps
$stmt = $pdo->prepare("
    UPDATE buyers 
    SET company_name = :company_name, 
        updated_at = NOW() 
    WHERE id = :id
");
```

### 7.5 DELETE Queries
```php
// Always use soft delete
$stmt = $pdo->prepare("
    UPDATE buyers 
    SET deleted_at = NOW(), 
        status = 0 
    WHERE id = :id
");
```

---

## 8. TRANSACTION MANAGEMENT

### 8.1 When to Use Transactions
- **Multi-table inserts** - Related data across tables
- **Financial operations** - Any money movement
- **Inventory changes** - Stock level updates
- **Document generation** - Multi-step document creation

### 8.2 Transaction Best Practices
```php
// Set transaction isolation
$pdo->exec("SET TRANSACTION ISOLATION LEVEL REPEATABLE READ");

// Use try-catch for rollback
$pdo->beginTransaction();
try {
    // All database operations
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    // Log error
    throw $e;
}
```

### 8.3 Deadlock Prevention
- **Consistent lock ordering** - Always lock tables in same order
- **Keep transactions short** - Minimize lock time
- **Use appropriate isolation** - Don't over-isolate
- **Handle deadlocks** - Retry logic for deadlock errors

---

## 9. AUDIT TRAIL REQUIREMENTS

### 9.1 Audit Log Table
```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id BIGINT UNSIGNED NOT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    INDEX idx_user_action (user_id, action),
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_created_at (created_at)
);
```

### 9.2 Audit Events
| Action | When to Log | Required Fields |
|--------|-------------|----------------|
| CREATE | New record | new_values |
| UPDATE | Any field change | old_values, new_values |
| DELETE | Soft delete | old_values |
| RESTORE | Restore from delete | old_values, new_values |
| STATUS_CHANGE | Status field change | old_values, new_values |

---

## 10. DATA VALIDATION RULES

### 10.1 Database-Level Validation
- **NOT NULL** for required fields
- **UNIQUE** for business unique constraints
- **CHECK constraints** for data ranges
- **FOREIGN KEY** for referential integrity

### 10.2 Check Constraints Examples
```sql
-- Status must be valid
ALTER TABLE buyers 
ADD CONSTRAINT chk_buyers_status 
CHECK (status BETWEEN 0 AND 7);

-- Amount must be positive
ALTER TABLE invoices 
ADD CONSTRAINT chk_invoices_amount 
CHECK (total_amount >= 0);

-- Date cannot be future for past records
ALTER TABLE invoices 
ADD CONSTRAINT chk_invoices_date 
CHECK (invoice_date <= CURDATE() OR status = 2);
```

---

## 11. BACKUP AND MAINTENANCE

### 11.1 Backup Strategy
```bash
# Daily full backup
mysqldump --single-transaction --routines --triggers \
  --all-databases > backup_$(date +%Y%m%d).sql

# Hourly incremental backup
mysqlbinlog --start-datetime="$(date -d '1 hour ago' +%Y-%m-%d %H:%M:%S)" \
  /var/log/mysql/binlog.* > incremental_$(date +%Y%m%d_%H).sql
```

### 11.2 Maintenance Commands
```sql
-- Optimize tables monthly
OPTIMIZE TABLE buyers, suppliers, products, inquiries, invoices;

-- Analyze tables weekly
ANALYZE TABLE buyers, suppliers, products, inquiries, invoices;

-- Check tables daily
CHECK TABLE buyers, suppliers, products, inquiries, invoices;
```

### 11.3 Partition Strategy
```sql
-- Partition large tables by date
ALTER TABLE audit_logs 
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

---

## 12. PERFORMANCE OPTIMIZATION

### 12.1 Query Optimization
- **Use EXPLAIN** before optimizing
- **Avoid SELECT *** - Specify columns
- **Use LIMIT** for pagination
- **Index WHERE columns**
- **Avoid functions in WHERE**

### 12.2 Connection Optimization
```php
// Persistent connections
$pdo = new PDO(
    $dsn, 
    $user, 
    $pass, 
    [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);
```

### 12.3 Caching Strategy
- **Query cache** for lookup tables
- **Result cache** for reports
- **Session cache** for user data
- **Configuration cache** for settings

---

## 13. SECURITY RULES

### 13.1 Database User Permissions
```sql
-- Application user (read/write)
GRANT SELECT, INSERT, UPDATE, DELETE ON mesigo.* TO 'app_user'@'%';

-- Read-only user (reports)
GRANT SELECT ON mesigo.* TO 'report_user'@'%';

-- Admin user (full access)
GRANT ALL PRIVILEGES ON mesigo.* TO 'admin_user'@'%';
```

### 13.2 Sensitive Data Handling
- **Encrypt at application level** - Never store plain text
- **Hash passwords** - Use bcrypt, never MD5/SHA1
- **Mask PII** - Show partial data in logs
- **Separate databases** - For highly sensitive data

### 13.3 SQL Injection Prevention
```php
// ✅ Always use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);

// ❌ Never concatenate user input
$sql = "SELECT * FROM users WHERE email = '$email'"; // DANGEROUS!
```

---

## 14. DATA INTEGRITY RULES

### 14.1 Foreign Key Constraints
- **ON DELETE RESTRICT** - Prevent orphaned records
- **ON UPDATE CASCADE** - Update references automatically
- **NOT NULL** - For required relationships
- **Indexed** - All foreign key columns

### 14.2 Data Consistency
- **Transactions** for multi-step operations
- **Constraints** for business rules
- **Triggers** for automatic updates (sparingly)
- **Stored procedures** for complex operations

### 14.3 Data Quality Checks
```sql
-- Check for orphaned records
SELECT i.id 
FROM inquiries i 
LEFT JOIN buyers b ON i.buyer_id = b.id 
WHERE b.id IS NULL;

-- Check for invalid statuses
SELECT id, status 
FROM invoices 
WHERE status NOT IN (0, 1, 2, 3, 4, 5, 6, 7);
```

---

## 15. MONITORING AND ALERTS

### 15.1 Key Metrics to Monitor
- **Connection count** - Alert at 80% max
- **Slow query log** - Queries > 1 second
- **Deadlock count** - Alert on any deadlock
- **Replication lag** - Alert if > 10 seconds
- **Table size growth** - Alert on rapid growth

### 15.2 Health Check Queries
```sql
-- Check table fragmentation
SELECT 
    table_name,
    data_length,
    index_length,
    data_free
FROM information_schema.tables 
WHERE table_schema = 'mesigo' 
  AND data_free > 1000000;

-- Check slow queries
SELECT * FROM performance_schema.events_statements_summary_by_digest 
WHERE avg_timer_wait > 1000000000000  -- 1 second
ORDER BY avg_timer_wait DESC;
```

---

## 16. COMPLIANCE QUERIES

### 16.1 GST Compliance
```sql
-- Monthly sales report for GST
SELECT 
    DATE_FORMAT(invoice_date, '%Y-%m') as month,
    SUM(tax_amount) as total_tax,
    SUM(total_amount) as total_sales
FROM invoices 
WHERE invoice_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
GROUP BY DATE_FORMAT(invoice_date, '%Y-%m');
```

### 16.2 Audit Trail Query
```sql
-- User activity report
SELECT 
    u.username,
    a.action,
    a.table_name,
    a.created_at
FROM audit_logs a
JOIN users u ON a.user_id = u.id
WHERE a.created_at >= :start_date
  AND a.created_at <= :end_date
ORDER BY a.created_at DESC;
```

---

*This document defines the database standards for MESIGO ERP. All database operations must comply with these rules.*