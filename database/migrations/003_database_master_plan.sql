-- MESIGO ERP - Database Master Plan Migration
-- Creates the complete planned ERP schema in dependency order.
-- Safe to run multiple times via CREATE TABLE IF NOT EXISTS.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- STAGE 1 - CORE SECURITY AND CONFIGURATION
-- ============================================================

CREATE TABLE IF NOT EXISTS `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `display_name` VARCHAR(255) NULL,
    `permissions` JSON NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_name` (`name`),
    KEY `idx_roles_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `display_name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `module` VARCHAR(100) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permissions_name` (`name`),
    KEY `idx_permissions_module` (`module`),
    KEY `idx_permissions_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` BIGINT UNSIGNED NULL,
    `username` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `last_login_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_username` (`username`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_status` (`status`),
    KEY `idx_users_role_id` (`role_id`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`role_id`, `permission_id`),
    KEY `idx_role_permissions_permission` (`permission_id`),
    CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `role_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`, `role_id`),
    KEY `idx_user_roles_role` (`role_id`),
    CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `status` ENUM('success', 'failed') NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_login_user` (`user_id`),
    KEY `idx_login_status` (`status`),
    KEY `idx_login_created` (`created_at`),
    CONSTRAINT `fk_login_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `used_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_password_resets_token` (`token`),
    KEY `idx_password_resets_user` (`user_id`),
    KEY `idx_password_resets_expires` (`expires_at`),
    CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `session_id` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `last_activity_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_sessions_session` (`session_id`),
    KEY `idx_user_sessions_user` (`user_id`),
    CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'string',
    `group` VARCHAR(100) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`key`),
    KEY `idx_settings_group` (`group`),
    KEY `idx_settings_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(50) NOT NULL,
    `table_name` VARCHAR(100) NOT NULL,
    `record_id` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_audit_user_action` (`user_id`, `action`),
    KEY `idx_audit_table_record` (`table_name`, `record_id`),
    KEY `idx_audit_created` (`created_at`),
    CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- STAGE 2/3/4/5 - COMPANY, GEOGRAPHY, PRODUCT, CRM MASTERS
-- ============================================================

