<?php
// Конфигурация для переключения между dev и prod версиями
define('ENVIRONMENT', 'development'); // 'development' или 'production'

// Функция для получения правильных CSS файлов
function getCssFiles() {
    if (ENVIRONMENT === 'production') {
        return [
            '/css/production/unified-styles.min.css',
            '/css/production/main.min.css',
            '/css/fancybox.css',
            '/css/font.css',
            '/css/mobile.css',
            '/css/production/pages.min.css'
        ];
    } else {
        return [
            '/css/unified-styles.css',
            '/css/main.css',
            '/css/fancybox.css',
            '/css/font.css',
            '/css/mobile.css',
            '/css/pages.css'
        ];
    }
}

// Функция для вывода CSS файлов
function outputCssFiles() {
    $cssFiles = getCssFiles();
    foreach ($cssFiles as $cssFile) {
        echo '<link rel="stylesheet" href="' . $cssFile . '" />' . "\n";
    }
}
?> 