-- SQL DDL for gallery table
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image_path` VARCHAR(255) DEFAULT NULL,
  `image_small` VARCHAR(255) DEFAULT NULL,
  `image_medium` VARCHAR(255) DEFAULT NULL,
  `image_large` VARCHAR(255) DEFAULT NULL,
  `webp_path` VARCHAR(255) DEFAULT NULL,
  `avif_path` VARCHAR(255) DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `color_tag` VARCHAR(50) DEFAULT NULL,
  `display_order` INT DEFAULT 9999,
  `featured` TINYINT(1) DEFAULT 0,
  `effect_strength` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
