-- Update notes table to support file uploads
-- Add file-related columns to existing notes table

ALTER TABLE `notes` 
ADD COLUMN `file_path` VARCHAR(500) NULL DEFAULT NULL COMMENT 'Path to uploaded file',
ADD COLUMN `file_name` VARCHAR(255) NULL DEFAULT NULL COMMENT 'Original filename',
ADD COLUMN `file_type` VARCHAR(50) NULL DEFAULT NULL COMMENT 'File MIME type',
ADD COLUMN `file_size` INT NULL DEFAULT NULL COMMENT 'File size in bytes',
ADD COLUMN `note_type` ENUM('text', 'file') DEFAULT 'text' COMMENT 'Type of note: text or file upload';

-- Update existing notes to be text type
UPDATE `notes` SET `note_type` = 'text' WHERE `note_type` IS NULL;

-- Index for better performance
CREATE INDEX `idx_note_type` ON `notes` (`note_type`);
CREATE INDEX `idx_file_type` ON `notes` (`file_type`);
