<?php
/**
 * Подключение ресурсов для универсальной шапки
 * Включает CSS и JavaScript файлы
 */

// Определяем, нужно ли подключать ресурсы
$includeUniversalHeader = true;

// Можно отключить для определенных страниц
$excludePages = ['admin', 'maintenance'];
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

foreach ($excludePages as $excludePage) {
  if (strpos($currentPath, $excludePage) !== false) {
    $includeUniversalHeader = false;
    break;
  }
}

if ($includeUniversalHeader): ?>
  <!-- Универсальная шапка - CSS -->
  <link rel="stylesheet" href="/css/universal-header.css?v=1.0" type="text/css" media="all" />

  <!-- Универсальная шапка - JavaScript -->
  <script src="/js/universal-header.js?v=1.0" defer></script>

  <!-- Дополнительные стили для совместимости -->
  <style>
    /* Обеспечиваем совместимость с существующими стилями */
    .header {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      z-index: 1000 !important;
    }

    /* Отступ для контента под фиксированной шапкой */
    body {
      padding-top: 120px;
    }

    @media (max-width: 768px) {
      body {
        padding-top: 80px;
      }
    }

    /* Плавные переходы для всех элементов шапки */
    .header * {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Улучшенная анимация скрытия */
    .header.header--hidden {
      transform: translateY(-100%) !important;
    }

    .header.header--collapsed {
      transform: translateY(-60px) !important;
    }

    /* Стили для мобильного меню */
    .header__nav.active {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      bottom: 0 !important;
      background: white !important;
      z-index: 1001 !important;
      overflow-y: auto !important;
    }

    /* Анимация кнопки меню */
    .header__menu-btn.active span:nth-child(1) {
      transform: rotate(45deg) translate(5px, 5px) !important;
    }

    .header__menu-btn.active span:nth-child(2) {
      opacity: 0 !important;
    }

    .header__menu-btn.active span:nth-child(3) {
      transform: rotate(-45deg) translate(7px, -6px) !important;
    }

    /* Стили для счетчика корзины */
    .cart-counter {
      background: var(--brand-primary, #333) !important;
      color: white !important;
      border-radius: 50% !important;
      width: 20px !important;
      height: 20px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 12px !important;
      font-weight: 600 !important;
      min-width: 20px !important;
    }

    /* Анимация для счетчика корзины */
    @keyframes pulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.2);
      }

      100% {
        transform: scale(1);
      }
    }

    .cart-counter {
      animation: pulse 0.3s ease-in-out;
    }

    /* Поддержка prefers-reduced-motion */
    @media (prefers-reduced-motion: reduce) {

      .header,
      .header * {
        transition: none !important;
        animation: none !important;
      }
    }
  </style>
<?php endif; ?>
