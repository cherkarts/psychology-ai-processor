<?php
/**
 * Класс для работы с медитациями
 */
class Meditation
{
  private $db;

  public function __construct()
  {
    $this->db = Database::getInstance();
  }

  /**
   * Получить все медитации с фильтрами
   */
  public function getAll($filters = [])
  {
    $sql = "SELECT m.*, mc.name as category_name, mc.slug as category_slug 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE 1=1";
    $params = [];

    if (!empty($filters['category'])) {
      $sql .= " AND mc.slug = ?";
      $params[] = $filters['category'];
    }

    if (isset($filters['is_free'])) {
      $sql .= " AND m.is_free = ?";
      $params[] = $filters['is_free'];
    }

    if (isset($filters['telegram_required'])) {
      $sql .= " AND m.telegram_required = ?";
      $params[] = $filters['telegram_required'];
    }

    $sql .= " ORDER BY m.created_at DESC";

    if (!empty($filters['limit'])) {
      $sql .= " LIMIT ?";
      $params[] = $filters['limit'];
    }

    return $this->db->fetchAll($sql, $params);
  }

  /**
   * Получить медитацию по slug
   */
  public function getBySlug($slug)
  {
    $sql = "SELECT m.*, mc.name as category_name, mc.slug as category_slug 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE m.slug = ?";

    return $this->db->fetchOne($sql, [$slug]);
  }

  /**
   * Получить медитацию по ID
   */
  public function getById($id)
  {
    $sql = "SELECT m.*, mc.name as category_name, mc.slug as category_slug 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE m.id = ?";

    return $this->db->fetchOne($sql, [$id]);
  }

  /**
   * Получить бесплатные медитации
   */
  public function getFree($limit = null)
  {
    $sql = "SELECT m.*, mc.name as category_name 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE m.is_free = 1 
                ORDER BY m.created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$limit]);
    }

    return $this->db->fetchAll($sql);
  }

  /**
   * Получить медитации по категории
   */
  public function getByCategory($categorySlug, $limit = null)
  {
    $sql = "SELECT m.*, mc.name as category_name 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE mc.slug = ? 
                ORDER BY m.created_at DESC";

    if ($limit) {
      $sql .= " LIMIT ?";
      return $this->db->fetchAll($sql, [$categorySlug, $limit]);
    }

    return $this->db->fetchAll($sql, [$categorySlug]);
  }

  /**
   * Создать новую медитацию
   */
  public function create($data)
  {
    $meditationId = $this->db->insert('meditations', $data);

    // Логируем действие
    $this->db->logActivity('meditation_created', 'meditation', $meditationId);

    return $meditationId;
  }

  /**
   * Обновить медитацию
   */
  public function update($id, $data)
  {
    $result = $this->db->update('meditations', $data, 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('meditation_updated', 'meditation', $id);

    return $result;
  }

  /**
   * Удалить медитацию
   */
  public function delete($id)
  {
    $result = $this->db->delete('meditations', 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('meditation_deleted', 'meditation', $id);

    return $result;
  }

  /**
   * Увеличить счетчик лайков
   */
  public function incrementLikes($id)
  {
    $sql = "UPDATE meditations SET likes = likes + 1 WHERE id = ?";
    return $this->db->execute($sql, [$id]);
  }

  /**
   * Увеличить счетчик избранного
   */
  public function incrementFavorites($id)
  {
    $sql = "UPDATE meditations SET favorites = favorites + 1 WHERE id = ?";
    return $this->db->execute($sql, [$id]);
  }

  /**
   * Получить все категории медитаций
   */
  public function getCategories()
  {
    return $this->db->fetchAll("SELECT * FROM meditation_categories WHERE is_active = 1 ORDER BY sort_order, name");
  }

  /**
   * Получить категорию по slug
   */
  public function getCategoryBySlug($slug)
  {
    return $this->db->fetchOne("SELECT * FROM meditation_categories WHERE slug = ? AND is_active = 1", [$slug]);
  }

  /**
   * Создать категорию медитаций
   */
  public function createCategory($data)
  {
    $categoryId = $this->db->insert('meditation_categories', $data);

    // Логируем действие
    $this->db->logActivity('meditation_category_created', 'meditation_category', $categoryId);

    return $categoryId;
  }

  /**
   * Обновить категорию медитаций
   */
  public function updateCategory($id, $data)
  {
    $result = $this->db->update('meditation_categories', $data, 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('meditation_category_updated', 'meditation_category', $id);

    return $result;
  }

  /**
   * Удалить категорию медитаций
   */
  public function deleteCategory($id)
  {
    $result = $this->db->delete('meditation_categories', 'id = ?', [$id]);

    // Логируем действие
    $this->db->logActivity('meditation_category_deleted', 'meditation_category', $id);

    return $result;
  }

  /**
   * Поиск медитаций
   */
  public function search($query, $limit = 20)
  {
    $sql = "SELECT m.*, mc.name as category_name 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                WHERE (m.title LIKE ? OR m.description LIKE ? OR m.subtitle LIKE ?) 
                ORDER BY m.created_at DESC 
                LIMIT ?";

    $searchTerm = "%$query%";
    return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
  }

  /**
   * Получить статистику медитаций
   */
  public function getStats()
  {
    $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_free = 1 THEN 1 END) as free_count,
                    COUNT(CASE WHEN is_free = 0 THEN 1 END) as paid_count,
                    COUNT(CASE WHEN telegram_required = 1 THEN 1 END) as telegram_required_count,
                    SUM(likes) as total_likes,
                    SUM(favorites) as total_favorites,
                    AVG(duration) as avg_duration
                FROM meditations";

    return $this->db->fetchOne($sql);
  }

  /**
   * Получить популярные медитации
   */
  public function getPopular($limit = 10)
  {
    $sql = "SELECT m.*, mc.name as category_name 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                ORDER BY m.likes DESC, m.favorites DESC 
                LIMIT ?";

    return $this->db->fetchAll($sql, [$limit]);
  }

  /**
   * Получить последние медитации
   */
  public function getRecent($limit = 10)
  {
    $sql = "SELECT m.*, mc.name as category_name 
                FROM meditations m 
                LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                ORDER BY m.created_at DESC 
                LIMIT ?";

    return $this->db->fetchAll($sql, [$limit]);
  }
}
?>