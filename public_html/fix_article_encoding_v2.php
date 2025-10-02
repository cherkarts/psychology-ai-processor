<?php
/**
 * Исправление кодировки AI-статьи - версия 2
 * Решает проблему с кракозябрами и пустыми slug
 */

echo "<h1>🔧 Исправление кодировки AI-статьи v2</h1>";

try {
  // Подключаем конфигурацию
  $config = require_once 'config.php';

  // Извлекаем настройки базы данных
  $db_config = $config['database'];
  $dsn = "mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['dbname']};charset={$db_config['charset']}";
  $username = $db_config['username'];
  $password = $db_config['password'];
  $options = $db_config['options'];

  // Подключаемся к базе данных
  $pdo = new PDO($dsn, $username, $password, $options);

  echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>✅ Подключение к базе данных успешно!</h3>";
  echo "</div>";

  // Находим AI-статью
  $sql = "SELECT id, title, content, excerpt, slug, author FROM articles WHERE author = 'AI Assistant' ORDER BY created_at DESC LIMIT 1";
  $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $article = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$article) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ AI-статьи не найдены</h3>";
    echo "</div>";
    exit;
  }

  echo "<h2>📄 Найдена статья ID: {$article['id']}</h2>";
  echo "<p><strong>Название:</strong> " . htmlspecialchars($article['title']) . "</p>";
  echo "<p><strong>Текущий автор:</strong> " . htmlspecialchars($article['author']) . "</p>";
  echo "<p><strong>Текущий slug:</strong> " . htmlspecialchars($article['slug']) . "</p>";
  echo "<p><strong>Длина контента:</strong> " . strlen($article['content']) . " символов</p>";

  // Функция для правильного исправления кодировки
  function fixEncodingProperly($text)
  {
    if (empty($text))
      return $text;

    // Проверяем, что текст уже в правильной кодировке
    if (mb_check_encoding($text, 'UTF-8') && !preg_match('/[^\x00-\x7F]/', $text)) {
      // Если это ASCII, возвращаем как есть
      return $text;
    }

    // Если текст содержит кириллицу и выглядит нормально, возвращаем как есть
    if (mb_check_encoding($text, 'UTF-8') && preg_match('/[а-яё]/ui', $text)) {
      return $text;
    }

    // Пробуем разные варианты исправления
    $attempts = [];

    // Попытка 1: CP1251 -> UTF-8
    if (function_exists('iconv')) {
      $test = @iconv('CP1251', 'UTF-8', $text);
      if ($test !== false && $test !== $text) {
        $attempts[] = ['method' => 'CP1251->UTF-8', 'result' => $test];
      }
    }

    // Попытка 2: Latin1 -> UTF-8
    if (function_exists('iconv')) {
      $test = @iconv('ISO-8859-1', 'UTF-8', $text);
      if ($test !== false && $test !== $text) {
        $attempts[] = ['method' => 'Latin1->UTF-8', 'result' => $test];
      }
    }

    // Попытка 3: UTF-8 decode
    if (function_exists('utf8_decode')) {
      $test = utf8_decode($text);
      if ($test !== $text) {
        $attempts[] = ['method' => 'UTF-8 decode', 'result' => $test];
      }
    }

    // Попытка 4: mb_convert_encoding
    if (function_exists('mb_convert_encoding')) {
      $test = @mb_convert_encoding($text, 'UTF-8', 'CP1251');
      if ($test !== false && $test !== $text) {
        $attempts[] = ['method' => 'mb_convert CP1251->UTF-8', 'result' => $test];
      }
    }

    // Выбираем лучший результат
    foreach ($attempts as $attempt) {
      $result = $attempt['result'];
      // Проверяем, что результат содержит читаемую кириллицу
      if (preg_match('/[а-яё]/ui', $result) && !preg_match('/[^\x00-\x7Fа-яё\s]/ui', $result)) {
        return $result;
      }
    }

    // Если ничего не помогло, возвращаем исходный текст
    return $text;
  }

  // Создаем правильный slug
  $title = $article['title'];

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
  $slug = strtr($title, $transliteration);

  // Убираем все символы кроме букв, цифр, пробелов и дефисов
  $slug = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $slug);

  // Заменяем пробелы на дефисы
  $slug = preg_replace('/\s+/', '-', $slug);

  // Убираем множественные дефисы
  $slug = preg_replace('/-+/', '-', $slug);

  // Убираем дефисы в начале и конце
  $slug = trim($slug, '-');

  // Переводим в нижний регистр
  $slug = strtolower($slug);

  echo "<p><strong>Новый slug:</strong> {$slug}</p>";

  // Исправляем кодировку контента
  $fixed_content = fixEncodingProperly($article['content']);
  $fixed_excerpt = fixEncodingProperly($article['excerpt']);

  echo "<p><strong>Длина исправленного контента:</strong> " . strlen($fixed_content) . " символов</p>";
  echo "<p><strong>Длина исправленного excerpt:</strong> " . strlen($fixed_excerpt) . " символов</p>";

  // Показываем первые 200 символов для проверки
  echo "<h3>🔍 Проверка кодировки (первые 200 символов):</h3>";
  echo "<div style='background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #dee2e6;'>";
  echo "<strong>Исходный контент:</strong><br>";
  echo htmlspecialchars(mb_substr($article['content'], 0, 200)) . "...<br><br>";
  echo "<strong>Исправленный контент:</strong><br>";
  echo htmlspecialchars(mb_substr($fixed_content, 0, 200)) . "...";
  echo "</div>";

  // Проверяем, есть ли кириллица в исправленном тексте
  $hasCyrillic = preg_match('/[а-яё]/ui', $fixed_content);
  echo "<p><strong>Содержит кириллицу:</strong> " . ($hasCyrillic ? "✅ Да" : "❌ Нет") . "</p>";

  // Обновляем статью
  $update_sql = "UPDATE articles SET 
        content = :content, 
        excerpt = :excerpt, 
        slug = :slug, 
        author = :author,
        updated_at = NOW() 
        WHERE id = :id";

  $update_stmt = $pdo->prepare($update_sql);

  $result = $update_stmt->execute([
    ':content' => $fixed_content,
    ':excerpt' => $fixed_excerpt,
    ':slug' => $slug,
    ':author' => 'Денис Черкас',
    ':id' => $article['id']
  ]);

  if ($result) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Статья полностью исправлена!</h3>";
    echo "<p><strong>ID статьи:</strong> {$article['id']}</p>";
    echo "<p><strong>Новый автор:</strong> Денис Черкас</p>";
    echo "<p><strong>Новый slug:</strong> {$slug}</p>";
    echo "<p><strong>Кодировка исправлена:</strong> " . ($hasCyrillic ? "✅" : "❌") . "</p>";
    echo "</div>";

    // Проверяем результат
    $check_sql = "SELECT title, content, excerpt, slug, author FROM articles WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([':id' => $article['id']]);
    $check_result = $check_stmt->fetch(PDO::FETCH_ASSOC);

    echo "<h2>🔍 Проверка результата:</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p><strong>Название в БД:</strong> " . htmlspecialchars($check_result['title']) . "</p>";
    echo "<p><strong>Автор в БД:</strong> " . htmlspecialchars($check_result['author']) . "</p>";
    echo "<p><strong>Slug в БД:</strong> " . htmlspecialchars($check_result['slug']) . "</p>";
    echo "<p><strong>Длина контента:</strong> " . strlen($check_result['content']) . " символов</p>";
    echo "<p><strong>Длина excerpt:</strong> " . strlen($check_result['excerpt']) . " символов</p>";
    echo "</div>";

    // Показываем первые 300 символов из БД для проверки
    echo "<h3>🔍 Контент из БД (первые 300 символов):</h3>";
    echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; border: 1px solid #ffeaa7;'>";
    echo htmlspecialchars(mb_substr($check_result['content'], 0, 300)) . "...";
    echo "</div>";

    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🎉 Исправление завершено!</h3>";
    echo "<p>Проверьте результат:</p>";
    echo "<p><a href='/articles/' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>📋 Список статей</a></p>";
    echo "<p><a href='/article.php?id={$article['id']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по ID)</a></p>";
    echo "<p><a href='/article.php?slug={$check_result['slug']}' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>👁️ Просмотр статьи (по slug)</a></p>";
    echo "<p><a href='/admin/articles.php' target='_blank' style='color: #0066cc; text-decoration: none; font-weight: bold;'>⚙️ Админ панель</a></p>";
    echo "</div>";

  } else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Ошибка при обновлении статьи</h3>";
    echo "</div>";
  }

} catch (PDOException $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Ошибка базы данных</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
} catch (Exception $e) {
  echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
  echo "<h3>❌ Общая ошибка</h3>";
  echo "<p><strong>Ошибка:</strong> " . $e->getMessage() . "</p>";
  echo "</div>";
}
?>