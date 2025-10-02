<?php
// Улучшенный тестовый скрипт для проверки декодирования

// Тестовые данные (как в БД)
$testData = [
  'title' => '    :   ',
  'author' => '',
  'tags' => '["\u0434\u0435\u0442\u0441\u043a\u0438\u0435 \u0442\u0440\u0430\u0432\u043c\u044b"," \u043f\u0441\u0438\u0445\u043e\u043b\u043e\u0433\u0438\u0447\u0435\u0441\u043a\u0430\u044f \u0442\u0440\u0430\u0432\u043c\u0430"," \u0432\u043d\u0443\u0442\u0440\u0435\u043d\u043d\u0438\u0439 \u0440\u0435\u0431\u0435\u043d\u043e\u043a"]'
];

echo "<h2>Улучшенный тест декодирования</h2>";

foreach ($testData as $field => $value) {
  echo "<h3>Поле: $field</h3>";
  echo "<p><strong>Исходное значение:</strong> " . htmlspecialchars($value) . "</p>";

  $final = $value;

  // Способ 1: Unicode escape sequences
  if (strpos($final, '\\u') !== false) {
    echo "<p><strong>Найдены Unicode escape sequences</strong></p>";

    // Пробуем декодировать как JSON строку
    $decoded1 = json_decode('"' . $final . '"', true);
    if ($decoded1 !== null) {
      echo "<p><strong>Способ 1a (JSON строка):</strong> " . htmlspecialchars($decoded1) . "</p>";
      $final = $decoded1;
    } else {
      // Пробуем декодировать как JSON массив
      $decoded2 = json_decode($final, true);
      if ($decoded2 !== null && is_array($decoded2)) {
        $decoded2_str = json_encode($decoded2, JSON_UNESCAPED_UNICODE);
        echo "<p><strong>Способ 1b (JSON массив):</strong> " . htmlspecialchars($decoded2_str) . "</p>";
        echo "<p><strong>Массив:</strong> " . print_r($decoded2, true) . "</p>";
        $final = $decoded2_str;
      } else {
        echo "<p><strong>Способ 1 не сработал</strong></p>";
      }
    }
  }

  // Способ 2: Двойная кодировка UTF-8
  if (mb_check_encoding($final, 'UTF-8') && preg_match('/[^\x00-\x7F]/', $final)) {
    $decoded3 = utf8_decode($final);
    if ($decoded3 !== false && $decoded3 !== $final) {
      echo "<p><strong>Способ 2 (utf8_decode):</strong> " . htmlspecialchars($decoded3) . "</p>";
      $final = $decoded3;
    }
  }

  // Способ 3: CP1251
  if (function_exists('iconv')) {
    $decoded4 = @iconv('CP1251', 'UTF-8', $final);
    if ($decoded4 !== false && $decoded4 !== $final) {
      echo "<p><strong>Способ 3 (CP1251):</strong> " . htmlspecialchars($decoded4) . "</p>";
      $final = $decoded4;
    }
  }

  // Способ 4: Ручное декодирование Unicode
  if (strpos($final, '\\u') !== false) {
    $decoded5 = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
      return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
    }, $final);
    if ($decoded5 !== $final) {
      echo "<p><strong>Способ 4 (ручное Unicode):</strong> " . htmlspecialchars($decoded5) . "</p>";
      $final = $decoded5;
    }
  }

  echo "<p><strong>Финальный результат:</strong> " . htmlspecialchars($final) . "</p>";
  echo "<hr>";
}

// Дополнительный тест для тегов
echo "<h3>Дополнительный тест для тегов</h3>";
$tagsString = '["\u0434\u0435\u0442\u0441\u043a\u0438\u0435 \u0442\u0440\u0430\u0432\u043c\u044b"," \u043f\u0441\u0438\u0445\u043e\u043b\u043e\u0433\u0438\u0447\u0435\u0441\u043a\u0430\u044f \u0442\u0440\u0430\u0432\u043c\u0430"]';

echo "<p><strong>Исходные теги:</strong> " . htmlspecialchars($tagsString) . "</p>";

// Пробуем декодировать как JSON
$tagsArray = json_decode($tagsString, true);
if ($tagsArray !== null) {
  echo "<p><strong>Декодированный массив:</strong> " . print_r($tagsArray, true) . "</p>";

  // Конвертируем обратно в строку для отображения
  $tagsForDisplay = implode(', ', $tagsArray);
  echo "<p><strong>Теги для отображения:</strong> " . htmlspecialchars($tagsForDisplay) . "</p>";
} else {
  echo "<p><strong>Ошибка декодирования JSON:</strong> " . json_last_error_msg() . "</p>";
}
?>