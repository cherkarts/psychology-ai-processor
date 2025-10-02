<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

requirePermission('users');
$pageTitle = 'Управление пользователями';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid request token.';
    } else {
        $result = handleUserAction($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    header('Location: users.php');
    exit();
}

$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$users = getUsers($page, $status, $search);

require_once __DIR__ . '/includes/header.php';
?>

<div class="users-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-users"></i> Управление пользователями</h1>
            <p>Управление пользователями сайта и подписчиками</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="exportUsers()">
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
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все пользователи</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Активные</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Неактивные</option>
                    <option value="premium" <?php echo $status === 'premium' ? 'selected' : ''; ?>>Премиум</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" value="<?php echo sanitizeOutput($search); ?>" placeholder="Поиск по имени или email...">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Найти
                </button>
            </div>
        </form>
    </div>
    
    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-item">
            <span class="stat-number"><?php echo $users['stats']['total'] ?? 0; ?></span>
            <span class="stat-label">Всего пользователей</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $users['stats']['active'] ?? 0; ?></span>
            <span class="stat-label">Активных</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $users['stats']['premium'] ?? 0; ?></span>
            <span class="stat-label">Премиум</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $users['stats']['new_this_month'] ?? 0; ?></span>
            <span class="stat-label">Новых за месяц</span>
        </div>
    </div>
    
    <!-- Users List -->
    <div class="users-list">
        <?php if (empty($users['items'])): ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>Пользователи не найдены</h3>
                <p>Нет пользователей, соответствующих текущим фильтрам.</p>
            </div>
        <?php else: ?>
            <div class="users-table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Пользователь</th>
                            <th>Контакты</th>
                            <th>Подписка</th>
                            <th>Регистрация</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users['items'] as $user): ?>
                            <tr class="user-row">
                                <td class="user-info">
                                    <div class="user-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="user-details">
                                        <div class="user-name"><?php echo sanitizeOutput($user['name']); ?></div>
                                        <div class="user-id">ID: <?php echo $user['id']; ?></div>
                                    </div>
                                </td>
                                <td class="user-contact">
                                    <div class="contact-email"><?php echo sanitizeOutput($user['email']); ?></div>
                                    <?php if (!empty($user['phone'])): ?>
                                        <div class="contact-phone"><?php echo sanitizeOutput($user['phone']); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($user['telegram_username'])): ?>
                                        <div class="contact-telegram">@<?php echo sanitizeOutput($user['telegram_username']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="user-subscription">
                                    <span class="subscription-badge subscription-<?php echo $user['subscription_status'] ?? 'free'; ?>">
                                        <?php echo ucfirst($user['subscription_status'] ?? 'free'); ?>
                                    </span>
                                    <?php if (!empty($user['subscription_expires_at'])): ?>
                                        <div class="subscription-expires">
                                            Истекает: <?php echo date('d.m.Y', strtotime($user['subscription_expires_at'])); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="user-joined">
                                    <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="user-status">
                                    <span class="status-badge status-<?php echo ($user['is_active'] ?? true) ? 'active' : 'inactive'; ?>">
                                        <?php echo ($user['is_active'] ?? true) ? 'Активный' : 'Неактивный'; ?>
                                    </span>
                                </td>
                                <td class="user-actions">
                                    <button class="btn btn-sm btn-info" onclick="viewUserDetails('<?php echo $user['id']; ?>')">
                                        <i class="fas fa-eye"></i> Просмотр
                                    </button>
                                    
                                    <?php if ($user['is_active'] ?? true): ?>
                                        <button class="btn btn-sm btn-warning" onclick="updateUserStatus('<?php echo $user['id']; ?>', false)">
                                            <i class="fas fa-ban"></i> Отключить
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success" onclick="updateUserStatus('<?php echo $user['id']; ?>', true)">
                                            <i class="fas fa-check"></i> Активировать
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
    <?php if ($users['pagination']['total_pages'] > 1): ?>
        <div class="pagination-wrapper">
            <nav class="pagination">
                <?php if ($users['pagination']['current_page'] > 1): ?>
                    <a href="?page=<?php echo $users['pagination']['current_page'] - 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                        <i class="fas fa-chevron-left"></i> Предыдущая
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $users['pagination']['total_pages']; $i++): ?>
                    <?php if ($i == $users['pagination']['current_page']): ?>
                        <span class="page-link active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($users['pagination']['current_page'] < $users['pagination']['total_pages']): ?>
                    <a href="?page=<?php echo $users['pagination']['current_page'] + 1; ?>&status=<?php echo $status; ?>&search=<?php echo urlencode($search); ?>" class="page-link">
                        Следующая <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<script>
