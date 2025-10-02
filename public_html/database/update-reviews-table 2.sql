-- Обновление таблицы reviews для поддержки Telegram авторизации
-- Добавляем недостающие поля, если их нет

-- Добавляем поля для Telegram, если их нет
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `telegram_user_id` BIGINT NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `telegram_username` VARCHAR(100) NULL AFTER `telegram_user_id`,
ADD COLUMN IF NOT EXISTS `telegram_first_name` VARCHAR(100) NULL AFTER `telegram_username`,
ADD COLUMN IF NOT EXISTS `telegram_last_name` VARCHAR(100) NULL AFTER `telegram_first_name`,
ADD COLUMN IF NOT EXISTS `telegram_avatar` VARCHAR(500) NULL AFTER `telegram_last_name`,
ADD COLUMN IF NOT EXISTS `ip_address` VARCHAR(45) NULL AFTER `telegram_avatar`,
ADD COLUMN IF NOT EXISTS `user_agent` TEXT NULL AFTER `ip_address`;

-- Добавляем индексы, если их нет
ALTER TABLE `reviews` 
ADD KEY IF NOT EXISTS `idx_telegram_user` (`telegram_user_id`),
ADD KEY IF NOT EXISTS `idx_email` (`email`),
ADD KEY IF NOT EXISTS `idx_status` (`status`),
ADD KEY IF NOT EXISTS `idx_created_at` (`created_at`);

-- Проверяем, что все необходимые поля существуют
-- Если поле 'email' не существует, добавляем его
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) NULL AFTER `name`;

-- Если поле 'age' не существует, добавляем его
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `age` INT NULL AFTER `email`;

-- Если поле 'tags' не существует, добавляем его
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `tags` JSON NULL AFTER `age`;

-- Если поле 'image_type' не существует, добавляем его
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `image_type` VARCHAR(50) NULL AFTER `image`;

-- Если поле 'approved_at' не существует, добавляем его
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `approved_at` TIMESTAMP NULL AFTER `approved_by`;
