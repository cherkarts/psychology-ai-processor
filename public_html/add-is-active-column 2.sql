-- Добавляем колонку is_active в таблицу products
ALTER TABLE products ADD COLUMN is_active TINYINT(1) DEFAULT 1;

-- Обновляем существующие товары как активные
UPDATE products SET is_active = 1 WHERE is_active IS NULL;

-- Добавляем индекс для быстрого поиска
CREATE INDEX idx_products_is_active ON products(is_active);
