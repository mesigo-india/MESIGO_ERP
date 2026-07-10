-- Migration: 009_scaled_core_engines.sql
-- Description: Database updates for Unit/Currency/Costing engines, Incoterms, Containers, Taxes, Versioning, and Approvals.

-- 1. User Company Pivot Matrix
CREATE TABLE IF NOT EXISTS `user_companies` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `company_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`, `company_id`),
    CONSTRAINT `fk_user_comp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_comp_comp` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Unit Conversion Rules Matrix
CREATE TABLE IF NOT EXISTS `unit_conversions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `from_unit_id` BIGINT UNSIGNED NOT NULL,
    `to_unit_id` BIGINT UNSIGNED NOT NULL,
    `factor` DECIMAL(18,9) NOT NULL, -- Target scale multiplier
    `product_id` BIGINT UNSIGNED NULL, -- NULL if global; product-specific if set (e.g. Bag -> KG)
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_unit_conv` (`from_unit_id`, `to_unit_id`, `product_id`),
    CONSTRAINT `fk_unit_conv_from` FOREIGN KEY (`from_unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_unit_conv_to` FOREIGN KEY (`to_unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_unit_conv_prod` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Historical Exchange Rates Log
CREATE TABLE IF NOT EXISTS `exchange_rates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `currency_id` BIGINT UNSIGNED NOT NULL,
    `rate_date` DATE NOT NULL,
    `rate` DECIMAL(15,6) NOT NULL, -- Exchange rate relative to base currency (INR)
    `source` VARCHAR(50) NOT NULL DEFAULT 'manual', -- 'manual', 'api_live'
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_exchange_rates_date` (`currency_id`, `rate_date`),
    CONSTRAINT `fk_exchange_rates_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Cost Components Master
CREATE TABLE IF NOT EXISTS `cost_components` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `category` VARCHAR(50) NOT NULL, -- 'procurement', 'logistics_local', 'logistics_intl', 'finance', 'documentation', 'commission'
    `calculation_type` VARCHAR(20) NOT NULL DEFAULT 'flat', -- 'flat', 'percentage_fob', 'per_unit_qty', 'per_container'
    `default_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `default_currency_id` BIGINT UNSIGNED NOT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_cost_components_code` (`code`),
    CONSTRAINT `fk_cost_comp_currency` FOREIGN KEY (`default_currency_id`) REFERENCES `currencies` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Cost Templates Master
CREATE TABLE IF NOT EXISTS `cost_templates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `incoterm_id` BIGINT UNSIGNED NOT NULL,
    `destination_port_id` BIGINT UNSIGNED NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_cost_temp_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cost_temp_incoterm` FOREIGN KEY (`incoterm_id`) REFERENCES `incoterms` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_cost_temp_port` FOREIGN KEY (`destination_port_id`) REFERENCES `ports` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Cost Template Line Items
CREATE TABLE IF NOT EXISTS `cost_template_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cost_template_id` BIGINT UNSIGNED NOT NULL,
    `cost_component_id` BIGINT UNSIGNED NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `currency_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_cost_temp_item_temp` FOREIGN KEY (`cost_template_id`) REFERENCES `cost_templates` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cost_temp_item_comp` FOREIGN KEY (`cost_component_id`) REFERENCES `cost_components` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_cost_temp_item_curr` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Document Approvals Ledger
CREATE TABLE IF NOT EXISTS `document_approvals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_header_id` BIGINT UNSIGNED NOT NULL,
    `assigned_role_id` BIGINT UNSIGNED NOT NULL,
    `approver_id` BIGINT UNSIGNED NULL, -- User ID who approved/rejected
    `approval_status` VARCHAR(20) NOT NULL DEFAULT 'pending', -- 'pending', 'approved', 'rejected'
    `remarks` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actioned_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_doc_app_doc` FOREIGN KEY (`document_header_id`) REFERENCES `document_headers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_doc_app_role` FOREIGN KEY (`assigned_role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_doc_app_user` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Table Schema Alterations

-- Scope Products to specific legal entities
ALTER TABLE `products` 
    ADD COLUMN `company_id` BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD COLUMN `volume_per_package_cbm` DECIMAL(10,4) NULL DEFAULT NULL AFTER `gross_weight`,
    ADD CONSTRAINT `fk_products_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE;

-- Scope Documents to legal entities and cache Incoterm + Tax contexts
ALTER TABLE `document_headers`
    ADD COLUMN `company_id` BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD COLUMN `rate_locked` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `exchange_rate`,
    ADD COLUMN `lut_active` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `rate_locked`,
    ADD COLUMN `tax_basis` VARCHAR(20) NOT NULL DEFAULT 'lut' AFTER `lut_active`, -- 'lut', 'igst_paid', 'domestic'
    ADD COLUMN `estimated_containers_json` JSON NULL AFTER `tax_basis`,
    ADD CONSTRAINT `fk_doc_headers_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE CASCADE;

-- Link transaction items to specific physical warehouse locations
ALTER TABLE `document_items`
    ADD COLUMN `warehouse_id` BIGINT UNSIGNED NULL AFTER `product_id`,
    ADD COLUMN `tax_slab_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER `tax_percent`,
    ADD CONSTRAINT `fk_doc_items_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

-- Alter document_charges to act as our dynamic Costing Sheet ledger
ALTER TABLE `document_charges`
    ADD COLUMN `cost_component_id` BIGINT UNSIGNED NULL AFTER `document_header_id`,
    ADD COLUMN `currency_id` BIGINT UNSIGNED NOT NULL DEFAULT 1 AFTER `charge_amount`,
    ADD COLUMN `exchange_rate` DECIMAL(15,6) NOT NULL DEFAULT 1.000000 AFTER `currency_id`,
    ADD COLUMN `converted_amount_base` DECIMAL(15,2) NOT NULL DEFAULT 0.00 AFTER `exchange_rate`,
    ADD CONSTRAINT `fk_doc_charges_cost_comp` FOREIGN KEY (`cost_component_id`) REFERENCES `cost_components` (`id`) ON DELETE RESTRICT,
    ADD CONSTRAINT `fk_doc_charges_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE RESTRICT;

-- Alter stock_ledgers to incorporate agricultural lot information
ALTER TABLE `stock_ledgers`
    ADD COLUMN `lot_number` VARCHAR(50) NULL AFTER `quantity`,
    ADD COLUMN `moisture_percent` DECIMAL(5,2) NULL AFTER `lot_number`;