CREATE TABLE IF NOT EXISTS `company` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_name` VARCHAR(255) NOT NULL,
    `address` JSON NULL,
    `contact_person` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `gst_number` VARCHAR(50) NULL,
    `iec_code` VARCHAR(50) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_company_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `branches` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_id` BIGINT UNSIGNED NULL,
    `branch_code` VARCHAR(50) NOT NULL,
    `branch_name` VARCHAR(255) NOT NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `country` VARCHAR(100) NULL,
    `zip` VARCHAR(20) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `gst_number` VARCHAR(50) NULL,
    `contact_person` VARCHAR(255) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `remarks` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_branches_code` (`branch_code`),
    KEY `idx_branches_status` (`status`),
    KEY `idx_branches_company` (`company_id`),
    CONSTRAINT `fk_branches_company` FOREIGN KEY (`company_id`) REFERENCES `company` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_branches_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_branches_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_branches_deleted` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `financial_years` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `year` VARCHAR(20) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_current` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_financial_year` (`year`),
    KEY `idx_financial_current` (`is_current`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `currencies` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(3) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `symbol` VARCHAR(10) NULL,
    `exchange_rate` DECIMAL(15,6) NOT NULL DEFAULT 1.000000,
    `is_default` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_currencies_code` (`code`),
    KEY `idx_currencies_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `number_series` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `prefix` VARCHAR(20) NOT NULL,
    `next_number` INT UNSIGNED NOT NULL DEFAULT 1,
    `padding` TINYINT UNSIGNED NOT NULL DEFAULT 4,
    `suffix` VARCHAR(20) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_number_series_name` (`name`),
    KEY `idx_number_series_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `countries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(2) NOT NULL,
    `phone_code` VARCHAR(10) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_countries_code` (`code`),
    KEY `idx_countries_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `states` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `country_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(10) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_states_country` (`country_id`),
    KEY `idx_states_status` (`status`),
    CONSTRAINT `fk_states_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cities` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `state_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cities_state` (`state_id`),
    KEY `idx_cities_status` (`status`),
    CONSTRAINT `fk_cities_state` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ports` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `type` ENUM('air','sea','land') NOT NULL DEFAULT 'sea',
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_ports_code` (`code`),
    KEY `idx_ports_country` (`country_id`),
    KEY `idx_ports_type` (`type`),
    KEY `idx_ports_status` (`status`),
    CONSTRAINT `fk_ports_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shipping_lines` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `contact_person` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_shipping_lines_code` (`code`),
    KEY `idx_shipping_lines_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `containers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `number` VARCHAR(50) NOT NULL,
    `type` VARCHAR(20) NULL,
    `size` VARCHAR(20) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_containers_number` (`number`),
    KEY `idx_containers_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `freight_forwarders` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `forwarder_code` VARCHAR(50) NOT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `contact_person` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `mobile` VARCHAR(20) NULL,
    `services` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `remarks` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_freight_forwarders_code` (`forwarder_code`),
    KEY `idx_freight_forwarders_country` (`country_id`),
    KEY `idx_freight_forwarders_status` (`status`),
    CONSTRAINT `fk_freight_forwarders_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inspection_agencies` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `agency_code` VARCHAR(50) NOT NULL,
    `agency_name` VARCHAR(255) NOT NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `contact_person` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `mobile` VARCHAR(20) NULL,
    `accreditation` TEXT NULL,
    `services` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `remarks` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_inspection_agencies_code` (`agency_code`),
    KEY `idx_inspection_agencies_country` (`country_id`),
    KEY `idx_inspection_agencies_status` (`status`),
    CONSTRAINT `fk_inspection_agencies_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_categories_code` (`code`),
    KEY `idx_product_categories_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_varieties` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_varieties_code` (`code`),
    KEY `idx_product_varieties_category` (`category_id`),
    KEY `idx_product_varieties_status` (`status`),
    CONSTRAINT `fk_product_varieties_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `units` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `code` VARCHAR(10) NOT NULL,
    `description` VARCHAR(255) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_units_code` (`code`),
    KEY `idx_units_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `packing_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_packing_types_code` (`code`),
    KEY `idx_packing_types_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hs_codes` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `hs_code` VARCHAR(20) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(255) NULL,
    `duty_rate` DECIMAL(5,2) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `remarks` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_hs_codes_code` (`hs_code`),
    KEY `idx_hs_codes_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_grades` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `remarks` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_grades_code` (`code`),
    KEY `idx_product_grades_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_origins` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `remarks` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_product_origins_code` (`code`),
    KEY `idx_product_origins_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `category_id` BIGINT UNSIGNED NULL,
    `variety_id` BIGINT UNSIGNED NULL,
    `hsn_code` VARCHAR(20) NULL,
    `unit_id` BIGINT UNSIGNED NULL,
    `packing_type_id` BIGINT UNSIGNED NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_products_code` (`product_code`),
    KEY `idx_products_status` (`status`),
    KEY `idx_products_category` (`category_id`),
    KEY `idx_products_variety` (`variety_id`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_products_variety` FOREIGN KEY (`variety_id`) REFERENCES `product_varieties` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_products_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_products_packing` FOREIGN KEY (`packing_type_id`) REFERENCES `packing_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_packaging` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `packing_type_id` BIGINT UNSIGNED NULL,
    `unit_id` BIGINT UNSIGNED NULL,
    `quantity_per_pack` DECIMAL(15,3) NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_product_packaging_product` (`product_id`),
    CONSTRAINT `fk_product_packaging_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_packaging_type` FOREIGN KEY (`packing_type_id`) REFERENCES `packing_types` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_product_packaging_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `buyers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `buyer_code` VARCHAR(50) NOT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `address` JSON NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `state_id` BIGINT UNSIGNED NULL,
    `city_id` BIGINT UNSIGNED NULL,
    `gst_number` VARCHAR(50) NULL,
    `iec_number` VARCHAR(50) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_buyers_code` (`buyer_code`),
    KEY `idx_buyers_status` (`status`),
    KEY `idx_buyers_country` (`country_id`),
    CONSTRAINT `fk_buyers_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_state` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `suppliers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `supplier_code` VARCHAR(50) NOT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `contact_person` VARCHAR(255) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `address` JSON NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `state_id` BIGINT UNSIGNED NULL,
    `city_id` BIGINT UNSIGNED NULL,
    `gst_number` VARCHAR(50) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_suppliers_code` (`supplier_code`),
    KEY `idx_suppliers_status` (`status`),
    KEY `idx_suppliers_country` (`country_id`),
    CONSTRAINT `fk_suppliers_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_suppliers_state` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_suppliers_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `buyer_contacts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `buyer_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `designation` VARCHAR(100) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `mobile` VARCHAR(20) NULL,
    `is_primary` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_buyer_contacts_buyer` (`buyer_id`),
    CONSTRAINT `fk_buyer_contacts_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supplier_contacts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `supplier_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `designation` VARCHAR(100) NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(20) NULL,
    `mobile` VARCHAR(20) NULL,
    `is_primary` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_supplier_contacts_supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `buyer_addresses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `buyer_id` BIGINT UNSIGNED NOT NULL,
    `address_type` VARCHAR(50) NOT NULL DEFAULT 'billing',
    `address` TEXT NOT NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `state_id` BIGINT UNSIGNED NULL,
    `city_id` BIGINT UNSIGNED NULL,
    `zip` VARCHAR(20) NULL,
    `is_default` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_buyer_addresses_buyer` (`buyer_id`),
    CONSTRAINT `fk_buyer_addresses_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supplier_addresses` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `supplier_id` BIGINT UNSIGNED NOT NULL,
    `address_type` VARCHAR(50) NOT NULL DEFAULT 'billing',
    `address` TEXT NOT NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `state_id` BIGINT UNSIGNED NULL,
    `city_id` BIGINT UNSIGNED NULL,
    `zip` VARCHAR(20) NULL,
    `is_default` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_supplier_addresses_supplier` (`supplier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `crm_communications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `party_type` ENUM('buyer','supplier') NOT NULL,
    `party_id` BIGINT UNSIGNED NOT NULL,
    `communication_type` VARCHAR(50) NOT NULL,
    `subject` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `communication_at` DATETIME NOT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_crm_communications_party` (`party_type`, `party_id`),
    KEY `idx_crm_communications_created` (`created_by`),
    CONSTRAINT `fk_crm_communications_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `crm_activities` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `party_type` ENUM('buyer','supplier') NOT NULL,
    `party_id` BIGINT UNSIGNED NOT NULL,
    `activity_type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `due_at` DATETIME NULL,
    `assigned_to` BIGINT UNSIGNED NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_crm_activities_party` (`party_type`, `party_id`),
    KEY `idx_crm_activities_assigned` (`assigned_to`),
    CONSTRAINT `fk_crm_activities_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_crm_activities_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- STAGE 6 - DOCUMENT ENGINE
-- ============================================================

CREATE TABLE IF NOT EXISTS `incoterms` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(10) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_incoterms_code` (`code`),
    KEY `idx_incoterms_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_terms` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `days` INT UNSIGNED NULL,
    `description` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_payment_terms_code` (`code`),
    KEY `idx_payment_terms_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `is_active` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_document_types_code` (`code`),
    KEY `idx_document_types_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_headers` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_type_id` BIGINT UNSIGNED NOT NULL,
    `document_number` VARCHAR(100) NOT NULL,
    `document_date` DATE NOT NULL,
    `buyer_id` BIGINT UNSIGNED NULL,
    `seller_id` BIGINT UNSIGNED NULL,
    `currency_id` BIGINT UNSIGNED NOT NULL,
    `exchange_rate` DECIMAL(15,6) NOT NULL DEFAULT 1.000000,
    `shipment_type` VARCHAR(50) NULL,
    `incoterm_id` BIGINT UNSIGNED NULL,
    `loading_port_id` BIGINT UNSIGNED NULL,
    `destination_port_id` BIGINT UNSIGNED NULL,
    `payment_term_id` BIGINT UNSIGNED NULL,
    `validity_days` INT UNSIGNED NULL,
    `expected_shipment` DATE NULL,
    `remarks` TEXT NULL,
    `internal_notes` TEXT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_by` BIGINT UNSIGNED NULL,
    `updated_by` BIGINT UNSIGNED NULL,
    `deleted_by` BIGINT UNSIGNED NULL,
    `approved_by` BIGINT UNSIGNED NULL,
    `converted_from_id` BIGINT UNSIGNED NULL,
    `converted_to_id` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_document_headers_number` (`document_number`),
    KEY `idx_document_headers_type` (`document_type_id`),
    KEY `idx_document_headers_buyer` (`buyer_id`),
    KEY `idx_document_headers_status` (`status`),
    KEY `idx_document_headers_date` (`document_date`),
    CONSTRAINT `fk_document_headers_type` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_document_headers_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_headers_seller` FOREIGN KEY (`seller_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_headers_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_document_headers_incoterms` FOREIGN KEY (`incoterm_id`) REFERENCES `incoterms` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_headers_loading_port` FOREIGN KEY (`loading_port_id`) REFERENCES `ports` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_headers_destination_port` FOREIGN KEY (`destination_port_id`) REFERENCES `ports` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_headers_payment_term` FOREIGN KEY (`payment_term_id`) REFERENCES `payment_terms` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_header_id` BIGINT UNSIGNED NOT NULL,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `variety_id` BIGINT UNSIGNED NULL,
    `hsn_code` VARCHAR(20) NULL,
    `origin_country_id` BIGINT UNSIGNED NULL,
    `quality` VARCHAR(100) NULL,
    `packing_type_id` BIGINT UNSIGNED NULL,
    `unit_id` BIGINT UNSIGNED NULL,
    `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0,
    `rate` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `tax_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `net_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `gross_weight` DECIMAL(15,3) NULL,
    `net_weight` DECIMAL(15,3) NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_document_items_header` (`document_header_id`),
    KEY `idx_document_items_product` (`product_id`),
    CONSTRAINT `fk_document_items_header` FOREIGN KEY (`document_header_id`) REFERENCES `document_headers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_document_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_terms` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_header_id` BIGINT UNSIGNED NOT NULL,
    `term_text` TEXT NOT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_document_terms_header` (`document_header_id`),
    CONSTRAINT `fk_document_terms_header` FOREIGN KEY (`document_header_id`) REFERENCES `document_headers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_charges` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_header_id` BIGINT UNSIGNED NOT NULL,
    `charge_name` VARCHAR(100) NOT NULL,
    `charge_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `tax_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_document_charges_header` (`document_header_id`),
    CONSTRAINT `fk_document_charges_header` FOREIGN KEY (`document_header_id`) REFERENCES `document_headers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_status_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_header_id` BIGINT UNSIGNED NOT NULL,
    `old_status` TINYINT UNSIGNED NULL,
    `new_status` TINYINT UNSIGNED NOT NULL,
    `remarks` TEXT NULL,
    `changed_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_status_history_document` (`document_header_id`),
    KEY `idx_status_history_status` (`new_status`),
    CONSTRAINT `fk_status_history_document` FOREIGN KEY (`document_header_id`) REFERENCES `document_headers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_status_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_attachments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_header_id` BIGINT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `original_name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `file_type` VARCHAR(50) NULL,
    `file_size` BIGINT UNSIGNED NULL,
    `attachment_type` VARCHAR(50) NULL,
    `uploaded_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_document_attachments_header` (`document_header_id`),
    KEY `idx_document_attachments_type` (`attachment_type`),
    CONSTRAINT `fk_document_attachments_header` FOREIGN KEY (`document_header_id`) REFERENCES `document_headers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_document_attachments_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `document_revisions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `document_header_id` BIGINT UNSIGNED NOT NULL,
    `revision_number` INT UNSIGNED NOT NULL,
    `document_data` JSON NULL,
    `revision_notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_document_revisions` (`document_header_id`, `revision_number`),
    KEY `idx_document_revisions_header` (`document_header_id`),
    CONSTRAINT `fk_document_revisions_header` FOREIGN KEY (`document_header_id`) REFERENCES `document_headers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_document_revisions_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- STAGE 7+ - TRANSACTION, REPORTING, AUTOMATION, AI TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS `sales_orders` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `buyer_id` BIGINT UNSIGNED NULL, `source_document_id` BIGINT UNSIGNED NULL, `order_number` VARCHAR(100) NOT NULL, `order_date` DATE NOT NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, `deleted_at` TIMESTAMP NULL DEFAULT NULL, PRIMARY KEY (`id`), UNIQUE KEY `uk_sales_orders_number` (`order_number`), KEY `idx_sales_orders_buyer` (`buyer_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `sales_order_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `sales_order_id` BIGINT UNSIGNED NOT NULL, `product_id` BIGINT UNSIGNED NOT NULL, `unit_id` BIGINT UNSIGNED NULL, `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0, `rate` DECIMAL(15,4) NOT NULL DEFAULT 0, `amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_sales_order_items_order` (`sales_order_id`), CONSTRAINT `fk_sales_order_items_order` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `quotations` LIKE `document_headers`;
CREATE TABLE IF NOT EXISTS `quotation_items` LIKE `document_items`;
CREATE TABLE IF NOT EXISTS `proforma_invoices` LIKE `document_headers`;
CREATE TABLE IF NOT EXISTS `proforma_invoice_items` LIKE `document_items`;
CREATE TABLE IF NOT EXISTS `commercial_invoices` LIKE `document_headers`;
CREATE TABLE IF NOT EXISTS `commercial_invoice_items` LIKE `document_items`;

CREATE TABLE IF NOT EXISTS `purchase_requests` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `request_number` VARCHAR(100) NOT NULL, `request_date` DATE NOT NULL, `supplier_id` BIGINT UNSIGNED NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_purchase_requests_number` (`request_number`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `purchase_request_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `purchase_request_id` BIGINT UNSIGNED NOT NULL, `product_id` BIGINT UNSIGNED NOT NULL, `unit_id` BIGINT UNSIGNED NULL, `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_pr_items_request` (`purchase_request_id`), CONSTRAINT `fk_pr_items_request` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `purchase_orders` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `po_number` VARCHAR(100) NOT NULL, `po_date` DATE NOT NULL, `supplier_id` BIGINT UNSIGNED NULL, `currency_id` BIGINT UNSIGNED NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_purchase_orders_number` (`po_number`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `purchase_order_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `purchase_order_id` BIGINT UNSIGNED NOT NULL, `product_id` BIGINT UNSIGNED NOT NULL, `unit_id` BIGINT UNSIGNED NULL, `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0, `rate` DECIMAL(15,4) NOT NULL DEFAULT 0, `amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_po_items_order` (`purchase_order_id`), CONSTRAINT `fk_po_items_order` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `goods_receipts` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `receipt_number` VARCHAR(100) NOT NULL, `receipt_date` DATE NOT NULL, `purchase_order_id` BIGINT UNSIGNED NULL, `supplier_id` BIGINT UNSIGNED NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_goods_receipts_number` (`receipt_number`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `goods_receipt_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `goods_receipt_id` BIGINT UNSIGNED NOT NULL, `product_id` BIGINT UNSIGNED NOT NULL, `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_gr_items_receipt` (`goods_receipt_id`), CONSTRAINT `fk_gr_items_receipt` FOREIGN KEY (`goods_receipt_id`) REFERENCES `goods_receipts` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `warehouses` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `branch_id` BIGINT UNSIGNED NULL, `name` VARCHAR(255) NOT NULL, `code` VARCHAR(50) NOT NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_warehouses_code` (`code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `warehouse_locations` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `warehouse_id` BIGINT UNSIGNED NOT NULL, `name` VARCHAR(255) NOT NULL, `code` VARCHAR(50) NOT NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_warehouse_locations_warehouse` (`warehouse_id`), CONSTRAINT `fk_warehouse_locations_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `stock_batches` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `product_id` BIGINT UNSIGNED NOT NULL, `batch_number` VARCHAR(100) NOT NULL, `warehouse_id` BIGINT UNSIGNED NULL, `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_stock_batches_product` (`product_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `stock_ledgers` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `product_id` BIGINT UNSIGNED NOT NULL, `warehouse_id` BIGINT UNSIGNED NULL, `movement_type` ENUM('in','out','adjustment') NOT NULL, `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0, `source_type` VARCHAR(100) NULL, `source_id` BIGINT UNSIGNED NULL, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_stock_ledgers_product` (`product_id`), KEY `idx_stock_ledgers_created` (`created_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `stock_adjustments` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `adjustment_number` VARCHAR(100) NOT NULL, `adjustment_date` DATE NOT NULL, `warehouse_id` BIGINT UNSIGNED NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_stock_adjustments_number` (`adjustment_number`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `stock_adjustment_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `stock_adjustment_id` BIGINT UNSIGNED NOT NULL, `product_id` BIGINT UNSIGNED NOT NULL, `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_sa_items_adjustment` (`stock_adjustment_id`), CONSTRAINT `fk_sa_items_adjustment` FOREIGN KEY (`stock_adjustment_id`) REFERENCES `stock_adjustments` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shipments` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `shipment_number` VARCHAR(100) NOT NULL, `shipment_date` DATE NULL, `buyer_id` BIGINT UNSIGNED NULL, `shipping_line_id` BIGINT UNSIGNED NULL, `freight_forwarder_id` BIGINT UNSIGNED NULL, `loading_port_id` BIGINT UNSIGNED NULL, `destination_port_id` BIGINT UNSIGNED NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_shipments_number` (`shipment_number`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `shipment_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `shipment_id` BIGINT UNSIGNED NOT NULL, `product_id` BIGINT UNSIGNED NOT NULL, `quantity` DECIMAL(15,3) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_shipment_items_shipment` (`shipment_id`), CONSTRAINT `fk_shipment_items_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `shipment_containers` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `shipment_id` BIGINT UNSIGNED NOT NULL, `container_id` BIGINT UNSIGNED NULL, `seal_number` VARCHAR(100) NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_shipment_containers_shipment` (`shipment_id`), CONSTRAINT `fk_shipment_containers_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `shipment_milestones` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `shipment_id` BIGINT UNSIGNED NOT NULL, `milestone` VARCHAR(255) NOT NULL, `milestone_at` DATETIME NULL, `remarks` TEXT NULL, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_shipment_milestones_shipment` (`shipment_id`), CONSTRAINT `fk_shipment_milestones_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `packing_lists` LIKE `document_headers`;
CREATE TABLE IF NOT EXISTS `packing_list_items` LIKE `document_items`;
CREATE TABLE IF NOT EXISTS `shipping_bills` LIKE `document_headers`;
CREATE TABLE IF NOT EXISTS `bills_of_lading` LIKE `document_headers`;
CREATE TABLE IF NOT EXISTS `certificates` LIKE `document_headers`;
CREATE TABLE IF NOT EXISTS `certificate_items` LIKE `document_items`;

CREATE TABLE IF NOT EXISTS `banks` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `name` VARCHAR(100) NOT NULL, `code` VARCHAR(20) NOT NULL, `branch` VARCHAR(100) NULL, `address` TEXT NULL, `ifsc_code` VARCHAR(20) NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_banks_code` (`code`), KEY `idx_banks_status` (`status`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `expense_categories` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `name` VARCHAR(100) NOT NULL, `code` VARCHAR(20) NOT NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_expense_categories_code` (`code`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `expenses` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `expense_number` VARCHAR(100) NOT NULL, `expense_date` DATE NOT NULL, `expense_category_id` BIGINT UNSIGNED NULL, `amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_expenses_number` (`expense_number`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `payments` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `payment_number` VARCHAR(100) NOT NULL, `payment_date` DATE NOT NULL, `party_type` ENUM('buyer','supplier') NOT NULL, `party_id` BIGINT UNSIGNED NOT NULL, `bank_id` BIGINT UNSIGNED NULL, `currency_id` BIGINT UNSIGNED NULL, `amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_payments_number` (`payment_number`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `payment_allocations` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `payment_id` BIGINT UNSIGNED NOT NULL, `document_header_id` BIGINT UNSIGNED NULL, `amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_payment_allocations_payment` (`payment_id`), CONSTRAINT `fk_payment_allocations_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `payment_receipts` LIKE `document_headers`;
CREATE TABLE IF NOT EXISTS `receivables` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `buyer_id` BIGINT UNSIGNED NULL, `document_header_id` BIGINT UNSIGNED NULL, `amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `payables` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `supplier_id` BIGINT UNSIGNED NULL, `purchase_order_id` BIGINT UNSIGNED NULL, `amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `order_costings` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `costing_number` VARCHAR(100) NOT NULL, `source_type` VARCHAR(100) NULL, `source_id` BIGINT UNSIGNED NULL, `total_cost` DECIMAL(15,2) NOT NULL DEFAULT 0, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_order_costings_number` (`costing_number`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `order_costing_items` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `order_costing_id` BIGINT UNSIGNED NOT NULL, `expense_category_id` BIGINT UNSIGNED NULL, `amount` DECIMAL(15,2) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_order_costing_items_costing` (`order_costing_id`), CONSTRAINT `fk_order_costing_items_costing` FOREIGN KEY (`order_costing_id`) REFERENCES `order_costings` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tasks` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `title` VARCHAR(255) NOT NULL, `description` TEXT NULL, `assigned_to` BIGINT UNSIGNED NULL, `created_by` BIGINT UNSIGNED NULL, `related_type` VARCHAR(100) NULL, `related_id` BIGINT UNSIGNED NULL, `due_at` DATETIME NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `task_comments` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `task_id` BIGINT UNSIGNED NOT NULL, `user_id` BIGINT UNSIGNED NULL, `comment` TEXT NOT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_task_comments_task` (`task_id`), CONSTRAINT `fk_task_comments_task` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `notification_templates` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `name` VARCHAR(100) NOT NULL, `channel` VARCHAR(50) NOT NULL, `subject` VARCHAR(255) NULL, `body` TEXT NOT NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_notification_templates_name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `notifications` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `user_id` BIGINT UNSIGNED NOT NULL, `title` VARCHAR(255) NOT NULL, `message` TEXT NOT NULL, `related_type` VARCHAR(100) NULL, `related_id` BIGINT UNSIGNED NULL, `read_at` TIMESTAMP NULL DEFAULT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_notifications_user` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `email_templates` LIKE `notification_templates`;
CREATE TABLE IF NOT EXISTS `email_logs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `template_id` BIGINT UNSIGNED NULL, `recipient` VARCHAR(255) NOT NULL, `subject` VARCHAR(255) NULL, `status` VARCHAR(50) NOT NULL, `response` TEXT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `whatsapp_templates` LIKE `notification_templates`;
CREATE TABLE IF NOT EXISTS `whatsapp_logs` LIKE `email_logs`;
CREATE TABLE IF NOT EXISTS `scheduled_jobs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `name` VARCHAR(100) NOT NULL, `command` VARCHAR(255) NOT NULL, `schedule` VARCHAR(100) NOT NULL, `last_run_at` TIMESTAMP NULL DEFAULT NULL, `next_run_at` TIMESTAMP NULL DEFAULT NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_scheduled_jobs_name` (`name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `report_definitions` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `name` VARCHAR(100) NOT NULL, `module` VARCHAR(100) NOT NULL, `filters` JSON NULL, `created_by` BIGINT UNSIGNED NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `report_exports` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `report_definition_id` BIGINT UNSIGNED NULL, `file_path` VARCHAR(500) NULL, `status` VARCHAR(50) NOT NULL, `created_by` BIGINT UNSIGNED NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `dashboard_widgets` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `name` VARCHAR(100) NOT NULL, `widget_key` VARCHAR(100) NOT NULL, `config` JSON NULL, `status` TINYINT UNSIGNED NOT NULL DEFAULT 1, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_dashboard_widgets_key` (`widget_key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `dashboard_metrics_cache` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `metric_key` VARCHAR(100) NOT NULL, `metric_value` JSON NULL, `expires_at` TIMESTAMP NULL DEFAULT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), UNIQUE KEY `uk_dashboard_metrics_key` (`metric_key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `ai_conversations` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `user_id` BIGINT UNSIGNED NULL, `title` VARCHAR(255) NULL, `context` JSON NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `ai_messages` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `conversation_id` BIGINT UNSIGNED NOT NULL, `role` VARCHAR(50) NOT NULL, `message` MEDIUMTEXT NOT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`), KEY `idx_ai_messages_conversation` (`conversation_id`), CONSTRAINT `fk_ai_messages_conversation` FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations` (`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `ai_action_logs` (`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, `user_id` BIGINT UNSIGNED NULL, `action` VARCHAR(100) NOT NULL, `related_type` VARCHAR(100) NULL, `related_id` BIGINT UNSIGNED NULL, `payload` JSON NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BASE SEED DATA
-- ============================================================

INSERT IGNORE INTO `roles` (`id`, `name`, `display_name`, `permissions`, `status`) VALUES
(1, 'admin', 'Administrator', JSON_ARRAY('all'), 1);

INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `module`, `status`) VALUES
('roles.view', 'View Roles', 'View role records', 'roles', 1),
('roles.create', 'Create Roles', 'Create role records', 'roles', 1),
('roles.update', 'Update Roles', 'Update role records', 'roles', 1),
('roles.delete', 'Delete Roles', 'Disable role records', 'roles', 1),
('permissions.view', 'View Permissions', 'View permission records', 'permissions', 1),
('permissions.create', 'Create Permissions', 'Create permission records', 'permissions', 1),
('permissions.update', 'Update Permissions', 'Update permission records', 'permissions', 1),
('permissions.delete', 'Delete Permissions', 'Disable permission records', 'permissions', 1),
('users.view', 'View Users', 'View user records', 'users', 1),
('users.create', 'Create Users', 'Create user records', 'users', 1),
('users.update', 'Update Users', 'Update user records', 'users', 1),
('users.delete', 'Delete Users', 'Disable user records', 'users', 1),
('products.view', 'View Products', 'View product records', 'products', 1),
('products.create', 'Create Products', 'Create product records', 'products', 1),
('products.update', 'Update Products', 'Update product records', 'products', 1),
('products.delete', 'Delete Products', 'Disable product records', 'products', 1),
('buyers.view', 'View Buyers', 'View buyer records', 'buyers', 1),
('buyers.create', 'Create Buyers', 'Create buyer records', 'buyers', 1),
('buyers.update', 'Update Buyers', 'Update buyer records', 'buyers', 1),
('buyers.delete', 'Delete Buyers', 'Disable buyer records', 'buyers', 1),
('company.view', 'View Company', 'View company records', 'company', 1),
('company.create', 'Create Company', 'Create company records', 'company', 1),
('company.update', 'Update Company', 'Update company records', 'company', 1),
('company.delete', 'Delete Company', 'Disable company records', 'company', 1),
('settings.view', 'View Settings', 'View settings records', 'settings', 1),
('settings.create', 'Create Settings', 'Create settings records', 'settings', 1),
('settings.update', 'Update Settings', 'Update settings records', 'settings', 1),
('settings.delete', 'Delete Settings', 'Disable settings records', 'settings', 1),
('quotations.view', 'View Quotations', 'View quotation records', 'quotations', 1),
('quotations.create', 'Create Quotations', 'Create quotation records', 'quotations', 1),
('quotations.update', 'Update Quotations', 'Update quotation records', 'quotations', 1),
('quotations.convert', 'Convert Quotations', 'Convert quotation records', 'quotations', 1),
('proforma_invoices.view', 'View Proforma Invoices', 'View proforma invoice records', 'proforma_invoices', 1),
('proforma_invoices.create', 'Create Proforma Invoices', 'Create proforma invoice records', 'proforma_invoices', 1),
('proforma_invoices.update', 'Update Proforma Invoices', 'Update proforma invoice records', 'proforma_invoices', 1),
('proforma_invoices.convert', 'Convert Proforma Invoices', 'Convert proforma invoice records', 'proforma_invoices', 1),
('commercial_invoices.view', 'View Commercial Invoices', 'View commercial invoice records', 'commercial_invoices', 1),
('commercial_invoices.create', 'Create Commercial Invoices', 'Create commercial invoice records', 'commercial_invoices', 1),
('commercial_invoices.update', 'Update Commercial Invoices', 'Update commercial invoice records', 'commercial_invoices', 1),
('commercial_invoices.convert', 'Convert Commercial Invoices', 'Convert commercial invoice records', 'commercial_invoices', 1),
('packing_lists.view', 'View Packing Lists', 'View packing list records', 'packing_lists', 1),
('packing_lists.create', 'Create Packing Lists', 'Create packing list records', 'packing_lists', 1),
('packing_lists.update', 'Update Packing Lists', 'Update packing list records', 'packing_lists', 1),
('shipping_bills.view', 'View Shipping Bills', 'View shipping bill records', 'shipping_bills', 1),
('shipping_bills.create', 'Create Shipping Bills', 'Create shipping bill records', 'shipping_bills', 1),
('shipping_bills.update', 'Update Shipping Bills', 'Update shipping bill records', 'shipping_bills', 1),
('shipping_bills.convert', 'Convert Shipping Bills', 'Convert shipping bill records', 'shipping_bills', 1),
('bill_of_ladings.view', 'View Bills of Lading', 'View bill of lading records', 'bill_of_ladings', 1),
('bill_of_ladings.create', 'Create Bills of Lading', 'Create bill of lading records', 'bill_of_ladings', 1),
('bill_of_ladings.update', 'Update Bills of Lading', 'Update bill of lading records', 'bill_of_ladings', 1),
('bill_of_ladings.convert', 'Convert Bills of Lading', 'Convert bill of lading records', 'bill_of_ladings', 1),
('certificate_of_origins.view', 'View Certificates of Origin', 'View certificate of origin records', 'certificate_of_origins', 1),
('certificate_of_origins.create', 'Create Certificates of Origin', 'Create certificate of origin records', 'certificate_of_origins', 1),
('certificate_of_origins.update', 'Update Certificates of Origin', 'Update certificate of origin records', 'certificate_of_origins', 1),
('export_documents.view', 'View Export Documents', 'View export document records', 'export_documents', 1),
('export_documents.upload', 'Upload Export Documents', 'Upload export document attachments', 'export_documents', 1),
('export_documents.update', 'Update Export Documents', 'Update export document records', 'export_documents', 1),
('reports.view', 'View Reports', 'View report records', 'reports', 1);

INSERT IGNORE INTO `users` (`id`, `role_id`, `username`, `email`, `password`, `first_name`, `last_name`, `status`) VALUES
(1, 1, 'admin', 'admin@mesigoerp.local', '$2y$10$vZfvOrYdkubPQ0P9W7p6ruyTbzX4OR2HGqNuQv/p57p4c0UYI0.ZW', 'System', 'Administrator', 1);

INSERT IGNORE INTO `currencies` (`code`, `name`, `symbol`, `exchange_rate`, `is_default`, `status`) VALUES
('INR', 'Indian Rupee', '₹', 1.000000, 1, 1),
('USD', 'US Dollar', '$', 1.000000, 0, 1);

INSERT IGNORE INTO `document_types` (`name`, `code`, `description`, `is_active`) VALUES
('Inquiry', 'inquiry', 'Customer inquiry', 1),
('Quotation', 'quotation', 'Sales quotation', 1),
('Proforma Invoice', 'proforma_invoice', 'Proforma invoice', 1),
('Commercial Invoice', 'commercial_invoice', 'Commercial invoice', 1),
('Packing List', 'packing_list', 'Packing list', 1),
('Shipping Bill', 'shipping_bill', 'Shipping bill', 1),
('Bill of Lading', 'bill_of_lading', 'Bill of lading', 1),
('Certificate of Origin', 'certificate_of_origin', 'Certificate of origin', 1),
('Phytosanitary', 'phytosanitary', 'Phytosanitary certificate', 1),
('Insurance', 'insurance', 'Insurance certificate', 1),
('Inspection', 'inspection', 'Inspection certificate', 1),
('Payment Receipt', 'payment_receipt', 'Payment receipt', 1);

INSERT IGNORE INTO `number_series` (`name`, `prefix`, `next_number`, `padding`, `status`) VALUES
('quotation', 'QTN', 1, 5, 1),
('proforma_invoice', 'PI', 1, 5, 1),
('commercial_invoice', 'CI', 1, 5, 1),
('packing_list', 'PL', 1, 5, 1),
('shipping_bill', 'SB', 1, 5, 1),
('bill_of_lading', 'BL', 1, 5, 1),
('certificate_of_origin', 'CO', 1, 5, 1),
('phytosanitary', 'PS', 1, 5, 1),
('insurance', 'INS', 1, 5, 1),
('inspection', 'INSP', 1, 5, 1),
('payment_receipt', 'PR', 1, 5, 1);

SET FOREIGN_KEY_CHECKS = 1;