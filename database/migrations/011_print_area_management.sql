-- Migration: Print Area Management & Dimensions
-- Add custom margins for letterheads and print sizes for signatures, seals, and stamps to the company table.

ALTER TABLE company
ADD COLUMN print_margin_top INT DEFAULT 45,
ADD COLUMN print_margin_bottom INT DEFAULT 35,
ADD COLUMN print_margin_left INT DEFAULT 20,
ADD COLUMN print_margin_right INT DEFAULT 20,
ADD COLUMN signature_print_width INT DEFAULT 120,
ADD COLUMN seal_print_width INT DEFAULT 100,
ADD COLUMN stamp_print_width INT DEFAULT 100;
