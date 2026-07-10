-- MESIGO ERP - Export Supplier CRM Migration
-- Adds necessary columns to `suppliers` for world-class ERP features

ALTER TABLE `suppliers`
ADD COLUMN `pan_number` VARCHAR(20) NULL AFTER `gst_number`,
ADD COLUMN `iec_code` VARCHAR(20) NULL AFTER `pan_number`,
ADD COLUMN `registration_number` VARCHAR(100) NULL AFTER `iec_code`,
ADD COLUMN `fssai` VARCHAR(50) NULL AFTER `registration_number`,
ADD COLUMN `apeda` VARCHAR(50) NULL AFTER `fssai`,
ADD COLUMN `iso` VARCHAR(50) NULL AFTER `apeda`,
ADD COLUMN `haccp` VARCHAR(50) NULL AFTER `iso`,
ADD COLUMN `supplier_type` ENUM('domestic', 'international') NOT NULL DEFAULT 'domestic' AFTER `haccp`,
ADD COLUMN `priority` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium' AFTER `supplier_type`,
ADD COLUMN `rating` DECIMAL(3,2) NOT NULL DEFAULT 0.00 AFTER `priority`,
ADD COLUMN `is_preferred` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rating`,
ADD COLUMN `is_approved` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_preferred`,
ADD COLUMN `is_blocked` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_approved`,
ADD COLUMN `assigned_executive` BIGINT UNSIGNED NULL AFTER `is_blocked`,
ADD COLUMN `last_contact_date` DATE NULL AFTER `assigned_executive`,
ADD COLUMN `next_followup_date` DATE NULL AFTER `last_contact_date`,
ADD COLUMN `remarks` TEXT NULL AFTER `next_followup_date`,
ADD COLUMN `products_supplied` JSON NULL AFTER `remarks`,
ADD COLUMN `preferred_categories` JSON NULL AFTER `products_supplied`,
ADD COLUMN `moq` VARCHAR(100) NULL AFTER `preferred_categories`,
ADD COLUMN `lead_time_days` INT NULL AFTER `moq`,
ADD COLUMN `payment_terms` VARCHAR(255) NULL AFTER `lead_time_days`,
ADD COLUMN `default_currency` VARCHAR(10) NULL AFTER `payment_terms`,
ADD COLUMN `incoterm` VARCHAR(50) NULL AFTER `default_currency`,
ADD COLUMN `default_port` VARCHAR(100) NULL AFTER `incoterm`,
ADD COLUMN `container_capacity` VARCHAR(100) NULL AFTER `default_port`,
ADD COLUMN `website` VARCHAR(255) NULL AFTER `container_capacity`;

CREATE TABLE IF NOT EXISTS `supplier_bank_details` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `supplier_id` BIGINT UNSIGNED NOT NULL,
    `bank_name` VARCHAR(100) NOT NULL,
    `account_name` VARCHAR(100) NOT NULL,
    `account_number` VARCHAR(50) NOT NULL,
    `ifsc_code` VARCHAR(20) NULL,
    `swift_code` VARCHAR(20) NULL,
    `currency` VARCHAR(10) NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_supplier_banks_supplier` (`supplier_id`),
    CONSTRAINT `fk_supplier_banks_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: supplier_contacts and supplier_addresses tables are already created in 003_database_master_plan.sql.
