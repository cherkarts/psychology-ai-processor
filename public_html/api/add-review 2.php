<?php
/**
 * API для добавления отзывов через базу данных
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

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Models/Review.php';

$db = Database::getInstance();
$review = new Review();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit();
}

try {
  // Получаем данные из POST запроса
  $input = json_decode(file_get_contents('php://input'), true);

  if (!$input) {
    // Если JSON не получен, пробуем получить из POST
    $input = $_POST;
  }

  // Проверяем, что данные получены
  if (empty($input)) {
    throw new Exception('No data received');
  }

  // Валидация данных
  $errors = $review->validate($input);
  if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
      'success' => false,
      'error' => 'Validation failed',
      'details' => $errors
    ]);
    exit();
  }

  // Проверяем, не оставлял ли пользователь отзыв недавно (только если есть email)
  if (!empty($input['email']) && $review->existsByEmail($input['email'])) {
    http_response_code(429);
    echo json_encode([
      'success' => false,
      'error' => 'You can only leave one review per day'
    ]);
    exit();
  }

  // Обработка загруженных файлов
  if (!empty($_FILES['image'])) {
    $uploadDir = __DIR__ . '/../uploads/reviews/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['image'];
    $fileName = uniqid() . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filePath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
      $input['image'] = '/uploads/reviews/' . $fileName;
    }
  }

  // Создаем отзыв
  $reviewId = $review->create($input);

  // Отправляем уведомление администратору
  $notification = "💬 <b>Новый отзыв</b>\n\n";
  $notification .= "👤 <b>Имя:</b> " . htmlspecialchars($input['name']) . "\n";
  $notification .= "⭐ <b>Рейтинг:</b> {$input['rating']}/5\n";
  $notification .= "📝 <b>Текст:</b> " . htmlspecialchars(substr($input['text'], 0, 100)) . "...\n";
  if (!empty($input['email'])) {
    $notification .= "📧 <b>Email:</b> " . htmlspecialchars($input['email']) . "\n";
  }
  if (!empty($input['telegram_username'])) {
    $notification .= "📱 <b>Telegram:</b> @" . htmlspecialchars($input['telegram_username']) . "\n";
  }
  $notification .= "📅 <b>Дата:</b> " . date('d.m.Y H:i:s');

  // Отправляем в Telegram
  try {
    $config = require_once __DIR__ . '/../config.php';
    if (!empty($config['telegram']['bot_token']) && !empty($config['telegram']['chat_id'])) {
      $telegramUrl = "https://api.telegram.org/bot{$config['telegram']['bot_token']}/sendMessage";
      $telegramData = [
        'chat_id' => $config['telegram']['chat_id'],
        'text' => $notification,
        'parse_mode' => 'HTML'
      ];

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $telegramUrl);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $telegramData);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_exec($ch);
      curl_close($ch);
    }
  } catch (Exception $e) {
    // Логируем ошибку, но не прерываем процесс
    error_log('Telegram notification error: ' . $e->getMessage());
  }

  echo json_encode([
    'success' => true,
    'message' => 'Отзыв успешно добавлен и отправлен на модерацию',
    'id' => $reviewId
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => 'Server error: ' . $e->getMessage()
  ]);
}
?>