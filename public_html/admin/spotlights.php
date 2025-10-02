<?php
// Lightweight ping to debug routing without side effects
if (isset($_GET['ping'])) {
  header('Content-Type: text/plain; charset=utf-8');
  echo 'OK';
  exit;
}

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Models/Spotlight.php';

// Ensure only logged-in admins can access
requireLogin();
// hasPermission('promos') not required for admin; page is safe

require_once __DIR__ . '/includes/header.php';

$db = Database::getInstance();
$spotlightModel = new Spotlight();

$ensureTable = function () use ($db) {
  $sql = "CREATE TABLE IF NOT EXISTS `spotlights` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `title` VARCHAR(255) NOT NULL,
      `body` TEXT NULL,
      `cta_label` VARCHAR(120) NULL,
      `cta_url` VARCHAR(512) NULL,
      `media_type` ENUM('none','image','video') NOT NULL DEFAULT 'none',
      `media_url` VARCHAR(512) NULL,
      `bg_style` VARCHAR(120) NULL,
      `contexts` VARCHAR(255) NOT NULL DEFAULT 'all',
      `group_key` VARCHAR(120) NULL,
      `sort_order` INT NOT NULL DEFAULT 0,
      `is_active` TINYINT(1) NOT NULL DEFAULT 1,
      `starts_at` DATETIME NULL,
      `ends_at` DATETIME NULL,
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_spotlights_active` (`is_active`,`starts_at`,`ends_at`),
      KEY `idx_spotlights_contexts` (`contexts`),
      KEY `idx_spotlights_group` (`group_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
  try {
    $db->execute($sql);
  } catch (Throwable $e) { /* ignore */
  }
};

$action = $_GET['action'] ?? 'list';

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Normalize contexts: prefer checkbox list if provided
  $contexts = trim($_POST['contexts'] ?? '');
  if (!empty($_POST['contexts_list']) && is_array($_POST['contexts_list'])) {
    $contexts = implode(',', array_values(array_unique(array_map('trim', $_POST['contexts_list']))));
  }
  $payload = [
    'title' => trim($_POST['title'] ?? ''),
    'body' => trim($_POST['body'] ?? ''),
    'cta_label' => trim($_POST['cta_label'] ?? ''),
    'cta_url' => trim($_POST['cta_url'] ?? ''),
    'media_type' => $_POST['media_type'] ?? 'none',
    'media_url' => trim($_POST['media_url'] ?? ''),
    'bg_style' => trim($_POST['bg_style'] ?? ''),
    'contexts' => $contexts ?: 'all',
    'group_key' => $_POST['group_key'] === 'custom' ? trim($_POST['group_key_custom'] ?? '') : trim($_POST['group_key'] ?? ''),
    'sort_order' => (int) ($_POST['sort_order'] ?? 0),
    'is_active' => isset($_POST['is_active']) ? 1 : 0,
    'starts_at' => $_POST['starts_at'] ?: null,
    'ends_at' => $_POST['ends_at'] ?: null,
  ];

  // Process uploaded media file (image/video)
  if (isset($_FILES['media_file']) && is_array($_FILES['media_file']) && ($_FILES['media_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    try {
      $tmp = $_FILES['media_file']['tmp_name'];
      $origName = $_FILES['media_file']['name'] ?? 'file';
      $size = (int) ($_FILES['media_file']['size'] ?? 0);

      // Detect mime
      $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
      $mime = $finfo ? finfo_file($finfo, $tmp) : mime_content_type($tmp);
      if ($finfo)
        finfo_close($finfo);

      $allowedImage = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
      $allowedVideo = ['video/mp4' => 'mp4'];

      $ext = null;
      $detectedType = null;
      if (isset($allowedImage[$mime])) {
        $ext = $allowedImage[$mime];
        $detectedType = 'image';
      } elseif (isset($allowedVideo[$mime])) {
        $ext = $allowedVideo[$mime];
        $detectedType = 'video';
      }

      if ($ext && $size > 0) {
        $baseDir = realpath(__DIR__ . '/../');
        $uploadDir = $baseDir . '/uploads/spotlights';
        if (!is_dir($uploadDir)) {
          @mkdir($uploadDir, 0775, true);
        }

        $safeName = preg_replace('~[^a-z0-9\-_.]+~i', '-', pathinfo($origName, PATHINFO_FILENAME));
        $filename = $safeName . '-' . date('YmdHis') . '-' . substr(sha1(random_bytes(8)), 0, 8) . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;
        if (move_uploaded_file($tmp, $dest)) {
          $payload['media_type'] = $detectedType;
          $payload['media_url'] = '/uploads/spotlights/' . $filename;
        }
      }
    } catch (Throwable $e) {
      // Ignore upload error but log
      error_log('Spotlight upload failed: ' . $e->getMessage());
    }
  }

  if (!empty($_POST['id'])) {
    $spotlightModel->update((int) $_POST['id'], $payload);
    $_SESSION['admin_flash'] = 'Элемент обновлён';
  } else {
    $spotlightModel->create($payload);
    $_SESSION['admin_flash'] = 'Элемент создан';
  }
  header('Location: /admin/spotlights.php');
  exit;
}

