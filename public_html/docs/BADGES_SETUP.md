# Установка системы ярлыков для товаров

## 1. Создание таблиц в базе данных

Выполните SQL скрипт для создания таблиц:

```sql
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
```

## 2. Файлы для перезаписи на хостинг

Перезапишите следующие файлы:

### Админка:

- `/admin/products-v5.php` - форма редактирования товаров с выбором ярлыков
- `/admin/api/save-product.php` - API сохранения товаров с обработкой ярлыков
- `/admin/api/get-badges.php` - API получения списка ярлыков
- `/admin/api/get-product-badges.php` - API получения ярлыков товара

### Фронтенд:

- `/product.php` - страница товара с отображением ярлыков
- `/includes/Models/Product.php` - модель товара с загрузкой ярлыков

## 3. Функциональность

### В админке:

- При создании/редактировании товара можно выбрать ярлыки из списка
- Ярлыки отображаются с превью цветов
- Поддержка множественного выбора
- Автоматическая загрузка существующих ярлыков при редактировании

### На сайте:

- Ярлыки отображаются под заголовком товара
- Каждый ярлык имеет свой цвет и фон
- Адаптивный дизайн для мобильных устройств
- Анимация при наведении

## 4. Стандартные ярлыки

Система поставляется с 8 предустановленными ярлыками:

- **Акция** (красный фон)
- **Новый товар** (зеленый фон)
- **Хит продаж** (желтый фон)
- **Бесплатно** (голубой фон)
- **Рекомендуем** (фиолетовый фон)
- **Ограниченное предложение** (оранжевый фон)
- **Скоро в продаже** (серый фон)
- **Распродажа** (розовый фон)

## 5. Добавление новых ярлыков

Для добавления новых ярлыков выполните SQL запрос:

```sql
INSERT INTO product_badges (name, slug, color, background_color, sort_order)
VALUES ('Название', 'slug', '#цвет_текста', '#цвет_фона', порядок);
```

## 6. Настройка цветов

Цвета задаются в формате HEX:

- `color` - цвет текста
- `background_color` - цвет фона

Примеры:

- `#ffffff` - белый
- `#000000` - черный
- `#dc3545` - красный
- `#28a745` - зеленый
- `#ffc107` - желтый
