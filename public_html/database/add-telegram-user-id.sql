-- Добавляем недостающее поле telegram_user_id в таблицу reviews
ALTER TABLE `reviews` 
ADD COLUMN `telegram_user_id` BIGINT NULL AFTER `email`;

-- Добавляем индекс для нового поля
CREATE INDEX idx_reviews_telegram_user_id ON `reviews` (`telegram_user_id`);
