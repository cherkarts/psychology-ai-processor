<?php
/**
 * Класс для работы с заказами
 */
class Order
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Получить все заказы с фильтрами
   */
  public function getAll($filters = [])
  {
    $sql = "SELECT o.*, 
                       COUNT(oi.id) as items_count,
                       GROUP_CONCAT(oi.product_title SEPARATOR ', ') as products_list
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE 1=1";
    $params = [];

    if (!empty($filters['status'])) {
      $sql .= " AND o.status = ?";
      $params[] = $filters['status'];
    }

    if (!empty($filters['payment_status'])) {
      $sql .= " AND o.payment_status = ?";
      $params[] = $filters['payment_status'];
    }

    if (!empty($filters['email'])) {
      $sql .= " AND o.email LIKE ?";
      $params[] = "%{$filters['email']}%";
    }

    if (!empty($filters['date_from'])) {
      $sql .= " AND o.created_at >= ?";
      $params[] = $filters['date_from'];
    }

    if (!empty($filters['date_to'])) {
      $sql .= " AND o.created_at <= ?";
      $params[] = $filters['date_to'];
    }

    $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

    if (!empty($filters['limit'])) {
      $sql .= " LIMIT ?";
      $params[] = $filters['limit'];
    }

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Получить заказ по ID
   */
  public function getById($id)
  {
    $sql = "SELECT * FROM orders WHERE id = ?";
    $order = $this->db->fetchOne($sql, [$id]);

    if ($order) {
      $order['items'] = $this->getOrderItems($id);
    }

    return $order;
  }

  /**
   * Получить заказ по номеру
   */
  public function getByNumber($orderNumber)
  {
    $sql = "SELECT * FROM orders WHERE order_number = ?";
    $order = $this->db->fetchOne($sql, [$orderNumber]);

    if ($order) {
      $order['items'] = $this->getOrderItems($order['id']);
    }

    return $order;
  }

  /**
   * Получить элементы заказа
   */
  public function getOrderItems($orderId)
  {
    $sql = "SELECT oi.*, p.title as product_title, p.slug as product_slug 
                FROM order_items oi 
                LEFT JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";

    return $this->db->fetchAll($sql, [$orderId]);
  }

  /**
   * Создать новый заказ
   */
  public function create($data)
  {
    // Генерируем номер заказа если не указан
    if (empty($data['order_number'])) {
      $data['order_number'] = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    $orderId = $this->db->insert('orders', $data);

    // Логируем действие
    $this->db->logActivity('order_created', 'order', $orderId);

    return $orderId;
  }

  /**
   * Обновить заказ
   */
  public function update($id, $data)
  {
    $result = $this->db->update('orders', $data, 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('order_updated', 'order', $id);

    return $result;
  }

  /**
   * Удалить заказ
   */
  public function delete($id)
  {
    // Сначала удаляем элементы заказа
    $this->db->delete('order_items', 'order_id = ?', [$id]);

    // Затем удаляем сам заказ
    $result = $this->db->delete('orders', 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('order_deleted', 'order', $id);

    return $result;
  }

  /**
   * Добавить товар в заказ
   */
  public function addItem($orderId, $productId, $quantity = 1, $price = null)
  {
    // Получаем информацию о товаре
    $product = $this->db->fetchOne("SELECT title, price FROM products WHERE id = ?", [$productId]);

    if (!$product) {
      return false;
    }

    $itemPrice = $price ?? $product['price'];
    $totalPrice = $itemPrice * $quantity;

    $data = [
      'order_id' => $orderId,
      'product_id' => $productId,
      'product_title' => $product['title'],
      'quantity' => $quantity,
      'price' => $itemPrice,
      'total_price' => $totalPrice
    ];

    $itemId = $this->db->insert('order_items', $data);

    // Обновляем общую сумму заказа
    $this->updateOrderTotal($orderId);

    return $itemId;
  }

  /**
   * Обновить общую сумму заказа
   */
  public function updateOrderTotal($orderId)
  {
    $sql = "UPDATE orders o 
                SET total_amount = (
                    SELECT COALESCE(SUM(total_price), 0) 
                    FROM order_items 
                    WHERE order_id = ?
                ) 
                WHERE id = ?";

    return $this->db->execute($sql, [$orderId, $orderId]);
  }

  /**
   * Изменить статус заказа
   */
  public function updateStatus($id, $status, $notes = null)
  {
    $data = ['status' => $status];

    if ($status === 'completed' && empty($data['completed_at'])) {
      $data['completed_at'] = date('Y-m-d H:i:s');
    }

    if ($notes) {
      $data['notes'] = $notes;
    }

    $result = $this->update($id, $data);

    // Логируем действие
    $this->db->logActivity("order_status_changed_to_{$status}", 'order', $id);

    return $result;
  }

  /**
   * Изменить статус платежа
   */
  public function updatePaymentStatus($id, $paymentStatus, $paymentId = null)
  {
    $data = ['payment_status' => $paymentStatus];

    if ($paymentId) {
      $data['payment_id'] = $paymentId;
    }

    $result = $this->update($id, $data);

    // Логируем действие
    $this->db->logActivity("order_payment_status_changed_to_{$paymentStatus}", 'order', $id);

    return $result;
  }

  /**
   * Получить статистику заказов
   */
  public function getStats()
  {
    $sql = "SELECT 
                    COUNT(*) as total_orders,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
                    COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_orders,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
                    COUNT(CASE WHEN payment_status = 'paid' THEN 1 END) as paid_orders,
                    COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as unpaid_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value
                FROM orders";

    return $this->db->fetchOne($sql);
  }

  /**
   * Получить заказы пользователя
   */
  public function getByUser($userId, $limit = null)
  {
    $sql = "SELECT o.*, 
                       COUNT(oi.id) as items_count
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.user_id = ? 
                GROUP BY o.id 
                ORDER BY o.created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    return $this->db->fetchAll($sql, [$userId]);
  }

  /**
   * Получить заказы по email
   */
  public function getByEmail($email, $limit = null)
  {
    $sql = "SELECT o.*, 
                       COUNT(oi.id) as items_count
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                WHERE o.email = ? 
                GROUP BY o.id 
                ORDER BY o.created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$email, $limit]);
    }

    return $this->db->fetchAll($sql, [$email]);
  }

  /**
   * Получить последние заказы
   */
  public function getRecent($limit = 10)
  {
    $sql = "SELECT o.*, 
                       COUNT(oi.id) as items_count
                FROM orders o 
                LEFT JOIN order_items oi ON o.id = oi.order_id 
                GROUP BY o.id 
                ORDER BY o.created_at DESC 
                LIMIT ?";

    return $this->db->fetchAll($sql, [$limit]);
  }

  /**
   * Создать заказ из корзины
   */
  public function createFromCart($cartData, $userData)
  {
    // Создаем заказ
    $orderData = [
      'email' => $userData['email'],
      'phone' => $userData['phone'] ?? null,
      'name' => $userData['name'],
      'total_amount' => $cartData['total'],
      'currency' => 'RUB',
      'payment_method' => $userData['payment_method'] ?? null,
      'notes' => $userData['notes'] ?? null
    ];

    $orderId = $this->create($orderData);

    // Добавляем товары из корзины
    foreach ($cartData['items'] as $item) {
      $this->addItem($orderId, $item['id'], $item['quantity'], $item['price']);
    }

    return $orderId;
  }

  /**
   * Экспорт заказов в CSV
   */
  public function exportToCsv($filters = [])
  {
    $orders = $this->getAll($filters);

    $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.csv';
    $filepath = __DIR__ . '/../../exports/' . $filename;

    // Создаем директорию если её нет
    if (!is_dir(dirname($filepath))) {
      mkdir(dirname($filepath), 0755, true);
    }

    $fp = fopen($filepath, 'w');

    // Заголовки CSV
    fputcsv($fp, [
      'ID заказа',
      'Номер заказа',
      'Email',
      'Имя',
      'Телефон',
      'Статус',
      'Статус платежа',
      'Сумма',
      'Валюта',
      'Метод оплаты',
      'Количество товаров',
      'Товары',
      'Дата создания',
      'Дата завершения'
    ]);

    // Данные
    foreach ($orders as $order) {
      fputcsv($fp, [
        $order['id'],
        $order['order_number'],
        $order['email'],
        $order['name'],
        $order['phone'],
        $order['status'],
        $order['payment_status'],
        $order['total_amount'],
        $order['currency'],
        $order['payment_method'],
        $order['items_count'],
        $order['products_list'],
        $order['created_at'],
        $order['completed_at']
      ]);
    }

    fclose($fp);
    return $filepath;
  }
}
?>