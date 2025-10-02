<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

// Check permissions
requirePermission('articles');

$pageTitle = 'Управление статьями';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Неверный токен безопасности.';
    } else {
        $result = handleArticleAction($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    
    header('Location: articles.php');
    exit();
}

// Get articles with pagination
$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$articles = getArticles($page, $status, $category, $search);
$categories = getArticleCategories();

require_once __DIR__ . '/includes/header.php';
?>

<div class="articles-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-newspaper"></i> Управление статьями</h1>
            <p>Управление статьями по психологии и блогом</p>
        </div>
        <div class="header-actions">
            <a href="article-edit.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Новая статья
            </a>
            <button class="btn btn-secondary" onclick="exportArticles()">
                <i class="fas fa-download"></i> Экспорт
            </button>
        </div>
    </div>
    
    <!-- Filters and Search -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="status">Статус:</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все статьи</option>
                    <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Опубликованные</option>
                    <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Черновики</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="category">Категория:</label>
                <select name="category" id="category" onchange="this.form.submit()">
                    <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>Все категории</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo sanitizeOutput($cat['slug']); ?>" <?php echo $category === $cat['slug'] ? 'selected' : ''; ?>>
                            <?php echo sanitizeOutput($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" value="<?php echo sanitizeOutput($search); ?>" placeholder="Поиск по заголовку или содержанию...">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Найти
                </button>
            </div>
        </form>
    </div>
    
    <!-- Articles Stats -->
    <div class="stats-row">
        <div class="stat-item">
            <span class="stat-number"><?php echo $articles['stats']['total'] ?? 0; ?></span>
            <span class="stat-label">Всего статей</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $articles['stats']['published'] ?? 0; ?></span>
            <span class="stat-label">Опубликованные</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $articles['stats']['draft'] ?? 0; ?></span>
            <span class="stat-label">Черновики</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo count($categories); ?></span>
            <span class="stat-label">Категории</span>
        </div>
    </div>
    
    <!-- Articles List -->
    <div class="articles-list">
        <?php if (empty($articles['items'])): ?>
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3>Статей не найдено</h3>
                <p>Нет статей, соответствующих текущим фильтрам.</p>
                <a href="article-edit.php" class="btn btn-primary">Создать первую статью</a>
            </div>
        <?php else: ?>
            <div class="articles-grid">
                <?php foreach ($articles['items'] as $article): ?>
                    <div class="article-card" data-article-id="<?php echo $article['id'] ?? $article['slug']; ?>">
                        <div class="article-header">
                            <?php if (!empty($article['featured_image'])): ?>
                                <div class="article-image">
                                    <img src="<?php echo sanitizeOutput($article['featured_image']); ?>" alt="<?php echo sanitizeOutput($article['title']); ?>">
                                    <div class="article-status status-<?php echo $article['status'] ?? 'published'; ?>">
                                        <?php echo ucfirst($article['status'] ?? 'published'); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="article-image placeholder">
                                    <i class="fas fa-newspaper"></i>
                                    <div class="article-status status-<?php echo $article['status'] ?? 'published'; ?>">
                                        <?php echo ucfirst($article['status'] ?? 'published'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="article-content">
                            <h3 class="article-title">
                                <a href="article-edit.php?id=<?php echo urlencode($article['slug']); ?>">
                                    <?php echo sanitizeOutput($article['title']); ?>
                                </a>
                            </h3>
                            
                            <?php if (!empty($article['excerpt'])): ?>
                                <p class="article-excerpt"><?php echo sanitizeOutput($article['excerpt']); ?></p>
                            <?php endif; ?>
                            
                            <div class="article-meta">
                                <span class="article-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d.m.Y', strtotime($article['created_at'] ?? $article['date'] ?? 'now')); ?>
                                </span>
                                
                                <?php if (!empty($article['category'])): ?>
                                    <span class="article-category">
                                        <i class="fas fa-tag"></i>
                                        <?php echo sanitizeOutput($article['category']); ?>
                                    </span>
                                <?php endif; ?>
                                
                                <span class="article-author">
                                    <i class="fas fa-user"></i>
                                    <?php echo sanitizeOutput($article['author'] ?? 'Denis Cherkas'); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="article-actions">
                            <a href="article-edit.php?id=<?php echo urlencode($article['slug']); ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-edit"></i> Редактировать
                            </a>
                            
                            <a href="../article.php?slug=<?php echo urlencode($article['slug']); ?>" class="btn btn-sm btn-info" target="_blank">
                                <i class="fas fa-eye"></i> Просмотр
                            </a>
                            
                            <?php if (($article['status'] ?? 'published') === 'draft'): ?>
                                <button class="btn btn-sm btn-success" onclick="updateArticleStatus('<?php echo $article['slug']; ?>', 'published')">
                                    <i class="fas fa-check"></i> Опубликовать
                                </button>
                            <?php else: ?>
                                <button class="btn btn-sm btn-warning" onclick="updateArticleStatus('<?php echo $article['slug']; ?>', 'draft')">
                                    <i class="fas fa-archive"></i> Снять с публикации
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-sm btn-danger" onclick="deleteArticle('<?php echo $article['slug']; ?>');">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($articles['pagination']['total_pages'] > 1): ?>
        <div class="pagination-wrapper">
            <nav class="pagination">
                <?php if ($articles['pagination']['current_page'] > 1): ?>
                    <a href="?page=<?php echo $articles['pagination']['current_page'] - 1; ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Предыдущая
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $articles['pagination']['total_pages']; $i++): ?>
                    <?php if ($i == $articles['pagination']['current_page']): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>" class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($articles['pagination']['current_page'] < $articles['pagination']['total_pages']): ?>
                    <a href="?page=<?php echo $articles['pagination']['current_page'] + 1; ?>&status=<?php echo $status; ?>&category=<?php echo $category; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                        Следующая <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
function updateArticleStatus(articleId, status) {
    const actionText = status === 'published' ? 'publish' : 'unpublish';
    
    showConfirmModal(
        `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Article`,
        `Are you sure you want to ${actionText} this article?`,
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="article_id" value="${articleId}">
                <input type="hidden" name="status" value="${status}">
                <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    );
}

function deleteArticle(articleId) {
    showConfirmModal(
        'Delete Article',
        'Are you sure you want to permanently delete this article? This action cannot be undone.',
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="article_id" value="${articleId}">
                <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    );
}

function exportArticles() {
    const params = new URLSearchParams({
        status: '<?php echo $status; ?>',
        category: '<?php echo $category; ?>',
        search: '<?php echo $search; ?>'
    });
    window.open(`api/export-articles.php?${params.toString()}`, '_blank');
}

// Add styles for articles page
const articlesStyles = `
    <style>
        .articles-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: 2rem;
        }
        
        .filters-form {
            display: flex;
            gap: 2rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            min-width: 150px;
        }
        
        .filter-group label {
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--border-radius-md);
            font-size: 0.875rem;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-item {
            background: white;
            padding: 1rem;
            border-radius: var(--border-radius-md);
            box-shadow: var(--shadow-sm);
            text-align: center;
        }
        
        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: var(--gray-600);
        }
        
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        
        .article-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: all var(--transition-normal);
            display: flex;
            flex-direction: column;
        }
        
        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }
        
        .article-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .article-image.placeholder {
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-400);
            font-size: 3rem;
        }
        
        .article-status {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-published {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .article-content {
            padding: 1.5rem;
            flex: 1;
        }
        
        .article-title {
            margin: 0 0 1rem 0;
            font-size: 1.125rem;
            line-height: 1.4;
        }
        
        .article-title a {
            color: var(--gray-800);
            text-decoration: none;
            transition: color var(--transition-fast);
        }
        
        .article-title a:hover {
            color: var(--primary-color);
        }
        
        .article-excerpt {
            color: var(--gray-600);
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .article-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.75rem;
            color: var(--gray-500);
        }
        
        .article-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .article-actions {
            padding: 1rem 1.5rem;
            background: var(--gray-50);
            border-top: 1px solid var(--gray-200);
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-600);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--gray-400);
        }
        
        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }
        
        .empty-state p {
            margin-bottom: 1.5rem;
        }
        
        .pagination-wrapper {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .page-link {
            padding: 0.5rem 1rem;
            background: white;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: var(--border-radius-md);
            border: 1px solid var(--gray-300);
            transition: all var(--transition-fast);
        }
        
        .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .page-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .articles-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filters-form {
                flex-direction: column;
                gap: 1rem;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .article-actions {
                justify-content: stretch;
            }
            
            .article-actions .btn {
                flex: 1;
            }
        }
    </style>
`;

document.head.insertAdjacentHTML('beforeend', articlesStyles);
</script>

<?php
require_once __DIR__ . '/includes/footer.php';

// Helper functions
function getArticles($page = 1, $status = 'all', $category = 'all', $search = '') {
    $db = getAdminDB();
    if (!$db) {
        throw new Exception('Database connection failed. Cannot retrieve articles.');
    }
    
    $itemsPerPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $itemsPerPage;
    
    // Build query conditions
    $conditions = [];
    $params = [];
    
    if ($status !== 'all') {
        if ($status === 'published') {
            $conditions[] = "a.is_published = 1";
        } else {
            $conditions[] = "a.is_published = 0";
        }
    }
    
    if ($category !== 'all') {
        $conditions[] = "ac.slug = ?";
        $params[] = $category;
    }
    
    if (!empty($search)) {
        $conditions[] = "(a.title LIKE ? OR a.content LIKE ? OR a.excerpt LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
    
    // Get total count for pagination
    $countSql = "SELECT COUNT(*) as total 
                 FROM articles a 
                 LEFT JOIN article_categories ac ON a.category_id = ac.id 
                 " . $whereClause;
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $totalItems = $stmt->fetch()['total'];
    
    // Get articles with proper sorting (newest first)
    $sql = "SELECT a.*, ac.name as category_name, ac.slug as category_slug,
                   a.slug as id,  -- Use slug as id for consistent edit links
                   CASE WHEN a.is_published = 1 THEN 'published' ELSE 'draft' END as status
            FROM articles a 
            LEFT JOIN article_categories ac ON a.category_id = ac.id 
            " . $whereClause . " 
            ORDER BY a.created_at DESC 
            LIMIT ? OFFSET ?";
    $params[] = $itemsPerPage;
    $params[] = $offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $articles = $stmt->fetchAll();
    
    // Get statistics
    $stats = ['total' => 0, 'published' => 0, 'draft' => 0];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM articles");
    $stats['total'] = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM articles WHERE is_published = 1");
    $stats['published'] = $stmt->fetch()['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM articles WHERE is_published = 0");
    $stats['draft'] = $stmt->fetch()['count'];
    
    return [
        'items' => $articles,
        'stats' => $stats,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $itemsPerPage),
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]
    ];
}

function getArticleCategories() {
    $db = getAdminDB();
    
    if ($db) {
        $stmt = $db->query("SELECT * FROM article_categories WHERE is_active = 1 ORDER BY sort_order, name");
        return $stmt->fetchAll();
    } else {
        // Database connection failed - return default categories
        return [
            ['id' => 1, 'slug' => 'psihologiya', 'name' => 'Психология'],
            ['id' => 2, 'slug' => 'terapiya', 'name' => 'Терапия'],
            ['id' => 3, 'slug' => 'otnosheniya', 'name' => 'Отношения'],
            ['id' => 4, 'slug' => 'samorazvitie', 'name' => 'Саморазвитие'],
            ['id' => 5, 'slug' => 'psihicheskoe-zdorove', 'name' => 'Психическое здоровье']
        ];
    }
}

function handleArticleAction($data) {
    $action = $data['action'] ?? '';
    $articleId = $data['article_id'] ?? '';
    
    if (empty($articleId)) {
        return ['success' => false, 'message' => 'Требуется указать ID статьи.'];
    }
    
    $db = getAdminDB();
    
    if ($db) {
        try {
            // Always use slug for article identification to maintain consistency
            // This avoids issues with numeric IDs vs slugs
            $idField = 'slug';
            
            switch ($action) {
                case 'update_status':
                    $status = $data['status'] ?? '';
                    $isPublished = $status === 'published' ? 1 : 0;
                    
                    // First check if the article exists
                    $stmt = $db->prepare("SELECT id, slug, title FROM articles WHERE {$idField} = ?");
                    $stmt->execute([$articleId]);
                    $article = $stmt->fetch();
                    
                    if (!$article) {
                        return ['success' => false, 'message' => "Статья не найдена: {$articleId}"];
                    }
                    
                    // Update the article
                    $stmt = $db->prepare("UPDATE articles SET is_published = ?, updated_at = NOW() WHERE {$idField} = ?");
                    $result = $stmt->execute([$isPublished, $articleId]);
                    
                    if ($result) {
                        logAdminActivity('update', "Article '{$article['title']}' status changed to {$status}");
                        $statusText = $status === 'published' ? 'опубликована' : 'снята с публикации';
                        return ['success' => true, 'message' => "Статья успешно {$statusText}."];
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        error_log("SQL Error: " . implode(' ', $errorInfo));
                        return ['success' => false, 'message' => "Ошибка обновления статуса статьи. SQL Error: {$errorInfo[2]}"];
                    }
                    
                case 'delete':
                    $stmt = $db->prepare("DELETE FROM articles WHERE {$idField} = ?");
                    $result = $stmt->execute([$articleId]);
                    
                    if ($result) {
                        logAdminActivity('delete', "Article #{$articleId} deleted");
                        return ['success' => true, 'message' => 'Статья успешно удалена.'];
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        error_log("SQL Error: " . implode(' ', $errorInfo));
                        return ['success' => false, 'message' => "Ошибка удаления статьи. SQL Error: {$errorInfo[2]}"];
                    }
                    
                default:
                    return ['success' => false, 'message' => 'Некорректное действие.'];
            }
        } catch (PDOException $e) {
            error_log("Article action failed: " . $e->getMessage());
            error_log("PDO Error Code: " . $e->getCode());
            error_log("SQL State: " . ($e->errorInfo[0] ?? 'Unknown'));
            return ['success' => false, 'message' => "Ошибка базы данных: {$e->getMessage()}"];
        }
    } else {
        return ['success' => false, 'message' => 'Ошибка подключения к базе данных.'];
    }
}


?>