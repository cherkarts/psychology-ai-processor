<?php
/**
 * API для модерации отзывов товаров
 * Позволяет одобрять, отклонять отзывы и добавлять комментарии модератора
 */

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/auth.php';
require_once '../../includes/Database.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Необходима авторизация']);
  exit;
}

// Проверяем права доступа
if (!hasPermission('reviews')) {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Недостаточно прав']);
  exit;
}

try {
  $db = Database::getInstance();
  $pdo = $db->getConnection();

  $input = json_decode(file_get_contents('php://input'), true);
  $reviewId = intval($input['review_id'] ?? 0);
  $action = $input['action'] ?? '';
  $comment = trim($input['comment'] ?? '');

  // Валидация
  if ($reviewId <= 0) {
    throw new Exception('Неверный ID отзыва');
  }

  if (!in_array($action, ['approved', 'rejected', 'delete'])) {
    throw new Exception('Неверное действие');
  }

  // Проверяем, существует ли отзыв
  $checkSql = "SELECT id, status FROM product_reviews WHERE id = :review_id";
  $checkStmt = $pdo->prepare($checkSql);
  $checkStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
  $checkStmt->execute();

  $review = $checkStmt->fetch(PDO::FETCH_ASSOC);
  if (!$review) {
    throw new Exception('Отзыв не найден');
  }

  if ($action === 'delete') {
    // Удаляем отзыв (включая связанные данные)
    $pdo->beginTransaction();

    try {
      // Удаляем лайки отзыва
      $deleteLikesSql = "DELETE FROM product_review_likes WHERE review_id = :review_id";
      $deleteLikesStmt = $pdo->prepare($deleteLikesSql);
      $deleteLikesStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
      $deleteLikesStmt->execute();

      // Удаляем жалобы на отзыв
      $deleteReportsSql = "DELETE FROM product_review_reports WHERE review_id = :review_id";
      $deleteReportsStmt = $pdo->prepare($deleteReportsSql);
      $deleteReportsStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
      $deleteReportsStmt->execute();

      // Удаляем сам отзыв
      $deleteReviewSql = "DELETE FROM product_reviews WHERE id = :review_id";
      $deleteReviewStmt = $pdo->prepare($deleteReviewSql);
      $deleteReviewStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
      $deleteReviewStmt->execute();

      $pdo->commit();

      // Логируем действие (если таблица существует)
      try {
        $logSql = "
              INSERT INTO admin_logs (
                  admin_user, action, target_type, target_id, details, created_at
              ) VALUES (
                  :admin_user, :action, :target_type, :target_id, :details, NOW()
              )
          ";

        $logStmt = $pdo->prepare($logSql);
        $logStmt->bindValue(':admin_user', $_SESSION['admin_user']['username'], PDO::PARAM_STR);
        $logStmt->bindValue(':action', 'delete_review', PDO::PARAM_STR);
        $logStmt->bindValue(':target_type', 'product_review', PDO::PARAM_STR);
        $logStmt->bindValue(':target_id', $reviewId, PDO::PARAM_INT);
        $logStmt->bindValue(':details', json_encode([
          'comment' => $comment
        ]), PDO::PARAM_STR);
        $logStmt->execute();
      } catch (Exception $logError) {
        // Игнорируем ошибку логирования, если таблица не существует
        error_log("Admin logs table not found, skipping log entry: " . $logError->getMessage());
      }

      echo json_encode([
        'success' => true,
        'message' => 'Отзыв успешно удален'
      ]);

    } catch (Exception $e) {
      $pdo->rollBack();
      throw $e;
    }

  } else {
    // Обычная модерация (одобрить/отклонить)
    if ($review['status'] !== 'pending') {
      throw new Exception('Отзыв уже обработан');
    }

    // Обновляем статус отзыва
    $updateSql = "
          UPDATE product_reviews 
          SET status = :status, 
              moderator_comment = :comment,
              updated_at = NOW()
          WHERE id = :review_id
      ";

    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->bindValue(':status', $action, PDO::PARAM_STR);
    $updateStmt->bindValue(':comment', $comment, PDO::PARAM_STR);
    $updateStmt->bindValue(':review_id', $reviewId, PDO::PARAM_INT);
    $updateStmt->execute();

    // Логируем действие (если таблица существует)
    try {
      $logSql = "
            INSERT INTO admin_logs (
                admin_user, action, target_type, target_id, details, created_at
            ) VALUES (
                :admin_user, :action, :target_type, :target_id, :details, NOW()
            )
        ";

      $logStmt = $pdo->prepare($logSql);
      $logStmt->bindValue(':admin_user', $_SESSION['admin_user']['username'], PDO::PARAM_STR);
      $logStmt->bindValue(':action', "moderate_review_{$action}", PDO::PARAM_STR);
      $logStmt->bindValue(':target_type', 'product_review', PDO::PARAM_STR);
      $logStmt->bindValue(':target_id', $reviewId, PDO::PARAM_INT);
      $logStmt->bindValue(':details', json_encode([
        'action' => $action,
        'comment' => $comment
      ]), PDO::PARAM_STR);
      $logStmt->execute();
    } catch (Exception $logError) {
      // Игнорируем ошибку логирования, если таблица не существует
      error_log("Admin logs table not found, skipping log entry: " . $logError->getMessage());
    }

    echo json_encode([
      'success' => true,
      'message' => 'Отзыв успешно обработан'
    ]);
  }

} catch (Exception $e) {
  error_log("Moderate product review error: " . $e->getMessage());
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'error' => $e->getMessage()
  ]);
}
?>