function updateUserStatus(userId, isActive) {
    const actionText = isActive ? 'activate' : 'deactivate';
    
    showConfirmModal(
        `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} User`,
        `Are you sure you want to ${actionText} this user?`,
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="is_active" value="${isActive ? 1 : 0}">
                <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    );
}

function viewUserDetails(userId) {
    // Placeholder - would show user details modal
    showToast(`Viewing details for user ${userId}`, 'info');
}

function exportUsers() {
    const params = new URLSearchParams({
        status: '<?php echo $status; ?>',
        search: '<?php echo $search; ?>'
    });
    window.open(`api/export-users.php?${params.toString()}`, '_blank');
}

// Styles
const usersStyles = `
    <style>
        .users-container { max-width: 1400px; margin: 0 auto; }
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
        .users-table-wrapper { background: white; border-radius: var(--border-radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
        .users-table { width: 100%; border-collapse: collapse; }
        .users-table th { background: var(--gray-50); padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700); border-bottom: 1px solid var(--gray-200); }
        .users-table td { padding: 1rem; border-bottom: 1px solid var(--gray-200); }
        .user-row:hover { background: var(--gray-50); }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .user-avatar { font-size: 2rem; color: var(--gray-400); }
        .user-name { font-weight: 500; color: var(--gray-800); }
        .user-id { font-size: 0.75rem; color: var(--gray-500); }
        .user-contact { display: flex; flex-direction: column; gap: 0.25rem; }
        .contact-email { font-weight: 500; color: var(--gray-800); }
        .contact-phone, .contact-telegram { font-size: 0.875rem; color: var(--gray-600); }
        .subscription-badge { padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; }
        .subscription-free { background: #f8f9fa; color: var(--gray-600); }
        .subscription-premium { background: #fff3cd; color: #856404; }
        .subscription-cancelled { background: #f8d7da; color: #721c24; }
        .subscription-expires { font-size: 0.75rem; color: var(--gray-500); margin-top: 0.25rem; }
        .status-badge { padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 500; text-transform: uppercase; }
        .status-active { background: #d4edda; color: #155724; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        .user-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--gray-600); background: white; border-radius: var(--border-radius-lg); }
        .empty-state i { font-size: 4rem; margin-bottom: 1rem; color: var(--gray-400); }
        .pagination-wrapper { margin-top: 2rem; display: flex; justify-content: center; }
        .pagination { display: flex; gap: 0.5rem; align-items: center; }
        .page-link { padding: 0.5rem 1rem; background: white; color: var(--gray-700); text-decoration: none; border-radius: var(--border-radius-md); border: 1px solid var(--gray-300); transition: all var(--transition-fast); }
        .page-link:hover { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        .page-link.active { background: var(--primary-color); color: white; border-color: var(--primary-color); }
        @media (max-width: 768px) { .users-table-wrapper { overflow-x: auto; } .page-header { flex-direction: column; align-items: stretch; } .filters-form { flex-direction: column; gap: 1rem; } .stats-row { grid-template-columns: repeat(2, 1fr); } }
    </style>
`;
document.head.insertAdjacentHTML('beforeend', usersStyles);
</script>

