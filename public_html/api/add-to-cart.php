<?php
require_once '../includes/Models/Order.php';
require_once '../includes/Models/Article.php';
require_once '../includes/Models/Meditation.php';
require_once '../includes/Models/Review.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

header('Content-Type: application/json');

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

// Получаем данные
$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;

if (!$productId) {
  http_response_code(400);
  echo json_encode(['error' => 'Product ID is required']);
  exit;
}

try {
  // Подключаемся к базе данных
  require_once '../config.php';
  require_once '../includes/Database.php';
  require_once '../includes/Models/Product.php';

  $db = Database::getInstance();
  $db->selectDatabase('cherkas_therapy');
  $productModel = new Product($db);

  // Получаем информацию о товаре
  $product = $productModel->getById($productId);

  if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found', 'product_id' => $productId]);
    exit;
  }

  // Инициализируем корзину если её нет
  if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
  }

  // Проверяем, есть ли уже такой товар в корзине
  $found = false;
  foreach ($_SESSION['cart'] as &$item) {
    if ($item['id'] == $productId) {
      $item['quantity']++;
      $found = true;
      break;
    }
  }

  // Если товара нет в корзине, добавляем его
  if (!$found) {
    $_SESSION['cart'][] = [
      'id' => $product['id'],
      'title' => $product['title'],
      'price' => $product['price'],
      'image' => $product['image'],
      'slug' => $product['slug'],
      'quantity' => 1
    ];
  }

  // Вычисляем общую сумму корзины
  $total = 0;
  foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
  }

  // Генерируем код для отслеживания добавления в корзину через Яндекс Метрику
  $metrikaCode = '';
  if (function_exists('trackYandexMetrikaAddToCart')) {
    $metrikaCode = trackYandexMetrikaAddToCart(
      $product['id'],
      $product['title'],
      $product['price'],
      1
    );
  }

  echo json_encode([
    'success' => true,
    'message' => 'Товар добавлен в корзину',
    'cart_count' => count($_SESSION['cart']),
    'cart_total' => $total,
    'metrika_code' => $metrikaCode
  ]);

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>