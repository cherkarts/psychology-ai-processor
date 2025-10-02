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

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || empty($data['username'])) {
  echo json_encode(['success' => false, 'error' => 'Username required']);
  exit();
}

$username = $data['username'];
$bot_token = '7657713367:AAEDdQSD1K1g8ckRI-R-ePB7s1AtXc4OuyE'; // Ваш бот токен
$chat_id = '-1002418481743'; // ID вашего канала

// Функция для проверки подписки через Telegram Bot API
function checkSubscription($bot_token, $chat_id, $username)
{
  $url = "https://api.telegram.org/bot{$bot_token}/getChatMember";

  $post_data = [
    'chat_id' => $chat_id,
    'user_id' => '@' . $username
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);

  $response = curl_exec($ch);
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http_code !== 200) {
    return false;
  }

  $result = json_decode($response, true);

  if (!$result || !$result['ok']) {
    return false;
  }

  $status = $result['result']['status'];

  // Проверяем, что пользователь является участником канала
  return in_array($status, ['member', 'administrator', 'creator']);
}

// Проверяем подписку
$is_subscribed = checkSubscription($bot_token, $chat_id, $username);

if ($is_subscribed) {
  echo json_encode([
    'success' => true,
    'subscribed' => true,
    'message' => 'Пользователь подписан на канал'
  ], JSON_UNESCAPED_UNICODE);
} else {
  echo json_encode([
    'success' => true,
    'subscribed' => false,
    'message' => 'Пользователь не подписан на канал'
  ], JSON_UNESCAPED_UNICODE);
}
?>