if ($action === 'delete' && !empty($_GET['id'])) {
  $spotlightModel->delete((int) $_GET['id']);
  $_SESSION['admin_flash'] = 'Элемент удалён';
  header('Location: /admin/spotlights.php');
  exit;
}

$editItem = null;
if ($action === 'edit' && !empty($_GET['id'])) {
  $editItem = $spotlightModel->getById((int) $_GET['id']);
}

try {
  $items = $spotlightModel->getAll();
} catch (Throwable $e) {
  // Вероятно, отсутствует таблица: пытаемся создать и повторяем
  $ensureTable();
  try {
    $items = $spotlightModel->getAll();
  } catch (Throwable $e2) {
    $items = [];
    $_SESSION['admin_flash'] = 'Не удалось получить элементы Витрины: ' . $e2->getMessage();
  }
}
?>

<main class="admin-main">
  <div class="container">
    <div class="page-header">
      <h1>Витрина (Spotlights)</h1>
      <a class="btn btn-primary" href="/admin/spotlights.php?action=create">Добавить элемент</a>
    </div>

    <?php if (!empty($_SESSION['admin_flash'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_SESSION['admin_flash']) ?></div>
      <?php unset($_SESSION['admin_flash']); ?>
    <?php endif; ?>

    <?php if ($action === 'create' || $action === 'edit'): ?>
      <?php $item = $editItem ?: [
        'id' => '',
        'title' => '',
        'body' => '',
        'cta_label' => '',
        'cta_url' => '',
        'media_type' => 'none',
        'media_url' => '',
        'bg_style' => '',
        'contexts' => 'all',
        'group_key' => '',
        'sort_order' => 0,
        'is_active' => 1,
        'starts_at' => '',
        'ends_at' => '',
      ]; ?>
      <section class="card">
        <div class="card-header">
          <h2><?= $editItem ? 'Редактировать' : 'Создать' ?></h2>
        </div>
        <div class="card-body">
          <form method="post" action="/admin/spotlights.php" enctype="multipart/form-data">
            <?php if ($editItem): ?>
              <input type="hidden" name="id" value="<?= (int) $item['id'] ?>" />
            <?php endif; ?>
            <div class="form-grid">
              <div class="form-group">
                <label>Заголовок</label>
                <input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required />
              </div>
              <div class="form-group">
                <label>Текст</label>
                <textarea name="body" rows="4"><?= htmlspecialchars($item['body']) ?></textarea>
              </div>
              <div class="form-group">
                <label>Кнопка — текст</label>
                <input type="text" name="cta_label" value="<?= htmlspecialchars($item['cta_label']) ?>" />
              </div>
              <div class="form-group">
                <label>Кнопка — ссылка</label>
                <input type="text" name="cta_url" value="<?= htmlspecialchars($item['cta_url']) ?>" />
              </div>
              <div class="form-group">
                <label>Медиа тип</label>
                <select name="media_type">
                  <option value="none" <?= $item['media_type'] === 'none' ? 'selected' : ''; ?>>Без медиа</option>
                  <option value="image" <?= $item['media_type'] === 'image' ? 'selected' : ''; ?>>Изображение</option>
                  <option value="video" <?= $item['media_type'] === 'video' ? 'selected' : ''; ?>>Видео (mp4)</option>
                </select>
              </div>
              <div class="form-group">
                <label>Медиа URL</label>
                <input type="text" name="media_url" value="<?= htmlspecialchars($item['media_url']) ?>" />
              </div>
              <div class="form-group">
                <label>Загрузить файл (изображение/видео)</label>
                <input type="file" name="media_file" accept="image/*,video/mp4" />
                <small>Если выбрать файл — ссылка выше будет заменена на загруженный путь.</small>
              </div>
              <div class="form-group">
                <label>Фон/стиль (css класс)</label>
                <input type="text" name="bg_style" value="<?= htmlspecialchars($item['bg_style']) ?>"
                  placeholder="unit--accent" />
                <small>Необязательно. Пример: <code>unit--accent</code> или <code>unit--primary</code>.</small>
              </div>
              <div class="form-group">
                <label>Контексты (через запятую)</label>
                <input type="text" name="contexts" value="<?= htmlspecialchars($item['contexts']) ?>"
                  placeholder="all,shop,articles,article_sidebar,product,meditations" />
                <?php $ctxAll = ['shop' => 'Магазин', 'articles' => 'Списки статей', 'article_sidebar' => 'Сайдбар статьи', 'product' => 'Страница товара', 'meditations' => 'Медитации', 'all' => 'Везде'];
                $selected = array_filter(array_map('trim', explode(',', $item['contexts'])));
                ?>
                <div
                  style="margin-top:12px;display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:8px;">
                  <?php foreach ($ctxAll as $val => $label): ?>
                    <label
                      style="display:flex;align-items:center;gap:8px;font-size:14px;padding:8px 12px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:6px;cursor:pointer;transition:all 0.2s ease;hover:background:#e9ecef;">
                      <input type="checkbox" name="contexts_list[]" value="<?= $val ?>" <?= in_array($val, $selected, true) ? 'checked' : '' ?> style="width:16px;height:16px;margin:0;" />
                      <span style="font-weight:500;color:#495057;"><?= $label ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
                <small>Отметьте секции, где показывать блок. Поле выше можно оставить пустым — используются отмеченные
                  значения.</small>
              </div>
              <div class="form-group">
                <label>Группа (для карусели)</label>
                <select name="group_key"
                  style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;margin-bottom:4px;">
                  <option value="">— Выберите группу —</option>
                  <option value="shop-banner" <?= $item['group_key'] === 'shop-banner' ? 'selected' : '' ?>>Магазин — Баннеры
                  </option>
                  <option value="shop-featured" <?= $item['group_key'] === 'shop-featured' ? 'selected' : '' ?>>Магазин —
                    Популярное</option>
                  <option value="article-sidebar" <?= $item['group_key'] === 'article-sidebar' ? 'selected' : '' ?>>Статьи —
                    Сайдбар</option>
                  <option value="meditation-hero" <?= $item['group_key'] === 'meditation-hero' ? 'selected' : '' ?>>Медитации
                    — Hero блок</option>
                  <option value="custom" <?= $item['group_key'] && !in_array($item['group_key'], ['shop-banner', 'shop-featured', 'article-sidebar', 'meditation-hero']) ? 'selected' : '' ?>>Своя группа</option>
                </select>
                <input type="text" name="group_key_custom"
                  value="<?= htmlspecialchars($item['group_key'] && !in_array($item['group_key'], ['shop-banner', 'shop-featured', 'article-sidebar', 'meditation-hero']) ? $item['group_key'] : '') ?>"
                  placeholder="Введите название своей группы"
                  style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;<?= $item['group_key'] && !in_array($item['group_key'], ['shop-banner', 'shop-featured', 'article-sidebar', 'meditation-hero']) ? '' : 'display:none;' ?>" />
                <small>Выберите готовую группу или создайте свою. Одинаковая группа объединяет несколько элементов в
                  карусель.</small>
              </div>
              <div class="form-group">
                <label>Порядок</label>
                <input type="number" name="sort_order" value="<?= (int) $item['sort_order'] ?>" />
                <small>Чем меньше число — тем выше позиция среди блоков в одной области.</small>
              </div>
              <div class="form-group">
                <label>Активен</label>
                <input type="checkbox" name="is_active" <?= !empty($item['is_active']) ? 'checked' : ''; ?> />
              </div>
              <div class="form-group">
                <label>Начало показа</label>
                <input type="datetime-local" name="starts_at"
                  value="<?= $item['starts_at'] ? date('Y-m-d\TH:i', strtotime($item['starts_at'])) : '' ?>" />
              </div>
              <div class="form-group">
                <label>Окончание показа</label>
                <input type="datetime-local" name="ends_at"
                  value="<?= $item['ends_at'] ? date('Y-m-d\TH:i', strtotime($item['ends_at'])) : '' ?>" />
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn btn-primary">Сохранить</button>
              <a class="btn" href="/admin/spotlights.php">Отмена</a>
            </div>
          </form>

          <script>
            document.addEventListener('DOMContentLoaded', function () {
              const groupSelect = document.querySelector('select[name="group_key"]');
              const customInput = document.querySelector('input[name="group_key_custom"]');

              if (groupSelect && customInput) {
                groupSelect.addEventListener('change', function () {
                  if (this.value === 'custom') {
                    customInput.style.display = 'block';
                    customInput.focus();
                  } else {
                    customInput.style.display = 'none';
                    customInput.value = '';
                  }
                });
              }
            });
          </script>
        </div>
      </section>
    <?php endif; ?>

    <section class="card">
      <div class="card-header">
        <h2>Элементы</h2>
      </div>
      <div class="card-body">
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Заголовок</th>
                <th>Контексты</th>
                <th>Группа</th>
                <th>Порядок</th>
                <th>Статус</th>
                <th>Период</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($items)): ?>
                <tr>
                  <td colspan="8">Пока нет элементов</td>
                </tr>
              <?php else: ?>
                <?php foreach ($items as $it): ?>
                  <tr>
                    <td><?= (int) $it['id'] ?></td>
                    <td><?= htmlspecialchars($it['title']) ?></td>
                    <td><?= htmlspecialchars($it['contexts']) ?></td>
                    <td><?= htmlspecialchars($it['group_key'] ?? '') ?></td>
                    <td><?= (int) $it['sort_order'] ?></td>
                    <td><?= !empty($it['is_active']) ? 'Активен' : 'Выключен' ?></td>
                    <td>
                      <?php if ($it['starts_at'] || $it['ends_at']): ?>
                        <?= htmlspecialchars($it['starts_at'] ?? '—') ?> → <?= htmlspecialchars($it['ends_at'] ?? '—') ?>
                      <?php else: ?>
                        —
                      <?php endif; ?>
                    </td>
                    <td class="row-actions">
                      <a class="btn btn-small" href="/admin/spotlights.php?action=edit&id=<?= (int) $it['id'] ?>">Изм.</a>
                      <a class="btn btn-small btn-danger"
                        href="/admin/spotlights.php?action=delete&id=<?= (int) $it['id'] ?>"
                        onclick="return confirm('Удалить элемент?')">Удалить</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>