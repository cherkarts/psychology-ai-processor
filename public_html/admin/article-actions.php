<?php
/**
 * Функции для работы со статьями в админке
 */

function handleArticleAction($data)
{
  $action = $data['action'] ?? '';
  $articleId = $data['article_id'] ?? '';

  // Отладочная информация
  error_log("handleArticleAction called with action: " . $action . ", articleId: " . $articleId);

  if (empty($articleId)) {
    error_log("handleArticleAction: articleId is empty");
    return ['success' => false, 'message' => 'Требуется указать ID статьи.'];
  }

  $db = getAdminDB();
  if (!$db) {
    error_log("handleArticleAction: getAdminDB() returned false");
    return ['success' => false, 'message' => 'Ошибка подключения к базе данных.'];
  }

  if ($db) {
    try {
      // Always use slug for article identification to maintain consistency
      // This avoids issues with numeric IDs vs slugs
      $idField = 'slug';

      switch ($action) {
        case 'update_status':
          $status = $data['status'] ?? '';
          $isPublished = $status === 'published' ? 1 : 0;

          // First check if the article exists
          $stmt = $db->prepare("SELECT id, slug, title FROM articles WHERE slug = ?");
          $stmt->execute([$articleId]);
          $article = $stmt->fetch();

          if (!$article) {
            return ['success' => false, 'message' => "Статья не найдена: {$articleId}"];
          }

          // Update the article
          $stmt = $db->prepare("UPDATE articles SET is_published = ?, updated_at = NOW() WHERE slug = ?");
          $result = $stmt->execute([$isPublished, $articleId]);

          if ($result) {
            logAdminActivity('update', "Article '{$article['title']}' status changed to {$status}");
            $statusText = $status === 'published' ? 'опубликована' : 'снята с публикации';
            return ['success' => true, 'message' => "Статья успешно {$statusText}."];
          } else {
            $errorInfo = $stmt->errorInfo();
            error_log("SQL Error: " . implode(' ', $errorInfo));
            return ['success' => false, 'message' => "Ошибка обновления статуса статьи. SQL Error: {$errorInfo[2]}"];
          }

        case 'delete':
          error_log("handleArticleAction: Attempting to delete article with slug: " . $articleId);
          $stmt = $db->prepare("DELETE FROM articles WHERE slug = ?");
          $result = $stmt->execute([$articleId]);

          if ($result) {
            error_log("handleArticleAction: Article deleted successfully");
            logAdminActivity('delete', "Article #{$articleId} deleted");
            return ['success' => true, 'message' => 'Статья успешно удалена.'];
          } else {
            $errorInfo = $stmt->errorInfo();
            error_log("handleArticleAction: SQL Error: " . implode(' ', $errorInfo));
            return ['success' => false, 'message' => "Ошибка удаления статьи. SQL Error: {$errorInfo[2]}"];
          }

        default:
          return ['success' => false, 'message' => 'Некорректное действие.'];
      }
    } catch (PDOException $e) {
      error_log("Article action failed: " . $e->getMessage());
      error_log("PDO Error Code: " . $e->getCode());
      error_log("SQL State: " . ($e->errorInfo[0] ?? 'Unknown'));
      return ['success' => false, 'message' => "Ошибка базы данных: {$e->getMessage()}"];
    }
  } else {
    return ['success' => false, 'message' => 'Ошибка подключения к базе данных.'];
  }
}
?>