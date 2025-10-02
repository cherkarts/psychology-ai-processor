<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Проверяем права доступа
requirePermission('admin');

try {
  $db = getAdminDB();

  $sql = "SELECT u.id, u.username, u.email, u.is_active, u.last_login, r.name as role_name 
            FROM admin_users u 
            LEFT JOIN admin_roles r ON u.role_id = r.id 
            ORDER BY u.created_at DESC";

  $stmt = $db->query($sql);
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'users' => $users
  ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
  error_log("Error loading admin users: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка загрузки пользователей: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>



