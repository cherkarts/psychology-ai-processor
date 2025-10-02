<?php
// Упрощенная версия API ярлыков для тестирования
header('Content-Type: application/json; charset=utf-8');

// Включаем отображение ошибок для диагностики
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
  echo "Starting API...\n";

  // Проверяем существование файлов
  $configPath = __DIR__ . '/../../config.php';
  $dbPath = __DIR__ . '/../../includes/Database.php';

  if (!file_exists($configPath)) {
    throw new Exception("Config file not found: $configPath");
  }

  if (!file_exists($dbPath)) {
    throw new Exception("Database class not found: $dbPath");
  }

  echo "Files exist, loading...\n";

  require_once $configPath;
  require_once $dbPath;

  echo "Files loaded, connecting to database...\n";

  // Используем класс Database для подключения
  $db = Database::getInstance();
  $pdo = $db->getConnection();

  echo "Database connected, querying badges...\n";

  // Получаем все активные ярлыки
  $stmt = $pdo->query("SELECT * FROM product_badges WHERE is_active = 1 ORDER BY sort_order, name");
  $badges = $stmt->fetchAll();

  echo "Badges queried, count: " . count($badges) . "\n";

  $response = [
    'success' => true,
    'badges' => $badges,
    'debug' => [
      'config_loaded' => true,
      'database_connected' => true,
      'badges_count' => count($badges)
    ]
  ];

  echo "Sending response...\n";
  echo json_encode($response, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  $errorResponse = [
    'success' => false,
    'message' => 'Ошибка: ' . $e->getMessage(),
    'debug' => [
      'config_loaded' => file_exists(__DIR__ . '/../../config.php'),
      'database_loaded' => file_exists(__DIR__ . '/../../includes/Database.php'),
      'error_line' => $e->getLine(),
      'error_file' => $e->getFile()
    ]
  ];

  echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
}
?>