-- Migration: 012_register_non_hazardous_document_type.sql
-- Description: Register 'non_hazardous_cert' document type and its number series

INSERT IGNORE INTO `document_types` (`name`, `code`, `description`, `is_active`) VALUES
('Non-Hazardous Certificate', 'non_hazardous_cert', 'Non-Hazardous cargo declaration certificate', 1);

INSERT IGNORE INTO `number_series` (`name`, `prefix`, `next_number`, `padding`, `status`, `created_at`) VALUES
('non_hazardous_cert', 'NHC', 1, 5, 1, NOW());
