-- Idempotent ALTER statements for gallery table (add missing columns)
-- Review before running. These statements are safe non-destructive additions.

ALTER TABLE `gallery`
  ADD COLUMN IF NOT EXISTS `image_path` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `image_small` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `image_medium` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `image_large` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `webp_path` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `avif_path` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `title` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `description` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `color_tag` VARCHAR(50) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `display_order` INT DEFAULT 9999,
  ADD COLUMN IF NOT EXISTS `featured` TINYINT(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `effect_strength` INT DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Note: Some MySQL versions don't support IF NOT EXISTS on ADD COLUMN; if your
-- server errors on this, run the migration script: php scripts/migrate_gallery_schema.php
