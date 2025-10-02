<?php
// Чистая версия для получения данных медитации
session_start();
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Проверка ID медитации
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo json_encode(['success' => false, 'message' => 'Неверный ID медитации'], JSON_UNESCAPED_UNICODE);
  exit();
}

$meditationId = (int) $_GET['id'];

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

  // Получаем данные медитации
  $stmt = $pdo->prepare("SELECT * FROM meditations WHERE id = ?");
  $stmt->execute([$meditationId]);
  $meditation = $stmt->fetch();

  if (!$meditation) {
    echo json_encode(['success' => false, 'message' => 'Медитация не найдена'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  echo json_encode([
    'success' => true,
    'meditation' => $meditation
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>