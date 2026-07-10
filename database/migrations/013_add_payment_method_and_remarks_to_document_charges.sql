-- Migration: 013_add_payment_method_and_remarks_to_document_charges.sql
-- Description: Add payment_method and remarks to document_charges table to support costing sheet parameters

ALTER TABLE `document_charges`
    ADD COLUMN `payment_method` VARCHAR(100) NULL AFTER `converted_amount_base`,
    ADD COLUMN `remarks` TEXT NULL AFTER `payment_method`;
