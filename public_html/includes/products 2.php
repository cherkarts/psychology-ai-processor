<?php
/**
 * Система управления товарами через базу данных
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Models/Product.php';

// Инициализация сессии
if (!isset($_SESSION)) {
    session_start();
}

// Класс для работы с товарами через БД
class ProductManager
{
    private $db;
    private $product;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->product = new Product();
    }

    // Получить все товары
    public function getAllProducts($category = null)
    {
        $filters = [];
        if ($category) {
            $filters['category'] = $category;
        }

        return $this->product->getAll($filters);
    }

    // Получить товар по ID
    public function getProduct($id)
    {
        return $this->product->getById($id);
    }

    // Получить товар по slug
    public function getProductBySlug($slug)
    {
        return $this->product->getBySlug($slug);
    }

    // Добавить товар
    public function addProduct($productData)
    {
        return $this->product->create($productData);
    }

    // Обновить товар
    public function updateProduct($id, $productData)
    {
        return $this->product->update($id, $productData);
    }

    // Удалить товар
    public function deleteProduct($id)
    {
        return $this->product->delete($id);
    }

    // Получить избранные товары
    public function getFeaturedProducts($limit = 6)
    {
        return $this->product->getFeatured($limit);
    }

    // Поиск товаров
    public function searchProducts($query, $limit = 20)
    {
        return $this->product->search($query, $limit);
    }

    // Получить категории
    public function getCategories()
    {
        return $this->product->getCategories();
    }

    // Создать заказ
    public function createOrder($orderData)
    {
        $orderData['status'] = 'pending';
        $orderData['created_at'] = date('Y-m-d H:i:s');
        $orderData['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->insert('orders', $orderData);
    }

    // Получить заказ
    public function getOrder($id)
    {
        $sql = "SELECT o.*, oi.*, p.title as product_title, p.slug as product_slug 
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE o.id = ?";

        return $this->db->fetchOne($sql, [$id]);
    }

    // Обновить статус заказа
    public function updateOrderStatus($id, $status)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        return $this->db->update('orders', $data, 'id = ?', [$id]);
    }

    // Получить все заказы
    public function getAllOrders($status = null)
    {
        $sql = "SELECT o.*, COUNT(oi.id) as items_count 
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE 1=1";

        $params = [];

        if ($status) {
            $sql .= " AND o.status = ?";
            $params[] = $status;
        }

        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }
}

// Класс для работы с корзиной
class Cart
{
    public function __construct()
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    // Добавить товар в корзину
    public function addItem($productId, $quantity = 1)
    {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }

    // Удалить товар из корзины
    public function removeItem($productId)
    {
        unset($_SESSION['cart'][$productId]);
    }

    // Обновить количество
    public function updateQuantity($productId, $quantity)
    {
        if ($quantity > 0) {
            $_SESSION['cart'][$productId] = $quantity;
        } else {
            $this->removeItem($productId);
        }
    }

    // Получить содержимое корзины
    public function getItems()
    {
        $items = [];

        // Проверяем, какая структура данных используется
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            // Если это массив объектов (новая структура)
            if (isset($_SESSION['cart'][0]) && is_array($_SESSION['cart'][0])) {
                foreach ($_SESSION['cart'] as $item) {
                    if (isset($item['id']) && isset($item['quantity'])) {
                        $items[] = [
                            'product' => $item,
                            'quantity' => $item['quantity'],
                            'total' => $item['price'] * $item['quantity']
                        ];
                    }
                }
            } else {
                // Старая структура: $_SESSION['cart'][$productId] = $quantity
                $productManager = new ProductManager();
                foreach ($_SESSION['cart'] as $productId => $quantity) {
                    $product = $productManager->getProduct($productId);
                    if ($product) {
                        $items[] = [
                            'product' => $product,
                            'quantity' => $quantity,
                            'total' => $product['price'] * $quantity
                        ];
                    }
                }
            }
        }

        return $items;
    }

    // Получить общую сумму
    public function getTotal()
    {
        $items = $this->getItems();
        $total = 0;

        foreach ($items as $item) {
            $total += $item['total'];
        }

        return $total;
    }

    // Очистить корзину
    public function clear()
    {
        $_SESSION['cart'] = [];
    }

    // Получить количество товаров
    public function getCount()
    {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            return 0;
        }

        // Проверяем, какая структура данных используется
        if (isset($_SESSION['cart'][0]) && is_array($_SESSION['cart'][0])) {
            // Новая структура: массив объектов
            $count = 0;
            foreach ($_SESSION['cart'] as $item) {
                if (isset($item['quantity'])) {
                    $count += $item['quantity'];
                }
            }
            return $count;
        } else {
            // Старая структура: $_SESSION['cart'][$productId] = $quantity
            return array_sum($_SESSION['cart']);
        }
    }
}

// Класс для работы с оплатой
class PaymentManager
{
    private $config;

    public function __construct()
    {
        $this->config = getConfig();
    }

    // Создать платеж в Яндекс.Кассе
    public function createYandexPayment($order)
    {
        // Здесь будет интеграция с Яндекс.Кассой
        $paymentData = [
            'amount' => $order['total'],
            'currency' => 'RUB',
            'order_id' => $order['id'],
            'description' => 'Заказ на сайте cherkas-therapy.ru',
            'return_url' => $this->config['site']['url'] . '/payment-success.php',
            'cancel_url' => $this->config['site']['url'] . '/payment-cancel.php'
        ];

        // Логика создания платежа
        return $paymentData;
    }

    // Создать платеж в Сбербанке
    public function createSberPayment($order)
    {
        // Здесь будет интеграция со Сбербанком
        $paymentData = [
            'amount' => $order['total'],
            'currency' => 'RUB',
            'order_id' => $order['id'],
            'description' => 'Заказ на сайте cherkas-therapy.ru',
            'return_url' => $this->config['site']['url'] . '/payment-success.php',
            'cancel_url' => $this->config['site']['url'] . '/payment-cancel.php'
        ];

        // Логика создания платежа
        return $paymentData;
    }

    // Обработать уведомление об оплате
    public function processPaymentNotification($data)
    {
        // Обработка уведомления от платежной системы
        $orderId = $data['order_id'] ?? null;
        $status = $data['status'] ?? null;

        if ($orderId && $status) {
            $productManager = new ProductManager();
            $productManager->updateOrderStatus($orderId, $status);

            // Отправка уведомлений
            $this->sendOrderNotifications($orderId, $status);
        }
    }

    // Отправить уведомления о заказе
    private function sendOrderNotifications($orderId, $status)
    {
        $productManager = new ProductManager();
        $order = $productManager->getOrder($orderId);

        if ($order) {
            // Уведомление клиенту
            $this->sendCustomerNotification($order, $status);

            // Уведомление администратору
            $this->sendAdminNotification($order, $status);
        }
    }

    // Уведомление клиенту
    private function sendCustomerNotification($order, $status)
    {
        $subject = 'Статус заказа #' . $order['id'];
        $message = "Здравствуйте, {$order['name']}!\n\n";
        $message .= "Статус вашего заказа: {$status}\n";
        $message .= "Сумма заказа: {$order['total']} руб.\n\n";
        $message .= "Спасибо за покупку!";

        // Отправка email
        sendEmail($message, null);
    }

    // Уведомление администратору
    private function sendAdminNotification($order, $status)
    {
        $subject = 'Новый заказ #' . $order['id'];
        $message = "Новый заказ:\n";
        $message .= "ID: {$order['id']}\n";
        $message .= "Клиент: {$order['name']}\n";
        $message .= "Email: {$order['email']}\n";
        $message .= "Сумма: {$order['total']} руб.\n";
        $message .= "Статус: {$status}";

        // Отправка в Telegram
        sendToTelegram($message, null);
    }
}

// Инициализация классов
$productManager = new ProductManager();
$cart = new Cart();
$paymentManager = new PaymentManager();
?>