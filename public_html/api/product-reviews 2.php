<?php
/**
 * API для работы с отзывами товаров
 * Поддерживает добавление, получение, лайки и жалобы на отзывы
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

// Подключение к базе данных
require_once __DIR__ . '/../includes/Database.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

try {
  $db = Database::getInstance();
  $pdo = $db->getConnection();

  $action = $_GET['action'] ?? $_POST['action'] ?? '';

  switch ($action) {
    case 'list':
      handleListReviews($pdo);
      break;
    case 'count':
      handleCountReviews($pdo);
      break;
    case 'add':
      handleAddReview($pdo);
      break;
    case 'like':
      handleLikeReview($pdo);
      break;
    case 'report':
      handleReportReview($pdo);
      break;
    case 'check_user_review':
      handleCheckUserReview($pdo);
      break;
    default:
      sendError('Неизвестное действие');
  }
} catch (Exception $e) {
  error_log("Product reviews API error: " . $e->getMessage());
  sendError('Внутренняя ошибка сервера');
}

/**
 * Получение списка отзывов
 */
function handleListReviews($pdo)
{
  $productId = $_GET['product_id'] ?? '';
  $page = max(1, intval($_GET['page'] ?? 1));
  $limit = max(1, min(50, intval($_GET['limit'] ?? 10)));
  $offset = ($page - 1) * $limit;

  if (empty($productId)) {
    sendError('ID товара обязателен');
  }

  try {
    // Получаем отзывы с информацией о пользователях
    $sql = "
            SELECT 
                r.*,
                tu.telegram_id,
                tu.telegram_username,
                tu.telegram_first_name,
                tu.telegram_last_name,
                tu.telegram_avatar,
                COALESCE(l.likes_count, 0) as likes_count,
                CASE WHEN ul.review_id IS NOT NULL THEN 1 ELSE 0 END as user_liked
            FROM product_reviews r
            LEFT JOIN telegram_users tu ON r.telegram_user_id = tu.telegram_id
            LEFT JOIN (
                SELECT review_id, COUNT(*) as likes_count
                FROM product_review_likes
                GROUP BY review_id
            ) l ON r.id = l.review_id
            LEFT JOIN product_review_likes ul ON r.id = ul.review_id AND ul.telegram_user_id = :current_user_id
            WHERE r.product_id = :product_id 
            AND r.status = 'approved'
            ORDER BY r.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $stmt->bindValue(':current_user_id', $_SESSION['telegram_user']['id'] ?? null, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Форматируем даты и приводим типы
    foreach ($reviews as &$review) {
      $review['created_at_formatted'] = formatDate($review['created_at']);
      $review['rating'] = intval($review['rating']); // Приводим рейтинг к числу
      $review['likes_count'] = intval($review['likes_count']); // Приводим количество лайков к числу
      $review['user_liked'] = intval($review['user_liked']); // Приводим статус лайка к числу
    }

    // Получаем общее количество отзывов
    $countSql = "
            SELECT COUNT(*) as total
            FROM product_reviews
            WHERE product_id = :product_id AND status = 'approved'
        ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    $hasMore = ($offset + $limit) < $totalCount;

    sendSuccess([
      'reviews' => $reviews,
      'pagination' => [
        'current_page' => $page,
        'per_page' => $limit,
        'total' => $totalCount,
        'has_more' => $hasMore
      ]
    ]);
  } catch (PDOException $e) {
    error_log("Database error in handleListReviews: " . $e->getMessage());
    sendError('Ошибка базы данных');
  }
}

/**
 * Получение количества отзывов
 */
function handleCountReviews($pdo)
{
  $productId = $_GET['product_id'] ?? '';

  if (empty($productId)) {
    sendError('ID товара обязателен');
  }

  try {
    $sql = "
            SELECT COUNT(*) as count
            FROM product_reviews
            WHERE product_id = :product_id AND status = 'approved'
        ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    sendSuccess(['count' => intval($result['count'])]);
  } catch (PDOException $e) {
    error_log("Database error in handleCountReviews: " . $e->getMessage());
    sendError('Ошибка базы данных');
  }
}

/**
 * Добавление отзыва
 */
function handleAddReview($pdo)
{
  // Проверяем авторизацию
  if (!isset($_SESSION['telegram_user']) || empty($_SESSION['telegram_user'])) {
    sendError('Необходима авторизация через Telegram');
  }

  $input = json_decode(file_get_contents('php://input'), true);
  $productId = $input['product_id'] ?? '';
  $rating = intval($input['rating'] ?? 0);
  $text = trim($input['text'] ?? '');

  // Логируем полученные данные
  error_log("Product review API - Received data: " . json_encode([
    'product_id' => $productId,
    'rating_raw' => $input['rating'] ?? 'not_set',
    'rating_parsed' => $rating,
    'text_length' => strlen($text)
  ]));

  // Валидация
  if (empty($productId)) {
    sendError('ID товара обязателен');
  }

  if ($rating < 1 || $rating > 5) {
    sendError('Оценка должна быть от 1 до 5 звезд');
  }

  if (strlen($text) < 10) {
    sendError('Текст отзыва должен содержать минимум 10 символов');
  }

  if (strlen($text) > 1000) {
    sendError('Текст отзыва не должен превышать 1000 символов');
  }

  $telegramUser = $_SESSION['telegram_user'];

  try {
    // Проверяем, не оставлял ли пользователь уже отзыв на этот товар
    $checkSql = "
            SELECT id FROM product_reviews 
            WHERE product_id = :product_id AND telegram_user_id = :telegram_user_id
        ";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $checkStmt->bindValue(':telegram_user_id', $telegramUser['id'], PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->fetch()) {
      sendError('Вы уже оставляли отзыв на этот товар');
    }

    // Проверяем, существует ли товар
    $productSql = "SELECT id FROM products WHERE id = :product_id";
    $productStmt = $pdo->prepare($productSql);
    $productStmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $productStmt->execute();

    if (!$productStmt->fetch()) {
      sendError('Товар не найден');
    }

    // Сохраняем или обновляем информацию о пользователе Telegram
    $userSql = "
            INSERT INTO telegram_users (
                telegram_id, telegram_username, telegram_first_name, 
                telegram_last_name, telegram_avatar, created_at, updated_at
            ) VALUES (
                :telegram_id, :telegram_username, :telegram_first_name,
                :telegram_last_name, :telegram_avatar, NOW(), NOW()
            ) ON DUPLICATE KEY UPDATE
                telegram_username = VALUES(telegram_username),
                telegram_first_name = VALUES(telegram_first_name),
                telegram_last_name = VALUES(telegram_last_name),
                telegram_avatar = VALUES(telegram_avatar),
                updated_at = NOW()
        ";

    $userStmt = $pdo->prepare($userSql);
    $userStmt->bindValue(':telegram_id', $telegramUser['id'], PDO::PARAM_INT);
    $userStmt->bindValue(':telegram_username', $telegramUser['username'] ?? '', PDO::PARAM_STR);
    $userStmt->bindValue(':telegram_first_name', $telegramUser['first_name'] ?? '', PDO::PARAM_STR);
    $userStmt->bindValue(':telegram_last_name', $telegramUser['last_name'] ?? '', PDO::PARAM_STR);
    $userStmt->bindValue(':telegram_avatar', $telegramUser['photo_url'] ?? '', PDO::PARAM_STR);
    $userStmt->execute();

    // Добавляем отзыв
    $reviewSql = "
            INSERT INTO product_reviews (
                product_id, telegram_user_id, rating, text, status, created_at
            ) VALUES (
                :product_id, :telegram_user_id, :rating, :text, 'pending', NOW()
            )
        ";

    $reviewStmt = $pdo->prepare($reviewSql);
    $reviewStmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $reviewStmt->bindValue(':telegram_user_id', $telegramUser['id'], PDO::PARAM_INT);
    $reviewStmt->bindValue(':rating', $rating, PDO::PARAM_INT);
    $reviewStmt->bindValue(':text', $text, PDO::PARAM_STR);

    // Логируем данные перед сохранением
    error_log("Product review API - Saving to database: " . json_encode([
      'product_id' => $productId,
      'telegram_user_id' => $telegramUser['id'],
      'rating' => $rating,
      'text_length' => strlen($text)
    ]));

    $reviewStmt->execute();

    $reviewId = $pdo->lastInsertId();

    // Отправляем уведомление в Telegram (если настроен бот)
    sendTelegramNotification($reviewId, $productId, $rating, $text, $telegramUser);

    sendSuccess(['review_id' => $reviewId, 'message' => 'Отзыв отправлен на модерацию']);
  } catch (PDOException $e) {
    error_log("Database error in handleAddReview: " . $e->getMessage());
    sendError('Ошибка при сохранении отзыва');
  }
}

