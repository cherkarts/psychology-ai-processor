-- Исправление ошибки с csrf_token
-- Удаляем колонку csrf_token из таблицы reviews, если она существует

-- Проверяем, существует ли колонка csrf_token
-- Если да, то удаляем её
ALTER TABLE `reviews` DROP COLUMN IF EXISTS `csrf_token`;

-- Также удаляем другие ненужные колонки, если они есть
ALTER TABLE `reviews` DROP COLUMN IF EXISTS `website`;
ALTER TABLE `reviews` DROP COLUMN IF EXISTS `honeypot`;
