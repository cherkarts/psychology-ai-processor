<?php
// Включаем отображение всех ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/Models/Order.php';
require_once '../includes/Models/Article.php';
require_once '../includes/Models/Meditation.php';
require_once '../includes/Models/Review.php';
require_once '../includes/Models/Product.php';
require_once '../includes/Database.php';
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check permissions (упрощенная проверка вместо requirePermission)
if (!isset($_SESSION['admin_user'])) {
  header('Location: login.php');
  exit;
}
$pageTitle = 'Управление товарами';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid request token.';
    } else {
        $result = handleProductAction($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    header('Location: products.php');
    exit();
}

$page = intval($_GET['page'] ?? 1);
$type = $_GET['type'] ?? 'all';
$search = $_GET['search'] ?? '';
$products = getProducts($page, $type, $search);

require_once __DIR__ . '/includes/header.php';
?>

<div class="products-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-box"></i> Управление товарами</h1>
            <p>Управление медитативными продуктами и цифровым контентом</p>
        </div>
        <div class="header-actions">
            <a href="product-edit.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Новый товар
            </a>
            <button class="btn btn-secondary" onclick="exportProducts()">
                <i class="fas fa-download"></i> Экспорт
            </button>
        </div>
    </div>

    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="type">Тип товара:</label>
                <select name="type" id="type">
                    <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>Все типы</option>
                    <option value="digital" <?php echo $type === 'digital' ? 'selected' : ''; ?>>Цифровой</option>
                    <option value="physical" <?php echo $type === 'physical' ? 'selected' : ''; ?>>Физический</option>
                    <option value="service" <?php echo $type === 'service' ? 'selected' : ''; ?>>Услуга</option>
                    <option value="free" <?php echo $type === 'free' ? 'selected' : ''; ?>>Бесплатный</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" value="<?php echo sanitizeOutput($search); ?>"
                       placeholder="Название или описание товара">
            </div>
            <button type="submit" class="btn btn-primary">Применить</button>
        </form>
    </div>

    <div class="stats-row">
        <div class="stat-item">
            <span class="stat-number"><?php echo $products['stats']['total'] ?? 0; ?></span>
            <span class="stat-label">Всего товаров</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $products['stats']['in_stock'] ?? 0; ?></span>
            <span class="stat-label">В наличии</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $products['stats']['digital'] ?? 0; ?></span>
            <span class="stat-label">Цифровые</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $products['stats']['free'] ?? 0; ?></span>
            <span class="stat-label">Бесплатные</span>
        </div>
    </div>

    <?php if (empty($products['items'])): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>Товары не найдены</h3>
            <p>Создайте первый товар или измените параметры поиска</p>
        </div>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products['items'] as $product): ?>
                <div class="product-card">
                    <div class="product-header">
                        <?php if (!empty($product['image'])): ?>
                            <div class="product-image">
                                <img src="<?php echo sanitizeOutput($product['image']); ?>"
                                    alt="<?php echo sanitizeOutput($product['title']); ?>">
                                <div class="product-badges">
                                    <span class="badge badge-<?php echo $product['type'] ?? 'digital'; ?>">
                                        <?php echo ucfirst($product['type'] ?? 'digital'); ?>
                                    </span>
                                    <?php if (!($product['in_stock'] ?? true)): ?>
                                        <span class="badge badge-danger">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="product-image placeholder">
                                <i class="fas fa-box"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="product-content">
                        <h3 class="product-title">
                            <a href="product-edit.php?id=<?php echo urlencode($product['id'] ?? $product['slug']); ?>">
                                <?php echo sanitizeOutput($product['title']); ?>
                            </a>
                        </h3>
                        <?php if (!empty($product['short_description'])): ?>
                            <p class="product-description"><?php echo sanitizeOutput($product['short_description']); ?></p>
                        <?php endif; ?>
                        <div class="product-price">
                            <?php if (($product['type'] ?? 'digital') === 'free' || ($product['price'] ?? 0) == 0): ?>
                                <span class="price-free">Free</span>
                            <?php else: ?>
                                <span class="price-current"><?php echo number_format($product['price'] ?? 0, 0); ?> ₽</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="product-actions">
                        <a href="product-edit.php?id=<?php echo urlencode($product['id'] ?? $product['slug']); ?>"
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="../product.php?slug=<?php echo urlencode($product['slug']); ?>" class="btn btn-sm btn-info"
                           target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="btn btn-sm btn-danger"
                                onclick="deleteProduct('<?php echo $product['id'] ?? $product['slug']; ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    // Admin CSRF token for AJAX requests
    window.adminCSRFToken = '<?php echo generateCSRFToken(); ?>';

    function deleteProduct(productId) {
        if (!confirm('Вы уверены, что хотите удалить этот товар?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('product_id', productId);
        formData.append('csrf_token', window.adminCSRFToken);

        fetch('api/product-actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Ошибка: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при удалении товара');
        });
    }

    function exportProducts() {
        window.open(`api/export-products.php?type=${encodeURIComponent('<?php echo $type; ?>')}&search=${encodeURIComponent('<?php echo $search; ?>')}`, '_blank');
    }

    // Styles
    const productsStyles = `
    <style>
        .products-container { max-width: 1400px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .header-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .filters-section { background: white; padding: 1.5rem; border-radius: var(--border-radius-lg); box-shadow: var(--shadow-sm); margin-bottom: 2rem; }
        .filters-form { display: flex; gap: 2rem; align-items: end; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; gap: 0.5rem; min-width: 150px; }
        .filter-group label { font-weight: 500; color: var(--gray-700); font-size: 0.875rem; }
        .filter-group select, .filter-group input { padding: 0.5rem 0.75rem; border: 1px solid var(--gray-300); border-radius: var(--border-radius-md); font-size: 0.875rem; }
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-item { background: white; padding: 1rem; border-radius: var(--border-radius-md); box-shadow: var(--shadow-sm); text-align: center; }
        .stat-number { display: block; font-size: 1.5rem; font-weight: 700; color: var(--primary-color); }
        .stat-label { font-size: 0.875rem; color: var(--gray-600); }
        .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
        .product-card { background: white; border-radius: var(--border-radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; transition: all var(--transition-normal); display: flex; flex-direction: column; }
        .product-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
        .product-image { position: relative; height: 200px; overflow: hidden; }
        .product-image img { width: 100%; height: 100%; object-fit: contain; object-position: center; transition: transform 0.3s ease; }
        .product-image.placeholder { background: var(--gray-100); display: flex; align-items: center; justify-content: center; color: var(--gray-400); font-size: 3rem; }
        .product-badges { position: absolute; top: 0.75rem; right: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; }
        .badge-digital { background: #e3f2fd; color: #1565c0; }
        .badge-free { background: #e8f5e8; color: #2e7d32; }
        .badge-danger { background: #ffebee; color: #c62828; }
        .product-content { padding: 1.5rem; flex: 1; }
        .product-title { margin: 0 0 1rem 0; font-size: 1.125rem; line-height: 1.4; }
        .product-title a { color: var(--gray-800); text-decoration: none; transition: color var(--transition-fast); }
        .product-title a:hover { color: var(--primary-color); }
        .product-description { color: var(--gray-600); font-size: 0.875rem; line-height: 1.5; margin-bottom: 1rem; }
        .product-price { margin-bottom: 1rem; }
        .price-current { font-size: 1.25rem; font-weight: 700; color: var(--primary-color); }
        .price-free { font-size: 1.25rem; font-weight: 700; color: var(--success-color); }
        .product-actions { padding: 1rem 1.5rem; background: var(--gray-50); border-top: 1px solid var(--gray-200); display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--gray-600); }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; color: var(--gray-400); }
        @media (max-width: 768px) { .products-grid { grid-template-columns: 1fr; } .page-header { flex-direction: column; align-items: stretch; } .filters-form { flex-direction: column; gap: 1rem; } .stats-row { grid-template-columns: repeat(2, 1fr); } .product-image { height: 150px; } } @media (max-width: 480px) { .product-image { height: 120px; } }
    </style>
`;
    document.head.insertAdjacentHTML('beforeend', productsStyles);
