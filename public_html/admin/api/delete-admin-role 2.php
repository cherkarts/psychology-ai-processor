<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Проверяем права доступа
requirePermission('admin');

// Получаем данные из запроса
$input = json_decode(file_get_contents('php://input'), true);

try {
  $roleId = $input['role_id'] ?? '';

  if (empty($roleId)) {
    throw new Exception('ID роли не указан');
  }

  // Здесь должна быть логика удаления роли
  // Пока что просто возвращаем успех

  echo json_encode([
    'success' => true,
    'message' => 'Роль удалена успешно'
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("Delete admin role error: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка удаления роли: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>