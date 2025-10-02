-- Создание таблицы промокодов
USE cherkas_therapy;

CREATE TABLE IF NOT EXISTS promo_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    value DECIMAL(10,2) NOT NULL,
    min_amount DECIMAL(10,2) DEFAULT 0.00,
    max_uses INT NULL,
    used_count INT DEFAULT 0,
    valid_from TIMESTAMP NULL,
    valid_until TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_is_active (is_active),
    INDEX idx_valid_from (valid_from),
    INDEX idx_valid_until (valid_until),
    INDEX idx_created_at (created_at)
);

-- Создание таблицы использования промокодов
CREATE TABLE IF NOT EXISTS promo_code_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    promo_code_id INT NOT NULL,
    order_id INT NOT NULL,
    user_id INT NULL,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_promo_code_id (promo_code_id),
    INDEX idx_order_id (order_id),
    INDEX idx_user_id (user_id),
    INDEX idx_used_at (used_at)
);

-- Добавление поля promo_code_id в таблицу заказов
ALTER TABLE orders ADD COLUMN promo_code_id INT NULL AFTER payment_method;
ALTER TABLE orders ADD FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id) ON DELETE SET NULL;
ALTER TABLE orders ADD INDEX idx_promo_code_id (promo_code_id);

-- Добавление поля discount_amount в таблицу заказов
ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00 AFTER total_amount;

-- Вставка тестовых промокодов
INSERT INTO promo_codes (code, name, description, type, value, min_amount, max_uses, valid_from, valid_until, is_active) VALUES
('WELCOME10', 'Приветственная скидка 10%', 'Скидка 10% для новых клиентов', 'percentage', 10.00, 1000.00, 100, NULL, '2025-12-31 23:59:59', TRUE),
('FIXED500', 'Фиксированная скидка 500₽', 'Скидка 500 рублей при заказе от 2000₽', 'fixed', 500.00, 2000.00, 50, NULL, '2025-12-31 23:59:59', TRUE),
('SUMMER20', 'Летняя скидка 20%', 'Скидка 20% на все услуги', 'percentage', 20.00, 500.00, 200, '2025-06-01 00:00:00', '2025-08-31 23:59:59', TRUE),
('NEWYEAR15', 'Новогодняя скидка 15%', 'Скидка 15% в новом году', 'percentage', 15.00, 1500.00, 150, '2025-01-01 00:00:00', '2025-01-31 23:59:59', FALSE);

-- Обновление счетчика использований (если есть данные в promo_code_usage)
UPDATE promo_codes pc 
SET used_count = (
    SELECT COUNT(*) 
    FROM promo_code_usage pcu 
    WHERE pcu.promo_code_id = pc.id
);


