-- Быстрое исправление: добавляем недостающие поля в таблицу reviews
ALTER TABLE `reviews` ADD COLUMN IF NOT EXISTS `website` VARCHAR(255) NULL AFTER `telegram_user_id`;
ALTER TABLE `reviews` ADD COLUMN IF NOT EXISTS `telegram_avatar` VARCHAR(500) NULL AFTER `telegram_username`;

-- Убираем ограничение на поле tags (если оно существует)
-- MariaDB не поддерживает DROP CHECK IF EXISTS, поэтому используем другой подход

-- Убеждаемся, что поле tags может принимать NULL значения
ALTER TABLE `reviews` MODIFY COLUMN `tags` JSON NULL;зыв одобрен

