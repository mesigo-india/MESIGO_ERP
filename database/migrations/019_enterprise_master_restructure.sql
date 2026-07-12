-- 1. Restructure Product Categories Table
ALTER TABLE `product_categories` 
    ADD COLUMN `parent_category_id` BIGINT UNSIGNED NULL AFTER `description`,
    ADD COLUMN `commodity_group` VARCHAR(100) NULL AFTER `parent_category_id`,
    ADD COLUMN `hs_chapter` VARCHAR(10) NULL AFTER `commodity_group`,
    ADD COLUMN `default_gst` DECIMAL(5,2) NULL DEFAULT 0.00 AFTER `hs_chapter`,
    ADD COLUMN `default_unit_id` BIGINT UNSIGNED NULL AFTER `default_gst`,
    ADD COLUMN `default_currency_id` BIGINT UNSIGNED NULL AFTER `default_unit_id`,
    ADD COLUMN `default_storage` VARCHAR(150) NULL AFTER `default_currency_id`,
    ADD COLUMN `shelf_life` INT UNSIGNED NULL COMMENT 'in days' AFTER `default_storage`,
    ADD COLUMN `temperature` VARCHAR(50) NULL AFTER `shelf_life`,
    ADD COLUMN `export_allowed` TINYINT(1) NOT NULL DEFAULT 1 AFTER `temperature`,
    ADD COLUMN `import_allowed` TINYINT(1) NOT NULL DEFAULT 1 AFTER `export_allowed`,
    ADD COLUMN `default_packaging_id` BIGINT UNSIGNED NULL AFTER `import_allowed`,
    ADD COLUMN `preferred_warehouse_id` BIGINT UNSIGNED NULL AFTER `default_packaging_id`,
    ADD COLUMN `quality_standard` VARCHAR(150) NULL AFTER `preferred_warehouse_id`,
    ADD COLUMN `remarks` TEXT NULL AFTER `quality_standard`,
    ADD COLUMN `internal_notes` TEXT NULL AFTER `remarks`,
    ADD COLUMN `tags` VARCHAR(255) NULL AFTER `internal_notes`,
    ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL;

-- 2. Restructure Units Table
ALTER TABLE `units`
    ADD COLUMN `base_unit` VARCHAR(50) NULL AFTER `description`,
    ADD COLUMN `conversion_formula` VARCHAR(255) NULL AFTER `base_unit`,
    ADD COLUMN `decimal_precision` INT UNSIGNED NOT NULL DEFAULT 2 AFTER `conversion_formula`,
    ADD COLUMN `internal_notes` TEXT NULL AFTER `decimal_precision`,
    ADD COLUMN `tags` VARCHAR(255) NULL AFTER `internal_notes`;

-- 3. Restructure Packing Types Table
ALTER TABLE `packing_types`
    ADD COLUMN `material` VARCHAR(100) NULL AFTER `description`,
    ADD COLUMN `net_weight` DECIMAL(12,4) NULL DEFAULT 0.0000 AFTER `material`,
    ADD COLUMN `gross_weight` DECIMAL(12,4) NULL DEFAULT 0.0000 AFTER `net_weight`,
    ADD COLUMN `length` DECIMAL(10,2) NULL COMMENT 'in mm' AFTER `gross_weight`,
    ADD COLUMN `width` DECIMAL(10,2) NULL COMMENT 'in mm' AFTER `length`,
    ADD COLUMN `height` DECIMAL(10,2) NULL COMMENT 'in mm' AFTER `width`,
    ADD COLUMN `volume` DECIMAL(12,4) NULL COMMENT 'in cbm' AFTER `height`,
    ADD COLUMN `container_capacity` DECIMAL(10,2) NULL COMMENT 'in packages' AFTER `volume`,
    ADD COLUMN `internal_notes` TEXT NULL AFTER `container_capacity`,
    ADD COLUMN `tags` VARCHAR(255) NULL AFTER `internal_notes`;

