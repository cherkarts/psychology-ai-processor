<?php
/**
 * Простая файловая система кэширования
 * Ускоряет работу сайта путем кэширования результатов запросов к БД
 */
class Cache
{
  private static $instance = null;
  private $cacheDir;
  private $enabled;

  private function __construct()
  {
    $this->cacheDir = __DIR__ . '/../cache/';
    $this->enabled = true; // Можно отключить для дебага

    // Создаем директорию кэша если её нет
    if (!is_dir($this->cacheDir)) {
      mkdir($this->cacheDir, 0755, true);
    }
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Получить данные из кэша
   * @param string $key Ключ кэша
   * @return mixed|null Данные или null если кэш не найден или истёк
   */
  public function get($key)
  {
    if (!$this->enabled) {
      return null;
    }

    $file = $this->getCacheFile($key);

    if (!file_exists($file)) {
      return null;
    }

    $data = @file_get_contents($file);
    if ($data === false) {
      return null;
    }

    $cache = @unserialize($data);
    if ($cache === false) {
      @unlink($file);
      return null;
    }

    // Проверяем не истёк ли кэш
    if ($cache['expires'] < time()) {
      @unlink($file);
      return null;
    }

    return $cache['data'];
  }

  /**
   * Сохранить данные в кэш
   * @param string $key Ключ кэша
   * @param mixed $data Данные для кэширования
   * @param int $ttl Время жизни в секундах (по умолчанию 3600 = 1 час)
   * @return bool Успешность операции
   */
  public function set($key, $data, $ttl = 3600)
  {
    if (!$this->enabled) {
      return false;
    }

    $file = $this->getCacheFile($key);

    $cache = [
      'expires' => time() + $ttl,
      'data' => $data
    ];

    $serialized = serialize($cache);
    $result = @file_put_contents($file, $serialized, LOCK_EX);

    return $result !== false;
  }

  /**
   * Удалить данные из кэша
   * @param string $key Ключ кэша
   * @return bool Успешность операции
   */
  public function delete($key)
  {
    $file = $this->getCacheFile($key);

    if (file_exists($file)) {
      return @unlink($file);
    }

    return true;
  }

  /**
   * Очистить весь кэш
   * @return int Количество удалённых файлов
   */
  public function clear()
  {
    $count = 0;
    $files = glob($this->cacheDir . 'cache_*.tmp');

    foreach ($files as $file) {
      if (is_file($file)) {
        @unlink($file);
        $count++;
      }
    }

    return $count;
  }

  /**
   * Очистить устаревший кэш
   * @return int Количество удалённых файлов
   */
  public function clearExpired()
  {
    $count = 0;
    $files = glob($this->cacheDir . 'cache_*.tmp');

    foreach ($files as $file) {
      if (is_file($file)) {
        $data = @file_get_contents($file);
        if ($data !== false) {
          $cache = @unserialize($data);
          if ($cache !== false && $cache['expires'] < time()) {
            @unlink($file);
            $count++;
          }
        }
      }
    }

    return $count;
  }

  /**
   * Получить размер кэша в байтах
   * @return int Размер кэша
   */
  public function getSize()
  {
    $size = 0;
    $files = glob($this->cacheDir . 'cache_*.tmp');

    foreach ($files as $file) {
      if (is_file($file)) {
        $size += filesize($file);
      }
    }

    return $size;
  }

  /**
   * Включить/выключить кэш
   * @param bool $enabled
   */
  public function setEnabled($enabled)
  {
    $this->enabled = $enabled;
  }

  /**
   * Получить путь к файлу кэша
   * @param string $key Ключ кэша
   * @return string Путь к файлу
   */
  private function getCacheFile($key)
  {
    $hash = md5($key);
    return $this->cacheDir . 'cache_' . $hash . '.tmp';
  }

  /**
   * Вспомогательная функция для кэширования результата функции
   * @param string $key Ключ кэша
   * @param callable $callback Функция для получения данных
   * @param int $ttl Время жизни кэша
   * @return mixed Результат функции или кэша
   */
  public function remember($key, $callback, $ttl = 3600)
  {
    $cached = $this->get($key);

    if ($cached !== null) {
      return $cached;
    }

    $data = call_user_func($callback);
    $this->set($key, $data, $ttl);

    return $data;
  }
}

