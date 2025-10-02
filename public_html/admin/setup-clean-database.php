<?php
// Скрипт для создания чистой структуры базы данных
session_start();

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
  echo "❌ Неавторизован. Пожалуйста, войдите в админ-панель.\n";
  exit();
}

echo "=== СОЗДАНИЕ ЧИСТОЙ СТРУКТУРЫ БАЗЫ ДАННЫХ ===\n";

try {
  // Подключение к БД
  $config = require '../config.php';

  $pdo = new PDO(
    "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'],
    $config['database']['username'],
    $config['database']['password'],
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

  echo "✅ Подключение к БД успешно\n";

  // Создаем таблицу категорий медитаций
  echo "\nСоздание таблицы meditation_categories...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS meditation_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица meditation_categories создана\n";

  // Создаем таблицу медитаций
  echo "\nСоздание таблицы meditations...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS meditations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            audio_file VARCHAR(255),
            category_id INT,
            duration INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES meditation_categories(id) ON DELETE SET NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица meditations создана\n";

  // Создаем таблицу категорий товаров
  echo "\nСоздание таблицы product_categories...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS product_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица product_categories создана\n";

  // Создаем таблицу товаров
  echo "\nСоздание таблицы products...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            short_description TEXT,
            price DECIMAL(10,2) NOT NULL,
            category_id INT,
            image VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE SET NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица products создана\n";

  // Создаем таблицу категорий статей
  echo "\nСоздание таблицы article_categories...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS article_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            description TEXT,
            sort_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица article_categories создана\n";

  // Создаем таблицу статей
  echo "\nСоздание таблицы articles...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS articles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            content TEXT,
            excerpt TEXT,
            author VARCHAR(255),
            tags TEXT,
            category_id INT,
            image VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES article_categories(id) ON DELETE SET NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица articles создана\n";

  // Создаем таблицу отзывов
  echo "\nСоздание таблицы reviews...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            rating INT NOT NULL,
            comment TEXT,
            is_approved BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица reviews создана\n";

  // Создаем таблицу отзывов о товарах
  echo "\nСоздание таблицы product_reviews...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            rating INT NOT NULL,
            comment TEXT,
            is_approved BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица product_reviews создана\n";

  // Создаем таблицу промокодов
  echo "\nСоздание таблицы promo_codes...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS promo_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            code VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            discount_type ENUM('percentage', 'fixed') NOT NULL,
            discount_value DECIMAL(10,2) NOT NULL,
            min_order_amount DECIMAL(10,2) DEFAULT 0,
            max_uses INT DEFAULT NULL,
            used_count INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            valid_from TIMESTAMP NULL,
            valid_until TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица promo_codes создана\n";

  // Создаем таблицу заказов
  echo "\nСоздание таблицы orders...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50),
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            payment_method VARCHAR(50),
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица orders создана\n";

  // Создаем таблицу элементов заказов
  echo "\nСоздание таблицы order_items...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица order_items создана\n";

  // Создаем таблицу админов
  echo "\nСоздание таблицы admin_users...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'moderator') DEFAULT 'moderator',
            is_active BOOLEAN DEFAULT TRUE,
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица admin_users создана\n";

  // Создаем таблицу настроек
  echo "\nСоздание таблицы settings...\n";
  $sql = "
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
  $pdo->exec($sql);
  echo "✅ Таблица settings создана\n";

  // Добавляем базовые категории медитаций
  echo "\nДобавление базовых категорий медитаций...\n";
  $categories = [
    ['name' => 'Релаксация', 'slug' => 'relaxation', 'description' => 'Медитации для расслабления', 'sort_order' => 1],
    ['name' => 'Стресс', 'slug' => 'stress-relief', 'description' => 'Медитации для снятия стресса', 'sort_order' => 2],
    ['name' => 'Сон', 'slug' => 'sleep', 'description' => 'Медитации для улучшения сна', 'sort_order' => 3],
    ['name' => 'Фокус', 'slug' => 'focus', 'description' => 'Медитации для концентрации', 'sort_order' => 4]
  ];

  foreach ($categories as $category) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO meditation_categories (name, slug, description, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$category['name'], $category['slug'], $category['description'], $category['sort_order']]);
  }
  echo "✅ Базовые категории медитаций добавлены\n";

  // Добавляем базовые категории товаров
  echo "\nДобавление базовых категорий товаров...\n";
  $categories = [
    ['name' => 'Курсы', 'slug' => 'courses', 'description' => 'Обучающие курсы', 'sort_order' => 1],
    ['name' => 'Консультации', 'slug' => 'consultations', 'description' => 'Индивидуальные консультации', 'sort_order' => 2],
    ['name' => 'Материалы', 'slug' => 'materials', 'description' => 'Полезные материалы', 'sort_order' => 3]
  ];

  foreach ($categories as $category) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO product_categories (name, slug, description, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$category['name'], $category['slug'], $category['description'], $category['sort_order']]);
  }
  echo "✅ Базовые категории товаров добавлены\n";

  // Добавляем базовые категории статей
  echo "\nДобавление базовых категорий статей...\n";
  $categories = [
    ['name' => 'Психология', 'slug' => 'psychology', 'description' => 'Статьи по психологии', 'sort_order' => 1],
    ['name' => 'Медитация', 'slug' => 'meditation', 'description' => 'Статьи о медитации', 'sort_order' => 2],
    ['name' => 'Здоровье', 'slug' => 'health', 'description' => 'Статьи о здоровье', 'sort_order' => 3]
  ];

  foreach ($categories as $category) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO article_categories (name, slug, description, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$category['name'], $category['slug'], $category['description'], $category['sort_order']]);
  }
  echo "✅ Базовые категории статей добавлены\n";

  // Добавляем базовые настройки
  echo "\nДобавление базовых настроек...\n";
  $settings = [
    ['setting_key' => 'site_name', 'setting_value' => 'Cherkas Therapy', 'description' => 'Название сайта'],
    ['setting_key' => 'site_description', 'setting_value' => 'Психологическая помощь и терапия', 'description' => 'Описание сайта'],
    ['setting_key' => 'maintenance_mode', 'setting_value' => '0', 'description' => 'Режим технического обслуживания'],
    ['setting_key' => 'contact_email', 'setting_value' => 'info@cherkas-therapy.ru', 'description' => 'Контактный email']
  ];

  foreach ($settings as $setting) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    $stmt->execute([$setting['setting_key'], $setting['setting_value'], $setting['description']]);
  }
  echo "✅ Базовые настройки добавлены\n";

  echo "\n✅ ЧИСТАЯ СТРУКТУРА БАЗЫ ДАННЫХ СОЗДАНА УСПЕШНО!\n";
  echo "Теперь можно приступать к работе с админкой.\n";

} catch (Exception $e) {
  echo "❌ Ошибка: " . $e->getMessage() . "\n";
}
?>
