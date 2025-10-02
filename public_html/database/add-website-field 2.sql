-- Добавляем поле website (honeypot) в таблицу reviews
ALTER TABLE `reviews` 
ADD COLUMN IF NOT EXISTS `website` VARCHAR(255) NULL AFTER `telegram_user_id`;

