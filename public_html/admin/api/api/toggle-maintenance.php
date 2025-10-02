<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Проверяем права доступа
requirePermission('admin');

// Проверяем CSRF токен
$csrfToken = '';
if (function_exists('getallheaders')) {
  $headers = getallheaders();
  $csrfToken = $headers['X-CSRF-Token'] ?? '';
} else {
  // Альтернативный способ для серверов без getallheaders()
  $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
}

if (!verifyCSRFToken($csrfToken)) {
  echo json_encode([
    'success' => false,
    'message' => 'Неверный токен безопасности'
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// Получаем данные из запроса
$input = json_decode(file_get_contents('php://input'), true);

try {
  $maintenanceMode = $input['maintenance_mode'] ?? false;
  $maintenanceMessage = $input['maintenance_message'] ?? 'Сайт временно недоступен по техническим причинам.';

  // Отладочная информация
  error_log("TOGGLE MAINTENANCE API: maintenance_mode=" . ($maintenanceMode ? 'true' : 'false'));
  error_log("TOGGLE MAINTENANCE API: message=" . $maintenanceMessage);
  error_log("TOGGLE MAINTENANCE API: csrf_token=" . $csrfToken);

  // Пути к файлам
  $maintenanceFile = __DIR__ . '/../../maintenance.flag';
  $messageFile = __DIR__ . '/../../maintenance.message';

  error_log("TOGGLE MAINTENANCE API: maintenance_file=" . $maintenanceFile);
  error_log("TOGGLE MAINTENANCE API: message_file=" . $messageFile);

  if ($maintenanceMode) {
    // Включаем режим обслуживания
    error_log("TOGGLE MAINTENANCE API: Enabling maintenance mode");
    $flagResult = file_put_contents($maintenanceFile, '1');
    $messageResult = file_put_contents($messageFile, $maintenanceMessage);

    error_log("TOGGLE MAINTENANCE API: flag_result=" . ($flagResult !== false ? 'success' : 'failed'));
    error_log("TOGGLE MAINTENANCE API: message_result=" . ($messageResult !== false ? 'success' : 'failed'));

    if ($flagResult === false || $messageResult === false) {
      throw new Exception('Не удалось создать файлы режима обслуживания. Проверьте права доступа к папке.');
    }
  } else {
    // Выключаем режим обслуживания
    error_log("TOGGLE MAINTENANCE API: Disabling maintenance mode");
    $deleted = 0;
    if (file_exists($maintenanceFile)) {
      if (unlink($maintenanceFile))
        $deleted++;
      error_log("TOGGLE MAINTENANCE API: Deleted maintenance.flag");
    }
    if (file_exists($messageFile)) {
      if (unlink($messageFile))
        $deleted++;
      error_log("TOGGLE MAINTENANCE API: Deleted maintenance.message");
    }
  }

  // Также обновляем настройки в базе данных (если возможно)
  try {
    $db = getAdminDB();
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->execute([$maintenanceMode ? '1' : '0', 'maintenance_mode']);
    $stmt->execute([$maintenanceMessage, 'maintenance_message']);
  } catch (Exception $dbError) {
    // Игнорируем ошибки БД, файловый режим имеет приоритет
    error_log("Database update failed (ignored): " . $dbError->getMessage());
  }

  // Логируем действие
  if (function_exists('logAdminActivity')) {
    $action = $maintenanceMode ? 'maintenance_enabled' : 'maintenance_disabled';
    $description = $maintenanceMode ? 'Режим обслуживания включен' : 'Режим обслуживания выключен';
    logAdminActivity($action, $description);
  }

  echo json_encode([
    'success' => true,
    'message' => $maintenanceMode ? 'Режим обслуживания включен' : 'Режим обслуживания выключен',
    'maintenance_mode' => $maintenanceMode
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("Error toggling maintenance mode: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка изменения режима обслуживания: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>