-- Create tables for Print Studio & Asset Manager

CREATE TABLE IF NOT EXISTS `print_company_assets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `asset_type` VARCHAR(50) NOT NULL COMMENT 'logo, signature, seal, stamp, cert_logo, letterhead_bg',
    `name` VARCHAR(100) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `metadata_json` JSON NULL COMMENT 'width, height, crop, rotation, opacity, filters',
    `branch_id` BIGINT UNSIGNED NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_assets_type` (`asset_type`),
    KEY `idx_assets_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `print_templates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `document_type` VARCHAR(50) NOT NULL COMMENT 'quotation, proforma_invoice, commercial_invoice, packing_list, shipping_bill, bill_of_lading_draft, bill_of_lading_final, certificate_of_origin, phytosanitary_certificate, etc',
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `branch_id` BIGINT UNSIGNED NULL,
    `buyer_id` BIGINT UNSIGNED NULL,
    `country_code` VARCHAR(10) NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_templates_type` (`document_type`),
    KEY `idx_templates_context` (`branch_id`, `buyer_id`, `country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `print_template_sections` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_id` BIGINT UNSIGNED NOT NULL,
    `section_code` VARCHAR(50) NOT NULL COMMENT 'header, buyer, grid, summary, bank, terms, footer, signatures',
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `bg_asset_id` BIGINT UNSIGNED NULL,
    `custom_style_json` JSON NULL COMMENT 'padding, borders, margins, height, background-color',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_sections_template` FOREIGN KEY (`template_id`) REFERENCES `print_templates` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sections_asset` FOREIGN KEY (`bg_asset_id`) REFERENCES `print_company_assets` (`id`) ON DELETE SET NULL,
    UNIQUE KEY `uk_template_section` (`template_id`, `section_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `print_fields` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `section_id` BIGINT UNSIGNED NOT NULL,
    `field_key` VARCHAR(100) NOT NULL COMMENT 'document_number, date, buyer_name, payment_terms, etc',
    `custom_label` VARCHAR(150) NULL,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `col_span` INT NOT NULL DEFAULT 6 COMMENT 'Grid columns span, e.g. 1-12',
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `alignment` VARCHAR(20) NOT NULL DEFAULT 'left' COMMENT 'left, right, center, justify',
    `style_json` JSON NULL COMMENT 'font-weight, font-size, color, text-transform',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_fields_section` FOREIGN KEY (`section_id`) REFERENCES `print_template_sections` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_section_field` (`section_id`, `field_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `print_settings` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_id` BIGINT UNSIGNED NOT NULL,
    `paper_size` VARCHAR(20) NOT NULL DEFAULT 'A4' COMMENT 'A4, Letter, Legal',
    `orientation` VARCHAR(20) NOT NULL DEFAULT 'portrait' COMMENT 'portrait, landscape',
    `margin_top` DECIMAL(5,2) NOT NULL DEFAULT '15.00' COMMENT 'in mm',
    `margin_bottom` DECIMAL(5,2) NOT NULL DEFAULT '15.00' COMMENT 'in mm',
    `margin_left` DECIMAL(5,2) NOT NULL DEFAULT '15.00' COMMENT 'in mm',
    `margin_right` DECIMAL(5,2) NOT NULL DEFAULT '15.00' COMMENT 'in mm',
    `header_height` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
    `footer_height` DECIMAL(5,2) NOT NULL DEFAULT '0.00',
    `show_page_numbers` TINYINT(1) NOT NULL DEFAULT 1,
    `page_number_format` VARCHAR(50) NOT NULL DEFAULT 'Page {page} of {pages}',
    `letterhead_mode` VARCHAR(50) NOT NULL DEFAULT 'blank' COMMENT 'blank, letterhead, logo_only, header_only',
    `print_metadata_json` JSON NULL COMMENT 'print_date, print_time, printed_by',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_settings_template` FOREIGN KEY (`template_id`) REFERENCES `print_templates` (`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_settings_template` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `print_watermarks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_id` BIGINT UNSIGNED NOT NULL,
    `watermark_type` VARCHAR(20) NOT NULL DEFAULT 'text' COMMENT 'text, image',
    `text_value` VARCHAR(100) NULL,
    `asset_id` BIGINT UNSIGNED NULL,
    `opacity` DECIMAL(3,2) NOT NULL DEFAULT '0.15',
    `rotation` INT NOT NULL DEFAULT -30 COMMENT 'degrees',
    `scale` DECIMAL(5,2) NOT NULL DEFAULT '100.00',
    `position_x` VARCHAR(50) NOT NULL DEFAULT 'center',
    `position_y` VARCHAR(50) NOT NULL DEFAULT 'center',
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_watermark_template` FOREIGN KEY (`template_id`) REFERENCES `print_templates` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_watermark_asset` FOREIGN KEY (`asset_id`) REFERENCES `print_company_assets` (`id`) ON DELETE SET NULL,
    UNIQUE KEY `uk_watermark_template` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `print_qr` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_id` BIGINT UNSIGNED NOT NULL,
    `qr_source` VARCHAR(50) NOT NULL DEFAULT 'url' COMMENT 'website, upi, verification_url, custom_text',
    `qr_data_template` TEXT NULL COMMENT 'Contains placeholders like {doc_number}, {grand_total}',
    `size_px` INT UNSIGNED NOT NULL DEFAULT 100,
    `alignment` VARCHAR(20) NOT NULL DEFAULT 'right',
    `sort_order` INT NOT NULL DEFAULT 99,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_qr_template` FOREIGN KEY (`template_id`) REFERENCES `print_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `print_signature` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_id` BIGINT UNSIGNED NOT NULL,
    `signature_asset_id` BIGINT UNSIGNED NOT NULL,
    `authorized_person` VARCHAR(150) NOT NULL,
    `designation` VARCHAR(100) NOT NULL,
    `position_x` INT NOT NULL DEFAULT 0,
    `position_y` INT NOT NULL DEFAULT 0,
    `scale_percent` INT UNSIGNED NOT NULL DEFAULT 100,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_sig_template` FOREIGN KEY (`template_id`) REFERENCES `print_templates` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sig_asset` FOREIGN KEY (`signature_asset_id`) REFERENCES `print_company_assets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
