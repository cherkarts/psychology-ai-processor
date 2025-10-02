<?php
/**
 * API для обработки авторизации через Telegram
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
  // Получаем данные из POST запроса
  $input = json_decode(file_get_contents('php://input'), true);

  if (!$input) {
    throw new Exception('Invalid JSON data');
  }

  // Проверяем подпись Telegram
  if (!verifyTelegramAuth($input)) {
    throw new Exception('Invalid Telegram signature');
  }

  // Сохраняем данные пользователя в сессию
  $_SESSION['telegram_user'] = [
    'id' => $input['id'],
    'first_name' => $input['first_name'] ?? '',
    'last_name' => $input['last_name'] ?? '',
    'username' => $input['username'] ?? '',
    'photo_url' => $input['photo_url'] ?? '',
    'auth_date' => $input['auth_date']
  ];

  // Сохраняем пользователя в базу данных
  saveTelegramUser($_SESSION['telegram_user']);

  echo json_encode([
    'success' => true,
    'message' => 'Authorization successful',
    'user' => [
      'id' => $input['id'],
      'first_name' => $input['first_name'] ?? '',
      'last_name' => $input['last_name'] ?? '',
      'username' => $input['username'] ?? ''
    ]
  ]);

} catch (Exception $e) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}

/**
 * Проверяем подпись Telegram
 */
function verifyTelegramAuth($auth_data)
{
  $check_hash = $auth_data['hash'];
  unset($auth_data['hash']);

  $data_check_arr = [];
  foreach ($auth_data as $key => $value) {
    $data_check_arr[] = $key . '=' . $value;
  }
  sort($data_check_arr);

  $data_check_string = implode("\n", $data_check_arr);
  $secret_key = hash('sha256', '7657713367:AAEDdQSD1K1g8ckRI-R-ePB7s1AtXc4OuyE', true);
  $hash = hash_hmac('sha256', $data_check_string, $secret_key);

  if (strcmp($hash, $check_hash) !== 0) {
    return false;
  }

  if ((time() - $auth_data['auth_date']) > 86400) {
    return false;
  }

  return true;
}

/**
 * Сохраняем пользователя Telegram в базу данных
 */
function saveTelegramUser($user)
{
  try {
    require_once __DIR__ . '/../includes/Database.php';
    $db = Database::getInstance();

    // Проверяем, существует ли пользователь
    $existing = $db->fetchOne(
      "SELECT telegram_id FROM telegram_users WHERE telegram_id = ?",
      [$user['id']]
    );

    if ($existing) {
      // Обновляем существующего пользователя
      $db->update('telegram_users', [
        'telegram_username' => $user['username'],
        'telegram_first_name' => $user['first_name'],
        'telegram_last_name' => $user['last_name'],
        'telegram_avatar' => $user['photo_url'],
        'updated_at' => date('Y-m-d H:i:s')
      ], 'telegram_id = ?', [$user['id']]);
    } else {
      // Создаем нового пользователя
      $db->insert('telegram_users', [
        'telegram_id' => $user['id'],
        'telegram_username' => $user['username'],
        'telegram_first_name' => $user['first_name'],
        'telegram_last_name' => $user['last_name'],
        'telegram_avatar' => $user['photo_url'],
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
      ]);
    }

    return true;
  } catch (Exception $e) {
    // Логируем ошибку, но не прерываем авторизацию
    error_log('Error saving Telegram user: ' . $e->getMessage());
    return false;
  }
}
?>