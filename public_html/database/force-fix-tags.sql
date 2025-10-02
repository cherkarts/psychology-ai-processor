-- Принудительное исправление ограничения tags
-- Этот SQL удаляет ограничение и пересоздает поле tags

-- Сначала удаляем поле tags
ALTER TABLE `reviews` DROP COLUMN `tags`;

-- Затем добавляем его заново без ограничений
ALTER TABLE `reviews` ADD COLUMN `tags` JSON NULL AFTER `age`;

-- Добавляем недостающие поля
ALTER TABLE `reviews` ADD COLUMN IF NOT EXISTS `website` VARCHAR(255) NULL AFTER `telegram_user_id`;
ALTER TABLE `reviews` ADD COLUMN IF NOT EXISTS `telegram_avatar` VARCHAR(500) NULL AFTER `telegram_username`;

