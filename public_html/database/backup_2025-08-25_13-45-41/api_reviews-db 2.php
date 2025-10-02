<?php
/**
 * API для работы с отзывами через базу данных
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Models/Review.php';

$db = Database::getInstance();
$review = new Review();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
  switch ($method) {
    case 'GET':
      handleGet($action, $review);
      break;

    case 'POST':
      handlePost($action, $review);
      break;

    case 'PUT':
      handlePut($action, $review);
      break;

    case 'DELETE':
      handleDelete($action, $review);
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

function handleGet($action, $review)
{
  switch ($action) {
    case 'all':
      // Получить все одобренные отзывы
      $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
      $offset = (int) ($_GET['offset'] ?? 0);

      $reviews = $review->getApproved($limit, $offset);
      echo json_encode(['success' => true, 'data' => $reviews]);
      break;

    case 'by-type':
      // Получить отзывы по типу
      $type = $_GET['type'] ?? '';
      $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;

      if (empty($type)) {
        http_response_code(400);
        echo json_encode(['error' => 'Type parameter is required']);
        return;
      }

      $reviews = $review->getByType($type, $limit);
      echo json_encode(['success' => true, 'data' => $reviews]);
      break;

    case 'stats':
      // Получить статистику отзывов
      $stats = $review->getStats();
      echo json_encode(['success' => true, 'data' => $stats]);
      break;

    case 'paginated':
      // Получить отзывы с пагинацией
      $page = (int) ($_GET['page'] ?? 1);
      $perPage = (int) ($_GET['per_page'] ?? 10);
      $status = $_GET['status'] ?? 'approved';

      $result = $review->getPaginated($page, $perPage, $status);
      echo json_encode(['success' => true, 'data' => $result]);
      break;

    case 'moderation':
      // Получить отзывы для модерации (только для админов)
      $status = $_GET['status'] ?? 'pending';
      $limit = (int) ($_GET['limit'] ?? 50);

      $reviews = $review->getForModeration($status, $limit);
      echo json_encode(['success' => true, 'data' => $reviews]);
      break;

    default:
      // Получить конкретный отзыв
      if (!empty($action)) {
        $reviewData = $review->getById($action);
        if ($reviewData) {
          echo json_encode(['success' => true, 'data' => $reviewData]);
        } else {
          http_response_code(404);
          echo json_encode(['error' => 'Review not found']);
        }
      } else {
        http_response_code(400);
        echo json_encode(['error' => 'Review ID is required']);
      }
      break;
  }
}

function handlePost($action, $review)
{
  $input = json_decode(file_get_contents('php://input'), true);

  if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    return;
  }

  switch ($action) {
    case 'create':
      // Создать новый отзыв
      $errors = $review->validate($input);
      if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['error' => 'Validation failed', 'details' => $errors]);
        return;
      }

      // Проверяем, не оставлял ли пользователь отзыв недавно
      if (!empty($input['email']) && $review->existsByEmail($input['email'])) {
        http_response_code(429);
        echo json_encode(['error' => 'You can only leave one review per day']);
        return;
      }

      $reviewId = $review->create($input);
      echo json_encode(['success' => true, 'id' => $reviewId]);
      break;

    default:
      http_response_code(400);
      echo json_encode(['error' => 'Invalid action']);
      break;
  }
}

function handlePut($action, $review)
{
  $input = json_decode(file_get_contents('php://input'), true);

  if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    return;
  }

  if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'Review ID is required']);
    return;
  }

  switch ($action) {
    case 'approve':
      // Одобрить отзыв
      $reviewId = $_GET['id'] ?? '';
      if (empty($reviewId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Review ID is required']);
        return;
      }

      $reviewData = $review->getById($reviewId);
      if (!$reviewData) {
        http_response_code(404);
        echo json_encode(['error' => 'Review not found']);
        return;
      }

      $approvedBy = $input['approved_by'] ?? 'admin';
      $result = $review->approve($reviewId, $approvedBy);
      echo json_encode(['success' => true, 'updated' => $result]);
      break;

    case 'reject':
      // Отклонить отзыв
      $reviewId = $_GET['id'] ?? '';
      if (empty($reviewId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Review ID is required']);
        return;
      }

      $reviewData = $review->getById($reviewId);
      if (!$reviewData) {
        http_response_code(404);
        echo json_encode(['error' => 'Review not found']);
        return;
      }

      $rejectedBy = $input['rejected_by'] ?? 'admin';
      $result = $review->reject($reviewId, $rejectedBy);
      echo json_encode(['success' => true, 'updated' => $result]);
      break;

    default:
      // Обновить отзыв
      $reviewData = $review->getById($action);
      if (!$reviewData) {
        http_response_code(404);
        echo json_encode(['error' => 'Review not found']);
        return;
      }

      $result = $review->update($action, $input);
      echo json_encode(['success' => true, 'updated' => $result]);
      break;
  }
}

function handleDelete($action, $review)
{
  if (empty($action)) {
    http_response_code(400);
    echo json_encode(['error' => 'Review ID is required']);
    return;
  }

  // Удалить отзыв
  $reviewData = $review->getById($action);
  if (!$reviewData) {
    http_response_code(404);
    echo json_encode(['error' => 'Review not found']);
    return;
  }

  $result = $review->delete($action);
  echo json_encode(['success' => true, 'deleted' => $result]);
}

