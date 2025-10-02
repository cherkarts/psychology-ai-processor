<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin-functions.php';

requirePermission('articles');

function tailFile($filename, $lines = 200)
{
  if (!file_exists($filename))
    return [];
  $f = fopen($filename, 'r');
  if (!$f)
    return [];

  $buffer = '';
  $chunkSize = 4096;
  $pos = -1;
  $lineCount = 0;
  $stat = fstat($f);
  $filesize = $stat['size'];
  $output = [];

  if ($filesize === 0) {
    fclose($f);
    return [];
  }

  fseek($f, 0, SEEK_END);
  $pos = ftell($f);

  while ($pos > 0 && $lineCount <= $lines) {
    $step = ($pos - $chunkSize) > 0 ? $chunkSize : $pos;
    $pos -= $step;
    fseek($f, $pos);
    $buffer = fread($f, $step) . $buffer;
    $lineCount = substr_count($buffer, "\n");
  }
  fclose($f);
  $rows = explode("\n", trim($buffer));
  return array_slice($rows, -$lines);
}

$db = getAdminDB();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = 'Неверный токен безопасности.';
    header('Location: logs.php');
    exit();
  }

  $action = $_POST['action'] ?? '';
  try {
    switch ($action) {
      case 'clear_actions_log':
        $file = dirname(__DIR__) . '/logs/actions.log';
        if (file_exists($file))
          file_put_contents($file, '');
        $_SESSION['success_message'] = 'Файл actions.log очищен';
        break;
      case 'clear_ai_generation_logs':
        if ($db) {
          $db->query('DELETE FROM ai_generation_logs');
        }
        $_SESSION['success_message'] = 'Логи генерации очищены';
        break;
      case 'clear_ai_generation_logs_older':
        $days = max(0, (int) ($_POST['days'] ?? 30));
        if ($db) {
          $db->query('DELETE FROM ai_generation_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)', [$days]);
        }
        $_SESSION['success_message'] = "Удалено старше {$days} дней";
        break;
      case 'clear_activity_logs':
        if ($db) {
          $db->query('DELETE FROM activity_logs');
        }
        $_SESSION['success_message'] = 'Системный журнал активности очищен';
        break;
    }
  } catch (Exception $e) {
    $_SESSION['error_message'] = 'Ошибка: ' . $e->getMessage();
  }

  header('Location: logs.php');
  exit();
}

// Fetch data
$actionsLog = tailFile(dirname(__DIR__) . '/logs/actions.log', 300);

$aiGenLogs = [];
if ($db) {
  try {
    $stmt = $db->prepare('SELECT id, task_id, action, message, created_at FROM ai_generation_logs ORDER BY created_at DESC, id DESC LIMIT 300');
    $stmt->execute();
    $aiGenLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
  }
}

$activityLogs = [];
if ($db) {
  try {
    $stmt = $db->prepare('SELECT id, user_id, action, entity_type, entity_id, created_at FROM activity_logs ORDER BY created_at DESC, id DESC LIMIT 300');
    $stmt->execute();
    $activityLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
  }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="admin-container" style="max-width:1200px;margin:0 auto;padding:20px;">
  <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i>
      <?php echo sanitizeOutput($_SESSION['success_message']);
      unset($_SESSION['success_message']); ?>
    </div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-circle"></i>
      <?php echo sanitizeOutput($_SESSION['error_message']);
      unset($_SESSION['error_message']); ?>
    </div>
  <?php endif; ?>

  <h1 style="margin:0 0 20px 0;display:flex;align-items:center;gap:10px;"><i class="fas fa-clipboard-list"></i> Логи
    системы</h1>

  <div class="tabs">
    <div class="tab-headers" style="display:flex;gap:10px;margin-bottom:15px;">
      <button class="btn btn-secondary" data-tab="t1">Файл actions.log</button>
      <button class="btn btn-secondary" data-tab="t2">AI генерация (БД)</button>
      <button class="btn btn-secondary" data-tab="t3">Активность (БД)</button>
    </div>

    <div class="tab-body" id="t1" style="display:block;">
      <form method="POST" style="margin-bottom:10px;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="clear_actions_log">
        <button class="btn btn-danger" type="submit"><i class="fas fa-trash"></i> Очистить actions.log</button>
      </form>
      <pre style="background:#111;color:#0f0;padding:15px;border-radius:8px;max-height:500px;overflow:auto;">
<?php echo htmlspecialchars(implode("\n", $actionsLog)); ?>
      </pre>
    </div>

    <div class="tab-body" id="t2" style="display:none;">
      <form method="POST" style="margin:10px 0;display:flex;gap:10px;align-items:center;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="clear_ai_generation_logs_older">
        <label>Удалить записи старше (дней):</label>
        <input type="number" name="days" value="30" min="0" style="width:100px;">
        <button class="btn btn-secondary" type="submit">Очистить</button>
      </form>
      <form method="POST" style="margin-bottom:10px;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="clear_ai_generation_logs">
        <button class="btn btn-danger" type="submit"><i class="fas fa-trash"></i> Очистить все</button>
      </form>

      <div class="table-wrap" style="overflow:auto;">
        <table class="table" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left;">#</th>
              <th style="text-align:left;">task_id</th>
              <th style="text-align:left;">action</th>
              <th style="text-align:left;">message</th>
              <th style="text-align:left;">created_at</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($aiGenLogs as $row): ?>
              <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo htmlspecialchars((string) $row['task_id']); ?></td>
                <td><?php echo htmlspecialchars((string) $row['action']); ?></td>
                <td><?php echo htmlspecialchars((string) $row['message']); ?></td>
                <td><?php echo htmlspecialchars((string) $row['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="tab-body" id="t3" style="display:none;">
      <form method="POST" style="margin-bottom:10px;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="clear_activity_logs">
        <button class="btn btn-danger" type="submit"><i class="fas fa-trash"></i> Очистить активность</button>
      </form>
      <div class="table-wrap" style="overflow:auto;">
        <table class="table" style="width:100%;border-collapse:collapse;">
          <thead>
            <tr>
              <th style="text-align:left;">#</th>
              <th style="text-align:left;">user_id</th>
              <th style="text-align:left;">action</th>
              <th style="text-align:left;">entity</th>
              <th style="text-align:left;">id</th>
              <th style="text-align:left;">created_at</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($activityLogs as $row): ?>
              <tr>
                <td><?php echo (int) $row['id']; ?></td>
                <td><?php echo htmlspecialchars((string) $row['user_id']); ?></td>
                <td><?php echo htmlspecialchars((string) $row['action']); ?></td>
                <td><?php echo htmlspecialchars((string) $row['entity_type']); ?></td>
                <td><?php echo htmlspecialchars((string) $row['entity_id']); ?></td>
                <td><?php echo htmlspecialchars((string) $row['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  const headers = document.querySelectorAll('.tab-headers .btn');
  headers.forEach(btn => btn.addEventListener('click', () => {
    const id = btn.getAttribute('data-tab');
    document.querySelectorAll('.tab-body').forEach(b => b.style.display = 'none');
    document.getElementById(id).style.display = 'block';
  }));
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>