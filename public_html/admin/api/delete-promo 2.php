<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

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

// Проверяем авторизацию
require_once '../includes/auth.php';
if (!isLoggedIn()) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['id'])) {
  echo json_encode(['success' => false, 'error' => 'ID промокода не указан']);
  exit();
}

// Подключаем модель промокодов
require_once '../../includes/Models/PromoCode.php';

try {
  $promoModel = new PromoCode();

  // Проверяем существование промокода
  $promo = $promoModel->getById($data['id']);
  if (!$promo) {
    echo json_encode(['success' => false, 'error' => 'Промокод не найден']);
    exit();
  }

  // Удаляем промокод
  $result = $promoModel->delete($data['id']);

  if ($result) {
    echo json_encode([
      'success' => true,
      'message' => 'Промокод успешно удален'
    ], JSON_UNESCAPED_UNICODE);
  } else {
    echo json_encode(['success' => false, 'error' => 'Ошибка при удалении промокода']);
  }

} catch (Exception $e) {
  error_log("Error in delete-promo.php: " . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Произошла ошибка при удалении промокода']);
}
?>


