-- ============================================================
-- ИСПРАВЛЕНИЕ КОДИРОВКИ СТАТЕЙ (кракозябры → нормальный текст)
-- ============================================================
-- Дата: 2025-10-01
-- Выполнять БЛОКАМИ через phpMyAdmin
-- ============================================================

-- ┌────────────────────────────────────────────────────────┐
-- │ ШАГ 1: БЭКАП (ОБЯЗАТЕЛЬНО!)                            │
-- │ Создаёт копию таблицы articles перед любыми изменениями│
-- └────────────────────────────────────────────────────────┘

CREATE TABLE articles_backup_20251001 AS SELECT * FROM articles;

-- Проверить, что бэкап создан:
-- SELECT COUNT(*) FROM articles_backup_20251001;


-- ┌────────────────────────────────────────────────────────┐
-- │ ШАГ 2: УСТАНОВИТЬ UTF-8 ДЛЯ СЕССИИ                    │
-- └────────────────────────────────────────────────────────┘

SET NAMES utf8mb4;


-- ┌────────────────────────────────────────────────────────┐
-- │ ШАГ 3: ПРЕВЬЮ "БИТЫХ" СТРОК                            │
-- │ Посмотрите, какие статьи содержат кракозябры           │
-- └────────────────────────────────────────────────────────┘

-- Проверка заголовков:
SELECT id, title, LEFT(content, 100) AS preview
FROM articles
WHERE title LIKE '%Р%' OR title LIKE '%РІ%' OR title LIKE '%Рѕ%'
   OR content LIKE '%Р%' OR content LIKE '%РІ%' OR content LIKE '%Рѕ%'
ORDER BY id DESC
LIMIT 20;

-- Если видите кракозябры (РІС‹РіРѕСЂР°РЅРёРµ и т.п.) - переходите к Шагу 4


-- ┌────────────────────────────────────────────────────────┐
-- │ ШАГ 4: ИСПРАВЛЕНИЕ ДАННЫХ                              │
-- │ Конвертация cp1251 → utf8mb4                           │
-- └────────────────────────────────────────────────────────┘

-- 4.1 Исправить заголовки и краткое описание:
UPDATE articles
SET title   = CONVERT(CAST(CONVERT(title   USING cp1251) AS BINARY) USING utf8mb4),
    excerpt = CONVERT(CAST(CONVERT(excerpt USING cp1251) AS BINARY) USING utf8mb4)
WHERE title   LIKE '%Р%' OR title   LIKE '%РІ%' OR title   LIKE '%Рѕ%'
   OR excerpt LIKE '%Р%' OR excerpt LIKE '%РІ%' OR excerpt LIKE '%Рѕ%';

-- 4.2 Исправить содержимое статей:
UPDATE articles
SET content = CONVERT(CAST(CONVERT(content USING cp1251) AS BINARY) USING utf8mb4)
WHERE content LIKE '%Р%' OR content LIKE '%РІ%' OR content LIKE '%Рѕ%';

-- 4.3 Исправить имена авторов (если нужно):
UPDATE articles
SET author = CONVERT(CAST(CONVERT(author USING cp1251) AS BINARY) USING utf8mb4)
WHERE author LIKE '%Р%' OR author LIKE '%РІ%' OR author LIKE '%Рѕ%';


-- ┌────────────────────────────────────────────────────────┐
-- │ ШАГ 5: ЗАФИКСИРОВАТЬ КОДИРОВКУ ТАБЛИЦЫ                │
-- │ Все новые данные будут автоматически utf8mb4           │
-- └────────────────────────────────────────────────────────┘

ALTER TABLE articles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- ┌────────────────────────────────────────────────────────┐
-- │ ШАГ 6: ПРОВЕРКА РЕЗУЛЬТАТА                             │
-- │ Заголовки должны быть читаемыми                        │
-- └────────────────────────────────────────────────────────┘

SELECT id, title, author, LEFT(content, 150) AS preview
FROM articles
ORDER BY id DESC
LIMIT 10;


-- ┌────────────────────────────────────────────────────────┐
-- │ ЕСЛИ ЧТО-ТО ПОШЛО НЕ ТАК - ОТКАТ К БЭКАПУ            │
-- └────────────────────────────────────────────────────────┘

-- Удалить испорченную таблицу:
-- DROP TABLE articles;

-- Восстановить из бэкапа:
-- CREATE TABLE articles AS SELECT * FROM articles_backup_20251001;

-- Вернуть индексы и ключи (если нужно):
-- ALTER TABLE articles ADD PRIMARY KEY (id);
-- ALTER TABLE articles MODIFY id INT AUTO_INCREMENT;

