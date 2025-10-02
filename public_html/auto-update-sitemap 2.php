<?php
/**
 * Автоматическое обновление карты сайта
 * Этот файл запускается по cron для регулярного обновления
 */

// Устанавливаем временную зону
date_default_timezone_set('Europe/Moscow');

// Функция для логирования
function logMessage($message, $type = 'INFO')
{
  $timestamp = date('Y-m-d H:i:s');
  $logMessage = "[$timestamp] [$type] $message" . PHP_EOL;

  // Выводим в консоль
  echo $logMessage;

  // Записываем в лог файл
  $logFile = __DIR__ . '/logs/sitemap-update.log';
  $logDir = dirname($logFile);

  if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
  }

  file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Начинаем логирование
logMessage('Начинаю автоматическое обновление карты сайта');

// Проверяем, нужно ли обновлять карту сайта
$sitemapFile = __DIR__ . '/sitemap.xml';
$lastUpdateFile = __DIR__ . '/sitemap-last-update.txt';
$updateInterval = 86400; // 24 часа в секундах

// Проверяем существование файлов
if (!file_exists($sitemapFile)) {
  logMessage('Карта сайта не найдена, создаю новую', 'WARNING');
  $needUpdate = true;
} elseif (!file_exists($lastUpdateFile)) {
  logMessage('Файл времени последнего обновления не найден, обновляю карту', 'WARNING');
  $needUpdate = true;
} else {
  $lastUpdate = filemtime($lastUpdateFile);
  $timeSinceUpdate = time() - $lastUpdate;

  if ($timeSinceUpdate > $updateInterval) {
    logMessage("Карта сайта устарела (последнее обновление: " . date('Y-m-d H:i:s', $lastUpdate) . ")", 'INFO');
    $needUpdate = true;
  } else {
    logMessage("Карта сайта актуальна. Последнее обновление: " . date('Y-m-d H:i:s', $lastUpdate), 'INFO');
    $needUpdate = false;
  }
}

if ($needUpdate) {
  try {
    logMessage('Запускаю генерацию карты сайта...');

    // Запускаем генерацию
    require_once __DIR__ . '/generate-sitemap.php';

    // Записываем время последнего обновления
    file_put_contents($lastUpdateFile, date('Y-m-d H:i:s'));

    // Проверяем, что файл создался
    if (file_exists($sitemapFile)) {
      $fileSize = filesize($sitemapFile);
      $fileTime = filemtime($sitemapFile);

      logMessage("Карта сайта успешно обновлена", 'SUCCESS');
      logMessage("Размер файла: " . number_format($fileSize) . " байт");
      logMessage("Время создания: " . date('Y-m-d H:i:s', $fileTime));

      // Проверяем содержимое sitemap
      $sitemapContent = file_get_contents($sitemapFile);
      $urlCount = substr_count($sitemapContent, '<url>');
      logMessage("Количество URL в карте: $urlCount");

    } else {
      logMessage("Ошибка: карта сайта не была создана", 'ERROR');
    }

  } catch (Exception $e) {
    logMessage("Ошибка при обновлении карты сайта: " . $e->getMessage(), 'ERROR');
    logMessage("Стек вызовов: " . $e->getTraceAsString(), 'DEBUG');
  }
} else {
  logMessage('Обновление не требуется');
}

// Проверяем robots.txt
$robotsFile = __DIR__ . '/robots.txt';
if (!file_exists($robotsFile)) {
  logMessage('robots.txt не найден, создаю...', 'WARNING');

  $robotsContent = "User-agent: *\n";
  $robotsContent .= "Allow: /\n\n";
  $robotsContent .= "Disallow: /admin/\n";
  $robotsContent .= "Disallow: /includes/\n";
  $robotsContent .= "Disallow: /logs/\n";
  $robotsContent .= "Disallow: /vendor/\n\n";
  $robotsContent .= "Sitemap: https://cherkas-therapy.ru/sitemap.xml\n";
  $robotsContent .= "Host: https://cherkas-therapy.ru\n";

  if (file_put_contents($robotsFile, $robotsContent)) {
    logMessage("robots.txt успешно создан", 'SUCCESS');
  } else {
    logMessage("Ошибка при создании robots.txt", 'ERROR');
  }
}

// Проверяем права доступа
$filesToCheck = [
  'sitemap.xml' => 644,
  'robots.txt' => 644,
  'logs/' => 755
];

foreach ($filesToCheck as $file => $permissions) {
  $filePath = __DIR__ . '/' . $file;
  if (file_exists($filePath)) {
    $currentPerms = substr(sprintf('%o', fileperms($filePath)), -3);
    if ($currentPerms != $permissions) {
      chmod($filePath, $permissions);
      logMessage("Исправлены права доступа для $file: $currentPerms → $permissions", 'INFO');
    }
  }
}

logMessage('Автоматическое обновление завершено');
logMessage('Следующая проверка: ' . date('Y-m-d H:i:s', time() + $updateInterval));
?>