</script>

<?php
require_once __DIR__ . '/includes/footer.php';

// Функция getProducts должна быть определена здесь или в отдельном файле
function getProducts($page = 1, $type = 'all', $search = '')
{
    $db = getAdminDB();
    $itemsPerPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $itemsPerPage;

    $products = [];
    $stats = ['total' => 0, 'in_stock' => 0, 'digital' => 0, 'free' => 0];

    if ($db) {
        $conditions = [];
        $params = [];

        if ($type !== 'all') {
            $conditions[] = "type = ?";
            $params[] = $type;
        }

        if (!empty($search)) {
            $conditions[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);

        $countSql = "SELECT COUNT(*) as total FROM products " . $whereClause;
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $totalItems = $stmt->fetch()['total'];

        $sql = "SELECT * FROM products " . $whereClause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $itemsPerPage;
        $params[] = $offset;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Get stats
        $stmt = $db->query("SELECT COUNT(*) as count FROM products");
        $stats['total'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE in_stock = 1");
        $stats['in_stock'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE type = 'digital'");
        $stats['digital'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COUNT(*) as count FROM products WHERE type = 'free' OR price = 0");
        $stats['free'] = $stmt->fetch()['count'];

    } else {
        // Fallback to file-based storage if database is not available
        $productModel = new Product();
        $productsData = $productModel->getAll();
        $products = $productsData['products'] ?? [];
        
        // Filter by type
        if ($type !== 'all') {
            $products = array_filter($products, function($product) use ($type) {
                return ($product['type'] ?? 'digital') === $type;
            });
        }
        
        // Filter by search
        if (!empty($search)) {
            $products = array_filter($products, function($product) use ($search) {
                return stripos($product['title'] ?? '', $search) !== false || 
                       stripos($product['description'] ?? '', $search) !== false;
            });
        }
        
        // Pagination
        $totalItems = count($products);
        $products = array_slice($products, $offset, $itemsPerPage);
        
        // Calculate stats
        $stats['total'] = count($productsData['products'] ?? []);
        $stats['in_stock'] = count(array_filter($productsData['products'] ?? [], function($p) { return $p['in_stock'] ?? true; }));
        $stats['digital'] = count(array_filter($productsData['products'] ?? [], function($p) { return ($p['type'] ?? 'digital') === 'digital'; }));
        $stats['free'] = count(array_filter($productsData['products'] ?? [], function($p) { return ($p['type'] ?? 'digital') === 'free' || ($p['price'] ?? 0) == 0; }));
    }

    return [
        'items' => $products,
        'stats' => $stats,
        'pagination' => [
            'page' => $page,
            'total' => $totalItems,
            'per_page' => $itemsPerPage,
            'pages' => ceil($totalItems / $itemsPerPage)
        ]
    ];
}

// Функция handleProductAction должна быть определена здесь или в отдельном файле
function handleProductAction($data)
{
    $action = $data['action'] ?? '';
    $productId = $data['product_id'] ?? '';

    if ($action === 'delete' && !empty($productId)) {
        $db = getAdminDB();
        
        if ($db) {
            try {
                $stmt = $db->prepare("DELETE FROM products WHERE id = ? OR slug = ?");
                $stmt->execute([$productId, $productId]);
                
                if ($stmt->rowCount() > 0) {
                    // logAdminActivity('delete', "Product #{$productId} deleted");
                    return ['success' => true, 'message' => 'Product deleted successfully.'];
                }
            } catch (PDOException $e) {
                error_log("Product action failed: " . $e->getMessage());
                return ['success' => false, 'message' => 'Database error occurred.'];
            }
        } else {
            // Fallback to file-based storage if database is not available
            $productModel = new Product();
            $productsData = $productModel->getAll();
            $products = $productsData['products'] ?? [];
            $products = array_filter($products, function ($product) use ($productId) {
                return ($product['id'] ?? $product['slug']) != $productId;
            });
            $productsData['products'] = array_values($products);
            if ($productModel->save($productsData)) {
                // logAdminActivity('delete', "Product #{$productId} deleted");
                return ['success' => true, 'message' => 'Product deleted successfully.'];
            }
        }
    }
    
    return ['success' => false, 'message' => 'Failed to perform action.'];
}
?>
