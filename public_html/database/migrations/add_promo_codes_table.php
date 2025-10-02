<?php
/**
 * Миграция для добавления таблицы промокодов
 */

// Подключаем конфигурацию
$config = require __DIR__ . '/../../config.php';

try {
  // Создаем подключение к базе данных
  $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
  $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);

  echo "✅ Подключение к базе данных установлено\n";

  // Создаем таблицу промокодов
  $sql = "
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
        is_active BOOLEAN DEFAULT TRUE,
        valid_from TIMESTAMP NULL,
        valid_until TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_code (code),
        INDEX idx_is_active (is_active),
        INDEX idx_valid_from (valid_from),
        INDEX idx_valid_until (valid_until)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

  $pdo->exec($sql);
  echo "✅ Таблица promo_codes создана\n";

  // Создаем таблицу использования промокодов
  $sql = "
    CREATE TABLE IF NOT EXISTS promo_code_usage (
        id INT AUTO_INCREMENT PRIMARY KEY,
        promo_code_id INT NOT NULL,
        order_id INT NULL,
        user_email VARCHAR(255) NULL,
        discount_amount DECIMAL(10,2) NOT NULL,
        order_total DECIMAL(10,2) NOT NULL,
        used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (promo_code_id) REFERENCES promo_codes(id) ON DELETE CASCADE,
        INDEX idx_promo_code_id (promo_code_id),
        INDEX idx_order_id (order_id),
        INDEX idx_user_email (user_email),
        INDEX idx_used_at (used_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

  $pdo->exec($sql);
  echo "✅ Таблица promo_code_usage создана\n";

  // Добавляем несколько тестовых промокодов
  $promoCodes = [
    [
      'code' => 'WELCOME10',
      'name' => 'Добро пожаловать',
      'description' => 'Скидка 10% на первый заказ',
      'type' => 'percentage',
      'value' => 10.00,
      'min_amount' => 1000.00,
      'max_uses' => 100,
      'valid_until' => date('Y-m-d H:i:s', strtotime('+1 year'))
    ],
    [
      'code' => 'SUMMER20',
      'name' => 'Летняя скидка',
      'description' => 'Летняя скидка 20%',
      'type' => 'percentage',
      'value' => 20.00,
      'min_amount' => 2000.00,
      'max_uses' => 50,
      'valid_until' => date('Y-m-d H:i:s', strtotime('+3 months'))
    ],
    [
      'code' => 'FIXED500',
      'name' => 'Фиксированная скидка',
      'description' => 'Скидка 500 рублей',
      'type' => 'fixed',
      'value' => 500.00,
      'min_amount' => 1500.00,
      'max_uses' => 200,
      'valid_until' => date('Y-m-d H:i:s', strtotime('+6 months'))
    ]
  ];

  $stmt = $pdo->prepare("
        INSERT IGNORE INTO promo_codes 
        (code, name, description, type, value, min_amount, max_uses, valid_until) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

  foreach ($promoCodes as $promo) {
    $stmt->execute([
      $promo['code'],
      $promo['name'],
      $promo['description'],
      $promo['type'],
      $promo['value'],
      $promo['min_amount'],
      $promo['max_uses'],
      $promo['valid_until']
    ]);
  }

  echo "✅ Добавлены тестовые промокоды\n";
  echo "✅ Миграция завершена успешно!\n";

} catch (Exception $e) {
  echo "❌ Ошибка при создании таблицы промокодов: " . $e->getMessage() . "\n";
}
?>