/**
 * Лайк/анлайк отзыва
 */
function handleLikeReview($pdo)
{
  // Проверяем авторизацию
  if (!isset($_SESSION['telegram_user']) || empty($_SESSION['telegram_user'])) {
    sendError('Необходима авторизация через Telegram');
  }

  $input = json_decode(file_get_contents('php://input'), true);
  $reviewId = intval($input['review_id'] ?? 0);

  if ($reviewId <= 0) {
    sendError('Неверный ID отзыва');
  }

  $telegramUser = $_SESSION['telegram_user'];

  try {
    // Проверяем, существует ли отзыв
    $checkSql = "SELECT id FROM product_reviews WHERE id = :review_id AND status = 'approved'";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
    $checkStmt->execute();

    if (!$checkStmt->fetch()) {
      sendError('Отзыв не найден');
    }

    // Проверяем, лайкал ли пользователь уже этот отзыв
    $likeSql = "
            SELECT id FROM product_review_likes 
            WHERE review_id = :review_id AND telegram_user_id = :telegram_user_id
        ";
    $likeStmt = $pdo->prepare($likeSql);
    $likeStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
    $likeStmt->bindValue(':telegram_user_id', $telegramUser['id'], PDO::PARAM_INT);
    $likeStmt->execute();

    $existingLike = $likeStmt->fetch();

    if ($existingLike) {
      // Убираем лайк
      $deleteSql = "DELETE FROM product_review_likes WHERE id = :like_id";
      $deleteStmt = $pdo->prepare($deleteSql);
      $deleteStmt->bindValue(':like_id', $existingLike['id'], PDO::PARAM_INT);
      $deleteStmt->execute();

      $action = 'unliked';
    } else {
      // Добавляем лайк
      $insertSql = "
                INSERT INTO product_review_likes (review_id, telegram_user_id, created_at)
                VALUES (:review_id, :telegram_user_id, NOW())
            ";
      $insertStmt = $pdo->prepare($insertSql);
      $insertStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
      $insertStmt->bindValue(':telegram_user_id', $telegramUser['id'], PDO::PARAM_INT);
      $insertStmt->execute();

      $action = 'liked';
    }

    // Получаем обновленное количество лайков
    $countSql = "
            SELECT COUNT(*) as count FROM product_review_likes WHERE review_id = :review_id
        ";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
    $countStmt->execute();
    $likesCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];

    sendSuccess([
      'action' => $action,
      'likes_count' => intval($likesCount)
    ]);
  } catch (PDOException $e) {
    error_log("Database error in handleLikeReview: " . $e->getMessage());
    sendError('Ошибка при обработке лайка');
  }
}

