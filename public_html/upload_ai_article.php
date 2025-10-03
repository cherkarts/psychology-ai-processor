<?php
/**
 * Публичный API для загрузки AI-статей
 * Не требует авторизации, но проверяет источник
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Разрешаем только POST запросы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
  exit();
}

// Проверяем, что запрос от GitHub Actions или локального тестирования
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_github_actions = strpos($user_agent, 'GitHub-Actions') !== false;
$is_local_test = strpos($user_agent, 'python-requests') !== false;
$is_test_script = strpos($user_agent, 'Test-Script') !== false;

// Для отладки - логируем User-Agent
error_log("AI Article API - User-Agent: " . $user_agent);

if (!$is_github_actions && !$is_local_test && !$is_test_script) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Доступ запрещен. User-Agent: ' . $user_agent]);
  exit();
}

try {
  // Получаем данные разными способами
  $input = null;
  
  // Способ 1: php://input
  $raw_input = file_get_contents('php://input');
  if ($raw_input) {
    $input = json_decode($raw_input, true);
  }
  
  // Способ 2: $_POST (если данные пришли как form-data)
  if (!$input && !empty($_POST)) {
    $input = $_POST;
  }
  
  // Способ 3: $_REQUEST (fallback)
  if (!$input && !empty($_REQUEST)) {
    $input = $_REQUEST;
  }
  
  // Для отладки - логируем что получили
  error_log("AI Article API - Raw input: " . $raw_input);
  error_log("AI Article API - Parsed input: " . print_r($input, true));
  
  if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Неверные данные. Raw: ' . $raw_input]);
    exit();
  }

  // Функция для правильной обработки текста
  $processText = function ($text) {
    if (empty($text))
      return $text;

    $text = trim($text);

    // Убеждаемся, что текст в правильной кодировке UTF-8
    if (mb_check_encoding($text, 'UTF-8')) {
      return $text;
    }

    // Если нет, пытаемся исправить
    $fixed = mb_convert_encoding($text, 'UTF-8', 'auto');
    return $fixed;
  };

  // Обрабатываем данные
  $title = $processText($input['title'] ?? '');
  $content = $processText($input['content'] ?? '');
  $excerpt = $processText($input['excerpt'] ?? '');
  $author = $processText($input['author'] ?? 'AI Assistant');
  $tags = $processText($input['tags'] ?? '');
  $slug = $processText($input['slug'] ?? '');
  $categoryId = isset($input['category_id']) ? (int) $input['category_id'] : 1;
  $featuredImage = $processText($input['featured_image'] ?? '');
  $metaTitle = $processText($input['meta_title'] ?? $title);
  $metaDescription = $processText($input['meta_description'] ?? $excerpt);

  // Валидация
  if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Название обязательно']);
    exit();
  }

  if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Содержание обязательно']);
    exit();
  }

  // Генерируем slug если не передан
  if (empty($slug)) {
    $slug = generateSlug($title);
  }

  // Обрабатываем теги
  $tagsValue = null;
  if (!empty($tags)) {
    if (is_string($tags)) {
      // Если строка через запятую
      $parts = array_values(array_filter(array_map('trim', explode(',', $tags)), fn($v) => $v !== ''));
      $parts = array_values(array_unique($parts));
      $tagsValue = json_encode($parts, JSON_UNESCAPED_UNICODE);
    } elseif (is_array($tags)) {
      // Если уже массив
      $cleaned = array_values(array_unique(array_filter(array_map('trim', $tags), fn($v) => $v !== '')));
      $tagsValue = json_encode($cleaned, JSON_UNESCAPED_UNICODE);
    }
  }

  // Подключаемся к базе данных
  $config = require 'config.php';
  $dsn = "mysql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']};charset={$config['database']['charset']}";
  $pdo = new PDO($dsn, $config['database']['username'], $config['database']['password'], $config['database']['options']);

  // Убеждаемся, что соединение использует UTF-8
  $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

  // Проверяем, существует ли статья с таким slug
  $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
  $stmt->execute([$slug]);
  $existing = $stmt->fetch();

  if ($existing) {
    // Обновляем существующую статью
    $sql = "UPDATE articles SET 
                title = ?, content = ?, excerpt = ?, author = ?, tags = ?, 
                category_id = ?, featured_image = ?, meta_title = ?, meta_description = ?,
                is_active = 1, updated_at = NOW()
                WHERE slug = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $title,
      $content,
      $excerpt,
      $author,
      $tagsValue,
      $categoryId,
      $featuredImage,
      $metaTitle,
      $metaDescription,
      $slug
    ]);

    $articleId = $existing['id'];
    $message = 'Статья обновлена';
  } else {
    // Создаем новую статью
    $sql = "INSERT INTO articles (
                title, content, excerpt, author, tags, slug, category_id, 
                featured_image, meta_title, meta_description, is_active, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      $title,
      $content,
      $excerpt,
      $author,
      $tagsValue,
      $slug,
      $categoryId,
      $featuredImage,
      $metaTitle,
      $metaDescription
    ]);

    $articleId = $pdo->lastInsertId();
    $message = 'Статья создана';
  }

  echo json_encode([
    'success' => true,
    'message' => $message,
    'id' => $articleId,
    'slug' => $slug,
    'title' => $title
  ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
  error_log("AI Article Upload Error: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Ошибка: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}

function generateSlug($title)
{
  // Транслитерация кириллицы в латиницу
  $transliteration = [
    'а' => 'a',
    'б' => 'b',
    'в' => 'v',
    'г' => 'g',
    'д' => 'd',
    'е' => 'e',
    'ё' => 'yo',
    'ж' => 'zh',
    'з' => 'z',
    'и' => 'i',
    'й' => 'y',
    'к' => 'k',
    'л' => 'l',
    'м' => 'm',
    'н' => 'n',
    'о' => 'o',
    'п' => 'p',
    'р' => 'r',
    'с' => 's',
    'т' => 't',
    'у' => 'u',
    'ф' => 'f',
    'х' => 'h',
    'ц' => 'ts',
    'ч' => 'ch',
    'ш' => 'sh',
    'щ' => 'sch',
    'ъ' => '',
    'ы' => 'y',
    'ь' => '',
    'э' => 'e',
    'ю' => 'yu',
    'я' => 'ya',
    'А' => 'A',
    'Б' => 'B',
    'В' => 'V',
    'Г' => 'G',
    'Д' => 'D',
    'Е' => 'E',
    'Ё' => 'Yo',
    'Ж' => 'Zh',
    'З' => 'Z',
    'И' => 'I',
    'Й' => 'Y',
    'К' => 'K',
    'Л' => 'L',
    'М' => 'M',
    'Н' => 'N',
    'О' => 'O',
    'П' => 'P',
    'Р' => 'R',
    'С' => 'S',
    'Т' => 'T',
    'У' => 'U',
    'Ф' => 'F',
    'Х' => 'H',
    'Ц' => 'Ts',
    'Ч' => 'Ch',
    'Ш' => 'Sh',
    'Щ' => 'Sch',
    'Ъ' => '',
    'Ы' => 'Y',
    'Ь' => '',
    'Э' => 'E',
    'Ю' => 'Yu',
    'Я' => 'Ya'
  ];

  // Применяем транслитерацию
  $slug = $title;
  foreach ($transliteration as $cyrillic => $latin) {
    $slug = str_replace($cyrillic, $latin, $slug);
  }

  // Убираем все символы кроме букв, цифр, пробелов и дефисов
  $slug = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $slug);

  // Заменяем пробелы на дефисы
  $slug = preg_replace('/\s+/', '-', $slug);

  // Убираем множественные дефисы
  $slug = preg_replace('/-+/', '-', $slug);

  // Убираем дефисы в начале и конце
  $slug = trim($slug, '-');

  // Приводим к нижнему регистру
  $slug = strtolower($slug);

  // Ограничиваем длину
  if (strlen($slug) > 100) {
    $slug = substr($slug, 0, 100);
    $slug = substr($slug, 0, strrpos($slug, '-'));
  }

  return $slug;
}
?>
