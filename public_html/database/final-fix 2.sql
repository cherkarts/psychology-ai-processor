-- Финальное исправление всех проблем
-- Примените этот SQL через phpMyAdmin или admin/apply-migration.php

-- 1. Создаем таблицу comments
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_type` varchar(50) NOT NULL,
  `content_id` varchar(100) NOT NULL,
  `text` text NOT NULL,
  `telegram_user_id` bigint(20) NOT NULL,
  `telegram_username` varchar(100) DEFAULT NULL,
  `telegram_first_name` varchar(100) DEFAULT NULL,
  `telegram_last_name` varchar(100) DEFAULT NULL,
  `telegram_avatar` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comments_content` (`content_type`,`content_id`),
  KEY `idx_comments_user` (`telegram_user_id`),
  KEY `idx_comments_status` (`status`),
  KEY `idx_comments_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Создаем таблицу comment_likes
CREATE TABLE IF NOT EXISTS `comment_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comment_user_like` (`comment_id`,`user_id`),
  KEY `idx_comment_likes_comment` (`comment_id`),
  KEY `idx_comment_likes_user` (`user_id`),
  CONSTRAINT `fk_comment_likes_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Создаем таблицу comment_reports
CREATE TABLE IF NOT EXISTS `comment_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_comment_user_report` (`comment_id`,`user_id`),
  KEY `idx_comment_reports_comment` (`comment_id`),
  KEY `idx_comment_reports_user` (`user_id`),
  CONSTRAINT `fk_comment_reports_comment` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Добавляем поле telegram_user_id в таблицу reviews
ALTER TABLE `reviews` ADD COLUMN `telegram_user_id` BIGINT NULL AFTER `email`;

-- 5. Добавляем индекс для telegram_user_id
CREATE INDEX idx_reviews_telegram_user_id ON `reviews` (`telegram_user_id`);

-- 6. Добавляем поле website (honeypot) в таблицу reviews
ALTER TABLE `reviews` ADD COLUMN IF NOT EXISTS `website` VARCHAR(255) NULL AFTER `telegram_user_id`;
