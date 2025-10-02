<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// Проверяем права доступа
requirePermission('admin');

try {
  $db = getAdminDB();

  $sql = "SELECT id, name, description, permissions, created_at 
            FROM admin_roles 
            ORDER BY name";

  $stmt = $db->query($sql);
  $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'success' => true,
    'roles' => $roles
  ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
  error_log("Error loading admin roles: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка загрузки ролей: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
?>



