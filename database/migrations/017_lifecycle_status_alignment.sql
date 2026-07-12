-- Align existing status codes to Stage 5 standards

-- 1. Update document_headers status
UPDATE `document_headers` SET `status` = 2 WHERE `status` = 4;
UPDATE `document_headers` SET `status` = 4 WHERE `status` = 5;

-- 2. Update document_status_history old_status and new_status
UPDATE `document_status_history` SET `old_status` = 2 WHERE `old_status` = 4;
UPDATE `document_status_history` SET `new_status` = 2 WHERE `new_status` = 4;

UPDATE `document_status_history` SET `old_status` = 4 WHERE `old_status` = 5;
UPDATE `document_status_history` SET `new_status` = 4 WHERE `new_status` = 5;
