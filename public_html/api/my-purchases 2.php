<?php
require_once '../includes/Models/Order.php';
require_once '../includes/Models/Article.php';
require_once '../includes/Models/Meditation.php';
require_once '../includes/Models/Review.php';
require_once '../includes/Models/Product.php';
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

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['email'])) {
  echo json_encode(['success' => false, 'error' => 'Email не указан']);
  exit();
}

$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);

if (!$email) {
  echo json_encode(['success' => false, 'error' => 'Неверный формат email']);
  exit();
}

// Connect to database
require_once '../config.php';
require_once '../includes/Database.php';

try {
  $db = Database::getInstance();
  $db->selectDatabase('cherkas_therapy');

  // Get user purchases from database
  $stmt = $db->query(
    "SELECT id as order_id, created_at as date, status, total_amount as amount, payment_method 
         FROM orders 
         WHERE email = ? 
         ORDER BY created_at DESC",
    [$email]
  );

  $userPurchases = [];
  while ($order = $stmt->fetch()) {
    $userPurchases[] = [
      'order_id' => $order['order_id'],
      'date' => $order['date'],
      'status' => $order['status'],
      'amount' => $order['amount'],
      'payment_method' => $order['payment_method'] ?? 'unknown'
    ];
  }

  // Sort by date (newest first)
  usort($userPurchases, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
  });

} catch (Exception $e) {
  error_log("Database error in my-purchases.php: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'error' => 'Ошибка получения данных из базы данных'
  ]);
  exit();
}

if (empty($userPurchases)) {
  echo json_encode([
    'success' => true,
    'purchases' => [],
    'message' => 'Покупки не найдены для указанного email'
  ]);
} else {
  echo json_encode([
    'success' => true,
    'purchases' => $userPurchases,
    'message' => 'Найдено ' . count($userPurchases) . ' ' . getPluralForm(count($userPurchases), 'покупка', 'покупки', 'покупок')
  ]);
}

// Функция для склонения слов
function getPluralForm($number, $form1, $form2, $form5)
{
  $number = abs($number) % 100;
  $number1 = $number % 10;
  if ($number > 10 && $number < 20)
    return $form5;
  if ($number1 > 1 && $number1 < 5)
    return $form2;
  if ($number1 == 1)
    return $form1;
  return $form5;
}
?>