-- 4. Restructure Ports Table
ALTER TABLE `ports`
    ADD COLUMN `un_locode` VARCHAR(20) NULL AFTER `code`,
    ADD COLUMN `sea_port` TINYINT(1) NOT NULL DEFAULT 1 AFTER `type`,
    ADD COLUMN `air_port` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sea_port`,
    ADD COLUMN `land_port` TINYINT(1) NOT NULL DEFAULT 0 AFTER `air_port`,
    ADD COLUMN `nearest_icd` VARCHAR(100) NULL AFTER `land_port`,
    ADD COLUMN `custom_office` VARCHAR(150) NULL AFTER `nearest_icd`,
    ADD COLUMN `shipping_lines` TEXT NULL AFTER `custom_office`,
    ADD COLUMN `latitude` DECIMAL(10,8) NULL AFTER `shipping_lines`,
    ADD COLUMN `longitude` DECIMAL(11,8) NULL AFTER `latitude`,
    ADD COLUMN `internal_notes` TEXT NULL AFTER `longitude`,
    ADD COLUMN `tags` VARCHAR(255) NULL AFTER `internal_notes`;

-- 5. Restructure Banks Table
ALTER TABLE `banks`
    ADD COLUMN `account_name` VARCHAR(150) NULL AFTER `branch`,
    ADD COLUMN `account_number` VARCHAR(50) NULL AFTER `account_name`,
    ADD COLUMN `swift_code` VARCHAR(20) NULL AFTER `ifsc_code`,
    ADD COLUMN `iban` VARCHAR(50) NULL AFTER `swift_code`,
    ADD COLUMN `currency_id` BIGINT UNSIGNED NULL AFTER `iban`,
    ADD COLUMN `correspondent_bank` VARCHAR(150) NULL AFTER `currency_id`,
    ADD COLUMN `intermediary_bank` VARCHAR(150) NULL AFTER `correspondent_bank`,
    ADD COLUMN `internal_notes` TEXT NULL AFTER `intermediary_bank`,
    ADD COLUMN `tags` VARCHAR(255) NULL AFTER `internal_notes`;

-- 6. Restructure Currencies Table
ALTER TABLE `currencies`
    ADD COLUMN `decimal_places` INT UNSIGNED NOT NULL DEFAULT 2 AFTER `name`,
    ADD COLUMN `rate_source` VARCHAR(100) NULL DEFAULT 'manual' AFTER `exchange_rate`,
    ADD COLUMN `base_currency` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rate_source`,
    ADD COLUMN `auto_update` TINYINT(1) NOT NULL DEFAULT 0 AFTER `base_currency`,
    ADD COLUMN `internal_notes` TEXT NULL AFTER `auto_update`,
    ADD COLUMN `tags` VARCHAR(255) NULL AFTER `internal_notes`;

-- 7. Restructure Warehouses Table
ALTER TABLE `warehouses`
    ADD COLUMN `warehouse_type` VARCHAR(100) NULL AFTER `name`,
    ADD COLUMN `capacity` DECIMAL(15,2) NULL COMMENT 'in MT' AFTER `warehouse_type`,
    ADD COLUMN `temperature` VARCHAR(50) NULL AFTER `capacity`,
    ADD COLUMN `humidity` VARCHAR(50) NULL AFTER `temperature`,
    ADD COLUMN `storage_type` VARCHAR(100) NULL AFTER `humidity`,
    ADD COLUMN `manager` VARCHAR(100) NULL AFTER `storage_type`,
    ADD COLUMN `contact` VARCHAR(50) NULL AFTER `manager`,
    ADD COLUMN `gps` VARCHAR(50) NULL AFTER `contact`,
    ADD COLUMN `internal_notes` TEXT NULL AFTER `gps`,
    ADD COLUMN `tags` VARCHAR(255) NULL AFTER `internal_notes`;

-- 8. Create Product Grades Table
CREATE TABLE IF NOT EXISTS `product_grades` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `category_id` BIGINT UNSIGNED NULL,
    `product_id` BIGINT UNSIGNED NULL,
    `purity` DECIMAL(5,2) NULL COMMENT 'percent',
    `moisture` DECIMAL(5,2) NULL COMMENT 'percent',
    `foreign_matter` DECIMAL(5,2) NULL COMMENT 'percent',
    `broken` DECIMAL(5,2) NULL COMMENT 'percent',
    `oil` DECIMAL(5,2) NULL COMMENT 'percent',
    `sortex` TINYINT(1) NOT NULL DEFAULT 0,
    `machine_cleaned` TINYINT(1) NOT NULL DEFAULT 0,
    `steam_sterilized` TINYINT(1) NOT NULL DEFAULT 0,
    `organic` TINYINT(1) NOT NULL DEFAULT 0,
    `color` VARCHAR(50) NULL,
    `aroma` VARCHAR(50) NULL,
    `packing` VARCHAR(100) NULL,
    `quality_description` TEXT NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `internal_notes` TEXT NULL,
    `tags` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_grades_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Create Product Origins Table
