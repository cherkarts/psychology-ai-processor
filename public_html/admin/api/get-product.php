<?php
// API для получения данных товара
// Полностью отключаем все выводы
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Включаем буферизацию вывода
ob_start();

// Отключаем вывод предупреждений
ini_set('display_startup_errors', 0);

session_start();
header('Content-Type: application/json; charset=utf-8');

// Проверка авторизации (временно отключена для тестирования)
// if (!isset($_SESSION['admin_user'])) {
//   http_response_code(401);
//   echo json_encode(['success' => false, 'message' => 'Неавторизован'], JSON_UNESCAPED_UNICODE);
//   exit();
// }

// Проверка ID товара
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo json_encode(['success' => false, 'message' => 'Неверный ID товара'], JSON_UNESCAPED_UNICODE);
  exit();
}

$productId = (int) $_GET['id'];

try {
  // Подключение к БД
  $config = require '../../config.php';

  $pdo = new PDO(
    "mysql:host=" . $config['database']['host'] . ";dbname=" . $config['database']['dbname'],
    $config['database']['username'],
    $config['database']['password'],
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );

  // Получаем данные товара
  $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
  $stmt->execute([$productId]);
  $product = $stmt->fetch();

  if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Товар не найден'], JSON_UNESCAPED_UNICODE);
    exit();
  }

  // Отладочная информация
  error_log("=== GET PRODUCT DEBUG ===");
  error_log("Product data from DB: " . print_r($product, true));

  // Исправляем кодировку в данных товара
  if (isset($product['title'])) {
    $product['title'] = @iconv('UTF-8', 'Windows-1251//IGNORE', $product['title']) ?: $product['title'];
  }
  if (isset($product['description'])) {
    $product['description'] = @iconv('UTF-8', 'Windows-1251//IGNORE', $product['description']) ?: $product['description'];
  }
  if (isset($product['short_description'])) {
    $product['short_description'] = @iconv('UTF-8', 'Windows-1251//IGNORE', $product['short_description']) ?: $product['short_description'];
  }

  error_log("Product data after encoding fix: " . print_r($product, true));

  // Добавляем информацию об изображениях для превью
  if (!empty($product['image'])) {
    $product['image_preview'] = $product['image'];
  }

  if (!empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true);
    if (is_array($gallery)) {
      $product['gallery_preview'] = $gallery;
    } else {
      $product['gallery_preview'] = [];
    }
  } else {
    $product['gallery_preview'] = [];
  }

  if (!empty($product['download_url'])) {
    $product['download_file_preview'] = $product['download_url'];
  }

  // Очищаем буфер вывода перед отправкой JSON
  ob_clean();

  echo json_encode([
    'success' => true,
    'product' => $product
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  // Очищаем буфер вывода перед отправкой JSON
  ob_clean();

  echo json_encode([
    'success' => false,
    'message' => 'Ошибка: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>