-- Миграция для системы отзывов товаров
-- Создает таблицы для хранения отзывов, лайков и жалоб

-- Таблица пользователей Telegram
CREATE TABLE IF NOT EXISTS `telegram_users` (
  `telegram_id` bigint(20) NOT NULL COMMENT 'ID пользователя в Telegram',
  `telegram_username` varchar(100) DEFAULT NULL COMMENT 'Username в Telegram',
  `telegram_first_name` varchar(100) DEFAULT NULL COMMENT 'Имя пользователя',
  `telegram_last_name` varchar(100) DEFAULT NULL COMMENT 'Фамилия пользователя',
  `telegram_avatar` varchar(500) DEFAULT NULL COMMENT 'URL аватара',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`telegram_id`),
  KEY `idx_username` (`telegram_username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Пользователи Telegram';

-- Таблица отзывов товаров
CREATE TABLE IF NOT EXISTS `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(255) NOT NULL COMMENT 'ID товара',
  `telegram_user_id` bigint(20) NOT NULL COMMENT 'ID пользователя Telegram',
  `rating` tinyint(1) NOT NULL COMMENT 'Оценка от 1 до 5',
  `text` text NOT NULL COMMENT 'Текст отзыва',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT 'Статус модерации',
  `moderator_comment` text DEFAULT NULL COMMENT 'Комментарий модератора',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_product_review` (`product_id`, `telegram_user_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_telegram_user_id` (`telegram_user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_rating` (`rating`),
  KEY `idx_product_status` (`product_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Отзывы о товарах';

-- Таблица лайков отзывов
CREATE TABLE IF NOT EXISTS `product_review_likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `review_id` int(11) NOT NULL COMMENT 'ID отзыва',
  `telegram_user_id` bigint(20) NOT NULL COMMENT 'ID пользователя Telegram',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_review_like` (`review_id`, `telegram_user_id`),
  KEY `idx_review_id` (`review_id`),
  KEY `idx_telegram_user_id` (`telegram_user_id`),
  CONSTRAINT `fk_product_review_likes_review` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Лайки отзывов о товарах';

-- Таблица жалоб на отзывы
CREATE TABLE IF NOT EXISTS `product_review_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `review_id` int(11) NOT NULL COMMENT 'ID отзыва',
  `telegram_user_id` bigint(20) NOT NULL COMMENT 'ID пользователя, подавшего жалобу',
  `reason` text DEFAULT NULL COMMENT 'Причина жалобы',
  `status` enum('pending','reviewed','resolved') NOT NULL DEFAULT 'pending' COMMENT 'Статус жалобы',
  `moderator_comment` text DEFAULT NULL COMMENT 'Комментарий модератора',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_review_report` (`review_id`, `telegram_user_id`),
  KEY `idx_review_id` (`review_id`),
  KEY `idx_telegram_user_id` (`telegram_user_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_product_review_reports_review` FOREIGN KEY (`review_id`) REFERENCES `product_reviews` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Жалобы на отзывы о товарах';

-- Индексы уже включены в CREATE TABLE выше

-- Создаем представление для статистики отзывов
CREATE OR REPLACE VIEW `product_reviews_stats` AS
SELECT 
    pr.product_id,
    COUNT(*) as total_reviews,
    AVG(pr.rating) as average_rating,
    COUNT(CASE WHEN pr.rating = 5 THEN 1 END) as five_stars,
    COUNT(CASE WHEN pr.rating = 4 THEN 1 END) as four_stars,
    COUNT(CASE WHEN pr.rating = 3 THEN 1 END) as three_stars,
    COUNT(CASE WHEN pr.rating = 2 THEN 1 END) as two_stars,
    COUNT(CASE WHEN pr.rating = 1 THEN 1 END) as one_star,
    COUNT(prl.id) as total_likes,
    COUNT(prr.id) as total_reports
FROM product_reviews pr
LEFT JOIN product_review_likes prl ON pr.id = prl.review_id
LEFT JOIN product_review_reports prr ON pr.id = prr.review_id
WHERE pr.status = 'approved'
GROUP BY pr.product_id;
