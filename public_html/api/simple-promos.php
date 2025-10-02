<?php
// Простой API для промокодов без сложной логики
header('Content-Type: application/json; charset=utf-8');

session_start();

try {
  // Простая проверка авторизации
  if (!isset($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
  }

  // Простое подключение к БД
  require_once __DIR__ . '/../../config.php';
  $config = require __DIR__ . '/../../config.php';

  $dsn = "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']}";
  $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password']);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Простой запрос
  $stmt = $pdo->query("SELECT * FROM promo_codes ORDER BY created_at DESC LIMIT 20");
  $promos = $stmt->fetchAll();

  // Простой ответ
  echo json_encode([
    'success' => true,
    'promos' => $promos,
    'pagination' => [
      'page' => 1,
      'pages' => 1,
      'total' => count($promos),
      'limit' => 20
    ]
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'error' => 'Ошибка сервера: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>