-- MESIGO ERP - Product Permissions Runtime Fix
-- Ensures the four Product permissions required by ProductController exist
-- in the permissions table and are assigned to the admin role.
--
-- Required permissions:
--   products.view    -> ProductController::index
--   products.create  -> ProductController::create / store
--   products.update  -> ProductController::edit / update
--   products.delete  -> ProductController::delete
--
-- Safe to run multiple times (uses INSERT IGNORE and idempotent role update).

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. Ensure the four Product permissions exist in permissions table
-- ============================================================

INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `module`, `status`) VALUES
('products.view',   'View Products',   'View product records',   'products', 1),
('products.create', 'Create Products', 'Create product records', 'products', 1),
('products.update', 'Update Products', 'Update product records', 'products', 1),
('products.delete', 'Delete Products', 'Disable product records', 'products', 1);

-- ============================================================
-- 2. Ensure the admin role has all four Product permissions (deduplicated)
-- ============================================================
-- The admin role stores permissions as a JSON array. We rebuild the
-- array from a distinct set of the existing permissions plus the four
-- product permissions so there are no duplicates and no previously
-- granted permissions are lost.

UPDATE `roles` r
JOIN (
    SELECT
        r2.id,
        (
            SELECT JSON_ARRAYAGG(perm.val)
            FROM (
                SELECT DISTINCT j.val
                FROM roles r3,
                     JSON_TABLE(
                         COALESCE(r3.permissions, JSON_ARRAY()),
                         '$[*]' COLUMNS(val VARCHAR(100) PATH '$')
                     ) j
                WHERE r3.id = r2.id
                UNION
                SELECT 'products.view'
                UNION
                SELECT 'products.create'
                UNION
                SELECT 'products.update'
                UNION
                SELECT 'products.delete'
            ) perm
        ) AS merged_permissions
    FROM roles r2
    WHERE r2.name = 'admin'
) t ON r.id = t.id
SET r.permissions = t.merged_permissions;

SET FOREIGN_KEY_CHECKS = 1;