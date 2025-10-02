<?php
require_once '../includes/Models/Order.php';
require_once '../includes/Models/Article.php';
require_once '../includes/Models/Meditation.php';
require_once '../includes/Models/Review.php';
require_once '../includes/Models/Product.php';
require_once '../includes/Database.php';
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

requirePermission('orders');
$pageTitle = 'Управление заказами';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Неверный токен безопасности.';
    } else {
        $result = handleOrderAction($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    header('Location: orders.php');
    exit();
}

$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Отладочная информация
error_log('Orders page - page: ' . $page . ', status: ' . $status . ', search: ' . $search);

try {
    $orders = getOrders($page, $status, $search);
    error_log('Orders loaded successfully, count: ' . count($orders['items'] ?? []));
} catch (Exception $e) {
    error_log('Error loading orders: ' . $e->getMessage());
    $orders = ['items' => [], 'stats' => ['total' => 0, 'pending' => 0, 'completed' => 0, 'revenue' => 0], 'pagination' => []];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="orders-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-shopping-cart"></i> Управление заказами</h1>
            <p>Отслеживание заказов клиентов и платежей</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="exportOrders()">
                <i class="fas fa-download"></i> Экспорт
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="status">Статус:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все заказы</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Ожидающие</option>
                    <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Обрабатываются
                    </option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Завершенные
                    </option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Отмененные</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" value="<?php echo sanitizeOutput($search); ?>"
                    placeholder="Поиск по номеру заказа, email...">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Найти
                </button>
            </div>
        </form>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-item">
            <span class="stat-number"><?php echo $orders['stats']['total'] ?? 0; ?></span>
            <span class="stat-label">Всего заказов</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $orders['stats']['pending'] ?? 0; ?></span>
            <span class="stat-label">Ожидающие</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $orders['stats']['completed'] ?? 0; ?></span>
            <span class="stat-label">Завершенные</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($orders['stats']['revenue'] ?? 0, 0); ?> ₽</span>
            <span class="stat-label">Общая выручка</span>
        </div>
    </div>

    <!-- Orders List -->
    <div class="orders-list">
        <?php if (empty($orders['items'])): ?>
            <div class="empty-state">
                <i class="fas fa-shopping-cart"></i>
                <h3>Заказов не найдено</h3>
                <p>Нет заказов, соответствующих текущим фильтрам.</p>
            </div>
        <?php else: ?>
            <div class="orders-table-wrapper">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>№ Заказа</th>
                            <th>Клиент</th>
                            <th>Дата</th>
                            <th>Сумма</th>
                            <th>Статус</th>
                            <th>Платеж</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders['items'] as $order): ?>
                            <tr class="order-row">
                                <td class="order-number">
                                    <strong><?php echo sanitizeOutput($order['order_number'] ?? $order['id']); ?></strong>
                                </td>
                                <td class="order-customer">
                                    <div class="customer-info">
                                        <div class="customer-name"><?php echo sanitizeOutput($order['name']); ?></div>
                                        <div class="customer-email"><?php echo sanitizeOutput($order['email']); ?></div>
                                        <?php if (!empty($order['phone'])): ?>
                                            <div class="customer-phone"><?php echo sanitizeOutput($order['phone']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="order-date">
                                    <?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="order-total">
                                    <strong><?php echo number_format($order['total_amount'], 0); ?> ₽</strong>
                                </td>
                                <td class="order-status">
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td class="order-payment">
                                    <span class="payment-badge payment-<?php echo $order['payment_status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($order['payment_status'] ?? 'pending'); ?>
                                    </span>
                                    <?php if (!empty($order['payment_method'])): ?>
                                        <div class="payment-method"><?php echo ucfirst($order['payment_method']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="order-actions">
                                    <button class="btn btn-sm btn-info"
                                        onclick="viewOrderDetails('<?php echo $order['id']; ?>')">
                                        <i class="fas fa-eye"></i> Просмотр
                                    </button>

                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button class="btn btn-sm btn-success"
                                            onclick="updateOrderStatus('<?php echo $order['id']; ?>', 'processing')">
                                            <i class="fas fa-play"></i> Обработать
                                        </button>
                                    <?php elseif ($order['status'] === 'processing'): ?>
                                        <button class="btn btn-sm btn-success"
                                            onclick="updateOrderStatus('<?php echo $order['id']; ?>', 'completed')">
                                            <i class="fas fa-check"></i> Завершить
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($order['status'] !== 'cancelled'): ?>
                                        <button class="btn btn-sm btn-danger"
                                            onclick="updateOrderStatus('<?php echo $order['id']; ?>', 'cancelled')">
                                            <i class="fas fa-times"></i> Отменить
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($orders['pagination']['total_pages'] > 1): ?>
        <div class="pagination-wrapper">
            <nav class="pagination">
                <?php if ($orders['pagination']['current_page'] > 1): ?>
                    <a href="?page=<?php echo $orders['pagination']['current_page'] - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"
                        class="page-link">
                        <i class="fas fa-chevron-left"></i> Предыдущая
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $orders['pagination']['total_pages']; $i++): ?>
                    <?php if ($i == $orders['pagination']['current_page']): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"
                            class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($orders['pagination']['current_page'] < $orders['pagination']['total_pages']): ?>
                    <a href="?page=<?php echo $orders['pagination']['current_page'] + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>"
                        class="page-link">
                        Следующая <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Order Details Modal -->
<div class="modal-overlay" id="orderDetailsModal">
    <div class="modal-content order-modal">
        <div class="modal-header">
            <h3>Детали заказа</h3>
            <button class="modal-close" onclick="closeOrderModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="orderDetailsContent">
            Загрузка...
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeOrderModal()">Закрыть</button>
        </div>
    </div>
</div>

<script>
    function updateOrderStatus(orderId, status) {
        const statusText = {
            'processing': 'обработать',
            'completed': 'завершить',
            'cancelled': 'отменить',
            'refunded': 'вернуть'
        };

        const statusTitle = {
            'processing': 'Обработка заказа',
            'completed': 'Завершение заказа',
            'cancelled': 'Отмена заказа',
            'refunded': 'Возврат заказа'
        };

        showConfirmModal(
            statusTitle[status] || 'Действие с заказом',
            `Вы уверены, что хотите ${statusText[status] || status} этот заказ?`,
            function () {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" value="${orderId}">
                <input type="hidden" name="status" value="${status}">
                <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
            `;
                document.body.appendChild(form);
                form.submit();
            }
        );
    }

    function viewOrderDetails(orderId) {
        document.getElementById('orderDetailsModal').style.display = 'flex';
        document.getElementById('orderDetailsContent').innerHTML = 'Загрузка...';

        fetch(`api/order-details.php?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('orderDetailsContent').innerHTML = data.html;
                } else {
                    document.getElementById('orderDetailsContent').innerHTML = '<p>Ошибка загрузки деталей заказа.</p>';
                }
            })
            .catch(error => {
                document.getElementById('orderDetailsContent').innerHTML = '<p>Ошибка загрузки деталей заказа.</p>';
            });
    }

    function closeOrderModal() {
        document.getElementById('orderDetailsModal').style.display = 'none';
    }

    function exportOrders() {
        const params = new URLSearchParams({
            status: '<?php echo $status; ?>',
            search: '<?php echo $search; ?>'
        });
        window.open(`api/export-orders.php?${params.toString()}`, '_blank');
    }

    // Styles
    const ordersStyles = `
    <style>
        .orders-container { max-width: 1400px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .filters-section { background: white; padding: 1.5rem; border-radius: var(--border-radius-lg); box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .filters-form { display: flex; gap: 2rem; align-items: end; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; gap: 0.5rem; min-width: 150px; }
        .filter-group label { font-weight: 500; color: var(--gray-700); font-size: 0.875rem; }
        .filter-group select, .filter-group input { padding: 0.5rem 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius-md); font-size: 0.875rem; }
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-item { background: white; padding: 1rem; border-radius: var(--border-radius-md); box-shadow: var(--shadow-sm); text-align: center; }
        .stat-number { display: block; font-size: 1.5rem; font-weight: 700; color: var(--primary-color); }
        .stat-label { font-size: 0.875rem; color: var(--gray-600); }
        .orders-table-wrapper { background: white; border-radius: var(--border-radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
        .orders-table { width: 100%; border-collapse: collapse; }
        .orders-table th { background: var(--gray-50); padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700); border-bottom: 1px solid var(--gray-200); }
        .orders-table td { padding: 1rem; border-bottom: 1px solid var(--gray-200); }
        .order-row:hover { background: var(--gray-50); }
        .customer-info { display: flex; flex-direction: column; gap: 0.25rem; }
        .customer-name { font-weight: 500; color: var(--gray-800); }
        .customer-email { font-size: 0.875rem; color: var(--gray-600); }
        .customer-phone { font-size: 0.875rem; color: var(--gray-600); }
        .status-badge, .payment-badge { padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce7ff; color: #0056b3; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .payment-pending { background: #fff3cd; color: #856404; }
        .payment-paid { background: #d4edda; color: #155724; }
        .payment-failed { background: #f8d7da; color: #721c24; }
        .payment-method { font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem; }
        .order-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--gray-600); background: white; border-radius: var(--border-radius-lg); }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; color: var(--gray-400); }
        .order-modal .modal-content { max-width: 800px; }
        .pagination-wrapper { margin-top: 2rem; display: flex; justify-content: center; }
        .pagination { display: flex; gap: 0.5rem; align-items: center; }
        .page-link { padding: 0.5rem 1rem; background: white; color: var(--gray-700); text-decoration: none; border-radius: var(--border-radius-md); border: 1px solid var(--gray-300); transition: all var(--transition-fast); }
        .page-link:hover { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .page-link.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        @media (max-width: 768px) { .orders-table-wrapper { overflow-x: auto; } .page-header { flex-direction: column; align-items: stretch; } .filters-form { flex-direction: column; gap: 1rem; } .stats-row { grid-template-columns: repeat(2, 1fr); } .order-actions { justify-content: center; } }
    </style>
`;
    document.head.insertAdjacentHTML('beforeend', ordersStyles);
</script>

<?php
require_once __DIR__ . '/includes/footer.php';

function getOrders($page = 1, $status = 'all', $search = '')
{
    error_log('getOrders called with page: ' . $page . ', status: ' . $status . ', search: ' . $search);

    $db = getAdminDB();
    error_log('getAdminDB result: ' . ($db ? 'SUCCESS' : 'FAILED'));

    $itemsPerPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $itemsPerPage;

    $orders = [];
    $stats = ['total' => 0, 'pending' => 0, 'completed' => 0, 'revenue' => 0];

    if ($db) {
        $conditions = [];
        $params = [];

        if ($status !== 'all') {
            $conditions[] = "status = ?";
            $params[] = $status;
        }

        if (!empty($search)) {
            $conditions[] = "(order_number LIKE ? OR email LIKE ? OR name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        $countSql = "SELECT COUNT(*) as total FROM orders " . $whereClause;
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $totalItems = $stmt->fetch()['total'];

        $sql = "SELECT * FROM orders " . $whereClause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $itemsPerPage;
        $params[] = $offset;

        error_log('SQL query: ' . $sql);
        error_log('SQL params: ' . print_r($params, true));

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        error_log('Orders fetched: ' . count($orders));

        // Get stats
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders");
        $stats['total'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
        $stats['pending'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'");
        $stats['completed'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE status = 'completed'");
        $stats['revenue'] = $stmt->fetch()['revenue'];

    } else {

        $orderModel = new Order();
        $ordersData = $orderModel->getAll();
        $allOrders = $ordersData['orders'] ?? [];

        $filteredOrders = array_filter($allOrders, function ($order) use ($status, $search) {
            $statusMatch = $status === 'all' || ($order['status'] ?? 'pending') === $status;
            $searchMatch = empty($search) ||
                stripos($order['order_number'] ?? '', $search) !== false ||
                stripos($order['email'] ?? '', $search) !== false ||
                stripos($order['name'] ?? '', $search) !== false;
            return $statusMatch && $searchMatch;
        });

        $totalItems = count($filteredOrders);
        $orders = array_slice($filteredOrders, $offset, $itemsPerPage);

        $stats['total'] = count($allOrders);
        foreach ($allOrders as $order) {
            if (($order['status'] ?? 'pending') === 'pending')
                $stats['pending']++;
            if (($order['status'] ?? 'pending') === 'completed') {
                $stats['completed']++;
                $stats['revenue'] += floatval($order['total_amount'] ?? 0);
            }
        }
    }

    return [
        'items' => $orders,
        'stats' => $stats,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $itemsPerPage),
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]
    ];
}

function handleOrderAction($data)
{
    $action = $data['action'] ?? '';
    $orderId = $data['order_id'] ?? '';

    if (empty($orderId)) {
        return ['success' => false, 'message' => 'ID заказа обязателен.'];
    }

    $db = getAdminDB();

    if ($db) {
        try {
            if ($action === 'update_status') {
                $status = $data['status'] ?? '';
                if (!in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
                    return ['success' => false, 'message' => 'Неверный статус.'];
                }

                $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$status, $orderId]);

                logAdminActivity('update', "Order #{$orderId} status changed to {$status}");

                $statusText = [
                    'pending' => 'ожидание',
                    'processing' => 'обработка',
                    'completed' => 'завершен',
                    'cancelled' => 'отменен'
                ];

                return ['success' => true, 'message' => "Статус заказа изменен на: {$statusText[$status]}."];
            }
        } catch (PDOException $e) {
            logAdminError("Order action failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Ошибка базы данных.'];
        }
    }

    return ['success' => false, 'message' => 'Не удалось выполнить действие.'];
}
?>