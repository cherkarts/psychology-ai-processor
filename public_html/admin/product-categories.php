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

// Check permissions (упрощенная проверка вместо requirePermission)
if (!isset($_SESSION['admin_user'])) {
  header('Location: login.php');
  exit;
}

$pageTitle = 'Управление категориями товаров';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = 'Неверный токен безопасности.';
  } else {
    $result = handleCategoryAction($_POST);
    if ($result['success']) {
      $_SESSION['success_message'] = $result['message'];
    } else {
      $_SESSION['error_message'] = $result['message'];
    }
  }
  header('Location: product-categories.php');
  exit();
}

// Get categories
$categories = getProductCategories();

require_once __DIR__ . '/includes/header.php';
?>

<main class="admin-main">
  <div class="admin-container">
    <div class="page-header">
      <div class="header-content">
        <h1><i class="fas fa-tags"></i> Управление категориями товаров</h1>
        <p>Управление категориями для медитативных продуктов и цифрового контента</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" onclick="showAddCategoryModal()">
          <i class="fas fa-plus"></i> Новая категория
        </button>
      </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $_SESSION['success_message'];
        unset($_SESSION['success_message']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $_SESSION['error_message'];
        unset($_SESSION['error_message']); ?>
      </div>
    <?php endif; ?>

    <!-- Categories List -->
    <div class="categories-list">
      <?php if (empty($categories)): ?>
        <div class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-tags"></i>
          </div>
          <h3>Категории не найдены</h3>
          <p>Создайте первую категорию для организации товаров.</p>
          <button class="btn btn-primary" onclick="showAddCategoryModal()">
            <i class="fas fa-plus"></i> Создать первую категорию
          </button>
        </div>
      <?php else: ?>
        <div class="categories-grid">
          <?php foreach ($categories as $category): ?>
            <div class="category-card" data-category-id="<?php echo $category['id']; ?>">
              <div class="category-header">
                <div class="category-icon">
                  <i class="fas fa-tag"></i>
                </div>
                <div class="category-status">
                  <span class="status-badge <?php echo $category['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo $category['is_active'] ? 'Активна' : 'Неактивна'; ?>
                  </span>
                </div>
              </div>

              <div class="category-content">
                <h3 class="category-name">
                  <?php echo htmlspecialchars($category['name']); ?>
                </h3>

                <?php if (!empty($category['description'])): ?>
                  <p class="category-description">
                    <?php echo htmlspecialchars($category['description']); ?>
                  </p>
                <?php endif; ?>

                <div class="category-meta">
                  <span class="category-slug">
                    <i class="fas fa-link"></i>
                    <?php echo htmlspecialchars($category['slug']); ?>
                  </span>
                  <span class="category-order">
                    <i class="fas fa-sort"></i>
                    Порядок: <?php echo $category['sort_order']; ?>
                  </span>
                </div>
              </div>

              <div class="category-actions">
                <button class="btn btn-sm btn-secondary" onclick="editCategory(<?php echo $category['id']; ?>)"
                  title="Редактировать">
                  <i class="fas fa-edit"></i>
                </button>

                <?php if ($category['is_active']): ?>
                  <button class="btn btn-sm btn-warning" onclick="toggleCategoryStatus(<?php echo $category['id']; ?>, 0)"
                    title="Деактивировать">
                    <i class="fas fa-eye-slash"></i>
                  </button>
                <?php else: ?>
                  <button class="btn btn-sm btn-success" onclick="toggleCategoryStatus(<?php echo $category['id']; ?>, 1)"
                    title="Активировать">
                    <i class="fas fa-eye"></i>
                  </button>
                <?php endif; ?>

                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>);"
                  title="Удалить">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- Add/Edit Category Modal -->
