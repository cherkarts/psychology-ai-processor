<?php
// Пошаговая версия API с диагностикой
header('Content-Type: application/json; charset=utf-8');

$debug = [];

try {
  $debug[] = "Step 1: Starting API";

  // Проверяем существование файлов
  $configPath = __DIR__ . '/../../config.php';
  $dbPath = __DIR__ . '/../../includes/Database.php';

  if (!file_exists($configPath)) {
    throw new Exception("Config file not found: $configPath");
  }
  $debug[] = "Step 2: Config file exists";

  if (!file_exists($dbPath)) {
    throw new Exception("Database class not found: $dbPath");
  }
  $debug[] = "Step 3: Database class exists";

  // Загружаем файлы
  require_once $configPath;
  $debug[] = "Step 4: Config loaded";

  require_once $dbPath;
  $debug[] = "Step 5: Database class loaded";

  // Создаем подключение
  $db = Database::getInstance();
  $debug[] = "Step 6: Database instance created";

  $pdo = $db->getConnection();
  $debug[] = "Step 7: PDO connection established";

  // Выполняем запрос
  $stmt = $pdo->query("SELECT * FROM product_badges WHERE is_active = 1 ORDER BY sort_order, name");
  $badges = $stmt->fetchAll();
  $debug[] = "Step 8: Query executed, found " . count($badges) . " badges";

  // Формируем ответ
  $response = [
    'success' => true,
    'badges' => $badges,
    'debug' => $debug
  ];

  $debug[] = "Step 9: Response prepared";

  echo json_encode($response, JSON_UNESCAPED_UNICODE);
  $debug[] = "Step 10: JSON sent";

} catch (Exception $e) {
  $errorResponse = [
    'success' => false,
    'message' => $e->getMessage(),
    'debug' => $debug,
    'error_line' => $e->getLine(),
    'error_file' => basename($e->getFile())
  ];

  echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
}
?>