<?php
require_once __DIR__ . '/includes/footer.php';

function getUsers($page = 1, $status = 'all', $search = '') {
    $db = getAdminDB();
    $itemsPerPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $itemsPerPage;
    
    $users = [];
    $stats = ['total' => 0, 'active' => 0, 'premium' => 0, 'new_this_month' => 0];
    
    if ($db) {
        $conditions = [];
        $params = [];
        
        if ($status !== 'all') {
            if ($status === 'active') {
                $conditions[] = "is_active = 1";
            } elseif ($status === 'inactive') {
                $conditions[] = "is_active = 0";
            } elseif ($status === 'premium') {
                $conditions[] = "subscription_status = 'premium'";
            }
        }
        
        if (!empty($search)) {
            $conditions[] = "(name LIKE ? OR email LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $whereClause = empty($conditions) ? '' : 'WHERE ' . implode(' AND ', $conditions);
        
        $countSql = "SELECT COUNT(*) as total FROM users " . $whereClause;
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $totalItems = $stmt->fetch()['total'];
        
        $sql = "SELECT * FROM users " . $whereClause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $itemsPerPage;
        $params[] = $offset;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        // Get stats
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $stats['total'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
        $stats['active'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE subscription_status = 'premium'");
        $stats['premium'] = $stmt->fetch()['count'];
        $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
        $stats['new_this_month'] = $stmt->fetch()['count'];
        
    } else {
        // Mock data for demonstration since users table might not exist
        $mockUsers = [
            [
                'id' => 1,
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'phone' => '+7 999 123 4567',
                'telegram_username' => 'demouser',
                'subscription_status' => 'free',
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days'))
            ],
            [
                'id' => 2,
                'name' => 'Premium User',
                'email' => 'premium@example.com',
                'subscription_status' => 'premium',
                'subscription_expires_at' => date('Y-m-d', strtotime('+30 days')),
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s', strtotime('-15 days'))
            ]
        ];
        
        // Filter mock users
        $filteredUsers = array_filter($mockUsers, function($user) use ($status, $search) {
            $statusMatch = $status === 'all' || 
                           ($status === 'active' && $user['is_active']) ||
                           ($status === 'premium' && $user['subscription_status'] === 'premium');
            $searchMatch = empty($search) || 
                           stripos($user['name'], $search) !== false ||
                           stripos($user['email'], $search) !== false;
            return $statusMatch && $searchMatch;
        });
        
        $totalItems = count($filteredUsers);
        $users = array_slice($filteredUsers, $offset, $itemsPerPage);
        
        $stats['total'] = count($mockUsers);
        $stats['active'] = count(array_filter($mockUsers, fn($u) => $u['is_active']));
        $stats['premium'] = count(array_filter($mockUsers, fn($u) => $u['subscription_status'] === 'premium'));
        $stats['new_this_month'] = 1;
    }
    
    return [
        'items' => $users,
        'stats' => $stats,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $itemsPerPage),
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]
    ];
}

function handleUserAction($data) {
    $action = $data['action'] ?? '';
    $userId = $data['user_id'] ?? '';
    
    if (empty($userId)) {
        return ['success' => false, 'message' => 'User ID is required.'];
    }
    
    $db = getAdminDB();
    
    if ($db) {
        try {
            if ($action === 'update_status') {
                $isActive = intval($data['is_active'] ?? 1);
                
                $stmt = $db->prepare("UPDATE users SET is_active = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$isActive, $userId]);
                
                $status = $isActive ? 'activated' : 'deactivated';
                logAdminActivity('update', "User #{$userId} {$status}");
                return ['success' => true, 'message' => "User {$status} successfully."];
            }
        } catch (PDOException $e) {
            logAdminError("User action failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred.'];
        }
    }
    
    return ['success' => false, 'message' => 'Action completed (demo mode).'];
}
?>