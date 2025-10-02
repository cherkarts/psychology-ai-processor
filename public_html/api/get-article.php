<?php
// API для получения данных статьи
session_start();
header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['admin_user'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
  exit();
}

// Проверка ID статьи
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo json_encode(['success' => false, 'message' => 'Неверный ID статьи'], JSON_UNESCAPED_UNICODE);
  exit();
}

$articleId = (int) $_GET['id'];

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

  // Получаем данные статьи
  $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
  $stmt->execute([$articleId]);
  $article = $stmt->fetch();

  if (!$article) {
    echo json_encode(['success' => false, 'message' => 'Статья не найдена'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  echo json_encode([
    'success' => true,
    'article' => $article
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>