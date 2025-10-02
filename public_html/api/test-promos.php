<?php
header('Content-Type: application/json; charset=utf-8');

// Простой тест API для промокодов
session_start();

try {
  // Проверяем авторизацию
  require_once '../includes/auth.php';
  if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
  }

  // Простой ответ для тестирования
  echo json_encode([
    'success' => true,
    'promos' => [
      [
        'id' => 1,
        'code' => 'TEST10',
        'name' => 'Тестовый промокод',
        'type' => 'percentage',
        'value' => 10.00,
        'min_amount' => 1000.00,
        'used_count' => 0,
        'max_uses' => 100,
        'is_active' => 1,
        'valid_until' => '2025-12-31 23:59:59'
      ]
    ],
    'pagination' => [
      'page' => 1,
      'pages' => 1,
      'total' => 1,
      'limit' => 20
    ]
  ]);

} catch (Exception $e) {
  echo json_encode([
    'success' => false,
    'error' => 'Ошибка сервера: ' . $e->getMessage()
  ]);
}
?>