CREATE TABLE IF NOT EXISTS `product_origins` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `state` VARCHAR(100) NULL,
    `district` VARCHAR(100) NULL,
    `region` VARCHAR(100) NULL,
    `growing_area` VARCHAR(150) NULL,
    `harvest_season` VARCHAR(100) NULL,
    `climate` VARCHAR(100) NULL,
    `apeda_region` VARCHAR(150) NULL,
    `gi_tag` TINYINT(1) NOT NULL DEFAULT 0,
    `quality_notes` TEXT NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `internal_notes` TEXT NULL,
    `tags` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_origins_code` (`code`),
    CONSTRAINT `fk_origins_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Restructure Products Table
ALTER TABLE `products`
    ADD COLUMN `sku` VARCHAR(100) NULL AFTER `variety_id`,
    ADD COLUMN `scientific_name` VARCHAR(255) NULL AFTER `name`,
    ADD COLUMN `trade_name` VARCHAR(255) NULL AFTER `scientific_name`,
    ADD COLUMN `sub_category_id` BIGINT UNSIGNED NULL AFTER `category_id`,
    ADD COLUMN `brand` VARCHAR(100) NULL AFTER `hsn_code`,
    ADD COLUMN `country_of_origin_id` BIGINT UNSIGNED NULL AFTER `brand`,
    ADD COLUMN `state` VARCHAR(100) NULL AFTER `country_of_origin_id`,
    ADD COLUMN `district` VARCHAR(100) NULL AFTER `state`,
    ADD COLUMN `harvest_season` VARCHAR(100) NULL AFTER `district`,
    ADD COLUMN `grade_id` BIGINT UNSIGNED NULL AFTER `harvest_season`,
    ADD COLUMN `purity` DECIMAL(5,2) NULL AFTER `grade_id`,
    ADD COLUMN `moisture` DECIMAL(5,2) NULL AFTER `purity`,
    ADD COLUMN `foreign_matter` DECIMAL(5,2) NULL AFTER `moisture`,
    ADD COLUMN `machine_cleaned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `foreign_matter`,
    ADD COLUMN `sortex` TINYINT(1) NOT NULL DEFAULT 0 AFTER `machine_cleaned`,
    ADD COLUMN `organic` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sortex`,
    ADD COLUMN `quality_standard` VARCHAR(150) NULL AFTER `organic`,
    ADD COLUMN `storage` VARCHAR(150) NULL AFTER `quality_standard`,
    ADD COLUMN `shelf_life` INT UNSIGNED NULL COMMENT 'days' AFTER `storage`,
    ADD COLUMN `moq` DECIMAL(15,4) NULL DEFAULT 0.0000 AFTER `shelf_life`,
    ADD COLUMN `lead_time` INT UNSIGNED NULL AFTER `moq`,
    ADD COLUMN `buying_price` DECIMAL(15,4) NULL DEFAULT 0.0000 AFTER `lead_time`,
    ADD COLUMN `selling_price` DECIMAL(15,4) NULL DEFAULT 0.0000 AFTER `buying_price`,
    ADD COLUMN `default_currency_id` BIGINT UNSIGNED NULL AFTER `selling_price`,
    ADD COLUMN `preferred_supplier_id` BIGINT UNSIGNED NULL AFTER `default_currency_id`,
    ADD COLUMN `preferred_warehouse_id` BIGINT UNSIGNED NULL AFTER `preferred_supplier_id`,
    ADD COLUMN `images` JSON NULL AFTER `preferred_warehouse_id`,
    ADD COLUMN `certificates` JSON NULL AFTER `images`,
    ADD COLUMN `internal_notes` TEXT NULL AFTER `description`,
    ADD COLUMN `tags` VARCHAR(255) NULL AFTER `internal_notes`;

-- 11. Create AI Settings Table
CREATE TABLE IF NOT EXISTS `ai_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `provider` VARCHAR(50) NOT NULL,
    `api_key` TEXT NULL,
    `base_url` VARCHAR(255) NULL,
    `model` VARCHAR(100) NOT NULL,
    `temperature` DECIMAL(3,2) NOT NULL DEFAULT 0.70,
    `max_tokens` INT UNSIGNED NOT NULL DEFAULT 1000,
    `timeout` INT UNSIGNED NOT NULL DEFAULT 30,
    `retry_limit` INT UNSIGNED NOT NULL DEFAULT 3,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Create AI Request Logs Table
CREATE TABLE IF NOT EXISTS `ai_request_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `prompt` TEXT NOT NULL,
    `response` TEXT NOT NULL,
    `provider` VARCHAR(50) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `execution_time_ms` INT UNSIGNED NOT NULL DEFAULT 0,
    `tokens_used` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
