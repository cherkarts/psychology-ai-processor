<?php
/**
 * API для выхода из Telegram авторизации
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'error' => 'Method not allowed']);
  exit();
}

session_start();

try {
  // Очищаем данные пользователя из сессии
  unset($_SESSION['telegram_user']);

  // Уничтожаем сессию
  session_destroy();

  echo json_encode([
    'success' => true,
    'message' => 'Successfully logged out'
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => 'Logout failed: ' . $e->getMessage()
  ]);
}
?>