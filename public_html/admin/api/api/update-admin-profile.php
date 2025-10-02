<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Проверяем права доступа
requirePermission('admin');

// Получаем данные из запроса
$input = json_decode(file_get_contents('php://input'), true);

try {
  $db = getAdminDB();

  // Получаем текущего пользователя
  $currentUserId = $_SESSION['admin_user_id'] ?? null;
  if (!$currentUserId) {
    throw new Exception('Пользователь не найден в сессии');
  }

  $stmt = $db->prepare("SELECT username, password_hash FROM admin_users WHERE id = ?");
  $stmt->execute([$currentUserId]);
  $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$currentUser) {
    throw new Exception('Пользователь не найден');
  }

  // Проверяем текущий пароль
  if (!empty($input['current_password'])) {
    if (!password_verify($input['current_password'], $currentUser['password_hash'])) {
      throw new Exception('Неверный текущий пароль');
    }
  }

  // Подготавливаем данные для обновления
  $updates = [];
  $params = [];

  // Обновляем логин
  if (!empty($input['new_username']) && $input['new_username'] !== $currentUser['username']) {
    // Проверяем, что логин не занят
    $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
    $stmt->execute([$input['new_username'], $currentUserId]);
    if ($stmt->fetch()) {
      throw new Exception('Логин уже занят');
    }

    $updates[] = "username = ?";
    $params[] = $input['new_username'];
  }

  // Обновляем пароль
  if (!empty($input['new_password'])) {
    $updates[] = "password_hash = ?";
    $params[] = password_hash($input['new_password'], PASSWORD_DEFAULT);
  }

  if (empty($updates)) {
    throw new Exception('Нет данных для обновления');
  }

  // Выполняем обновление
  $params[] = $currentUserId;
  $sql = "UPDATE admin_users SET " . implode(', ', $updates) . " WHERE id = ?";
  $stmt = $db->prepare($sql);
  $stmt->execute($params);

  // Обновляем сессию
  if (!empty($input['new_username'])) {
    $_SESSION['admin_username'] = $input['new_username'];
  }

  // Логируем действие
  if (function_exists('logAdminActivity')) {
    logAdminActivity('profile_update', 'Profile updated');
  }

  echo json_encode([
    'success' => true,
    'message' => 'Профиль обновлен успешно'
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("Error updating admin profile: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>



