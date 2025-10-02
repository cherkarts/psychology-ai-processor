<?php
/**
 * Обработка заказов
 */

require_once 'includes/Models/Order.php';
require_once 'includes/Models/Product.php';
require_once 'includes/Database.php';
require_once 'includes/functions.php';

// Функция для записи в собственный лог файл
function writeToCustomLog($message)
{
  $logFile = __DIR__ . '/custom-error.log';
  $timestamp = date('Y-m-d H:i:s');
  $logMessage = "[$timestamp] $message\n";
  file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Альтернативная функция отправки в Telegram
function sendToTelegramAlternative($message)
{
  try {
    // Прямое чтение конфигурации
    $configFile = __DIR__ . '/config.php';
    if (!file_exists($configFile)) {
      writeToCustomLog("ALTERNATIVE TELEGRAM: config.php not found");
      return false;
    }

    $config = require $configFile;
    if (!isset($config['telegram']['bot_token']) || !isset($config['telegram']['chat_id'])) {
      writeToCustomLog("ALTERNATIVE TELEGRAM: Telegram config missing");
      return false;
    }

    $botToken = $config['telegram']['bot_token'];
    $chatId = $config['telegram']['chat_id'];

    writeToCustomLog("ALTERNATIVE TELEGRAM: Using bot_token: " . substr($botToken, 0, 10) . "... and chat_id: $chatId");

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
      'chat_id' => $chatId,
      'text' => $message,
      'parse_mode' => 'HTML'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $logMessage = "ALTERNATIVE TELEGRAM: HTTP $httpCode, Response: $response, Error: $error";
    error_log($logMessage);
    writeToCustomLog($logMessage);

    if ($error) {
      return false;
    }

    if ($httpCode === 200) {
      $responseData = json_decode($response, true);
      return $responseData && $responseData['ok'];
    }

    return false;
  } catch (Exception $e) {
    $errorMessage = "ALTERNATIVE TELEGRAM ERROR: " . $e->getMessage();
    error_log($errorMessage);
    writeToCustomLog($errorMessage);
    return false;
  }
}

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Проверяем подключение функций
error_log("ORDER PROCESSING: Starting order processing");
writeToCustomLog("ORDER PROCESSING: Starting order processing");
error_log("ORDER PROCESSING: sendToTelegram function exists: " . (function_exists('sendToTelegram') ? 'YES' : 'NO'));
error_log("ORDER PROCESSING: getConfig function exists: " . (function_exists('getConfig') ? 'YES' : 'NO'));


// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /checkout.php');
  exit;
}

// Проверяем CSRF токен
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
  $_SESSION['error_message'] = 'Неверный токен безопасности';
  header('Location: /checkout.php');
  exit;
}

try {
  // Получаем данные из формы
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $comment = trim($_POST['comment'] ?? '');
  $paymentMethod = $_POST['payment_method'] ?? 'card';

  error_log("ORDER PROCESSING: Form data received - name: $name, email: $email, phone: $phone, comment: $comment, payment: $paymentMethod");

  // Валидация
  $errors = [];

  if (empty($name)) {
    $errors[] = 'Имя обязательно';
  }

  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Некорректный email';
  }

  if (empty($phone)) {
    $errors[] = 'Телефон обязателен';
  }

  // Проверяем корзину
  $cartItems = $_SESSION['cart'] ?? [];
  error_log("ORDER PROCESSING: Cart items: " . json_encode($cartItems));
  if (empty($cartItems)) {
    $errors[] = 'Корзина пуста';
  }

  if (!empty($errors)) {
    $_SESSION['error_message'] = implode(', ', $errors);
    header('Location: /checkout.php');
    exit;
  }

  // Вычисляем общую сумму
  $total = 0;
  foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
  }

  // Получаем примененный промокод
  $appliedPromo = $_SESSION['applied_promo'] ?? null;
  $discount = $appliedPromo['discount'] ?? 0;
  $finalTotal = $total - $discount;

  // Создаем заказ
  $orderModel = new Order();

  $orderData = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'total_amount' => $finalTotal,
    'currency' => 'RUB',
    'payment_method' => $paymentMethod,
    'status' => 'pending',
    'notes' => $comment,
    'created_at' => date('Y-m-d H:i:s')
  ];

  error_log("ORDER PROCESSING: Order data: " . json_encode($orderData));
  $orderId = $orderModel->create($orderData);
  error_log("ORDER PROCESSING: Order created with ID: {$orderId}");

  // Добавляем товары в заказ
  foreach ($cartItems as $item) {
    $orderModel->addItem($orderId, $item['id'], $item['quantity'], $item['price']);
  }

  // Отправляем уведомление в Telegram
  error_log("ORDER PROCESSING: Starting Telegram notification for order #{$orderId}");
  error_log("ORDER PROCESSING: About to call sendToTelegram function");

  try {
    $telegramMessage = "🛒 <b>Новый заказ #{$orderId}</b>\n\n";
    $telegramMessage .= "👤 <b>Клиент:</b> {$name}\n";
    $telegramMessage .= "📧 <b>Email:</b> {$email}\n";
    $telegramMessage .= "📱 <b>Телефон:</b> {$phone}\n";
    $telegramMessage .= "💰 <b>Сумма:</b> {$finalTotal} ₽\n";
    $telegramMessage .= "💳 <b>Способ оплаты:</b> " . ($paymentMethod === 'card' ? 'Банковская карта' : 'СБП') . "\n";

    if (!empty($comment)) {
      $telegramMessage .= "💬 <b>Комментарий:</b> {$comment}\n";
      error_log("ORDER PROCESSING: Comment included: {$comment}");
    } else {
      error_log("ORDER PROCESSING: No comment provided");
    }

    $telegramMessage .= "\n📦 <b>Товары:</b>\n";
    foreach ($cartItems as $item) {
      $telegramMessage .= "• {$item['title']} x{$item['quantity']} = " . ($item['price'] * $item['quantity']) . " ₽\n";
    }

    error_log("ORDER PROCESSING: Telegram message prepared: " . substr($telegramMessage, 0, 200) . "...");

    // Проверяем, существует ли функция sendToTelegram
    if (!function_exists('sendToTelegram')) {
      error_log("ORDER PROCESSING: ERROR - sendToTelegram function does not exist!");
      throw new Exception('sendToTelegram function not found');
    }

    // Отправляем в Telegram
    error_log("ORDER PROCESSING: Calling sendToTelegram...");
    $telegramResult = sendToTelegram($telegramMessage);
    error_log("ORDER PROCESSING: Telegram send result: " . ($telegramResult ? 'SUCCESS' : 'FAILED'));

    // Альтернативный способ отправки в Telegram (если основной не сработал)
    if (!$telegramResult) {
      error_log("ORDER PROCESSING: Main Telegram function failed, trying alternative method...");
      $telegramResult = sendToTelegramAlternative($telegramMessage);
      error_log("ORDER PROCESSING: Alternative Telegram send result: " . ($telegramResult ? 'SUCCESS' : 'FAILED'));
    }

  } catch (Exception $e) {
    error_log("ORDER PROCESSING: Telegram notification failed: " . $e->getMessage());
  }

  // Очищаем корзину
  unset($_SESSION['cart']);
  unset($_SESSION['applied_promo']);

  // Перенаправляем на страницу благодарности
  header('Location: /thank-you.php?v=' . time());
  exit;

} catch (Exception $e) {
  error_log("Order processing error: " . $e->getMessage());
  $_SESSION['error_message'] = 'Произошла ошибка при оформлении заказа. Попробуйте еще раз.';
  header('Location: /checkout.php');
  exit;
}

?>