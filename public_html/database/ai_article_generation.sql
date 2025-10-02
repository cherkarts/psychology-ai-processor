-- Система автоматической генерации статей с ИИ
-- Таблица для хранения задач генерации статей

CREATE TABLE IF NOT EXISTS ai_generation_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id VARCHAR(64) UNIQUE NOT NULL, -- Уникальный ID задачи для API
    title VARCHAR(255) NOT NULL,
    topic VARCHAR(500) NOT NULL, -- Тема статьи
    keywords TEXT NULL, -- Ключевые слова (JSON)
    category_id INT NULL,
    target_audience VARCHAR(255) NULL, -- Целевая аудитория
    tone ENUM('professional', 'friendly', 'academic', 'conversational') DEFAULT 'professional',
    word_count INT DEFAULT 1500, -- Желаемое количество слов
    include_faq BOOLEAN DEFAULT FALSE, -- Включить FAQ раздел
    include_quotes BOOLEAN DEFAULT TRUE, -- Включить цитаты
    include_internal_links BOOLEAN DEFAULT TRUE, -- Включить внутренние ссылки
    include_table_of_contents BOOLEAN DEFAULT TRUE, -- Включить оглавление
    seo_optimization BOOLEAN DEFAULT TRUE, -- SEO оптимизация
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    created_by INT NULL, -- ID пользователя, создавшего задачу
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    api_response JSON NULL, -- Ответ от внешнего API
    generated_article_id INT NULL, -- ID созданной статьи
    FOREIGN KEY (category_id) REFERENCES article_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_task_id (task_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_created_at (created_at),
    INDEX idx_created_by (created_by)
);

-- Таблица для хранения настроек генерации
CREATE TABLE IF NOT EXISTS ai_generation_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT NULL,
    is_encrypted BOOLEAN DEFAULT FALSE, -- Для API ключей
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Таблица для хранения промптов и шаблонов
CREATE TABLE IF NOT EXISTS ai_prompts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    prompt_template TEXT NOT NULL, -- Шаблон промпта
    variables JSON NULL, -- Переменные для подстановки
    category VARCHAR(100) NULL, -- Категория промпта
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_is_active (is_active)
);

-- Таблица для логов генерации
CREATE TABLE IF NOT EXISTS ai_generation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NULL,
    action VARCHAR(100) NOT NULL,
    message TEXT NULL,
    details JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES ai_generation_tasks(id) ON DELETE CASCADE,
    INDEX idx_task_id (task_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Вставка базовых настроек
INSERT INTO ai_generation_settings (setting_key, setting_value, description) VALUES
('api_endpoint', 'https://your-ai-service.com/api/generate', 'URL внешнего API для генерации'),
('api_timeout', '300', 'Таймаут запроса в секундах'),
('max_concurrent_tasks', '5', 'Максимальное количество одновременных задач'),
('default_word_count', '1500', 'Стандартное количество слов для статьи'),
('image_search_enabled', 'true', 'Включить поиск изображений'),
('image_search_provider', 'unsplash', 'Провайдер для поиска изображений'),
('auto_publish', 'false', 'Автоматически публиковать сгенерированные статьи'),
('review_required', 'true', 'Требовать проверку перед публикацией');

-- Вставка базовых промптов
INSERT INTO ai_prompts (name, description, prompt_template, variables, category) VALUES
('Основной промпт для статей', 'Базовый промпт для генерации статей по психологии', 
'Создай информативную статью на тему "{topic}" для сайта психолога. 

Требования:
- Ключевые слова: {keywords}
- Целевая аудитория: {target_audience}
- Тон: {tone}
- Количество слов: {word_count}
- Включить FAQ: {include_faq}
- Включить цитаты: {include_quotes}
- Включить внутренние ссылки: {include_internal_links}
- Включить оглавление: {include_table_of_contents}
- SEO оптимизация: {seo_optimization}

Структура статьи:
1. Заголовок H1
2. Краткое введение
3. Оглавление с якорными ссылками (если включено)
4. Основной контент с подзаголовками H2, H3
5. Цитаты и примеры (если включено)
6. FAQ раздел (если включено)
7. Заключение
8. Призыв к действию

Формат ответа: HTML с правильной разметкой заголовков, параграфов, списков и ссылок.',
'{"topic": "string", "keywords": "array", "target_audience": "string", "tone": "string", "word_count": "integer", "include_faq": "boolean", "include_quotes": "boolean", "include_internal_links": "boolean", "include_table_of_contents": "boolean", "seo_optimization": "boolean"}',
'psychology_articles'),

('Промпт для FAQ', 'Промпт для генерации FAQ раздела',
'Создай FAQ раздел для статьи на тему "{topic}".

Вопросы должны быть:
- Актуальными для целевой аудитории: {target_audience}
- Основанными на ключевых словах: {keywords}
- Практичными и полезными
- Количество вопросов: 5-8

Формат: HTML с тегами <h3> для вопросов и <p> для ответов.',
'{"topic": "string", "keywords": "array", "target_audience": "string"}',
'faq_section'),

('Промпт для поиска изображений', 'Промпт для генерации запросов поиска изображений',
'Найди подходящее изображение для статьи на тему "{topic}".

Критерии:
- Соответствует теме: {topic}
- Ключевые слова: {keywords}
- Стиль: профессиональный, психологический
- Размер: минимум 1200x800 пикселей
- Лицензия: бесплатная для коммерческого использования

Предложи 3 варианта запросов для поиска изображений.',
'{"topic": "string", "keywords": "array"}',
'image_search');
