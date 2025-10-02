<?php
// Отключаем вывод ошибок для чистого JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

try {
  $config = require __DIR__ . '/../../config.php';
  require_once __DIR__ . '/../../includes/Database.php';

  // Используем класс Database для подключения
  $db = Database::getInstance();
  $pdo = $db->getConnection();

  // Получаем все активные ярлыки
  $stmt = $pdo->query("SELECT * FROM product_badges WHERE is_active = 1 ORDER BY sort_order, name");
  $badges = $stmt->fetchAll();

  // Исправляем кодировку UTF-8
  foreach ($badges as &$badge) {
    if (isset($badge['name'])) {
      $badge['name'] = mb_convert_encoding($badge['name'], 'UTF-8', 'auto');
    }
    if (isset($badge['slug'])) {
      $badge['slug'] = mb_convert_encoding($badge['slug'], 'UTF-8', 'auto');
    }
  }

  echo json_encode([
    'success' => true,
    'badges' => $badges
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>