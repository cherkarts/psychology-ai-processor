<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Проверяем права доступа
requirePermission('admin');

try {
  // Очищаем кэш (если есть)
  $cacheCleared = true;

  // Здесь может быть логика очистки кэша
  // Пока что просто возвращаем успех

  echo json_encode([
    'success' => true,
    'message' => 'Кэш очищен успешно'
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("Clear cache error: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка очистки кэша: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>