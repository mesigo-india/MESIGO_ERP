-- Migration: 015_document_standardization_schema.sql
-- Description: DB columns for Company AD Code, company_banks table, bank seeds, and Phytosanitary Certificate permissions.

ALTER TABLE `company`
    ADD COLUMN `ad_code` VARCHAR(50) NULL AFTER `iec_code`;

-- Create company_banks table
CREATE TABLE IF NOT EXISTS `company_banks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `beneficiary_name` VARCHAR(255) NOT NULL,
    `bank_name` VARCHAR(100) NOT NULL,
    `account_number` VARCHAR(100) NOT NULL,
    `ifsc_code` VARCHAR(50) NULL,
    `swift_code` VARCHAR(50) NULL,
    `iban` VARCHAR(50) NULL,
    `ad_code` VARCHAR(50) NULL,
    `is_default` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    KEY `idx_company_banks_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default company banks
INSERT INTO `company_banks` (`beneficiary_name`, `bank_name`, `account_number`, `ifsc_code`, `swift_code`, `iban`, `ad_code`, `is_default`, `status`)
VALUES 
('MESIGO EXPORTS PRIVATE LIMITED', 'State Bank of India', '33445566778', 'SBIN0001234', 'SBININBBXXX', '', 'SBI1234', 1, 1),
('MESIGO EXPORTS PRIVATE LIMITED', 'HSBC India', '99887766554', 'HSBC0110002', 'HSBCINBBAXX', '', 'HSB9988', 0, 1);

-- Insert phytosanitary permissions
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `module`, `status`) VALUES
('phytosanitary.view', 'View Phytosanitary', 'View phytosanitary certificate records', 'phytosanitary', 1),
('phytosanitary.create', 'Create Phytosanitary', 'Create phytosanitary certificate records', 'phytosanitary', 1),
('phytosanitary.update', 'Update Phytosanitary', 'Update phytosanitary certificate records', 'phytosanitary', 1),
('phytosanitary.delete', 'Delete Phytosanitary', 'Disable/Delete phytosanitary certificate records', 'phytosanitary', 1);