<div class="modal-overlay" id="categoryModal" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTitle">Новая категория</h3>
      <button class="modal-close" onclick="closeCategoryModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <form id="categoryForm" method="POST">
        <input type="hidden" name="action" value="save_category">
        <input type="hidden" name="category_id" id="categoryId" value="">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="form-group">
          <label for="categoryName">Название категории *</label>
          <input type="text" id="categoryName" name="name" required>
        </div>

        <div class="form-group">
          <label for="categorySlug">Slug (URL) *</label>
          <input type="text" id="categorySlug" name="slug" required>
        </div>

        <div class="form-group">
          <label for="categoryDescription">Описание</label>
          <textarea id="categoryDescription" name="description" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label for="categoryOrder">Порядок сортировки</label>
          <input type="number" id="categoryOrder" name="sort_order" value="0" min="0">
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" id="categoryActive" name="is_active" value="1" checked>
            Активная категория
          </label>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeCategoryModal()">Отмена</button>
      <button class="btn btn-primary" onclick="saveCategory()">Сохранить</button>
    </div>
  </div>
</div>

<style>
  /* Categories Page Specific Styles */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
    gap: var(--spacing-md);
  }

  .header-content h1 {
    font-size: var(--font-size-xxl);
    color: var(--dark-color);
    margin-bottom: var(--spacing-sm);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
  }

  .header-content p {
    color: var(--gray-600);
    font-size: var(--font-size-base);
  }

  .header-actions {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
  }

  .categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-lg);
  }

  .category-card {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: all var(--transition-normal);
    display: flex;
    flex-direction: column;
  }

  .category-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
  }

  .category-header {
    padding: var(--spacing-lg);
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .category-icon {
    font-size: var(--font-size-xl);
  }

  .status-badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--border-radius-xl);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
  }

  .status-active {
    background: rgba(76, 175, 80, 0.9);
    color: white;
  }

  .status-inactive {
    background: rgba(158, 158, 158, 0.9);
    color: white;
  }

  .category-content {
    padding: var(--spacing-lg);
    flex: 1;
  }

  .category-name {
    margin: 0 0 var(--spacing-md) 0;
    font-size: var(--font-size-lg);
    color: var(--gray-800);
  }

  .category-description {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
    line-height: 1.5;
    margin-bottom: var(--spacing-md);
  }

  .category-meta {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    font-size: var(--font-size-xs);
    color: var(--gray-500);
  }

  .category-meta span {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
  }

  .category-actions {
    padding: var(--spacing-md) var(--spacing-lg);
    background: var(--gray-50);
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: var(--spacing-sm);
    justify-content: center;
  }

  .category-actions .btn {
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--border-radius-md);
  }

  .empty-state {
    text-align: center;
    padding: var(--spacing-xxl) var(--spacing-xl);
    color: var(--gray-600);
  }

  .empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-lg);
    color: var(--gray-400);
    font-size: 2rem;
  }

  .empty-state h3 {
    margin-bottom: var(--spacing-sm);
    color: var(--gray-700);
    font-size: var(--font-size-xl);
  }

  .empty-state p {
    margin-bottom: var(--spacing-lg);
    color: var(--gray-600);
  }

  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }

  .modal-content {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h3 {
    margin: 0;
    color: var(--gray-800);
  }

  .modal-close {
    background: none;
    border: none;
    font-size: var(--font-size-lg);
    color: var(--gray-500);
    cursor: pointer;
    padding: var(--spacing-xs);
  }

  .modal-body {
    padding: var(--spacing-lg);
  }

  .modal-footer {
    padding: var(--spacing-lg);
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: var(--spacing-sm);
    justify-content: flex-end;
  }

  .form-group {
    margin-bottom: var(--spacing-lg);
  }

  .form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    font-weight: 500;
    color: var(--gray-700);
  }

  .form-group input,
  .form-group textarea {
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius-md);
    font-size: var(--font-size-sm);
    transition: border-color var(--transition-fast);
  }

  .form-group input:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
  }

  .form-group input[type="checkbox"] {
    width: auto;
    margin-right: var(--spacing-xs);
  }

  @media (max-width: 768px) {
    .categories-grid {
      grid-template-columns: 1fr;
    }

    .page-header {
      flex-direction: column;
      align-items: stretch;
    }

    .category-actions {
      justify-content: stretch;
    }

    .category-actions .btn {
      flex: 1;
    }
  }
</style>

