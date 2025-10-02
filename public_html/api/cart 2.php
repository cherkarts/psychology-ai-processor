<?php
/**
 * API для работы с корзиной через базу данных
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Models/Product.php';

$db = Database::getInstance();
$product = new Product();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGet($action, $product);
            break;

        case 'POST':
            handlePost($action, $product);
            break;

        case 'PUT':
            handlePut($action, $product);
            break;

        case 'DELETE':
            handleDelete($action, $product);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleGet($action, $product)
{
    switch ($action) {
        case 'items':
            // Получить товары в корзине
            $cartItems = getCartItems($product);
            echo json_encode(['success' => true, 'data' => $cartItems]);
            break;

        case 'total':
            // Получить общую сумму корзины
            $cartItems = getCartItems($product);
            $total = 0;

            foreach ($cartItems as $item) {
                $total += $item['total'];
            }

            echo json_encode(['success' => true, 'total' => $total]);
            break;

        case 'count':
            // Получить количество товаров в корзине
            $count = getCartCount();
            echo json_encode(['success' => true, 'data' => ['count' => $count]]);
            break;

        default:
            // Получить полную информацию о корзине
            $cartItems = getCartItems($product);
            $total = 0;
            $count = 0;

            foreach ($cartItems as $item) {
                $total += $item['total'];
                $count += $item['quantity'];
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'items' => $cartItems,
                    'total' => $total,
                    'count' => $count
                ]
            ]);
            break;
    }
}

function handlePost($action, $product)
{
    switch ($action) {
        case 'clear':
            // Очистить корзину - не требует JSON данных
            clearCart();
            echo json_encode(['success' => true, 'message' => 'Cart cleared']);
            break;

        default:
            // Для остальных действий требуются JSON данные
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                return;
            }

            switch ($action) {
                case 'add':
                    // Добавить товар в корзину
                    if (empty($input['product_id'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Product ID is required']);
                        return;
                    }

                    $quantity = $input['quantity'] ?? 1;
                    addToCart($input['product_id'], $quantity);

                    echo json_encode(['success' => true, 'message' => 'Product added to cart']);
                    break;

                case 'update':
                    // Обновить количество товара в корзине
                    if (empty($input['product_id'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Product ID is required']);
                        return;
                    }

                    $quantity = $input['quantity'] ?? 1;
                    updateCartItem($input['product_id'], $quantity);

                    echo json_encode(['success' => true, 'message' => 'Cart updated']);
                    break;

                case 'remove':
                    // Удалить товар из корзины
                    if (empty($input['product_id'])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Product ID is required']);
                        return;
                    }

                    removeFromCart($input['product_id']);
                    echo json_encode(['success' => true, 'message' => 'Product removed from cart']);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
                    break;
            }
            break;
    }
}

function handlePut($action, $product)
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        return;
    }

    switch ($action) {
        case 'update':
            // Обновить количество товара в корзине
            if (empty($input['product_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Product ID is required']);
                return;
            }

            $quantity = $input['quantity'] ?? 1;
            updateCartItem($input['product_id'], $quantity);

            echo json_encode(['success' => true, 'message' => 'Cart updated']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

function handleDelete($action, $product)
{
    switch ($action) {
        case 'remove':
            // Удалить товар из корзины
            $productId = $_GET['product_id'] ?? '';

            if (empty($productId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Product ID is required']);
                return;
            }

            removeFromCart($productId);
            echo json_encode(['success' => true, 'message' => 'Product removed from cart']);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

// Вспомогательные функции для работы с корзиной

function getCartItems($product)
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $items = [];

    // Используем новую структуру: массив объектов
    foreach ($_SESSION['cart'] as $item) {
        if (isset($item['id']) && isset($item['quantity'])) {
            $items[] = [
                'product' => $item,
                'quantity' => $item['quantity'],
                'total' => $item['price'] * $item['quantity']
            ];
        }
    }

    return $items;
}

function getCartCount()
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        return 0;
    }

    // Используем новую структуру: массив объектов
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        if (isset($item['quantity'])) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

function addToCart($productId, $quantity = 1)
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Проверяем, есть ли уже такой товар в корзине
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    // Если товара нет в корзине, добавляем его
    if (!$found) {
        // Получаем информацию о товаре из базы данных
        global $product;
        $productData = $product->getById($productId);
        if ($productData) {
            $_SESSION['cart'][] = [
                'id' => $productData['id'],
                'title' => $productData['title'],
                'price' => $productData['price'],
                'image' => $productData['image'],
                'slug' => $productData['slug'],
                'quantity' => $quantity
            ];
        }
    }
}

function updateCartItem($productId, $quantity)
{
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if ($quantity > 0) {
        // Находим товар в корзине и обновляем количество
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $productId) {
                $item['quantity'] = $quantity;
                break;
            }
        }
    } else {
        removeFromCart($productId);
    }
}

function removeFromCart($productId)
{
    if (!isset($_SESSION['cart'])) {
        return;
    }

    // Находим и удаляем товар из корзины
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $productId) {
            unset($_SESSION['cart'][$key]);
            break;
        }
    }

    // Переиндексируем массив
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

function clearCart()
{
    $_SESSION['cart'] = [];
}
?>