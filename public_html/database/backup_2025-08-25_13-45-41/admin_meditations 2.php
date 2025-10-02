<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

requirePermission('products');
$pageTitle = 'Управление медитациями';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid request token.';
    } else {
        $result = handleMeditationAction($_POST);
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
    }
    header('Location: meditations.php');
    exit();
}

$page = intval($_GET['page'] ?? 1);
$status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';
$meditations = getMeditations($page, $status, $search);

require_once __DIR__ . '/includes/header.php';
?>

<div class="meditations-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-spa"></i> Управление медитациями</h1>
            <p>Управление медитациями и аудиоконтентом</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="openMeditationModal()">
                <i class="fas fa-plus"></i> Добавить медитацию
            </button>
            <button class="btn btn-secondary" onclick="exportMeditations()">
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
                    <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Все медитации</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Активные</option>
                    <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Неактивные</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="search">Поиск:</label>
                <input type="text" name="search" id="search" value="<?php echo sanitizeOutput($search); ?>" placeholder="Поиск по названию или описанию...">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Найти
                </button>
            </div>
        </form>
    </div>
    
    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-item">
            <span class="stat-number"><?php echo $meditations['stats']['total'] ?? 0; ?></span>
            <span class="stat-label">Всего медитаций</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $meditations['stats']['active'] ?? 0; ?></span>
            <span class="stat-label">Активные</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $meditations['stats']['total_duration'] ?? 0; ?></span>
            <span class="stat-label">Общая продолжительность (мин)</span>
        </div>
    </div>
    
    <!-- Meditations List -->
    <div class="meditations-list">
        <?php if (empty($meditations['items'])): ?>
            <div class="empty-state">
                <i class="fas fa-spa"></i>
                <h3>Медитаций не найдено</h3>
                <p>Нет медитаций, соответствующих текущим фильтрам.</p>
            </div>
        <?php else: ?>
            <div class="meditations-grid">
                <?php foreach ($meditations['items'] as $meditation): ?>
                    <div class="meditation-card">
                        <div class="meditation-image">
                            <?php if (!empty($meditation['image'])): ?>
                                <img src="<?php echo sanitizeOutput($meditation['image']); ?>" alt="<?php echo sanitizeOutput($meditation['title']); ?>">
                            <?php else: ?>
                                <div class="placeholder-image">
                                    <i class="fas fa-spa"></i>
                                </div>
                            <?php endif; ?>
                            <div class="meditation-duration">
                                <?php echo $meditation['duration'] ?? 0; ?> мин
                            </div>
                        </div>
                        
                        <div class="meditation-content">
                            <h3><?php echo sanitizeOutput($meditation['title']); ?></h3>
                            <p><?php echo sanitizeOutput($meditation['description']); ?></p>
                            
                            <div class="meditation-meta">
                                <span class="category"><?php echo sanitizeOutput($meditation['category'] ?? 'Общие'); ?></span>
                                <span class="status status-<?php echo $meditation['status'] ?? 'active'; ?>">
                                    <?php echo ($meditation['status'] ?? 'active') === 'active' ? 'Активна' : 'Неактивна'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="meditation-actions">
                            <button class="btn btn-sm btn-info" onclick="editMeditation('<?php echo $meditation['id']; ?>')">
                                <i class="fas fa-edit"></i> Редактировать
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteMeditation('<?php echo $meditation['id']; ?>')">
                                <i class="fas fa-trash"></i> Удалить
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Meditation Modal -->
<div class="modal-overlay" id="meditationModal">
    <div class="modal-content meditation-modal">
        <div class="modal-header">
            <h3 id="meditationModalTitle">Добавить медитацию</h3>
            <button class="modal-close" onclick="closeMeditationModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="meditationForm">
                <input type="hidden" name="meditation_id" id="meditationId">
                <input type="hidden" name="action" id="meditationAction" value="create">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="meditationTitle">Название *</label>
                    <input type="text" id="meditationTitle" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="meditationDescription">Описание *</label>
                    <textarea id="meditationDescription" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="meditationDuration">Продолжительность (мин) *</label>
                        <input type="number" id="meditationDuration" name="duration" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="meditationCategory">Категория</label>
                        <select id="meditationCategory" name="category">
                            <option value="relaxation">Релаксация</option>
                            <option value="sleep">Сон</option>
                            <option value="focus">Концентрация</option>
                            <option value="anxiety">Тревожность</option>
                            <option value="mindfulness">Осознанность</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="meditationAudio">Аудиофайл</label>
                    <input type="file" id="meditationAudio" name="audio" accept="audio/*">
                </div>
                
                <div class="form-group">
                    <label for="meditationImage">Изображение</label>
                    <input type="file" id="meditationImage" name="image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label for="meditationStatus">Статус</label>
                    <select id="meditationStatus" name="status">
                        <option value="active">Активна</option>
                        <option value="inactive">Неактивна</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeMeditationModal()">Отмена</button>
            <button type="button" class="btn btn-primary" onclick="saveMeditation()">Сохранить</button>
        </div>
    </div>
</div>

