ALTER TABLE `reviews` ADD COLUMN IF NOT EXISTS `website` VARCHAR(255) NULL AFTER `telegram_user_id`;
ALTER TABLE `reviews` ADD COLUMN IF NOT EXISTS `telegram_avatar` VARCHAR(500) NULL AFTER `telegram_username`;
ALTER TABLE `reviews` MODIFY COLUMN `tags` JSON NULL;
