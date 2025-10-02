-- Таблица комментариев
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `content_type` ENUM('article', 'product') NOT NULL,
  `content_id` INT UNSIGNED NOT NULL,
  `text` TEXT NOT NULL,
  `telegram_user_id` BIGINT NOT NULL,
  `telegram_username` VARCHAR(100) NULL,
  `telegram_first_name` VARCHAR(100) NULL,
  `telegram_last_name` VARCHAR(100) NULL,
  `telegram_avatar` VARCHAR(500) NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` TIMESTAMP NULL,
  `approved_by` VARCHAR(100) NULL,
  PRIMARY KEY (`id`),
  KEY `idx_content` (`content_type`, `content_id`),
  KEY `idx_telegram_user` (`telegram_user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица лайков комментариев
CREATE TABLE IF NOT EXISTS `comment_likes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_id` INT UNSIGNED NOT NULL,
  `user_id` BIGINT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`comment_id`, `user_id`),
  KEY `idx_comment` (`comment_id`),
  KEY `idx_user` (`user_id`),
  FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица жалоб на комментарии
CREATE TABLE IF NOT EXISTS `comment_reports` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_id` INT UNSIGNED NOT NULL,
  `user_id` BIGINT NOT NULL,
  `reason` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_report` (`comment_id`, `user_id`),
  KEY `idx_comment` (`comment_id`),
  KEY `idx_user` (`user_id`),
  FOREIGN KEY (`comment_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Обновляем таблицу отзывов, добавляя поля для Telegram
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `telegram_user_id` BIGINT NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `telegram_username` VARCHAR(100) NULL AFTER `telegram_user_id`,
ADD COLUMN IF NOT EXISTS `telegram_first_name` VARCHAR(100) NULL AFTER `telegram_username`,
ADD COLUMN IF NOT EXISTS `telegram_last_name` VARCHAR(100) NULL AFTER `telegram_first_name`,
ADD COLUMN IF NOT EXISTS `telegram_avatar` VARCHAR(500) NULL AFTER `telegram_last_name`,
ADD KEY IF NOT EXISTS `idx_telegram_user` (`telegram_user_id`);

-- Создаем таблицу для хранения сессий Telegram пользователей
CREATE TABLE IF NOT EXISTS `telegram_users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `telegram_user_id` BIGINT UNIQUE NOT NULL,
  `username` VARCHAR(100) NULL,
  `first_name` VARCHAR(100) NULL,
  `last_name` VARCHAR(100) NULL,
  `photo_url` VARCHAR(500) NULL,
  `is_bot` BOOLEAN DEFAULT FALSE,
  `language_code` VARCHAR(10) NULL,
  `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_telegram_user` (`telegram_user_id`),
  KEY `idx_username` (`username`),
  KEY `idx_last_seen` (`last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
