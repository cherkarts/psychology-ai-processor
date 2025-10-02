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

// Подключаем модель промокодов
require_once __DIR__ . '/../../includes/Models/PromoCode.php';

try {
  $promoModel = new PromoCode();

  // Получаем параметры пагинации
  $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
  $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 20;
  $offset = ($page - 1) * $limit;

  // Получаем промокоды
  $promos = $promoModel->getAll($limit, $offset);
  $totalCount = $promoModel->getCount();

  // Получаем статистику использования
  $stats = $promoModel->getUsageStats();

  // Формируем статистику в удобном виде
  $statsMap = [];
  foreach ($stats as $stat) {
    $statsMap[$stat['code']] = $stat;
  }

  // Объединяем данные промокодов со статистикой
  foreach ($promos as &$promo) {
    $promo['usage_stats'] = $statsMap[$promo['code']] ?? [
      'total_discount' => 0,
      'total_uses' => 0
    ];

    // Форматируем даты
    if ($promo['valid_from']) {
      $promo['valid_from_formatted'] = date('d.m.Y H:i', strtotime($promo['valid_from']));
    }
    if ($promo['valid_until']) {
      $promo['valid_until_formatted'] = date('d.m.Y H:i', strtotime($promo['valid_until']));
    }

    // Проверяем статус промокода
    // Устанавливаем часовой пояс для Москвы
    date_default_timezone_set('Europe/Moscow');
    $now = new DateTime();
    $promo['is_expired'] = false;
    $promo['is_not_started'] = false;

    // Добавляем отладочную информацию
    $promo['debug'] = [
      'current_time' => $now->format('Y-m-d H:i:s'),
      'valid_from' => $promo['valid_from'],
      'valid_until' => $promo['valid_until']
    ];

    if ($promo['valid_until'] && new DateTime($promo['valid_until']) < $now) {
      $promo['is_expired'] = true;
    }

    if ($promo['valid_from'] && new DateTime($promo['valid_from']) > $now) {
      $promo['is_not_started'] = true;
    }

    // Проверяем лимит использования
    $promo['is_limit_reached'] = false;
    if ($promo['max_uses'] && $promo['used_count'] >= $promo['max_uses']) {
      $promo['is_limit_reached'] = true;
    }
  }

  echo json_encode([
    'success' => true,
    'promos' => $promos,
    'pagination' => [
      'page' => $page,
      'limit' => $limit,
      'total' => $totalCount,
      'pages' => ceil($totalCount / $limit)
    ]
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("Error in get-promos.php: " . $e->getMessage());
  echo json_encode(['success' => false, 'error' => 'Произошла ошибка при получении промокодов']);
}
?>