<?php
/**
 * Класс для работы с отзывами
 */
class Review
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Получить все отзывы с фильтрами
   */
  public function getAll($filters = [])
  {
    $sql = "SELECT * FROM reviews WHERE 1=1";
    $params = [];

    if (!empty($filters['status'])) {
      $sql .= " AND status = ?";
      $params[] = $filters['status'];
    }

    if (!empty($filters['type'])) {
      $sql .= " AND type = ?";
      $params[] = $filters['type'];
    }

    if (!empty($filters['rating'])) {
      $sql .= " AND rating = ?";
      $params[] = $filters['rating'];
    }

    $sql .= " ORDER BY created_at DESC";

    if (!empty($filters['limit'])) {
      $sql .= " LIMIT ?";
      $params[] = $filters['limit'];
    }

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Получить одобренные отзывы для публикации
   */
  public function getApproved($limit = null)
  {
    $sql = "SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$limit]);
    }

    return $this->db->fetchAll($sql);
  }

  /**
   * Получить отзыв по ID
   */
  public function getById($id)
  {
    return $this->db->fetchOne("SELECT * FROM reviews WHERE id = ?", [$id]);
  }

  /**
   * Валидация данных отзыва
   */
  public function validate($data)
  {
    $errors = [];

    // Проверяем обязательные поля
    if (empty($data['name'])) {
      $errors[] = 'Имя обязательно';
    }

    if (empty($data['text'])) {
      $errors[] = 'Текст отзыва обязателен';
    }

    if (empty($data['rating'])) {
      $errors[] = 'Оценка обязательна';
    }

    // Проверяем длину текста
    if (!empty($data['text']) && strlen($data['text']) < 10) {
      $errors[] = 'Текст отзыва должен содержать минимум 10 символов';
    }

    if (!empty($data['text']) && strlen($data['text']) > 2000) {
      $errors[] = 'Текст отзыва не должен превышать 2000 символов';
    }

    // Проверяем рейтинг
    if (!empty($data['rating']) && (!is_numeric($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5)) {
      $errors[] = 'Оценка должна быть от 1 до 5';
    }

    return $errors;
  }

  /**
   * Создать новый отзыв
   */
  public function create($data)
  {
    // Обработка JSON полей
    if (!empty($data['tags']) && is_array($data['tags'])) {
      $data['tags'] = json_encode($data['tags']);
    }

    // Добавляем IP адрес и User Agent
    $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
    $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;

    $reviewId = $this->db->insert('reviews', $data);

    // Логируем действие (если метод существует)
    if (method_exists($this->db, 'logActivity')) {
      $this->db->logActivity('review_created', 'review', $reviewId);
    }

    return $reviewId;
  }

  /**
   * Обновить отзыв
   */
  public function update($id, $data)
  {
    // Обработка JSON полей
    if (!empty($data['tags']) && is_array($data['tags'])) {
      $data['tags'] = json_encode($data['tags']);
    }

    $result = $this->db->update('reviews', $data, 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('review_updated', 'review', $id);

    return $result;
  }

  /**
   * Одобрить отзыв
   */
  public function approve($id, $approvedBy = null)
  {
    $data = [
      'status' => 'approved',
      'approved_at' => date('Y-m-d H:i:s'),
      'approved_by' => $approvedBy
    ];

    $result = $this->update($id, $data);

    // Логируем действие
    $this->db->logActivity('review_approved', 'review', $id);

    return $result;
  }

  /**
   * Отклонить отзыв
   */
  public function reject($id, $rejectedBy = null)
  {
    $data = [
      'status' => 'rejected',
      'approved_by' => $rejectedBy
    ];

    $result = $this->update($id, $data);

    // Логируем действие
    $this->db->logActivity('review_rejected', 'review', $id);

    return $result;
  }

  /**
   * Удалить отзыв
   */
  public function delete($id)
  {
    $result = $this->db->delete('reviews', 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('review_deleted', 'review', $id);

    return $result;
  }

  /**
   * Получить статистику отзывов
   */
  public function getStats()
  {
    $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected,
                    AVG(rating) as avg_rating,
                    COUNT(CASE WHEN type = 'text' THEN 1 END) as text_reviews,
                    COUNT(CASE WHEN type = 'photo' THEN 1 END) as photo_reviews,
                    COUNT(CASE WHEN type = 'video' THEN 1 END) as video_reviews
                FROM reviews";

    return $this->db->fetchOne($sql);
  }

  /**
   * Получить отзывы по рейтингу
   */
  public function getByRating($rating, $limit = null)
  {
    $sql = "SELECT * FROM reviews WHERE rating = ? AND status = 'approved' ORDER BY created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$rating, $limit]);
    }

    return $this->db->fetchAll($sql, [$rating]);
  }

  /**
   * Получить отзывы по типу
   */
  public function getByType($type, $limit = null)
  {
    $sql = "SELECT * FROM reviews WHERE type = ? AND status = 'approved' ORDER BY created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$type, $limit]);
    }

    return $this->db->fetchAll($sql, [$type]);
  }

  /**
   * Проверить, оставлял ли пользователь отзыв
   */
  public function hasUserReviewed($email, $timeLimit = 86400) // 24 часа
  {
    $sql = "SELECT COUNT(*) FROM reviews 
                WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)";

    $count = $this->db->fetchColumn($sql, [$email, $timeLimit]);
    return $count > 0;
  }

  /**
   * Проверить, существует ли отзыв с таким email
   */
  public function existsByEmail($email)
  {
    $sql = "SELECT COUNT(*) FROM reviews WHERE email = ?";
    $count = $this->db->fetchColumn($sql, [$email]);
    return $count > 0;
  }

  /**
   * Получить последние отзывы
   */
  public function getRecent($limit = 10)
  {
    $sql = "SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC LIMIT ?";
    return $this->db->fetchAll($sql, [$limit]);
  }
}
?>