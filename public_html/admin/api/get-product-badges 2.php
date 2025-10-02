<?php
// Отключаем вывод ошибок для чистого JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');

try {
  $config = require __DIR__ . '/../../config.php';
  require_once __DIR__ . '/../../includes/Database.php';

  $productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : 0;

  if (!$productId) {
    throw new Exception('Не указан ID товара');
  }

  // Используем класс Database для подключения
  $db = Database::getInstance();
  $pdo = $db->getConnection();

  // Получаем ярлыки товара
  $stmt = $pdo->prepare("
        SELECT pbr.badge_id, pb.name, pb.slug, pb.color, pb.background_color
        FROM product_badge_relations pbr
        JOIN product_badges pb ON pbr.badge_id = pb.id
        WHERE pbr.product_id = ? AND pb.is_active = 1
        ORDER BY pb.sort_order, pb.name
    ");
  $stmt->execute([$productId]);
  $badges = $stmt->fetchAll();

  // Исправляем кодировку UTF-8
  foreach ($badges as &$badge) {
    if (isset($badge['name'])) {
      // Пробуем разные способы исправления кодировки
      $name = $badge['name'];
      if ($name === false || $name === null) {
        $name = '';
      }
      // Пробуем конвертировать из Windows-1251 в UTF-8
      $name = @iconv('Windows-1251', 'UTF-8//IGNORE', $name) ?: $name;
      // Если не помогло, пробуем из CP1251
      if (empty($name) || $name === false) {
        $name = @iconv('CP1251', 'UTF-8//IGNORE', $badge['name']) ?: $badge['name'];
      }
      $badge['name'] = $name;
    }
    if (isset($badge['slug'])) {
      $badge['slug'] = @iconv('Windows-1251', 'UTF-8//IGNORE', $badge['slug']) ?: $badge['slug'];
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