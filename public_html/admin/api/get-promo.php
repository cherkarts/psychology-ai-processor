<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

if (!isset($_GET['id'])) {
  echo json_encode(['success' => false, 'error' => 'ID промокода не указан']);
  exit();
}

// Подключаем модель промокодов
require_once '../../includes/Models/PromoCode.php';

try {
  $promoModel = new PromoCode();
  $promo = $promoModel->getById($_GET['id']);

  if (!$promo) {
    echo json_encode(['success' => false, 'error' => 'Промокод не найден']);
    exit();
  }

  // Форматируем даты для HTML datetime-local
  if ($promo['valid_from']) {
    $promo['valid_from_formatted'] = date('Y-m-d\TH:i', strtotime($promo['valid_from']));
  }
  if ($promo['valid_until']) {
    $promo['valid_until_formatted'] = date('Y-m-d\TH:i', strtotime($promo['valid_until']));
  }

  echo json_encode([
    'success' => true,
    'promo' => $promo
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("Error in get-promo.php: " . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Произошла ошибка при получении промокода']);
}
?>


