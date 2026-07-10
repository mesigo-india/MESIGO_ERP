-- ============================================================
-- MESIGO ERP - Buyer CRM Phase 2 Enhancement
-- Migration: 007_buyer_crm_columns.sql
-- Adds missing CRM + Export Business columns to buyers table
-- ============================================================

ALTER TABLE `buyers`
    ADD COLUMN IF NOT EXISTS `lead_status`               VARCHAR(50)  NULL DEFAULT 'New Lead'  COMMENT 'CRM pipeline status'           AFTER `lead_source`,
    ADD COLUMN IF NOT EXISTS `customer_since`            DATE         NULL DEFAULT NULL         COMMENT 'Date became a customer'         AFTER `lead_status`,
    ADD COLUMN IF NOT EXISTS `preferred_currency`        VARCHAR(10)  NULL DEFAULT NULL         COMMENT 'Preferred trading currency'     AFTER `credit_days`,
    ADD COLUMN IF NOT EXISTS `preferred_incoterm`        VARCHAR(20)  NULL DEFAULT NULL         COMMENT 'Preferred Incoterm (FOB, CIF…)' AFTER `preferred_currency`,
    ADD COLUMN IF NOT EXISTS `preferred_destination_port` VARCHAR(100) NULL DEFAULT NULL        COMMENT 'Preferred destination port'     AFTER `preferred_port`,
    ADD COLUMN IF NOT EXISTS `preferred_container`       VARCHAR(50)  NULL DEFAULT NULL         COMMENT 'Preferred container type'       AFTER `preferred_destination_port`,
    ADD COLUMN IF NOT EXISTS `preferred_packing`         VARCHAR(100) NULL DEFAULT NULL         COMMENT 'Preferred packing type'         AFTER `preferred_container`,
    ADD COLUMN IF NOT EXISTS `preferred_products`        TEXT         NULL DEFAULT NULL         COMMENT 'Products of interest'           AFTER `preferred_packing`,
    ADD COLUMN IF NOT EXISTS `import_countries`          TEXT         NULL DEFAULT NULL         COMMENT 'Countries buyer imports from'   AFTER `preferred_products`;

-- Index for lead_status filter performance
CREATE INDEX IF NOT EXISTS `idx_buyers_lead_status` ON `buyers` (`lead_status`);
CREATE INDEX IF NOT EXISTS `idx_buyers_next_followup` ON `buyers` (`next_followup`);
