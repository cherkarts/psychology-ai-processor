<?php
// Упрощенная версия API режима обслуживания без CSRF проверки
header('Content-Type: application/json');

// Получаем данные из запроса
$input = json_decode(file_get_contents('php://input'), true);

try {
  $maintenanceMode = $input['maintenance_mode'] ?? false;
  $maintenanceMessage = $input['maintenance_message'] ?? 'Сайт временно недоступен по техническим причинам.';

  // Отладочная информация
  error_log("SIMPLE TOGGLE MAINTENANCE API: maintenance_mode=" . ($maintenanceMode ? 'true' : 'false'));
  error_log("SIMPLE TOGGLE MAINTENANCE API: message=" . $maintenanceMessage);

  // Пути к файлам
  $maintenanceFile = __DIR__ . '/../../maintenance.flag';
  $messageFile = __DIR__ . '/../../maintenance.message';

  error_log("SIMPLE TOGGLE MAINTENANCE API: maintenance_file=" . $maintenanceFile);
  error_log("SIMPLE TOGGLE MAINTENANCE API: message_file=" . $messageFile);

  if ($maintenanceMode) {
    // Включаем режим обслуживания
    error_log("SIMPLE TOGGLE MAINTENANCE API: Enabling maintenance mode");
    $flagResult = file_put_contents($maintenanceFile, '1');
    $messageResult = file_put_contents($messageFile, $maintenanceMessage);

    error_log("SIMPLE TOGGLE MAINTENANCE API: flag_result=" . ($flagResult !== false ? 'success' : 'failed'));
    error_log("SIMPLE TOGGLE MAINTENANCE API: message_result=" . ($messageResult !== false ? 'success' : 'failed'));

    if ($flagResult === false || $messageResult === false) {
      throw new Exception('Не удалось создать файлы режима обслуживания. Проверьте права доступа к папке.');
    }
  } else {
    // Выключаем режим обслуживания
    error_log("SIMPLE TOGGLE MAINTENANCE API: Disabling maintenance mode");
    $deleted = 0;
    if (file_exists($maintenanceFile)) {
      if (unlink($maintenanceFile))
        $deleted++;
      error_log("SIMPLE TOGGLE MAINTENANCE API: Deleted maintenance.flag");
    }
    if (file_exists($messageFile)) {
      if (unlink($messageFile))
        $deleted++;
      error_log("SIMPLE TOGGLE MAINTENANCE API: Deleted maintenance.message");
    }
  }

  echo json_encode([
    'success' => true,
    'message' => $maintenanceMode ? 'Режим обслуживания включен' : 'Режим обслуживания выключен',
    'maintenance_mode' => $maintenanceMode
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("SIMPLE TOGGLE MAINTENANCE API ERROR: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка изменения режима обслуживания: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>