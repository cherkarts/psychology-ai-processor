-- Spotlights table for site showcase units
CREATE TABLE IF NOT EXISTS `spotlights` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT NULL,
  `cta_label` VARCHAR(120) NULL,
  `cta_url` VARCHAR(512) NULL,
  `media_type` ENUM('none','image','video') NOT NULL DEFAULT 'none',
  `media_url` VARCHAR(512) NULL,
  `bg_style` VARCHAR(120) NULL,
  `contexts` VARCHAR(255) NOT NULL DEFAULT 'all', -- comma-separated: shop,articles,article_sidebar,product,meditations
  `group_key` VARCHAR(120) NULL, -- carousel grouping key
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `starts_at` DATETIME NULL,
  `ends_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_spotlights_active` (`is_active`,`starts_at`,`ends_at`),
  KEY `idx_spotlights_contexts` (`contexts`),
  KEY `idx_spotlights_group` (`group_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Полная схема базы данных для сайта психолога Дениса Черкаса
-- Создаем базу данных
CREATE DATABASE IF NOT EXISTS cherkas_therapy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cherkas_therapy;

-- Таблица пользователей/клиентов
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) NULL,
    name VARCHAR(100) NOT NULL,
    telegram_username VARCHAR(100) NULL,
    telegram_id BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    subscription_status ENUM('free', 'premium', 'cancelled') DEFAULT 'free',
    subscription_expires_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_telegram_username (telegram_username),
    INDEX idx_created_at (created_at)
);

-- Таблица категорий товаров
CREATE TABLE IF NOT EXISTS product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    image VARCHAR(500) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_sort_order (sort_order)
);

-- Таблица товаров/продуктов
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(500) NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    old_price DECIMAL(10,2) NULL,
    currency ENUM('RUB', 'USD', 'EUR') DEFAULT 'RUB',
    category_id INT NULL,
    type ENUM('digital', 'physical', 'service', 'free') NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    image VARCHAR(500) NULL,
    gallery JSON NULL,
    features JSON NULL,
    content LONGTEXT NULL,
    download_url VARCHAR(500) NULL,
    telegram_required BOOLEAN DEFAULT FALSE,
    whatsapp_contact VARCHAR(20) NULL,
    telegram_contact VARCHAR(100) NULL,
    in_stock BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    tags JSON NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category_id (category_id),
    INDEX idx_type (type),
    INDEX idx_is_featured (is_featured),
    INDEX idx_in_stock (in_stock),
    INDEX idx_created_at (created_at)
);

-- Таблица заказов
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    name VARCHAR(100) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    currency ENUM('RUB', 'USD', 'EUR') DEFAULT 'RUB',
    payment_method ENUM('card', 'cash', 'transfer', 'telegram') NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_id VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_order_number (order_number),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at)
);

-- Таблица элементов заказа
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_title VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);

-- Таблица категорий медитаций
CREATE TABLE IF NOT EXISTS meditation_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(50) NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_sort_order (sort_order)
);

-- Таблица медитаций
CREATE TABLE IF NOT EXISTS meditations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255) NULL,
    category_id INT NULL,
    duration INT NULL, -- в секундах
    description TEXT NULL,
    audio_file VARCHAR(500) NULL,
    icon VARCHAR(50) NULL,
    meta_description TEXT NULL,
    likes INT DEFAULT 0,
    favorites INT DEFAULT 0,
    is_free BOOLEAN DEFAULT FALSE,
    telegram_required BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES meditation_categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category_id (category_id),
    INDEX idx_is_free (is_free),
    INDEX idx_created_at (created_at)
);

-- Таблица категорий статей
CREATE TABLE IF NOT EXISTS article_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_sort_order (sort_order)
);

-- Таблица статей
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    category_id INT NULL,
    author VARCHAR(100) DEFAULT 'Денис Черкас',
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    featured_image VARCHAR(500) NULL,
    tags JSON NULL,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES article_categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category_id (category_id),
    INDEX idx_is_published (is_published),
    INDEX idx_published_at (published_at),
    INDEX idx_created_at (created_at)
);

-- Таблица отзывов (обновленная версия)
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('text', 'photo', 'video') DEFAULT 'text',
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telegram_username VARCHAR(100) NULL,
    telegram_avatar VARCHAR(500) NULL,
    phone VARCHAR(20) NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    text TEXT NOT NULL,
    age INT NULL,
    tags JSON NULL,
    image VARCHAR(500) NULL,
    image_type VARCHAR(50) NULL,
    video VARCHAR(500) NULL,
    thumbnail VARCHAR(500) NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    approved_by VARCHAR(100) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    verification_method ENUM('telegram', 'email', 'sms', 'none') NOT NULL,
    verification_status BOOLEAN DEFAULT FALSE,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_rating (rating),
    INDEX idx_type (type)
);

-- Таблица логов верификации
CREATE TABLE IF NOT EXISTS verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NULL,
    method ENUM('telegram', 'email', 'sms') NOT NULL,
    identifier VARCHAR(255) NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    verification_code VARCHAR(10) NULL,
    attempts INT DEFAULT 0,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE SET NULL,
    INDEX idx_identifier (identifier),
    INDEX idx_status (status),
    INDEX idx_expires_at (expires_at)
);

-- Таблица настроек сайта
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица настроек модерации
CREATE TABLE IF NOT EXISTS moderation_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица логов действий
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id INT NULL,
    details JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity_type (entity_type),
    INDEX idx_created_at (created_at)
);

-- Вставляем базовые настройки сайта
INSERT INTO site_settings (setting_key, setting_value, description) VALUES
('site_name', 'Психолог Денис Черкас', 'Название сайта'),
('site_url', 'https://cherkas-therapy.ru', 'URL сайта'),
('site_description', 'Консультации психолога: онлайн, от 15 мин бесплатно до 50 мин - 2500₽ с поддержкой.', 'Описание сайта'),
('telegram_bot_token', '7657713367:AAEDdQSD1K1g8ckRI-R-ePB7s1AtXc4OuyE', 'Токен Telegram бота'),
('telegram_chat_id', '-1002418481743', 'ID Telegram чата'),
('email_to', 'cherkarts.denis@gmail.com', 'Email для уведомлений'),
('email_from', 'cherkarts.denis@gmail.com', 'Email отправителя'),
('csrf_token_name', 'cherkas_csrf_token', 'Название CSRF токена'),
('honeypot_field', 'website', 'Поле-ловушка для спама');

-- Вставляем базовые настройки модерации
INSERT INTO moderation_settings (setting_key, setting_value, description) VALUES
('require_verification', '1', 'Требовать ли верификацию пользователей'),
('verification_method', 'telegram', 'Метод верификации: telegram, email, sms, none'),
('auto_approve', '0', 'Автоматически одобрять отзывы'),
('min_rating', '1', 'Минимальная оценка'),
('max_rating', '5', 'Максимальная оценка'),
('min_text_length', '10', 'Минимальная длина текста отзыва'),
('max_text_length', '2000', 'Максимальная длина текста отзыва'),
('telegram_channel_username', '@cherkas_therapy', 'Username Telegram канала для проверки подписки'),
('email_notifications', '1', 'Уведомления на email о новых отзывах'),
('telegram_notifications', '1', 'Уведомления в Telegram о новых отзывах');

-- Вставляем базовые категории товаров
INSERT INTO product_categories (slug, name, description, sort_order) VALUES
('meditations', 'Медитации', 'Аудио медитации для различных целей', 1),
('groups', 'Групповые занятия', 'Онлайн группы и групповые занятия', 2),
('seminars', 'Семинары', 'Обучающие семинары и вебинары', 3),
('books', 'Книги', 'Печатные и электронные книги', 4),
('courses', 'Курсы', 'Онлайн курсы и программы', 5);

-- Вставляем базовые категории медитаций
INSERT INTO meditation_categories (slug, name, description, icon, sort_order) VALUES
('anxiety', 'Тревога и стресс', 'Медитации для снятия тревожности и стресса', 'anxiety', 1),
('confidence', 'Уверенность в себе', 'Медитации для повышения самооценки и уверенности', 'confidence', 2),
('self-esteem', 'Самооценка', 'Медитации для работы с самооценкой', 'self-esteem', 3),
('children', 'Детские медитации', 'Специальные медитации для детей', 'children', 4),
('stories', 'Детские сказки', 'Расслабляющие сказки для детей', 'stories', 5),
('sleep', 'Сон и релаксация', 'Медитации для улучшения сна', 'sleep', 6),
('relationships', 'Отношения', 'Медитации для гармонии в отношениях', 'relationships', 7),
('gratitude', 'Благодарность', 'Медитации благодарности', 'gratitude', 8),
('success', 'Успех и достижения', 'Медитации для достижения успеха и реализации целей', 'success', 9);

-- Вставляем базовые категории статей
INSERT INTO article_categories (slug, name, description, sort_order) VALUES
('psychology', 'Психология', 'Статьи по общей психологии', 1),
('relationships', 'Отношения', 'Статьи о взаимоотношениях', 2),
('self-development', 'Саморазвитие', 'Статьи о личностном росте', 3),
('stress-management', 'Управление стрессом', 'Статьи о работе со стрессом', 4),
('depression-help', 'Помощь при депрессии', 'Статьи о преодолении депрессии', 5);



