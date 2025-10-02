<?php
/**
 * Класс для работы с базой данных
 */
class Database
{
  private $pdo;
  private static $instance = null;

  private function __construct()
  {
    // Всегда получаем массив конфигурации; include возвращает массив даже при повторных вызовах
    $config = include __DIR__ . '/../config.php';

    // Определяем окружение
    $environment = $config['environment'] ?? 'development';

    // Выбираем настройки базы данных в зависимости от окружения
    if ($environment === 'production' && isset($config['production']['database'])) {
      $dbConfig = $config['production']['database'];
    } else {
      $dbConfig = $config['database'];
    }

    $host = $dbConfig['host'];
    $dbname = $dbConfig['dbname'];
    $username = $dbConfig['username'];
    $password = $dbConfig['password'];
    $charset = $dbConfig['charset'] ?? 'utf8mb4';
    $options = $dbConfig['options'] ?? [];

    try {
      // Отладочная информация
      $port = $dbConfig['port'] ?? 3306;
      error_log("Подключение к БД: host=$host, port=$port, dbname=$dbname");

      // Всегда используем TCP-подключение; не используем unix-сокеты на продакшене
      $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
      if (trim($charset) !== '') {
        $dsn .= ";charset=$charset";
      }
      $this->pdo = new PDO($dsn, $username, $password, $options);

      // Устанавливаем кодировку только если указана
      if (trim($charset) !== '') {
        try {
          $this->pdo->exec("SET NAMES $charset");
        } catch (Throwable $t) { /* ignore */
        }
      }

      error_log("База данных '$dbname' выбрана успешно");
    } catch (PDOException $e) {
      error_log("Ошибка подключения к БД: " . $e->getMessage());
      die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function getConnection()
  {
    return $this->pdo;
  }

  /**
   * Выбрать базу данных
   */
  public function selectDatabase($dbname)
  {
    try {
      $this->pdo->exec("USE `$dbname`");
      return true;
    } catch (PDOException $e) {
      error_log("Ошибка выбора базы данных: " . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Выполнить запрос с параметрами
   */
  public function query($sql, $params = [])
  {
    try {
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt;
    } catch (PDOException $e) {
      error_log("Database error: " . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Выполнить запрос без возврата результата
   */
  public function execute($sql, $params = [])
  {
    try {
      $stmt = $this->pdo->prepare($sql);
      return $stmt->execute($params);
    } catch (PDOException $e) {
      error_log("Database error: " . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Подготовить запрос
   */
  public function prepare($sql)
  {
    try {
      return $this->pdo->prepare($sql);
    } catch (PDOException $e) {
      error_log("Database error: " . $e->getMessage());
      throw $e;
    }
  }

  /**
   * Получить одну запись
   */
  public function fetchOne($sql, $params = [])
  {
    $stmt = $this->query($sql, $params);
    return $stmt->fetch();
  }

  /**
   * Получить все записи
   */
  public function fetchAll($sql, $params = [])
  {
    $stmt = $this->query($sql, $params);
    return $stmt->fetchAll();
  }

  /**
   * Получить все записи с кэшированием
   * @param string $sql SQL запрос
   * @param array $params Параметры запроса
   * @param int $ttl Время жизни кэша в секундах (по умолчанию 3600 = 1 час)
   * @return array Результат запроса
   */
  public function fetchAllCached($sql, $params = [], $ttl = 3600)
  {
    if (!class_exists('Cache')) {
      require_once __DIR__ . '/Cache.php';
    }

    $cache = Cache::getInstance();
    $cacheKey = 'db_' . md5($sql . serialize($params));

    return $cache->remember($cacheKey, function () use ($sql, $params) {
      $stmt = $this->query($sql, $params);
      return $stmt->fetchAll();
    }, $ttl);
  }

  /**
   * Получить одну запись с кэшированием
   * @param string $sql SQL запрос
   * @param array $params Параметры запроса
   * @param int $ttl Время жизни кэша в секундах (по умолчанию 3600 = 1 час)
   * @return array|false Результат запроса
   */
  public function fetchOneCached($sql, $params = [], $ttl = 3600)
  {
    if (!class_exists('Cache')) {
      require_once __DIR__ . '/Cache.php';
    }

    $cache = Cache::getInstance();
    $cacheKey = 'db_' . md5($sql . serialize($params));

    return $cache->remember($cacheKey, function () use ($sql, $params) {
      $stmt = $this->query($sql, $params);
      return $stmt->fetch();
    }, $ttl);
  }

  /**
   * Получить одно значение (первый столбец первой строки)
   */
  public function fetchColumn($sql, $params = [])
  {
    $stmt = $this->query($sql, $params);
    return $stmt->fetchColumn();
  }

  /**
   * Вставить запись и вернуть ID
   */
  public function insert($table, $data)
  {
    $fields = array_keys($data);
    $placeholders = ':' . implode(', :', $fields);
    $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES ($placeholders)";

    $this->query($sql, $data);
    return $this->pdo->lastInsertId();
  }

  /**
   * Обновить запись
   */
  public function update($table, $data, $where, $whereParams = [])
  {
    $setParts = [];
    foreach (array_keys($data) as $field) {
      $setParts[] = "$field = :$field";
    }

    $sql = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE $where";
    $params = array_merge($data, $whereParams);

    $stmt = $this->query($sql, $params);
    return $stmt->rowCount();
  }

  /**
   * Удалить запись
   */
  public function delete($table, $where, $params = [])
  {
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = $this->query($sql, $params);
    return $stmt->rowCount();
  }

  /**
   * Начать транзакцию
   */
  public function beginTransaction()
  {
    return $this->pdo->beginTransaction();
  }

  /**
   * Подтвердить транзакцию
   */
  public function commit()
  {
    return $this->pdo->commit();
  }

  /**
   * Откатить транзакцию
   */
  public function rollback()
  {
    return $this->pdo->rollback();
  }

  /**
   * Получить настройку сайта
   */
  public function getSetting($key, $default = null)
  {
    $result = $this->fetchOne("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$key]);
    return $result ? $result['setting_value'] : $default;
  }

  /**
   * Установить настройку сайта
   */
  public function setSetting($key, $value, $description = null)
  {
    $sql = "INSERT INTO site_settings (setting_key, setting_value, description) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)";
    return $this->query($sql, [$key, $value, $description]);
  }

  /**
   * Получить настройку модерации
   */
  public function getModerationSetting($key, $default = null)
  {
    $result = $this->fetchOne("SELECT setting_value FROM moderation_settings WHERE setting_key = ?", [$key]);
    return $result ? $result['setting_value'] : $default;
  }

  /**
   * Установить настройку модерации
   */
  public function setModerationSetting($key, $value, $description = null)
  {
    $sql = "INSERT INTO moderation_settings (setting_key, setting_value, description) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)";
    return $this->query($sql, [$key, $value, $description]);
  }

  /**
   * Логировать действие
   */
  public function logActivity($action, $entityType = null, $entityId = null, $details = null, $userId = null)
  {
    $data = [
      'user_id' => $userId,
      'action' => $action,
      'entity_type' => $entityType,
      'entity_id' => $entityId,
      'details' => $details ? json_encode($details) : null,
      'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
      'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];

    return $this->insert('activity_logs', $data);
  }
}
