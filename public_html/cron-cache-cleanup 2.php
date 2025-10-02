<?php
/**
 * Скрипт для автоматической очистки устаревшего кэша
 * Рекомендуется запускать раз в день через cron
 * 
 * Добавьте в crontab:
 * 0 3 * * * /usr/bin/php /path/to/public_html/cron-cache-cleanup.php
 */

// Подключаем систему кэширования
require_once __DIR__ . '/includes/Cache.php';

// Запускаем очистку
$cache = Cache::getInstance();
$deletedCount = $cache->clearExpired();

// Логируем результат
$logMessage = date('Y-m-d H:i:s') . " - Очищено устаревших файлов кэша: {$deletedCount}\n";
file_put_contents(__DIR__ . '/logs/cache-cleanup.log', $logMessage, FILE_APPEND);

// Выводим результат
echo "Очистка кэша завершена. Удалено файлов: {$deletedCount}\n";

// Опционально: показываем текущий размер кэша
$cacheSize = $cache->getSize();
$cacheSizeMB = round($cacheSize / 1024 / 1024, 2);
echo "Текущий размер кэша: {$cacheSizeMB} MB\n";

// Если размер кэша больше 100MB, очищаем весь кэш
if ($cacheSize > 100 * 1024 * 1024) {
  echo "Размер кэша превышает 100MB, выполняется полная очистка...\n";
  $totalDeleted = $cache->clear();
  echo "Полная очистка выполнена. Удалено файлов: {$totalDeleted}\n";

  $logMessage = date('Y-m-d H:i:s') . " - ПОЛНАЯ очистка кэша. Удалено файлов: {$totalDeleted}\n";
  file_put_contents(__DIR__ . '/logs/cache-cleanup.log', $logMessage, FILE_APPEND);
}

