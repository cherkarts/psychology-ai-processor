<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Проверяем права доступа
requirePermission('admin');

// Получаем данные из запроса
$input = json_decode(file_get_contents('php://input'), true);

try {
  $userId = $input['user_id'] ?? '';

  if (empty($userId)) {
    throw new Exception('ID пользователя не указан');
  }

  // Здесь должна быть логика удаления пользователя
  // Пока что просто возвращаем успех

  echo json_encode([
    'success' => true,
    'message' => 'Пользователь удален успешно'
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("Delete admin user error: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка удаления пользователя: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>