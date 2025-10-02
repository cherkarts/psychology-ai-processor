-- Создание таблицы для ярлыков товаров
CREATE TABLE IF NOT EXISTS product_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#007bff',
    background_color VARCHAR(7) DEFAULT '#ffffff',
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Создание таблицы связи товаров и ярлыков
CREATE TABLE IF NOT EXISTS product_badge_relations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    badge_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES product_badges(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_badge (product_id, badge_id)
);

-- Вставка стандартных ярлыков
INSERT INTO product_badges (name, slug, color, background_color, sort_order) VALUES
('Акция', 'sale', '#ffffff', '#dc3545', 1),
('Новый товар', 'new', '#ffffff', '#28a745', 2),
('Хит продаж', 'bestseller', '#ffffff', '#ffc107', 3),
('Бесплатно', 'free', '#ffffff', '#17a2b8', 4),
('Рекомендуем', 'recommended', '#ffffff', '#6f42c1', 5),
('Ограниченное предложение', 'limited', '#ffffff', '#fd7e14', 6),
('Скоро в продаже', 'coming-soon', '#ffffff', '#6c757d', 7),
('Распродажа', 'clearance', '#ffffff', '#e83e8c', 8)
ON DUPLICATE KEY UPDATE name = VALUES(name);
