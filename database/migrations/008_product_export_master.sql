-- Migration: 008_product_export_master.sql
-- Description: Adds all necessary CRM, Export, Packing, Quality, and Inventory fields to the products table.

ALTER TABLE products
    -- Export Information
    ADD COLUMN country_of_origin VARCHAR(100) NULL AFTER name,
    ADD COLUMN scientific_name VARCHAR(255) NULL AFTER country_of_origin,
    ADD COLUMN crop_year VARCHAR(50) NULL AFTER scientific_name,
    ADD COLUMN harvest_season VARCHAR(100) NULL AFTER crop_year,
    ADD COLUMN shelf_life VARCHAR(100) NULL AFTER harvest_season,
    ADD COLUMN storage_conditions VARCHAR(255) NULL AFTER shelf_life,
    ADD COLUMN temperature_req VARCHAR(100) NULL AFTER storage_conditions,
    ADD COLUMN moisture_percent DECIMAL(5,2) NULL AFTER temperature_req,
    ADD COLUMN purity_percent DECIMAL(5,2) NULL AFTER moisture_percent,
    ADD COLUMN admixture_percent DECIMAL(5,2) NULL AFTER purity_percent,
    ADD COLUMN broken_percent DECIMAL(5,2) NULL AFTER admixture_percent,
    ADD COLUMN color VARCHAR(100) NULL AFTER broken_percent,
    ADD COLUMN smell VARCHAR(100) NULL AFTER color,

    -- Commercial & Pricing
    ADD COLUMN default_currency VARCHAR(10) NULL AFTER smell,
    ADD COLUMN purchase_price DECIMAL(15,2) NULL AFTER default_currency,
    ADD COLUMN selling_price DECIMAL(15,2) NULL AFTER purchase_price,
    ADD COLUMN moq DECIMAL(15,2) NULL AFTER selling_price,
    ADD COLUMN max_oq DECIMAL(15,2) NULL AFTER moq,
    ADD COLUMN lead_time_days INT NULL AFTER max_oq,
    ADD COLUMN gst_percent DECIMAL(5,2) NULL AFTER lead_time_days,

    -- Packing & Export Features
    ADD COLUMN packing_size VARCHAR(100) NULL AFTER gst_percent,
    ADD COLUMN net_weight DECIMAL(10,3) NULL AFTER packing_size,
    ADD COLUMN gross_weight DECIMAL(10,3) NULL AFTER net_weight,
    ADD COLUMN bags_per_container INT NULL AFTER gross_weight,
    ADD COLUMN container_type VARCHAR(100) NULL AFTER bags_per_container,
    ADD COLUMN pallet_type VARCHAR(100) NULL AFTER container_type,
    ADD COLUMN shipping_marks VARCHAR(255) NULL AFTER pallet_type,
    ADD COLUMN default_shipment_type VARCHAR(100) NULL AFTER shipping_marks,
    ADD COLUMN preferred_loading_port VARCHAR(100) NULL AFTER default_shipment_type,
    ADD COLUMN preferred_destination_port VARCHAR(100) NULL AFTER preferred_loading_port,
    ADD COLUMN preferred_incoterm VARCHAR(50) NULL AFTER preferred_destination_port,
    ADD COLUMN preferred_payment_method VARCHAR(100) NULL AFTER preferred_incoterm,

    -- Quality Parameters (TINYINT/Boolean)
    ADD COLUMN is_machine_clean TINYINT(1) NOT NULL DEFAULT 0 AFTER preferred_payment_method,
    ADD COLUMN is_sortex TINYINT(1) NOT NULL DEFAULT 0 AFTER is_machine_clean,
    ADD COLUMN is_hand_picked TINYINT(1) NOT NULL DEFAULT 0 AFTER is_sortex,
    ADD COLUMN is_steam_sterilized TINYINT(1) NOT NULL DEFAULT 0 AFTER is_hand_picked,
    ADD COLUMN is_organic TINYINT(1) NOT NULL DEFAULT 0 AFTER is_steam_sterilized,
    ADD COLUMN cert_eu_standard TINYINT(1) NOT NULL DEFAULT 0 AFTER is_organic,
    ADD COLUMN cert_us_fda TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_eu_standard,
    ADD COLUMN cert_iso TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_us_fda,
    ADD COLUMN cert_haccp TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_iso,
    ADD COLUMN cert_fssai TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_haccp,
    ADD COLUMN cert_apeda TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_fssai,
    ADD COLUMN cert_asta TINYINT(1) NOT NULL DEFAULT 0 AFTER cert_apeda,

    -- Inventory Information
    ADD COLUMN opening_stock DECIMAL(15,2) NULL AFTER cert_asta,
    ADD COLUMN reorder_level DECIMAL(15,2) NULL AFTER opening_stock,
    ADD COLUMN safety_stock DECIMAL(15,2) NULL AFTER reorder_level,
    ADD COLUMN warehouse_location VARCHAR(100) NULL AFTER safety_stock,
    ADD COLUMN rack_location VARCHAR(100) NULL AFTER warehouse_location,
    ADD COLUMN bin_location VARCHAR(100) NULL AFTER rack_location,

    -- CRM & Status
    ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER bin_location,
    ADD COLUMN is_priority TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured,
    ADD COLUMN is_export TINYINT(1) NOT NULL DEFAULT 0 AFTER is_priority,
    ADD COLUMN is_domestic TINYINT(1) NOT NULL DEFAULT 0 AFTER is_export,
    ADD COLUMN remarks TEXT NULL AFTER is_domestic;
