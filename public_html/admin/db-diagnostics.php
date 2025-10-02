<?php
// Диагностика БД: список таблиц, колонки, индексы, внешние ключи, количество строк и первые записи
// Доступно только администраторам
session_start();
require_once __DIR__ . '/includes/auth.php';
requireLogin();
require_once __DIR__ . '/includes/config.php';

header('Content-Type: text/html; charset=UTF-8');

function h($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

$pdo = getAdminDB();
if (!$pdo) {
  echo '<h1>Ошибка</h1><p>Не удалось подключиться к базе данных.</p>';
  exit;
}

// Получаем список таблиц текущей базы
$dbName = $pdo->query('SELECT DATABASE()')->fetchColumn();
$tables = [];
try {
  $stmt = $pdo->query('SHOW FULL TABLES');
  while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $tables[] = $row[0];
  }
} catch (Throwable $e) {
  echo '<p>Ошибка получения списка таблиц: ' . h($e->getMessage()) . '</p>';
}

echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>DB Diagnostics</title>';
echo '<style>body{font-family:system-ui,Arial,sans-serif;line-height:1.4;padding:16px} h1{margin:0 0 16px} .card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;margin:12px 0;padding:12px} table{width:100%;border-collapse:collapse;margin:8px 0} th,td{border:1px solid #e5e7eb;padding:6px 8px;font-size:13px} th{background:#f9fafb;text-align:left} .muted{color:#6b7280} .ok{color:#047857} .warn{color:#b45309} .err{color:#b91c1c} code{background:#f3f4f6;padding:2px 4px;border-radius:4px} .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px}</style>';
echo '</head><body>';
echo '<h1>Диагностика БД (' . h($dbName) . ')</h1>';
echo '<p>Всего таблиц: <strong>' . count($tables) . '</strong></p>';

foreach ($tables as $table) {
  echo '<div class="card">';
  echo '<h2 style="margin:0 0 8px">Таблица: <code>' . h($table) . '</code></h2>';

  // Колонки
  try {
    $cols = $pdo->query('SHOW FULL COLUMNS FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);
    echo '<details open><summary><strong>Колонки</strong> (' . count($cols) . ')</summary>';
    echo '<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th><th>Comment</th></tr>';
    foreach ($cols as $c) {
      echo '<tr><td>' . h($c['Field']) . '</td><td>' . h($c['Type']) . '</td><td>' . h($c['Null']) . '</td><td>' . h($c['Key']) . '</td><td>' . h($c['Default']) . '</td><td>' . h($c['Extra']) . '</td><td>' . h($c['Comment']) . '</td></tr>';
    }
    echo '</table></details>';
  } catch (Throwable $e) {
    echo '<p class="err">Ошибка чтения колонок: ' . h($e->getMessage()) . '</p>';
  }

  // Индексы
  try {
    $idx = $pdo->query('SHOW INDEX FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC);
    echo '<details><summary><strong>Индексы</strong> (' . count($idx) . ')</summary>';
    if ($idx) {
      echo '<table><tr><th>Key_name</th><th>Non_unique</th><th>Column_name</th><th>Seq_in_index</th><th>Index_type</th></tr>';
      foreach ($idx as $i) {
        echo '<tr><td>' . h($i['Key_name']) . '</td><td>' . h($i['Non_unique']) . '</td><td>' . h($i['Column_name']) . '</td><td>' . h($i['Seq_in_index']) . '</td><td>' . h($i['Index_type']) . '</td></tr>';
      }
      echo '</table>';
    } else {
      echo '<p class="muted">Нет индексов</p>';
    }
    echo '</details>';
  } catch (Throwable $e) {
    echo '<p class="err">Ошибка чтения индексов: ' . h($e->getMessage()) . '</p>';
  }

  // Внешние ключи
  try {
    $sqlFk = "SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME, kcu.REFERENCED_COLUMN_NAME
                 FROM information_schema.KEY_COLUMN_USAGE kcu
                 WHERE kcu.TABLE_SCHEMA = :db AND kcu.TABLE_NAME = :tbl AND kcu.REFERENCED_TABLE_NAME IS NOT NULL";
    $st = $pdo->prepare($sqlFk);
    $st->execute([':db' => $dbName, ':tbl' => $table]);
    $fks = $st->fetchAll(PDO::FETCH_ASSOC);
    echo '<details><summary><strong>Внешние ключи</strong> (' . count($fks) . ')</summary>';
    if ($fks) {
      echo '<table><tr><th>Constraint</th><th>Column</th><th>Ref table</th><th>Ref column</th></tr>';
      foreach ($fks as $fk) {
        echo '<tr><td>' . h($fk['CONSTRAINT_NAME']) . '</td><td>' . h($fk['COLUMN_NAME']) . '</td><td>' . h($fk['REFERENCED_TABLE_NAME']) . '</td><td>' . h($fk['REFERENCED_COLUMN_NAME']) . '</td></tr>';
      }
      echo '</table>';
    } else {
      echo '<p class="muted">Нет внешних ключей</p>';
    }
    echo '</details>';
  } catch (Throwable $e) {
    echo '<p class="err">Ошибка чтения внешних ключей: ' . h($e->getMessage()) . '</p>';
  }

  // Количество строк и примеры
  try {
    $cnt = (int) $pdo->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn();
    echo '<p>Количество строк: <strong>' . $cnt . '</strong></p>';
    if ($cnt > 0) {
      $sample = $pdo->query('SELECT * FROM `' . $table . '` LIMIT 3')->fetchAll(PDO::FETCH_ASSOC);
      echo '<details><summary><strong>Первые записи</strong></summary><pre>' . h(json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre></details>';
    }
  } catch (Throwable $e) {
    echo '<p class="err">Ошибка чтения строк: ' . h($e->getMessage()) . '</p>';
  }

  echo '</div>';
}

// Быстрый список «ожидаемых» таблиц (по проекту)
$expected = ['products', 'product_categories', 'product_files', 'articles', 'article_categories', 'meditations', 'meditation_categories', 'orders', 'order_items', 'reviews', 'product_reviews', 'promo_codes', 'admin_users', 'settings'];
echo '<div class="card"><h2 style="margin:0 0 8px">Ожидаемые таблицы</h2><ul>';
foreach ($expected as $t) {
  $ok = in_array($t, $tables, true);
  echo '<li>' . h($t) . ' — ' . ($ok ? '<span class="ok">OK</span>' : '<span class="warn">missing</span>') . '</li>';
}
echo '</ul></div>';

echo '<p class="muted">Готово. Если нужна выгрузка в JSON — сообщите, добавлю экспорт.</p>';
echo '</body></html>';


