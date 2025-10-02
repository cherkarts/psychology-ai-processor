<?php

class AIGenerationTask
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db->getConnection();
  }

  /**
   * Создать новую задачу генерации
   */
  public function createTask($data)
  {
    $taskId = $this->generateTaskId();

    $sql = "INSERT INTO ai_generation_tasks (
            task_id, title, topic, keywords, category_id, target_audience, 
            tone, word_count, include_faq, include_quotes, include_internal_links,
            include_table_of_contents, seo_optimization, priority, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->db->prepare($sql);
    $keywords = is_array($data['keywords']) ? json_encode($data['keywords']) : $data['keywords'];

    $result = $stmt->execute([
      $taskId,
      $data['title'],
      $data['topic'],
      $keywords,
      $data['category_id'] ?? null,
      $data['target_audience'] ?? null,
      $data['tone'] ?? 'professional',
      $data['word_count'] ?? 1500,
      (bool) ($data['include_faq'] ?? false),
      (bool) ($data['include_quotes'] ?? true),
      (bool) ($data['include_internal_links'] ?? true),
      (bool) ($data['include_table_of_contents'] ?? true),
      (bool) ($data['seo_optimization'] ?? true),
      $data['priority'] ?? 'normal',
      $data['created_by'] ?? null
    ]);

    if ($result) {
      $taskId = $this->db->lastInsertId();
      $this->logAction($taskId, 'task_created', 'Задача создана', $data);
      return ['success' => true, 'task_id' => $taskId, 'api_task_id' => $taskId];
    }

    return ['success' => false, 'message' => 'Ошибка создания задачи'];
  }

  /**
   * Получить задачу по ID
   */
  public function getTaskById($id)
  {
    $sql = "SELECT * FROM ai_generation_tasks WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Получить задачу по API task_id
   */
  public function getTaskByApiId($taskId)
  {
    $sql = "SELECT * FROM ai_generation_tasks WHERE task_id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$taskId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Получить все задачи с пагинацией
   */
  public function getTasks($page = 1, $status = 'all', $limit = 20)
  {
    $offset = ($page - 1) * $limit;

    $whereConditions = [];
    $params = [];

    if ($status !== 'all') {
      $whereConditions[] = "status = ?";
      $params[] = $status;
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    $sql = "SELECT t.*, c.name as category_name, u.name as created_by_name 
                FROM ai_generation_tasks t 
                LEFT JOIN article_categories c ON t.category_id = c.id 
                LEFT JOIN users u ON t.created_by = u.id 
                {$whereClause} 
                ORDER BY t.created_at DESC 
                LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Получить общее количество
    $countSql = "SELECT COUNT(*) FROM ai_generation_tasks {$whereClause}";
    $countStmt = $this->db->prepare($countSql);
    $countParams = array_slice($params, 0, -2);
    $countStmt->execute($countParams);
    $total = $countStmt->fetchColumn();

    return [
      'tasks' => $tasks,
      'total' => $total,
      'pages' => ceil($total / $limit),
      'current_page' => $page
    ];
  }

  /**
   * Обновить статус задачи
   */
  public function updateTaskStatus($taskId, $status, $additionalData = [])
  {
    $sql = "UPDATE ai_generation_tasks SET 
                status = ?, 
                updated_at = CURRENT_TIMESTAMP";

    $params = [$status];

    if ($status === 'processing' && !isset($additionalData['started_at'])) {
      $sql .= ", started_at = CURRENT_TIMESTAMP";
    } elseif ($status === 'completed' || $status === 'failed') {
      $sql .= ", completed_at = CURRENT_TIMESTAMP";
    }

    if (isset($additionalData['error_message'])) {
      $sql .= ", error_message = ?";
      $params[] = $additionalData['error_message'];
    }

    if (isset($additionalData['api_response'])) {
      $sql .= ", api_response = ?";
      $params[] = json_encode($additionalData['api_response']);
    }

    if (isset($additionalData['generated_article_id'])) {
      $sql .= ", generated_article_id = ?";
      $params[] = $additionalData['generated_article_id'];
    }

    $sql .= " WHERE task_id = ?";
    $params[] = $taskId;

    $stmt = $this->db->prepare($sql);
    $result = $stmt->execute($params);

    if ($result) {
      $this->logAction($taskId, 'status_updated', "Статус изменен на: {$status}", $additionalData);
    }

    return $result;
  }

  /**
   * Получить задачи для обработки
   */
  public function getPendingTasks($limit = 5)
  {
    $sql = "SELECT * FROM ai_generation_tasks 
                WHERE status = 'pending' 
                AND retry_count < max_retries 
                ORDER BY priority DESC, created_at ASC 
                LIMIT ?";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Увеличить счетчик попыток
   */
  public function incrementRetryCount($taskId)
  {
    $sql = "UPDATE ai_generation_tasks SET 
                retry_count = retry_count + 1,
                updated_at = CURRENT_TIMESTAMP 
                WHERE task_id = ?";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$taskId]);
  }

  /**
   * Удалить задачу
   */
  public function deleteTask($taskId)
  {
    $sql = "DELETE FROM ai_generation_tasks WHERE task_id = ?";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$taskId]);
  }

  /**
   * Получить статистику задач
   */
  public function getTaskStats()
  {
    $sql = "SELECT 
                status,
                COUNT(*) as count,
                AVG(TIMESTAMPDIFF(MINUTE, created_at, COALESCE(completed_at, NOW()))) as avg_duration
                FROM ai_generation_tasks 
                GROUP BY status";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($stats as $stat) {
      $result[$stat['status']] = [
        'count' => (int) $stat['count'],
        'avg_duration' => round($stat['avg_duration'], 2)
      ];
    }

    return $result;
  }

  /**
   * Логирование действий
   */
  private function logAction($taskId, $action, $message, $details = null)
  {
    // Приводим taskId к внутреннему числовому ID, если передан API task_id (строка)
    $internalId = $taskId;
    if (!is_numeric($taskId)) {
      $stmtLookup = $this->db->prepare("SELECT id FROM ai_generation_tasks WHERE task_id = ? LIMIT 1");
      $stmtLookup->execute([$taskId]);
      $row = $stmtLookup->fetch(PDO::FETCH_ASSOC);
      $internalId = $row ? (int) $row['id'] : null; // допускаем NULL, чтобы не нарушать FK
    } else {
      $internalId = (int) $taskId;
    }

    $sql = "INSERT INTO ai_generation_logs (task_id, action, message, details) VALUES (?, ?, ?, ?)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      $internalId,
      $action,
      $message,
      $details ? json_encode($details) : null
    ]);
  }

  /**
   * Генерация уникального ID задачи
   */
  private function generateTaskId()
  {
    return uniqid('task_', true) . '_' . time();
  }

  /**
   * Получить настройки генерации
   */
  public function getSettings()
  {
    $sql = "SELECT setting_key, setting_value, description FROM ai_generation_settings";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($settings as $setting) {
      $result[$setting['setting_key']] = [
        'value' => $setting['setting_value'],
        'description' => $setting['description']
      ];
    }

    return $result;
  }

  /**
   * Обновить настройку
   */
  public function updateSetting($key, $value)
  {
    $sql = "UPDATE ai_generation_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([$value, $key]);
  }

  /**
   * Получить промпты
   */
  public function getPrompts($category = null)
  {
    $sql = "SELECT * FROM ai_prompts WHERE is_active = 1";
    $params = [];

    if ($category) {
      $sql .= " AND category = ?";
      $params[] = $category;
    }

    $sql .= " ORDER BY name";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