/**
 * Жалоба на отзыв
 */
function handleReportReview($pdo)
{
  // Проверяем авторизацию
  if (!isset($_SESSION['telegram_user']) || empty($_SESSION['telegram_user'])) {
    sendError('Необходима авторизация через Telegram');
  }

  $input = json_decode(file_get_contents('php://input'), true);
  $reviewId = intval($input['review_id'] ?? 0);
  $reason = trim($input['reason'] ?? '');

  if ($reviewId <= 0) {
    sendError('Неверный ID отзыва');
  }

  $telegramUser = $_SESSION['telegram_user'];

  try {
    // Проверяем, существует ли отзыв
    $checkSql = "SELECT id FROM product_reviews WHERE id = :review_id";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
    $checkStmt->execute();

    if (!$checkStmt->fetch()) {
      sendError('Отзыв не найден');
    }

    // Проверяем, не жаловался ли пользователь уже на этот отзыв
    $reportSql = "
            SELECT id FROM product_review_reports 
            WHERE review_id = :review_id AND telegram_user_id = :telegram_user_id
        ";
    $reportStmt = $pdo->prepare($reportSql);
    $reportStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
    $reportStmt->bindValue(':telegram_user_id', $telegramUser['id'], PDO::PARAM_INT);
    $reportStmt->execute();

    if ($reportStmt->fetch()) {
      sendError('Вы уже жаловались на этот отзыв');
    }

    // Добавляем жалобу
    $insertSql = "
            INSERT INTO product_review_reports (
                review_id, telegram_user_id, reason, status, created_at
            ) VALUES (
                :review_id, :telegram_user_id, :reason, 'pending', NOW()
            )
        ";
    $insertStmt = $pdo->prepare($insertSql);
    $insertStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
    $insertStmt->bindValue(':telegram_user_id', $telegramUser['id'], PDO::PARAM_INT);
    $insertStmt->bindValue(':reason', $reason, PDO::PARAM_STR);
    $insertStmt->execute();

    // Отправляем уведомление в Telegram (если настроен бот)
    sendTelegramReportNotification($reviewId, $reason, $telegramUser);

    sendSuccess(['message' => 'Жалоба отправлена']);
  } catch (PDOException $e) {
    error_log("Database error in handleReportReview: " . $e->getMessage());
    sendError('Ошибка при отправке жалобы');
  }
}