<script>
function openMeditationModal(meditationData = null) {
    const modal = document.getElementById('meditationModal');
    const form = document.getElementById('meditationForm');
    const title = document.getElementById('meditationModalTitle');
    const action = document.getElementById('meditationAction');
    
    form.reset();
    
    if (meditationData) {
        title.textContent = 'Редактировать медитацию';
        action.value = 'update';
        
        // Populate form
        document.getElementById('meditationId').value = meditationData.id;
        document.getElementById('meditationTitle').value = meditationData.title || '';
        document.getElementById('meditationDescription').value = meditationData.description || '';
        document.getElementById('meditationDuration').value = meditationData.duration || '';
        document.getElementById('meditationCategory').value = meditationData.category || 'relaxation';
        document.getElementById('meditationStatus').value = meditationData.status || 'active';
    } else {
        title.textContent = 'Добавить медитацию';
        action.value = 'create';
    }
    
    modal.style.display = 'flex';
}

function closeMeditationModal() {
    document.getElementById('meditationModal').style.display = 'none';
}

function editMeditation(meditationId) {
    fetch(`api/meditation-details.php?id=${meditationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openMeditationModal(data.meditation);
            } else {
                showToast('Ошибка загрузки данных медитации', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Ошибка сети', 'error');
        });
}

function saveMeditation() {
    const form = document.getElementById('meditationForm');
    const formData = new FormData(form);
    
    const saveBtn = document.querySelector('#meditationModal .btn-primary');
    const originalText = saveBtn.textContent;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Сохранение...';
    
    fetch('api/save-meditation.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Медитация сохранена успешно', 'success');
            closeMeditationModal();
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(data.message || 'Ошибка сохранения', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Ошибка сети', 'error');
    })
    .finally(() => {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    });
}

function deleteMeditation(meditationId) {
    showConfirmModal(
        'Удаление медитации',
        'Вы уверены, что хотите удалить эту медитацию? Это действие нельзя отменить.',
        function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="meditation_id" value="${meditationId}">
                <input type="hidden" name="csrf_token" value="${window.adminCSRFToken}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    );
}

function exportMeditations() {
    window.open('api/export-meditations.php', '_blank');
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';

function getMeditations($page = 1, $status = 'all', $search = '') {
    $itemsPerPage = ADMIN_ITEMS_PER_PAGE;
    $offset = ($page - 1) * $itemsPerPage;
    
    $meditations = [];
    $stats = ['total' => 0, 'active' => 0, 'total_duration' => 0];
    
    // Try JSON file fallback
    $meditationsFile = __DIR__ . '/../data/meditations.json';
    if (file_exists($meditationsFile)) {
        $meditationsData = json_decode(file_get_contents($meditationsFile), true);
        $allMeditations = $meditationsData['meditations'] ?? [];
        
        // Filter meditations
        $filteredMeditations = array_filter($allMeditations, function($meditation) use ($status, $search) {
            $statusMatch = $status === 'all' || ($meditation['status'] ?? 'active') === $status;
            $searchMatch = empty($search) || 
                stripos($meditation['title'] ?? '', $search) !== false ||
                stripos($meditation['description'] ?? '', $search) !== false;
            return $statusMatch && $searchMatch;
        });
        
        $totalItems = count($filteredMeditations);
        $meditations = array_slice($filteredMeditations, $offset, $itemsPerPage);
        
        // Calculate stats
        $stats['total'] = count($allMeditations);
        foreach ($allMeditations as $meditation) {
            if (($meditation['status'] ?? 'active') === 'active') {
                $stats['active']++;
            }
            $stats['total_duration'] += intval($meditation['duration'] ?? 0);
        }
    }
    
    return [
        'items' => $meditations,
        'stats' => $stats,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($totalItems / $itemsPerPage),
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage
        ]
    ];
}

function handleMeditationAction($data) {
    $action = $data['action'] ?? '';
    $meditationId = $data['meditation_id'] ?? '';
    
    if ($action === 'delete' && empty($meditationId)) {
        return ['success' => false, 'message' => 'Meditation ID is required.'];
    }
    
    $meditationsFile = __DIR__ . '/../data/meditations.json';
    
    if (!file_exists($meditationsFile)) {
        $meditationsData = ['meditations' => []];
    } else {
        $meditationsData = json_decode(file_get_contents($meditationsFile), true);
        if (!$meditationsData) $meditationsData = ['meditations' => []];
    }
    
    $meditations = $meditationsData['meditations'];
    
    if ($action === 'delete') {
        $meditationIndex = -1;
        foreach ($meditations as $index => $meditation) {
            if ($meditation['id'] == $meditationId) {
                $meditationIndex = $index;
                break;
            }
        }
        
        if ($meditationIndex === -1) {
            return ['success' => false, 'message' => 'Meditation not found.'];
        }
        
        array_splice($meditations, $meditationIndex, 1);
        logAdminActivity('delete', "Meditation #{$meditationId} deleted");
        $message = 'Meditation deleted successfully.';
        
        // Save back to file
        $meditationsData['meditations'] = array_values($meditations);
        if (file_put_contents($meditationsFile, json_encode($meditationsData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
            return ['success' => true, 'message' => $message];
        } else {
            return ['success' => false, 'message' => 'Failed to save changes.'];
        }
    }
    
    return ['success' => false, 'message' => 'Invalid action.'];
}
?>