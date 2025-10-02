-- Таблица для отзывов с модерацией
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

-- Таблица для логов верификации
CREATE TABLE IF NOT EXISTS verification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NULL,
    method ENUM('telegram', 'email', 'sms') NOT NULL,
    identifier VARCHAR(255) NOT NULL, -- telegram username, email или phone
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

-- Таблица для настроек модерации
CREATE TABLE IF NOT EXISTS moderation_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Вставляем базовые настройки
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