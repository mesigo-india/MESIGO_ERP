-- Add theme_code support to print settings configuration
ALTER TABLE `print_settings` 
ADD COLUMN `theme_code` VARCHAR(50) NOT NULL DEFAULT 'mesigo-professional' AFTER `template_id`;
