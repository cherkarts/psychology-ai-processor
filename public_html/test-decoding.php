<?php
// Тестовый скрипт для проверки декодирования

// Тестовые данные (как в БД)
$testData = [
  'title' => '    :   ',
  'author' => '',
  'tags' => '["\u0434\u0435\u0442\u0441\u043a\u0438\u0435 \u0442\u0440\u0430\u0432\u043c\u044b"," \u043f\u0441\u0438\u0445\u043e\u043b\u043e\u0433\u0438\u0447\u0435\u0441\u043a\u0430\u044f \u0442\u0440\u0430\u0432\u043c\u0430"]'
];

echo "<h2>Тест декодирования</h2>";

foreach ($testData as $field => $value) {
  echo "<h3>Поле: $field</h3>";
  echo "<p><strong>Исходное значение:</strong> " . htmlspecialchars($value) . "</p>";

  // Способ 1: Unicode escape sequences
  if (strpos($value, '\\u') !== false) {
    $decoded1 = json_decode('"' . $value . '"', true);
    if ($decoded1 !== null) {
      echo "<p><strong>Способ 1 (Unicode):</strong> " . htmlspecialchars($decoded1) . "</p>";
    }
  }

  // Способ 2: Двойная кодировка UTF-8
  if (mb_check_encoding($value, 'UTF-8') && preg_match('/[^\x00-\x7F]/', $value)) {
    $decoded2 = utf8_decode($value);
    if ($decoded2 !== false && $decoded2 !== $value) {
      echo "<p><strong>Способ 2 (utf8_decode):</strong> " . htmlspecialchars($decoded2) . "</p>";
    }
  }

  // Способ 3: CP1251
  if (function_exists('iconv')) {
    $decoded3 = @iconv('CP1251', 'UTF-8', $value);
    if ($decoded3 !== false && $decoded3 !== $value) {
      echo "<p><strong>Способ 3 (CP1251):</strong> " . htmlspecialchars($decoded3) . "</p>";
    }
  }

  // Способ 4: Попробуем все комбинации
  $final = $value;

  // Сначала Unicode
  if (strpos($final, '\\u') !== false) {
    $decoded = json_decode('"' . $final . '"', true);
    if ($decoded !== null) {
      $final = $decoded;
    }
  }

  // Потом двойную кодировку
  if (mb_check_encoding($final, 'UTF-8') && preg_match('/[^\x00-\x7F]/', $final)) {
    $decoded = utf8_decode($final);
    if ($decoded !== false && $decoded !== $final) {
      $final = $decoded;
    }
  }

  // И наконец CP1251
  if (function_exists('iconv')) {
    $decoded = @iconv('CP1251', 'UTF-8', $final);
    if ($decoded !== false && $decoded !== $final) {
      $final = $decoded;
    }
  }

  echo "<p><strong>Финальный результат:</strong> " . htmlspecialchars($final) . "</p>";
  echo "<hr>";
}
?>