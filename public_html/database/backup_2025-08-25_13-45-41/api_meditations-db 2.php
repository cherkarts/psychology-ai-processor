<?php
/**
 * API для работы с медитациями через базу данных
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/Database.php';

$db = Database::getInstance();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
  switch ($method) {
    case 'GET':
      handleGet($action, $db);
      break;

    default:
      http_response_code(405);
      echo json_encode(['error' => 'Method not allowed']);
      break;
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}

function handleGet($action, $db)
{
  switch ($action) {
    case 'all':
      // Получить все медитации
      $categoryId = $_GET['category_id'] ?? null;
      $isFree = $_GET['is_free'] ?? null;

      $sql = "SELECT m.*, mc.name as category_name, mc.slug as category_slug 
                    FROM meditations m 
                    LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                    WHERE 1=1";

      $params = [];

      if ($categoryId) {
        $sql .= " AND m.category_id = ?";
        $params[] = $categoryId;
      }

      if ($isFree !== null) {
        $sql .= " AND m.is_free = ?";
        $params[] = $isFree ? 1 : 0;
      }

      $sql .= " ORDER BY m.created_at DESC";

      $meditations = $db->fetchAll($sql, $params);
      echo json_encode(['success' => true, 'data' => $meditations]);
      break;

    case 'categories':
      // Получить все категории медитаций
      $sql = "SELECT mc.*, COUNT(m.id) as meditation_count 
                    FROM meditation_categories mc 
                    LEFT JOIN meditations m ON mc.id = m.category_id 
                    WHERE mc.is_active = 1 
                    GROUP BY mc.id 
                    ORDER BY mc.sort_order, mc.name";

      $categories = $db->fetchAll($sql);
      echo json_encode(['success' => true, 'data' => $categories]);
      break;

    case 'by-category':
      // Получить медитации по категории
      $categorySlug = $_GET['category'] ?? '';
      if (empty($categorySlug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Category parameter is required']);
        return;
      }

      $sql = "SELECT m.*, mc.name as category_name 
                    FROM meditations m 
                    LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                    WHERE mc.slug = ? 
                    ORDER BY m.created_at DESC";

      $meditations = $db->fetchAll($sql, [$categorySlug]);
      echo json_encode(['success' => true, 'data' => $meditations]);
      break;

    case 'free':
      // Получить бесплатные медитации
      $sql = "SELECT m.*, mc.name as category_name 
                    FROM meditations m 
                    LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                    WHERE m.is_free = 1 
                    ORDER BY m.created_at DESC";

      $meditations = $db->fetchAll($sql);
      echo json_encode(['success' => true, 'data' => $meditations]);
      break;

    case 'search':
      // Поиск медитаций
      $query = $_GET['q'] ?? '';
      $limit = (int) ($_GET['limit'] ?? 20);

      if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Query parameter is required']);
        return;
      }

      $sql = "SELECT m.*, mc.name as category_name 
                    FROM meditations m 
                    LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                    WHERE (m.title LIKE ? OR m.description LIKE ? OR m.subtitle LIKE ?) 
                    ORDER BY m.created_at DESC 
                    LIMIT ?";

      $searchTerm = "%$query%";
      $meditations = $db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
      echo json_encode(['success' => true, 'data' => $meditations]);
      break;

    default:
      // Получить конкретную медитацию
      if (!empty($action)) {
        $sql = "SELECT m.*, mc.name as category_name, mc.slug as category_slug 
                        FROM meditations m 
                        LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                        WHERE m.slug = ?";

        $meditation = $db->fetchOne($sql, [$action]);
        if ($meditation) {
          echo json_encode(['success' => true, 'data' => $meditation]);
        } else {
          http_response_code(404);
          echo json_encode(['error' => 'Meditation not found']);
        }
      } else {
        http_response_code(400);
        echo json_encode(['error' => 'Meditation slug is required']);
      }
      break;
  }
}

