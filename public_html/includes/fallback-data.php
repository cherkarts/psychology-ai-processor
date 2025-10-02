<?php
/**
 * Fallback данные из JSON файлов для случаев, когда БД недоступна или содержит кракозябры
 */

// Функция для загрузки медитаций из JSON
function getMeditationsFromJson()
{
  $jsonFile = __DIR__ . '/../database/backup_full_2025-08-25_13-45-41/meditations.json';

  if (!file_exists($jsonFile)) {
    return ['categories' => [], 'meditations' => []];
  }

  $content = file_get_contents($jsonFile);
  $data = json_decode($content, true);

  if (!$data) {
    return ['categories' => [], 'meditations' => []];
  }

  return $data;
}

// Функция для загрузки товаров из JSON
function getProductsFromJson()
{
  $jsonFile = __DIR__ . '/../database/backup_full_2025-08-25_13-45-41/products.json';

  if (!file_exists($jsonFile)) {
    return [];
  }

  $content = file_get_contents($jsonFile);
  $data = json_decode($content, true);

  if (!$data) {
    return [];
  }

  return $data;
}

// Функция для проверки наличия кракозябр в тексте
function hasEncodingIssues($text)
{
  if (empty($text)) {
    return false;
  }

  // Паттерны кракозябр, которые мы видели на скриншотах
  $patterns = [
    '/РўРµСЃСЃ/u',
    '/РџРѕСЋС‰РёРµ/u',
    '/РўРёР±РµС‚/u',
    '/РџРѕРіСЂСѓР·Рё/u',
    '/РљСЂР°С/u',
    '/РєРѕРµ/u',
    '/РїСЂРёСЂРѕРґС‹/u',
    '/РїСЂРёСЂРѕРґС‹/u'
  ];

  foreach ($patterns as $pattern) {
    if (preg_match($pattern, $text)) {
      return true;
    }
  }

  return false;
}

// Функция для исправления кодировки текста
function fixEncoding($text)
{
  if (empty($text)) {
    return $text;
  }

  // Пытаемся исправить кодировку
  $fixed = mb_convert_encoding($text, 'UTF-8', 'Windows-1251');

  // Если исправление помогло (убрало кракозябры)
  if ($fixed !== $text && !hasEncodingIssues($fixed)) {
    return $fixed;
  }

  // Если не помогло, возвращаем исходный текст
  return $text;
}

// Функция для проверки и исправления данных из БД
function fixDatabaseData($data)
{
  if (empty($data)) {
    return $data;
  }

  // Если это массив записей
  if (is_array($data) && isset($data[0])) {
    foreach ($data as &$record) {
      $record = fixRecordEncoding($record);
    }
  } else {
    // Если это одна запись
    $data = fixRecordEncoding($data);
  }

  return $data;
}

// Функция для исправления кодировки одной записи
function fixRecordEncoding($record)
{
  if (!is_array($record)) {
    return $record;
  }

  $textFields = ['title', 'description', 'short_description', 'content', 'name', 'subtitle', 'text', 'comment'];

  foreach ($textFields as $field) {
    if (isset($record[$field]) && hasEncodingIssues($record[$field])) {
      $record[$field] = fixEncoding($record[$field]);
    }
  }

  return $record;
}

// Функция для получения медитаций с fallback
function getMeditationsWithFallback()
{
  try {
    // Сначала пытаемся получить из БД
    if (!function_exists('getDB')) {
      require_once __DIR__ . '/functions.php';
    }
    $pdo = getDB();

    // Получаем категории медитаций
    $stmt = $pdo->query("SELECT mc.*, COUNT(m.id) as meditation_count 
                             FROM meditation_categories mc 
                             LEFT JOIN meditations m ON mc.id = m.category_id 
                             WHERE mc.is_active = 1 
                             GROUP BY mc.id 
                             ORDER BY mc.sort_order, mc.name");
    $categories = $stmt->fetchAll();

    // Получаем все медитации
    $stmt = $pdo->query("SELECT m.*, mc.name as category_name 
                             FROM meditations m 
                             LEFT JOIN meditation_categories mc ON m.category_id = mc.id 
                             ORDER BY m.created_at DESC");
    $meditations = $stmt->fetchAll();

    // Проверяем на наличие кракозябр
    $hasIssues = false;
    foreach ($meditations as $med) {
      if (hasEncodingIssues($med['title']) || hasEncodingIssues($med['description'])) {
        $hasIssues = true;
        break;
      }
    }

    if ($hasIssues) {
      throw new Exception("Данные в БД содержат кракозябры");
    }

    return [
      'categories' => $categories,
      'meditations' => $meditations,
      'source' => 'database'
    ];

  } catch (Exception $e) {
    // Fallback на JSON
    error_log("Fallback на JSON для медитаций: " . $e->getMessage());

    $jsonData = getMeditationsFromJson();

    // Преобразуем JSON структуру в структуру БД
    $categories = [];
    $meditations = [];

    if (isset($jsonData['categories'])) {
      foreach ($jsonData['categories'] as $cat) {
        $categories[] = [
          'id' => $cat['id'],
          'name' => $cat['name'],
          'description' => $cat['description'],
          'meditation_count' => $cat['count'] ?? 0
        ];
      }
    }

    if (isset($jsonData['meditations'])) {
      foreach ($jsonData['meditations'] as $med) {
        $meditations[] = [
          'id' => $med['id'],
          'title' => $med['title'],
          'description' => $med['description'],
          'category_name' => $med['category'] ?? 'Без категории',
          'duration' => $med['duration'] ?? 0,
          'audio_file' => $med['audio_file'] ?? '',
          'likes' => $med['likes'] ?? 0
        ];
      }
    }

    return [
      'categories' => $categories,
      'meditations' => $meditations,
      'source' => 'json'
    ];
  }
}

// Функция для получения товаров с fallback
function getProductsWithFallback()
{
  try {
    // Сначала пытаемся получить из БД
    if (!function_exists('getDB')) {
      require_once __DIR__ . '/functions.php';
    }
    require_once __DIR__ . '/products.php';
    $productManager = new ProductManager();
    $products = $productManager->getAllProducts();

    // Проверяем на наличие кракозябр
    $hasIssues = false;
    foreach ($products as $product) {
      if (hasEncodingIssues($product['title']) || hasEncodingIssues($product['description'])) {
        $hasIssues = true;
        break;
      }
    }

    if ($hasIssues) {
      throw new Exception("Данные в БД содержат кракозябры");
    }

    return [
      'products' => $products,
      'source' => 'database'
    ];

  } catch (Exception $e) {
    // Fallback на JSON
    error_log("Fallback на JSON для товаров: " . $e->getMessage());

    $products = getProductsFromJson();

    return [
      'products' => $products,
      'source' => 'json'
    ];
  }
}
?>