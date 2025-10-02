<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Проверяем права доступа
requirePermission('orders');

$orderId = $_GET['id'] ?? '';

if (empty($orderId)) {
  echo json_encode(['success' => false, 'message' => 'ID заказа не указан']);
  exit;
}

try {
  $db = getAdminDB();

  // Получаем информацию о заказе
  $orderSql = "SELECT * FROM orders WHERE id = ?";
  $stmt = $db->prepare($orderSql);
  $stmt->execute([$orderId]);
  $order = $stmt->fetch();

  if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Заказ не найден']);
    exit;
  }

  // Получаем товары заказа
  $itemsSql = "SELECT oi.*, p.title as product_title, p.slug as product_slug 
                 FROM order_items oi 
                 LEFT JOIN products p ON oi.product_id = p.id 
                 WHERE oi.order_id = ?";
  $stmt = $db->prepare($itemsSql);
  $stmt->execute([$orderId]);
  $items = $stmt->fetchAll();

  // Формируем HTML
  $html = '<div class="order-details">';

  // Информация о заказе
  $html .= '<div class="order-info">';
  $html .= '<h4>Информация о заказе</h4>';
  $html .= '<div class="info-grid">';
  $html .= '<div class="info-item"><strong>№ Заказа:</strong> ' . htmlspecialchars($order['order_number'] ?? $order['id']) . '</div>';
  $html .= '<div class="info-item"><strong>Дата:</strong> ' . date('d.m.Y H:i', strtotime($order['created_at'])) . '</div>';
  $html .= '<div class="info-item"><strong>Статус:</strong> <span class="status-badge status-' . $order['status'] . '">' . ucfirst($order['status']) . '</span></div>';
  $html .= '<div class="info-item"><strong>Сумма:</strong> ' . number_format($order['total_amount'], 0) . ' ₽</div>';
  $html .= '<div class="info-item"><strong>Способ оплаты:</strong> ' . htmlspecialchars($order['payment_method'] ?? 'Не указан') . '</div>';
  $html .= '<div class="info-item"><strong>Статус платежа:</strong> <span class="payment-badge payment-' . ($order['payment_status'] ?? 'pending') . '">' . ucfirst($order['payment_status'] ?? 'pending') . '</span></div>';
  $html .= '</div>';
  $html .= '</div>';

  // Информация о клиенте
  $html .= '<div class="customer-info">';
  $html .= '<h4>Информация о клиенте</h4>';
  $html .= '<div class="info-grid">';
  $html .= '<div class="info-item"><strong>Имя:</strong> ' . htmlspecialchars($order['name']) . '</div>';
  $html .= '<div class="info-item"><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</div>';
  if (!empty($order['phone'])) {
    $html .= '<div class="info-item"><strong>Телефон:</strong> ' . htmlspecialchars($order['phone']) . '</div>';
  }
  $html .= '</div>';
  $html .= '</div>';

  // Комментарий к заказу
  if (!empty($order['notes'])) {
    $html .= '<div class="order-notes">';
    $html .= '<h4>Комментарий клиента</h4>';
    $html .= '<div class="notes-content">' . nl2br(htmlspecialchars($order['notes'])) . '</div>';
    $html .= '</div>';
  }

  // Товары в заказе
  $html .= '<div class="order-items">';
  $html .= '<h4>Товары в заказе</h4>';
  $html .= '<div class="items-table">';
  $html .= '<table>';
  $html .= '<thead><tr><th>Товар</th><th>Количество</th><th>Цена</th><th>Сумма</th></tr></thead>';
  $html .= '<tbody>';

  $totalAmount = 0;
  foreach ($items as $item) {
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($item['product_title']) . '</td>';
    $html .= '<td>' . $item['quantity'] . '</td>';
    $html .= '<td>' . number_format($item['price'], 0) . ' ₽</td>';
    $html .= '<td>' . number_format($item['total_price'], 0) . ' ₽</td>';
    $html .= '</tr>';
    $totalAmount += $item['total_price'];
  }

  $html .= '</tbody>';
  $html .= '<tfoot>';
  $html .= '<tr><th colspan="3">Итого:</th><th>' . number_format($totalAmount, 0) . ' ₽</th></tr>';
  $html .= '</tfoot>';
  $html .= '</table>';
  $html .= '</div>';
  $html .= '</div>';

  $html .= '</div>';

  echo json_encode(['success' => true, 'html' => $html]);

} catch (Exception $e) {
  error_log("Order details error: " . $e->getMessage());
  echo json_encode(['success' => false, 'message' => 'Ошибка загрузки деталей заказа']);
}
?>