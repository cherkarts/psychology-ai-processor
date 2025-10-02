<?php
// API для удаления товара
session_start();
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Метод не разрешен'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Получение данных
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !is_numeric($input['id'])) {
  echo json_encode(['success' => false, 'message' => 'Неверные данные'], JSON_UNESCAPED_UNICODE);
  exit();
}

$productId = (int) $input['id'];

try {
  // Подключение к БД
  $config = require '../../config.php';

  $pdo = new PDO(
    "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'],
    $config['database']['username'],
    $config['database']['password'],
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

  // Проверяем, существует ли товар
  $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
  $stmt->execute([$productId]);

  if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  // Удаляем товар
  $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
  $stmt->execute([$productId]);

  echo json_encode([
    'success' => true,
    'message' => 'Товар удален'
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>