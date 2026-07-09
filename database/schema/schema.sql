-- MESIGO ERP - Database Schema
-- MySQL 8.0 Compatible
-- Created: 2026-07-07

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+05:30";

-- ============================================================
-- CORE TABLES
-- ============================================================

-- Users table
CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` BIGINT UNSIGNED NOT NULL,
    `username` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_username` (`username`),
    UNIQUE KEY `uk_users_email` (`email`),
    KEY `idx_users_status` (`status`),
    KEY `idx_users_role_id` (`role_id`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Roles table
CREATE TABLE `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `permissions` JSON NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions table
CREATE TABLE `permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `display_name` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permissions_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role permissions pivot table
CREATE TABLE `role_permissions` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`role_id`, `permission_id`),
    KEY `idx_role_permissions_permission` (`permission_id`),
    CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User roles pivot table
CREATE TABLE `user_roles` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `role_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`user_id`, `role_id`),
    KEY `idx_user_roles_role` (`role_id`),
    CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Company table
CREATE TABLE `company` (
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

-- Settings table
CREATE TABLE `settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL,
    `value` TEXT NULL,
    `type` VARCHAR(50) NOT NULL DEFAULT 'string',
    `group` VARCHAR(100) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`key`),
    KEY `idx_settings_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Currencies table
CREATE TABLE `currencies` (
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

-- Countries table
CREATE TABLE `countries` (
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

-- States table
CREATE TABLE `states` (
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

-- Cities table
CREATE TABLE `cities` (
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

-- Audit logs table
CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `action` VARCHAR(50) NOT NULL,
    `table_name` VARCHAR(100) NOT NULL,
    `record_id` BIGINT UNSIGNED NOT NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    KEY `idx_audit_user_action` (`user_id`, `action`),
    KEY `idx_audit_table_record` (`table_name`, `record_id`),
    KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login logs table
CREATE TABLE `login_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL,
    `status` ENUM('success', 'failed') NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    KEY `idx_login_user` (`user_id`),
    KEY `idx_login_status` (`status`),
    KEY `idx_login_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Financial years table
CREATE TABLE `financial_years` (
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

-- Number series table
CREATE TABLE `number_series` (
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

-- ============================================================
-- INDEXES AND CONSTRAINTS
-- ============================================================

-- ============================================================
-- MASTER TABLES FOR DOCUMENT ENGINE
-- ============================================================

-- Buyers table
CREATE TABLE `buyers` (
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
    KEY `idx_buyers_created` (`created_at`),
    CONSTRAINT `fk_buyers_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_state` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_city` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_updated` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_deleted` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppliers table
CREATE TABLE `suppliers` (
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

-- Product categories table
CREATE TABLE `product_categories` (
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

-- Product varieties table
CREATE TABLE `product_varieties` (
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

-- Units table
CREATE TABLE `units` (
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

-- Packing types table
CREATE TABLE `packing_types` (
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

-- Incoterms table
CREATE TABLE `incoterms` (
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

-- Payment terms table
CREATE TABLE `payment_terms` (
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

-- Ports table
CREATE TABLE `ports` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `country_id` BIGINT UNSIGNED NULL,
    `type` ENUM('air', 'sea', 'land') NOT NULL DEFAULT 'sea',
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

-- Shipping lines table
CREATE TABLE `shipping_lines` (
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

-- Banks table
CREATE TABLE `banks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `branch` VARCHAR(100) NULL,
    `address` TEXT NULL,
    `ifsc_code` VARCHAR(20) NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_banks_code` (`code`),
    KEY `idx_banks_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Containers table
CREATE TABLE `containers` (
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

-- Products table
CREATE TABLE `products` (
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

-- ============================================================
-- DOCUMENT ENGINE TABLES
-- ============================================================

-- Document types table
CREATE TABLE `document_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `code` VARCHAR(20) NOT NULL,
    `description` TEXT NULL,
    `is_active` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_document_types_code` (`code`),
    KEY `idx_document_types_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document headers table
CREATE TABLE `document_headers` (
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
    CONSTRAINT `fk_document_headers_payment_term` FOREIGN KEY (`payment_term_id`) REFERENCES `payment_terms` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_headers_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_headers_approved` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document items table
CREATE TABLE `document_items` (
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
    CONSTRAINT `fk_document_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_document_items_variety` FOREIGN KEY (`variety_id`) REFERENCES `product_varieties` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_items_origin` FOREIGN KEY (`origin_country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_items_packing` FOREIGN KEY (`packing_type_id`) REFERENCES `packing_types` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_document_items_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document terms table
CREATE TABLE `document_terms` (
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

-- Document charges table
CREATE TABLE `document_charges` (
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

-- Document status history table
CREATE TABLE `document_status_history` (
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
    KEY `idx_status_history_created` (`created_at`),
    CONSTRAINT `fk_status_history_document` FOREIGN KEY (`document_header_id`) REFERENCES `document_headers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_status_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Document attachments table
CREATE TABLE `document_attachments` (
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

-- Document revisions table
CREATE TABLE `document_revisions` (
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

-- Check constraints for document status values
ALTER TABLE `buyers` ADD CONSTRAINT `chk_buyers_status` CHECK (`status` BETWEEN 0 AND 7);
ALTER TABLE `suppliers` ADD CONSTRAINT `chk_suppliers_status` CHECK (`status` BETWEEN 0 AND 7);
ALTER TABLE `products` ADD CONSTRAINT `chk_products_status` CHECK (`status` BETWEEN 0 AND 7);
ALTER TABLE `document_headers` ADD CONSTRAINT `chk_document_headers_status` CHECK (`status` BETWEEN 0 AND 7);
