-- Migration: 010_enterprise_branding_and_security.sql
-- Description: DB columns for Company pan/cin/apeda/fssai, banking, signatures, seals, logo, custom letterheads, and User profiles.

ALTER TABLE `company`
    ADD COLUMN `pan_number` VARCHAR(50) NULL AFTER `iec_code`,
    ADD COLUMN `cin_number` VARCHAR(50) NULL AFTER `pan_number`,
    ADD COLUMN `apeda_number` VARCHAR(50) NULL AFTER `cin_number`,
    ADD COLUMN `fssai_number` VARCHAR(50) NULL AFTER `apeda_number`,
    ADD COLUMN `iso_number` VARCHAR(50) NULL AFTER `fssai_number`,
    ADD COLUMN `haccp_number` VARCHAR(50) NULL AFTER `iso_number`,
    ADD COLUMN `website` VARCHAR(255) NULL AFTER `phone`,
    ADD COLUMN `bank_name` VARCHAR(100) NULL AFTER `website`,
    ADD COLUMN `account_name` VARCHAR(255) NULL AFTER `bank_name`,
    ADD COLUMN `account_number` VARCHAR(100) NULL AFTER `account_name`,
    ADD COLUMN `ifsc_code` VARCHAR(50) NULL AFTER `account_number`,
    ADD COLUMN `swift_code` VARCHAR(50) NULL AFTER `ifsc_code`,
    ADD COLUMN `logo_path` VARCHAR(255) NULL AFTER `swift_code`,
    ADD COLUMN `stamp_path` VARCHAR(255) NULL AFTER `logo_path`,
    ADD COLUMN `seal_path` VARCHAR(255) NULL AFTER `stamp_path`,
    ADD COLUMN `signature_path` VARCHAR(255) NULL AFTER `seal_path`,
    ADD COLUMN `digital_signature_path` VARCHAR(255) NULL AFTER `signature_path`,
    ADD COLUMN `letterhead_type` VARCHAR(30) NOT NULL DEFAULT 'plain' AFTER `digital_signature_path`,
    ADD COLUMN `letterhead_path` VARCHAR(255) NULL AFTER `letterhead_type`,
    ADD COLUMN `letterhead_export_path` VARCHAR(255) NULL AFTER `letterhead_path`,
    ADD COLUMN `letterhead_domestic_path` VARCHAR(255) NULL AFTER `letterhead_export_path`,
    ADD COLUMN `declaration` TEXT NULL AFTER `letterhead_domestic_path`;

ALTER TABLE `users`
    ADD COLUMN `photo_path` VARCHAR(255) NULL AFTER `phone`,
    ADD COLUMN `signature_path` VARCHAR(255) NULL AFTER `photo_path`,
    ADD COLUMN `language` VARCHAR(10) NOT NULL DEFAULT 'en' AFTER `signature_path`,
    ADD COLUMN `timezone` VARCHAR(100) NOT NULL DEFAULT 'Asia/Kolkata' AFTER `language`,
    ADD COLUMN `failed_attempts` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `timezone`,
    ADD COLUMN `locked_until` TIMESTAMP NULL DEFAULT NULL AFTER `failed_attempts`;
