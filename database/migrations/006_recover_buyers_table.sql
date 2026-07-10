-- ============================================================
-- MESIGO ERP - Buyers Table Recovery Migration
-- Migration: 006_recover_buyers_table.sql
-- Date: 2026-07-09
-- 
-- Source analysis:
--   - Schema authority  : database/schema/schema.sql (git: 868eb27)
--   - App column source : classes/Buyer.php mapData() / create() / update()
--   - FK verification   : document_headers.buyer_id = BIGINT UNSIGNED (matches)
--   - countries/states/cities/users.id = BIGINT UNSIGNED (matches)
--
-- This migration ONLY creates the buyers table.
-- It does NOT create buyer_contacts or buyer_addresses.
-- It does NOT touch any other existing table.
-- ============================================================

CREATE TABLE IF NOT EXISTS `buyers` (
    -- Identity
    `id`                  BIGINT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `buyer_code`          VARCHAR(50)         NOT NULL,
    `company_name`        VARCHAR(255)        NOT NULL,

    -- Classification (used by Buyer.php: buyer_type, priority, lead_source)
    `buyer_type`          VARCHAR(50)         NULL DEFAULT NULL,
    `priority`            VARCHAR(50)         NULL DEFAULT NULL,
    `lead_source`         VARCHAR(100)        NULL DEFAULT NULL,

    -- Primary Contact (used by Buyer.php: contact_person, designation, email, mobile, phone, website, whatsapp)
    `contact_person`      VARCHAR(255)        NULL DEFAULT NULL,
    `designation`         VARCHAR(100)        NULL DEFAULT NULL,
    `email`               VARCHAR(255)        NULL DEFAULT NULL,
    `mobile`              VARCHAR(20)         NULL DEFAULT NULL,
    `phone`               VARCHAR(20)         NULL DEFAULT NULL,
    `website`             VARCHAR(255)        NULL DEFAULT NULL,
    `whatsapp`            VARCHAR(20)         NULL DEFAULT NULL,

    -- Address (used by Buyer.php: billing_address, shipping_address, country, state, city, zip)
    -- country/state/city stored as plain text strings as per Buyer.php mapData() & form.php
    `billing_address`     TEXT                NULL DEFAULT NULL,
    `shipping_address`    TEXT                NULL DEFAULT NULL,
    `country`             VARCHAR(100)        NULL DEFAULT NULL,
    `state`               VARCHAR(100)        NULL DEFAULT NULL,
    `city`                VARCHAR(100)        NULL DEFAULT NULL,
    `zip`                 VARCHAR(20)         NULL DEFAULT NULL,

    -- FK-based location IDs retained from schema.sql (used by document_headers etc.)
    `country_id`          BIGINT UNSIGNED     NULL DEFAULT NULL,
    `state_id`            BIGINT UNSIGNED     NULL DEFAULT NULL,
    `city_id`             BIGINT UNSIGNED     NULL DEFAULT NULL,

    -- Business / Compliance (used by Buyer.php: gst_number, iec_number, registration_number, tax_number)
    `gst_number`          VARCHAR(50)         NULL DEFAULT NULL,
    `iec_number`          VARCHAR(50)         NULL DEFAULT NULL,
    `registration_number` VARCHAR(100)        NULL DEFAULT NULL,
    `tax_number`          VARCHAR(100)        NULL DEFAULT NULL,

    -- Banking (used by Buyer.php: bank_name, account_name, account_number, swift_ifsc)
    `bank_name`           VARCHAR(255)        NULL DEFAULT NULL,
    `account_name`        VARCHAR(255)        NULL DEFAULT NULL,
    `account_number`      VARCHAR(100)        NULL DEFAULT NULL,
    `swift_ifsc`          VARCHAR(50)         NULL DEFAULT NULL,

    -- Export Preferences (used by Buyer.php: payment_terms, credit_days, shipping_mode, preferred_port, shipping_marks)
    `payment_terms`       VARCHAR(255)        NULL DEFAULT NULL,
    `credit_days`         INT UNSIGNED        NULL DEFAULT NULL,
    `shipping_mode`       VARCHAR(100)        NULL DEFAULT NULL,
    `preferred_port`      VARCHAR(100)        NULL DEFAULT NULL,
    `shipping_marks`      TEXT                NULL DEFAULT NULL,

    -- CRM (used by Buyer.php: assigned_to, last_contact, next_followup, notes)
    `assigned_to`         VARCHAR(255)        NULL DEFAULT NULL,
    `last_contact`        DATE                NULL DEFAULT NULL,
    `next_followup`       DATE                NULL DEFAULT NULL,
    `notes`               TEXT                NULL DEFAULT NULL,

    -- Status & Soft-delete (from schema.sql + Buyer.php)
    `status`              TINYINT UNSIGNED    NOT NULL DEFAULT 1,
    `created_by`          BIGINT UNSIGNED     NULL DEFAULT NULL,
    `updated_by`          BIGINT UNSIGNED     NULL DEFAULT NULL,
    `deleted_by`          BIGINT UNSIGNED     NULL DEFAULT NULL,
    `created_at`          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at`          TIMESTAMP           NULL DEFAULT NULL,

    -- Keys (from schema.sql)
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_buyers_code`    (`buyer_code`),
    KEY `idx_buyers_status`        (`status`),
    KEY `idx_buyers_country_id`    (`country_id`),
    KEY `idx_buyers_created`       (`created_at`),

    -- Foreign keys (from schema.sql — all verified BIGINT UNSIGNED)
    CONSTRAINT `fk_buyers_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_state`   FOREIGN KEY (`state_id`)   REFERENCES `states`    (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_city`    FOREIGN KEY (`city_id`)    REFERENCES `cities`    (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_created` FOREIGN KEY (`created_by`) REFERENCES `users`     (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_updated` FOREIGN KEY (`updated_by`) REFERENCES `users`     (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_buyers_deleted` FOREIGN KEY (`deleted_by`) REFERENCES `users`     (`id`) ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Buyer / Customer master — recovered 2026-07-09';

-- CHECK constraint (from schema.sql line 702)
-- status values: 0=Deleted, 1=Active, 2=Inactive, etc. (0-7 range)
ALTER TABLE `buyers`
    ADD CONSTRAINT `chk_buyers_status` CHECK (`status` BETWEEN 0 AND 7);
