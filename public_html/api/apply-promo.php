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

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['promo_code'])) {
  echo json_encode(['success' => false, 'error' => 'Промокод не указан']);
  exit();
}

$promoCode = trim($data['promo_code']);
$cartTotal = $data['cart_total'] ?? 0;

// Логируем входящие данные для отладки
error_log("apply-promo.php: promo_code = '$promoCode', cart_total = $cartTotal");

// Connect to database and use PromoCode model
require_once '../config.php';
require_once '../includes/Database.php';
require_once '../includes/Models/PromoCode.php';

try {
  $promoModel = new PromoCode();

  // Get user email from session if available
  $userEmail = $_SESSION['user_email'] ?? null;

  // Apply promo code using the model
  $result = $promoModel->applyPromoCode($promoCode, $cartTotal, null, $userEmail);

  if (!$result['valid']) {
    echo json_encode([
      'success' => false,
      'error' => $result['error']
    ], JSON_UNESCAPED_UNICODE);
    exit();
  }

  // Сохраняем промокод в сессии
  $_SESSION['applied_promo'] = [
    'code' => $result['promo_code'],
    'discount' => $result['discount'],
    'description' => $result['description']
  ];

  echo json_encode([
    'success' => true,
    'promo_code' => $result['promo_code'],
    'discount' => $result['discount'],
    'final_total' => $result['final_total'],
    'description' => $result['description'],
    'message' => 'Промокод успешно применен!'
  ], JSON_UNESCAPED_UNICODE);
  exit();

} catch (Exception $e) {
  error_log("Database error in apply-promo.php: " . $e->getMessage());
  error_log("Stack trace: " . $e->getTraceAsString());

  // Добавляем отладочную информацию
  $debugInfo = [
    'error' => $e->getMessage(),
    'file' => $e->getFile(),
    'line' => $e->getLine(),
    'promo_code' => $promoCode,
    'cart_total' => $cartTotal,
    'user_email' => $userEmail
  ];

  echo json_encode([
    'success' => false,
    'error' => 'Ошибка при применении промокода',
    'debug' => $debugInfo
  ], JSON_UNESCAPED_UNICODE);
  exit();
}


?>