/**
 * Отправка уведомления о новом отзыве в Telegram
 */
function sendTelegramNotification($reviewId, $productId, $rating, $text, $telegramUser)
{
  // Здесь можно добавить отправку уведомления в Telegram бот для модерации
  // Пока просто логируем
  error_log("New product review: ID={$reviewId}, Product={$productId}, Rating={$rating}, User=@{$telegramUser['username']}");
}

/**
 * Отправка уведомления о жалобе в Telegram
 */
function sendTelegramReportNotification($reviewId, $reason, $telegramUser)
{
  // Здесь можно добавить отправку уведомления в Telegram бот для модерации
  // Пока просто логируем
  error_log("Product review report: Review ID={$reviewId}, Reason={$reason}, User=@{$telegramUser['username']}");
}

/**
 * Форматирование даты
 */
function formatDate($dateString)
{
  $date = new DateTime($dateString);
  $now = new DateTime();
  $diff = $now->diff($date);

  if ($diff->days == 0) {
    if ($diff->h == 0) {
      if ($diff->i == 0) {
        return 'только что';
      }
      return $diff->i . ' мин. назад';
    }
    return $diff->h . ' ч. назад';
  } elseif ($diff->days == 1) {
    return 'вчера';
  } elseif ($diff->days < 7) {
    return $diff->days . ' дн. назад';
  } else {
    return $date->format('d.m.Y');
  }
}

/**
 * Отправка успешного ответа
 */
function sendSuccess($data)
{
  echo json_encode([
    'success' => true,
    'data' => $data
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

/**
 * Проверка существующего отзыва пользователя
 */
function handleCheckUserReview($pdo)
{
  // Проверяем авторизацию
  if (!isset($_SESSION['telegram_user']) || empty($_SESSION['telegram_user'])) {
    sendError('Необходима авторизация через Telegram');
  }

  $productId = $_GET['product_id'] ?? '';

  if (empty($productId)) {
    sendError('ID товара обязателен');
  }

  $telegramUser = $_SESSION['telegram_user'];

  try {
    // Проверяем, оставлял ли пользователь отзыв на этот товар
    $sql = "
            SELECT id FROM product_reviews 
            WHERE product_id = :product_id AND telegram_user_id = :telegram_user_id
        ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':product_id', $productId, PDO::PARAM_STR);
    $stmt->bindValue(':telegram_user_id', $telegramUser['id'], PDO::PARAM_INT);
    $stmt->execute();

    $hasReview = $stmt->fetch() !== false;

    sendSuccess([
      'has_review' => $hasReview
    ]);
  } catch (PDOException $e) {
    error_log("Database error in handleCheckUserReview: " . $e->getMessage());
    sendError('Ошибка при проверке отзыва');
  }
}

/**
 * Отправка ошибки
 */
function sendError($message)
{
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $message
  ], JSON_UNESCAPED_UNICODE);
  exit;
}
?>