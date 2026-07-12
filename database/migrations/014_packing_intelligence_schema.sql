-- Migration: 014_packing_intelligence_schema.sql
-- Description: Adds all necessary packaging and stuffing specifications to the products table and expands quality column to support JSON strings.

ALTER TABLE products
    ADD COLUMN units_per_package DECIMAL(12,4) NOT NULL DEFAULT 1.0000 AFTER gross_weight,
    ADD COLUMN empty_package_weight DECIMAL(10,3) NOT NULL DEFAULT 0.000 AFTER units_per_package,
    ADD COLUMN gross_weight_formula VARCHAR(255) NULL AFTER empty_package_weight,
    ADD COLUMN package_length DECIMAL(10,2) NULL AFTER gross_weight_formula,
    ADD COLUMN package_width DECIMAL(10,2) NULL AFTER package_length,
    ADD COLUMN package_height DECIMAL(10,2) NULL AFTER package_width,
    ADD COLUMN package_material VARCHAR(100) NULL AFTER package_height,
    ADD COLUMN stack_limit INT NULL AFTER package_material,
    ADD COLUMN pallet_configuration VARCHAR(255) NULL AFTER stack_limit,
    ADD COLUMN storage_type VARCHAR(100) NULL AFTER pallet_configuration,
    ADD COLUMN loading_method VARCHAR(100) NULL AFTER storage_type,
    ADD COLUMN container_compatibility VARCHAR(255) NULL AFTER loading_method;

ALTER TABLE document_items MODIFY COLUMN quality TEXT NULL;