<script>
  // CSRF Token for JavaScript
  window.adminCSRFToken = '<?php echo generateCSRFToken(); ?>';

  // Скрываем модальное окно при загрузке страницы
  document.addEventListener('DOMContentLoaded', function () {
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) {
      confirmModal.style.display = 'none';
    }
  });

  function showAddCategoryModal() {
    document.getElementById('modalTitle').textContent = 'Новая категория';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryModal').style.display = 'flex';
  }

  function editCategory(categoryId) {
    // Здесь можно добавить AJAX запрос для получения данных категории
    // Пока что просто показываем модальное окно
    document.getElementById('modalTitle').textContent = 'Редактировать категорию';
    document.getElementById('categoryId').value = categoryId;
    document.getElementById('categoryModal').style.display = 'flex';
  }

  function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
  }

  function saveCategory() {
    document.getElementById('categoryForm').submit();
  }

  function toggleCategoryStatus(categoryId, status) {
    const actionText = status ? 'активировать' : 'деактивировать';

    // Используем встроенный confirm вместо showConfirmModal
    if (window.confirm(`Вы уверены, что хотите ${actionText} эту категорию?`)) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = `
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="category_id" value="${categoryId}">
            <input type="hidden" name="status" value="${status}">
            <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
        `;
      document.body.appendChild(form);
      form.submit();
    }
  }

  function deleteCategory(categoryId) {
    // Используем встроенный confirm вместо showConfirmModal
    if (window.confirm('Вы уверены, что хотите навсегда удалить эту категорию? Это действие нельзя отменить.')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="category_id" value="${categoryId}">
            <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
        `;
      document.body.appendChild(form);
      form.submit();
    }
  }

  // Auto-generate slug from name
  document.getElementById('categoryName').addEventListener('input', function () {
    const name = this.value;
    const slug = name.toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim('-');
    document.getElementById('categorySlug').value = slug;
  });
</script>

<?php
// Helper functions
function getProductCategories()
{
  $db = getAdminDB();
  if (!$db) {
    return [];
  }

  try {
    $stmt = $db->query("SELECT * FROM product_categories ORDER BY sort_order, name");
    return $stmt->fetchAll();
  } catch (PDOException $e) {
    error_log("Error fetching product categories: " . $e->getMessage());
    return [];
  }
}

function handleCategoryAction($data)
{
  $action = $data['action'] ?? '';
  $categoryId = $data['category_id'] ?? '';

  if (empty($categoryId) && $action !== 'save_category') {
    return ['success' => false, 'message' => 'Требуется указать ID категории.'];
  }

  $db = getAdminDB();

  if ($db) {
    try {
      switch ($action) {
        case 'save_category':
          $name = trim($data['name'] ?? '');
          $slug = trim($data['slug'] ?? '');
          $description = trim($data['description'] ?? '');
          $sortOrder = intval($data['sort_order'] ?? 0);
          $isActive = isset($data['is_active']) ? 1 : 0;

          if (empty($name) || empty($slug)) {
            return ['success' => false, 'message' => 'Название и slug обязательны.'];
          }

          // Check if slug already exists
          $checkStmt = $db->prepare("SELECT id FROM product_categories WHERE slug = ? AND id != ?");
          $checkStmt->execute([$slug, $categoryId]);
          if ($checkStmt->fetch()) {
            return ['success' => false, 'message' => 'Категория с таким slug уже существует.'];
          }

          if ($categoryId) {
            // Update existing category
            $stmt = $db->prepare("UPDATE product_categories SET name = ?, slug = ?, description = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$name, $slug, $description, $sortOrder, $isActive, $categoryId]);
            $message = 'Категория успешно обновлена.';
          } else {
            // Create new category
            $stmt = $db->prepare("INSERT INTO product_categories (name, slug, description, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $description, $sortOrder, $isActive]);
            $message = 'Категория успешно создана.';
          }

          return ['success' => true, 'message' => $message];

        case 'toggle_status':
          $status = intval($data['status'] ?? 0);
          $stmt = $db->prepare("UPDATE product_categories SET is_active = ?, updated_at = NOW() WHERE id = ?");
          $stmt->execute([$status, $categoryId]);
          $statusText = $status ? 'активирована' : 'деактивирована';
          return ['success' => true, 'message' => "Категория успешно {$statusText}."];

        case 'delete':
          // Check if category has products
          $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
          $checkStmt->execute([$categoryId]);
          $productCount = $checkStmt->fetch()['count'];

          if ($productCount > 0) {
            return ['success' => false, 'message' => "Нельзя удалить категорию, в которой есть товары ({$productCount} шт.). Сначала переместите или удалите товары."];
          }

          $stmt = $db->prepare("DELETE FROM product_categories WHERE id = ?");
          $stmt->execute([$categoryId]);
          return ['success' => true, 'message' => 'Категория успешно удалена.'];

        default:
          return ['success' => false, 'message' => 'Некорректное действие.'];
      }
    } catch (PDOException $e) {
      error_log("Category action failed: " . $e->getMessage());
      return ['success' => false, 'message' => "Ошибка базы данных: {$e->getMessage()}"];
    }
  } else {
    return ['success' => false, 'message' => 'Ошибка подключения к базе данных.'];
  }
}
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Функции для генерации и проверки CSRF токенов (определяем в самом начале)
if (!function_exists('generateCSRFToken')) {
function generateCSRFToken()
{
if (!isset($_SESSION['csrf_token'])) {
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
return $_SESSION['csrf_token'];
}
}

if (!function_exists('verifyCSRFToken')) {
function verifyCSRFToken($token)
{
return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
}

require_once '../includes/Models/Order.php';
require_once '../includes/Models/Article.php';
require_once '../includes/Models/Meditation.php';
require_once '../includes/Models/Review.php';
require_once '../includes/Models/Product.php';
require_once '../includes/Database.php';
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

// Check permissions (упрощенная проверка вместо requirePermission)
if (!isset($_SESSION['admin_user'])) {
header('Location: login.php');
exit;
}

$pageTitle = 'Управление категориями товаров';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
$_SESSION['error_message'] = 'Неверный токен безопасности.';
} else {
$result = handleCategoryAction($_POST);
if ($result['success']) {
$_SESSION['success_message'] = $result['message'];
} else {
$_SESSION['error_message'] = $result['message'];
}
}
header('Location: product-categories.php');
exit();
}

// Get categories
$categories = getProductCategories();

require_once __DIR__ . '/includes/header.php';
?>

<main class="admin-main">
  <div class="admin-container">
    <div class="page-header">
      <div class="header-content">
        <h1><i class="fas fa-tags"></i> Управление категориями товаров</h1>
        <p>Управление категориями для медитативных продуктов и цифрового контента</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-primary" onclick="showAddCategoryModal()">
          <i class="fas fa-plus"></i> Новая категория
        </button>
      </div>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $_SESSION['success_message'];
        unset($_SESSION['success_message']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
      <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $_SESSION['error_message'];
        unset($_SESSION['error_message']); ?>
      </div>
    <?php endif; ?>

    <!-- Categories List -->
    <div class="categories-list">
      <?php if (empty($categories)): ?>
        <div class="empty-state">
          <div class="empty-icon">
            <i class="fas fa-tags"></i>
          </div>
          <h3>Категории не найдены</h3>
          <p>Создайте первую категорию для организации товаров.</p>
          <button class="btn btn-primary" onclick="showAddCategoryModal()">
            <i class="fas fa-plus"></i> Создать первую категорию
          </button>
        </div>
      <?php else: ?>
        <div class="categories-grid">
          <?php foreach ($categories as $category): ?>
            <div class="category-card" data-category-id="<?php echo $category['id']; ?>">
              <div class="category-header">
                <div class="category-icon">
                  <i class="fas fa-tag"></i>
                </div>
                <div class="category-status">
                  <span class="status-badge <?php echo $category['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                    <?php echo $category['is_active'] ? 'Активна' : 'Неактивна'; ?>
                  </span>
                </div>
              </div>

              <div class="category-content">
                <h3 class="category-name">
                  <?php echo htmlspecialchars($category['name']); ?>
                </h3>

                <?php if (!empty($category['description'])): ?>
                  <p class="category-description">
                    <?php echo htmlspecialchars($category['description']); ?>
                  </p>
                <?php endif; ?>

                <div class="category-meta">
                  <span class="category-slug">
                    <i class="fas fa-link"></i>
                    <?php echo htmlspecialchars($category['slug']); ?>
                  </span>
                  <span class="category-order">
                    <i class="fas fa-sort"></i>
                    Порядок: <?php echo $category['sort_order']; ?>
                  </span>
                </div>
              </div>

              <div class="category-actions">
                <button class="btn btn-sm btn-secondary" onclick="editCategory(<?php echo $category['id']; ?>)"
                  title="Редактировать">
                  <i class="fas fa-edit"></i>
                </button>

                <?php if ($category['is_active']): ?>
                  <button class="btn btn-sm btn-warning" onclick="toggleCategoryStatus(<?php echo $category['id']; ?>, 0)"
                    title="Деактивировать">
                    <i class="fas fa-eye-slash"></i>
                  </button>
                <?php else: ?>
                  <button class="btn btn-sm btn-success" onclick="toggleCategoryStatus(<?php echo $category['id']; ?>, 1)"
                    title="Активировать">
                    <i class="fas fa-eye"></i>
                  </button>
                <?php endif; ?>

                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>);"
                  title="Удалить">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- Add/Edit Category Modal -->
<div class="modal-overlay" id="categoryModal" style="display: none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTitle">Новая категория</h3>
      <button class="modal-close" onclick="closeCategoryModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="modal-body">
      <form id="categoryForm" method="POST">
        <input type="hidden" name="action" value="save_category">
        <input type="hidden" name="category_id" id="categoryId" value="">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

        <div class="form-group">
          <label for="categoryName">Название категории *</label>
          <input type="text" id="categoryName" name="name" required>
        </div>

        <div class="form-group">
          <label for="categorySlug">Slug (URL) *</label>
          <input type="text" id="categorySlug" name="slug" required>
        </div>

        <div class="form-group">
          <label for="categoryDescription">Описание</label>
          <textarea id="categoryDescription" name="description" rows="3"></textarea>
        </div>

        <div class="form-group">
          <label for="categoryOrder">Порядок сортировки</label>
          <input type="number" id="categoryOrder" name="sort_order" value="0" min="0">
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" id="categoryActive" name="is_active" value="1" checked>
            Активная категория
          </label>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeCategoryModal()">Отмена</button>
      <button class="btn btn-primary" onclick="saveCategory()">Сохранить</button>
    </div>
  </div>
</div>

<style>
  /* Categories Page Specific Styles */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--spacing-xl);
    flex-wrap: wrap;
    gap: var(--spacing-md);
  }

  .header-content h1 {
    font-size: var(--font-size-xxl);
    color: var(--dark-color);
    margin-bottom: var(--spacing-sm);
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
  }

  .header-content p {
    color: var(--gray-600);
    font-size: var(--font-size-base);
  }

  .header-actions {
    display: flex;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
  }

  .categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--spacing-lg);
  }

  .category-card {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    transition: all var(--transition-normal);
    display: flex;
    flex-direction: column;
  }

  .category-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
  }

  .category-header {
    padding: var(--spacing-lg);
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .category-icon {
    font-size: var(--font-size-xl);
  }

  .status-badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--border-radius-xl);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
  }

  .status-active {
    background: rgba(76, 175, 80, 0.9);
    color: white;
  }

  .status-inactive {
    background: rgba(158, 158, 158, 0.9);
    color: white;
  }

  .category-content {
    padding: var(--spacing-lg);
    flex: 1;
  }

  .category-name {
    margin: 0 0 var(--spacing-md) 0;
    font-size: var(--font-size-lg);
    color: var(--gray-800);
  }

  .category-description {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
    line-height: 1.5;
    margin-bottom: var(--spacing-md);
  }

  .category-meta {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    font-size: var(--font-size-xs);
    color: var(--gray-500);
  }

  .category-meta span {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
  }

  .category-actions {
    padding: var(--spacing-md) var(--spacing-lg);
    background: var(--gray-50);
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: var(--spacing-sm);
    justify-content: center;
  }

  .category-actions .btn {
    min-width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--border-radius-md);
  }

  .empty-state {
    text-align: center;
    padding: var(--spacing-xxl) var(--spacing-xl);
    color: var(--gray-600);
  }

  .empty-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--spacing-lg);
    color: var(--gray-400);
    font-size: 2rem;
  }

  .empty-state h3 {
    margin-bottom: var(--spacing-sm);
    color: var(--gray-700);
    font-size: var(--font-size-xl);
  }

  .empty-state p {
    margin-bottom: var(--spacing-lg);
    color: var(--gray-600);
  }

  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }

  .modal-content {
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-lg);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
  }

  .modal-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--gray-200);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .modal-header h3 {
    margin: 0;
    color: var(--gray-800);
  }

  .modal-close {
    background: none;
    border: none;
    font-size: var(--font-size-lg);
    color: var(--gray-500);
    cursor: pointer;
    padding: var(--spacing-xs);
  }

  .modal-body {
    padding: var(--spacing-lg);
  }

  .modal-footer {
    padding: var(--spacing-lg);
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: var(--spacing-sm);
    justify-content: flex-end;
  }

  .form-group {
    margin-bottom: var(--spacing-lg);
  }

  .form-group label {
    display: block;
    margin-bottom: var(--spacing-xs);
    font-weight: 500;
    color: var(--gray-700);
  }

  .form-group input,
  .form-group textarea {
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius-md);
    font-size: var(--font-size-sm);
    transition: border-color var(--transition-fast);
  }

  .form-group input:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
  }

  .form-group input[type="checkbox"] {
    width: auto;
    margin-right: var(--spacing-xs);
  }

  @media (max-width: 768px) {
    .categories-grid {
      grid-template-columns: 1fr;
    }

    .page-header {
      flex-direction: column;
      align-items: stretch;
    }

    .category-actions {
      justify-content: stretch;
    }

    .category-actions .btn {
      flex: 1;
    }
  }
</style>

<script>
  // CSRF Token for JavaScript
  window.adminCSRFToken = '<?php echo generateCSRFToken(); ?>';

  // Скрываем модальное окно при загрузке страницы
  document.addEventListener('DOMContentLoaded', function () {
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) {
      confirmModal.style.display = 'none';
    }
  });

  function showAddCategoryModal() {
    document.getElementById('modalTitle').textContent = 'Новая категория';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryModal').style.display = 'flex';
  }

  function editCategory(categoryId) {
    // Здесь можно добавить AJAX запрос для получения данных категории
    // Пока что просто показываем модальное окно
    document.getElementById('modalTitle').textContent = 'Редактировать категорию';
    document.getElementById('categoryId').value = categoryId;
    document.getElementById('categoryModal').style.display = 'flex';
  }

  function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
  }

  function saveCategory() {
    document.getElementById('categoryForm').submit();
  }

  function toggleCategoryStatus(categoryId, status) {
    const actionText = status ? 'активировать' : 'деактивировать';

    // Используем встроенный confirm вместо showConfirmModal
    if (window.confirm(`Вы уверены, что хотите ${actionText} эту категорию?`)) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = `
            <input type="hidden" name="action" value="toggle_status">
            <input type="hidden" name="category_id" value="${categoryId}">
            <input type="hidden" name="status" value="${status}">
            <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
        `;
      document.body.appendChild(form);
      form.submit();
    }
  }

  function deleteCategory(categoryId) {
    // Используем встроенный confirm вместо showConfirmModal
    if (window.confirm('Вы уверены, что хотите навсегда удалить эту категорию? Это действие нельзя отменить.')) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="category_id" value="${categoryId}">
            <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
        `;
      document.body.appendChild(form);
      form.submit();
    }
  }

  // Auto-generate slug from name
  document.getElementById('categoryName').addEventListener('input', function () {
    const name = this.value;
    const slug = name.toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim('-');
    document.getElementById('categorySlug').value = slug;
  });
</script>

<?php
// Helper functions

?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>