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

try {
  switch ($method) {
    case 'GET':
      // Получить отзывы с фильтрацией по типу
      $type = $_GET['type'] ?? 'all';
      $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : null;
      $offset = (int) ($_GET['offset'] ?? 0);

      if ($type === 'all') {
        $reviews = $review->getApproved($limit);
      } else {
        $reviews = $review->getByType($type, $limit);
      }

      // Enrich avatars from telegram_users if missing
      try {
        $pdo = $db->getConnection();
        foreach ($reviews as &$rev) {
          if (
            (empty($rev['telegram_avatar']) || $rev['telegram_avatar'] === null || $rev['telegram_avatar'] === '')
            && !empty($rev['telegram_user_id'])
          ) {
            $stmt = $pdo->prepare("SELECT telegram_avatar, photo_url, telegram_username FROM telegram_users WHERE telegram_id = ?");
            $stmt->execute([$rev['telegram_user_id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
              $rev['telegram_avatar'] = $row['telegram_avatar'] ?: ($row['photo_url'] ?? null);
              if (empty($rev['telegram_username']) && !empty($row['telegram_username'])) {
                $rev['telegram_username'] = $row['telegram_username'];
              }
            }
          }
        }
        unset($rev);
      } catch (Throwable $t) {
        // ignore enrichment errors
      }

      // Очищаем UTF-8 символы для корректного JSON
      $cleanReviews = [];
      foreach ($reviews as $reviewItem) {
        $cleanReview = [];
        foreach ($reviewItem as $key => $value) {
          if (is_string($value)) {
            // Очищаем некорректные UTF-8 символы
            $cleanReview[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            // Удаляем невидимые символы
            $cleanReview[$key] = preg_replace('/[\x00-\x1F\x7F]/', '', $cleanReview[$key]);
          } else {
            $cleanReview[$key] = $value;
          }
        }
        $cleanReviews[] = $cleanReview;
      }

      echo json_encode(['success' => true, 'data' => $cleanReviews], JSON_UNESCAPED_UNICODE);
      break;

    case 'POST':
      // Добавить новый отзыв
      $input = json_decode(file_get_contents('php://input'), true);

      if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        break;
      }

      $errors = $review->validate($input);
      if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['error' => 'Validation failed', 'details' => $errors]);
        break;
      }

      // Проверяем, не оставлял ли пользователь отзыв недавно
      if (!empty($input['email']) && $review->existsByEmail($input['email'])) {
        http_response_code(429);
        echo json_encode(['error' => 'You can only leave one review per day']);
        break;
      }

      $reviewId = $review->create($input);
      echo json_encode(['success' => true, 'id' => $reviewId]);
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
?>