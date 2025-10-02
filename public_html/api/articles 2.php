<?php
require_once '../includes/Database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

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
            // Получить все статьи
            $categoryId = $_GET['category_id'] ?? null;
            $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
            $offset = (int) ($_GET['offset'] ?? 0);

            $sql = "SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                    FROM articles a 
                    LEFT JOIN article_categories ac ON a.category_id = ac.id 
                    WHERE a.is_published = 1";

            $params = [];

            if ($categoryId) {
                $sql .= " AND a.category_id = ?";
                $params[] = $categoryId;
            }

            $sql .= " ORDER BY a.created_at DESC";

            if ($limit) {
                $sql .= " LIMIT ? OFFSET ?";
                $params[] = $limit;
                $params[] = $offset;
            }

            $articles = $db->fetchAll($sql, $params);
            echo json_encode(['success' => true, 'data' => $articles]);
            break;

        case 'categories':
            // Получить все категории статей
            $sql = "SELECT ac.*, COUNT(a.id) as article_count 
                    FROM article_categories ac 
                    LEFT JOIN articles a ON ac.id = a.category_id AND a.is_published = 1 
                    WHERE ac.is_active = 1 
                    GROUP BY ac.id 
                    ORDER BY ac.sort_order, ac.name";

            $categories = $db->fetchAll($sql);
            echo json_encode(['success' => true, 'data' => $categories]);
            break;

        case 'by-category':
            // Получить статьи по категории
            $categorySlug = $_GET['category'] ?? '';
            $limit = (int) ($_GET['limit'] ?? null);

            if (empty($categorySlug)) {
                http_response_code(400);
                echo json_encode(['error' => 'Category parameter is required']);
                return;
            }

            $sql = "SELECT a.*, ac.name as category_name 
                    FROM articles a 
                    LEFT JOIN article_categories ac ON a.category_id = ac.id 
                    WHERE ac.slug = ? AND a.is_published = 1 
                    ORDER BY a.created_at DESC";

            $params = [$categorySlug];

            if ($limit) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
            }

            $articles = $db->fetchAll($sql, $params);
            echo json_encode(['success' => true, 'data' => $articles]);
            break;

        case 'search':
            // Поиск статей
            $query = $_GET['q'] ?? '';
            $limit = (int) ($_GET['limit'] ?? 20);

            if (empty($query)) {
                http_response_code(400);
                echo json_encode(['error' => 'Search query is required']);
                return;
            }

            $sql = "SELECT a.*, ac.name as category_name 
                    FROM articles a 
                    LEFT JOIN article_categories ac ON a.category_id = ac.id 
                    WHERE a.is_published = 1 AND (a.title LIKE ? OR a.description LIKE ? OR a.content LIKE ?) 
                    ORDER BY a.created_at DESC 
                    LIMIT ?";

            $searchTerm = "%{$query}%";
            $articles = $db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
            echo json_encode(['success' => true, 'data' => $articles]);
            break;

        default:
            // Получить конкретную статью
            if (!empty($action)) {
                $sql = "SELECT a.*, ac.name as category_name, ac.slug as category_slug 
                        FROM articles a 
                        LEFT JOIN article_categories ac ON a.category_id = ac.id 
                        WHERE a.slug = ? AND a.is_published = 1";

                $article = $db->fetchOne($sql, [$action]);
                if ($article) {
                    echo json_encode(['success' => true, 'data' => $article]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Article not found']);
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Article slug is required']);
            }
            break;
    }
}
?>