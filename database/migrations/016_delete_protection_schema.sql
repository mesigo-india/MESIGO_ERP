-- Migration: 016_delete_protection_schema.sql
-- Description: Add delete_reason column to document_headers table

ALTER TABLE `document_headers`
    ADD COLUMN `delete_reason` TEXT NULL AFTER `deleted_at`;
