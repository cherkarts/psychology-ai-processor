<?php
session_start();
require_once 'includes/functions.php';

$meta = [
  'title' => 'Сложности в отношениях - Психолог Денис Черкас | Москва',
  'description' => 'Помощь в решении проблем в отношениях: конфликты, недопонимание, кризисы, развод. Восстановление гармонии и развитие здоровых отношений.',
  'keywords' => 'проблемы в отношениях, семейная терапия, конфликты в семье, кризис отношений, развод, психолог, Москва'
];
$schema = generateSchemaMarkup('person');
?>
<!DOCTYPE html>
<html class="js" lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title><?= e($meta['title']) ?></title>
  <meta name="description" content="<?= e($meta['description']) ?>">
  <meta name="keywords" content="<?= e($meta['keywords']) ?>">

  <!-- Open Graph -->
  <meta property="og:title" content="Сложности в отношениях - Психолог Денис Черкас">
  <meta property="og:description" content="Помощь в решении проблем в отношениях и восстановлении гармонии">
  <meta property="og:image" content="https://cherkas-therapy.ru/image/23-1.jpg">
  <meta property="og:url" content="https://cherkas-therapy.ru/relationships.php">

  <!-- CSRF токен -->
  <meta name="csrf-token" content="<?= e(generateCSRFToken()) ?>">

  <!-- Стили -->
  <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css">
  <link rel="stylesheet" href="css/new-homepage.css?v=7.6">
  <link rel="stylesheet" href="css/fancybox.css">
  <link rel="stylesheet" href="css/header-unification.css">
  <link rel="stylesheet" href="css/pages.css">
  <link rel="stylesheet" href="css/specialization-pages.css">

  <!-- Шрифты -->
  <link rel="preload" href="fonts/Inter/Inter-Regular.woff" as="font" type="font/woff" crossorigin>
  <link rel="preload" href="fonts/Inter/Inter-Bold.woff" as="font" type="font/woff" crossorigin>

  <script type="application/ld+json"><?= $schema ?></script>
</head>

<body class="page relationships-page">
  <?php include 'includes/new-header.php'; ?>

  <!-- Hero секция -->
  <section class="relationships-hero">
    <div class="wrapper">
      <div class="relationships-hero__content">
        <div class="relationships-hero__badge">
          <span>Специализация</span>
        </div>
        <h1 class="relationships-hero__title md-main-title">
          <span class="relationships-hero__title-accent">СЛОЖНОСТИ</span> В ОТНОШЕНИЯХ
        </h1>
        <p class="relationships-hero__subtitle">
          Помогаю решить проблемы в отношениях, восстановить гармонию и развить
          здоровые паттерны взаимодействия. Работаю с парами и семьями.
        </p>
        <div class="relationships-hero__stats">
          <div class="hero-stat">
            <span class="hero-stat__number">88%</span>
            <span class="hero-stat__label">Улучшение отношений</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat__number">6+</span>
            <span class="hero-stat__label">Лет опыта</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat__number">180+</span>
            <span class="hero-stat__label">Пар</span>
          </div>
        </div>
      </div>
    </div>
    <div class="relationships-hero__decoration">
      <div class="hero-shape hero-shape--1"></div>
      <div class="hero-shape hero-shape--2"></div>
      <div class="hero-shape hero-shape--3"></div>
    </div>
  </section>

  <!-- Проблемы в отношениях -->
  <section class="relationships-problems">
    <div class="wrapper">
      <div class="relationships-problems__header">
        <h2 class="relationships-problems__title">Распространенные проблемы в отношениях</h2>
        <p class="relationships-problems__subtitle">
          Каждая проблема имеет решение при правильном подходе
        </p>
      </div>
      <div class="relationships-problems__grid">

        <div class="problem-card">
          <div class="problem-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#6a7e9f" opacity="0.1" />
              <path
                d="M24 8C15.163 8 8 15.163 8 24s7.163 16 16 16 16-7.163 16-16S32.837 8 24 8zm0 28c-6.627 0-12-5.373-12-12S17.373 8 24 8s12 5.373 12 12-5.373 12-12 12z"
                fill="#6a7e9f" />
              <path
                d="M24 16c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zm0 12c-2.206 0-4-1.794-4-4s1.794-4 4-4 4 1.794 4 4-1.794 4-4 4z"
                fill="#6a7e9f" />
            </svg>
          </div>
          <h3 class="problem-card__title">Конфликты и недопонимание</h3>
          <p class="problem-card__description">
            Помогаю научиться конструктивно решать конфликты,
            улучшить коммуникацию и понимание между партнерами.
          </p>
          <ul class="problem-card__features">
            <li>Улучшение коммуникации</li>
            <li>Конструктивное решение конфликтов</li>
            <li>Понимание потребностей партнера</li>
            <li>Развитие эмпатии</li>
          </ul>
        </div>

        <div class="problem-card">
          <div class="problem-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#d2afa0" opacity="0.1" />
              <path
                d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4zm0 36c-8.837 0-16-7.163-16-16S15.163 8 24 8s16 7.163 16 16-7.163 16-16 16z"
                fill="#d2afa0" />
              <path d="M20 16h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="#d2afa0" />
            </svg>
          </div>
          <h3 class="problem-card__title">Кризис в отношениях</h3>
          <p class="problem-card__description">
            Поддержка в период кризиса: потеря чувств, измены,
            разочарование, поиск новых смыслов в отношениях.
          </p>
          <ul class="problem-card__features">
            <li>Анализ причин кризиса</li>
            <li>Восстановление доверия</li>
            <li>Поиск новых смыслов</li>
            <li>Принятие решений</li>
          </ul>
        </div>

        <div class="problem-card">
          <div class="problem-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#6a7e9f" opacity="0.1" />
              <path
                d="M24 8C15.163 8 8 15.163 8 24s7.163 16 16 16 16-7.163 16-16S32.837 8 24 8zm0 28c-6.627 0-12-5.373-12-12S17.373 8 24 8s12 5.373 12 12-5.373 12-12 12z"
                fill="#6a7e9f" />
              <path
                d="M24 16c-4.418 0-8 3.582-8 8s3.582 8 8 8 8-3.582 8-8-3.582-8-8-8zm0 12c-2.206 0-4-1.794-4-4s1.794-4 4-4 4 1.794 4 4-1.794 4-4 4z"
                fill="#6a7e9f" />
            </svg>
          </div>
          <h3 class="problem-card__title">Семейные проблемы</h3>
          <p class="problem-card__description">
            Работа с проблемами в семье: отношения с детьми,
            родителями, родственниками, распределение ролей.
          </p>
          <ul class="problem-card__features">
            <li>Семейная терапия</li>
            <li>Работа с детьми</li>
            <li>Взаимоотношения с родственниками</li>
            <li>Распределение ролей</li>
          </ul>
        </div>

        <div class="problem-card">
          <div class="problem-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#d2afa0" opacity="0.1" />
              <path
                d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4zm0 36c-8.837 0-16-7.163-16-16S15.163 8 24 8s16 7.163 16 16-7.163 16-16 16z"
                fill="#d2afa0" />
              <path d="M20 16h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="#d2afa0" />
            </svg>
          </div>
          <h3 class="problem-card__title">Развод и расставание</h3>
          <p class="problem-card__description">
            Поддержка в процессе развода, помощь в адаптации
            к новым условиям жизни, работа с детьми.
          </p>
          <ul class="problem-card__features">
            <li>Поддержка в процессе развода</li>
            <li>Адаптация к новой жизни</li>
            <li>Работа с детьми</li>
            <li>Построение новых отношений</li>
          </ul>
        </div>

      </div>
    </div>
  </section>

  <!-- Методы работы -->
  <section class="relationships-methods">
    <div class="wrapper">
      <div class="relationships-methods__header">
        <h2 class="relationships-methods__title">Методы работы с отношениями</h2>
        <p class="relationships-methods__subtitle">
          Комплексный подход к решению проблем в отношениях
        </p>
      </div>
      <div class="relationships-methods__grid">

        <div class="method-item">
          <div class="method-item__number">01</div>
          <h3 class="method-item__title">Семейная терапия</h3>
          <p class="method-item__description">
            Работаю со всей семьей, помогая понять динамику отношений,
            выявить паттерны взаимодействия и найти пути улучшения.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">02</div>
          <h3 class="method-item__title">Парная терапия</h3>
          <p class="method-item__description">
            Специализированная работа с парами, направленная на улучшение
            коммуникации, разрешение конфликтов и укрепление связи.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">03</div>
          <h3 class="method-item__title">Индивидуальная терапия</h3>
          <p class="method-item__description">
            Помогаю разобраться в личных проблемах, которые влияют на отношения,
            развить эмоциональную зрелость и самопонимание.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">04</div>
          <h3 class="method-item__title">Системная терапия</h3>
          <p class="method-item__description">
            Рассматриваю проблемы в контексте всей семейной системы,
            помогаю понять взаимосвязи и влияние на отношения.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- Этапы работы -->
  <section class="relationships-stages">
    <div class="wrapper">
      <div class="relationships-stages__header">
        <h2 class="relationships-stages__title">Этапы работы над отношениями</h2>
        <p class="relationships-stages__subtitle">
          Пошаговый подход к решению проблем и улучшению отношений
        </p>
      </div>
      <div class="relationships-stages__grid">

        <div class="stage-item">
          <div class="stage-item__number">1</div>
          <h3 class="stage-item__title">Диагностика проблем</h3>
          <p class="stage-item__description">
            Вместе анализируем текущую ситуацию, выявляем основные проблемы
            и их причины. Определяем цели работы.
          </p>
        </div>

        <div class="stage-item">
          <div class="stage-item__number">2</div>
          <h3 class="stage-item__title">Улучшение коммуникации</h3>
          <p class="stage-item__description">
            Учимся конструктивно общаться, выражать чувства и потребности,
            слушать и понимать партнера.
          </p>
        </div>

        <div class="stage-item">
          <div class="stage-item__number">3</div>
          <h3 class="stage-item__title">Решение конфликтов</h3>
          <p class="stage-item__description">
            Осваиваем техники разрешения конфликтов, находим компромиссы
            и учимся договариваться.
          </p>
        </div>

        <div class="stage-item">
          <div class="stage-item__number">4</div>
          <h3 class="stage-item__title">Укрепление связи</h3>
          <p class="stage-item__description">
            Работаем над восстановлением доверия, близости и взаимопонимания.
            Развиваем новые паттерны взаимодействия.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- FAQ секция -->
  <section class="relationships-faq">
    <div class="wrapper">
      <div class="relationships-faq__header">
        <h2 class="relationships-faq__title">Часто задаваемые вопросы</h2>
        <p class="relationships-faq__subtitle">
          Ответы на популярные вопросы о работе с отношениями
        </p>
      </div>
      <div class="faq-accordion">

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Нужно ли приходить на терапию вдвоем?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Желательно, но не обязательно. Я могу работать как с парой вместе, так и индивидуально с каждым
              партнером. Иногда начинаем с индивидуальных встреч, а затем переходим к совместным. Главное - готовность к
              изменениям у обоих партнеров.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Сколько времени нужно для улучшения отношений?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Время зависит от сложности проблем и готовности партнеров к изменениям. Первые улучшения заметны через
              2-4 недели. Полный курс обычно составляет 3-8 месяцев. При серьезных кризисах может потребоваться больше
              времени.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Можно ли спасти отношения после измены?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Да, отношения можно восстановить после измены, но это требует серьезной работы от обоих партнеров.
              Изменивший партнер должен искренне раскаяться, а пострадавший - быть готовым к прощению. Терапия помогает
              восстановить доверие и залечить раны.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Что делать, если партнер не хочет идти к психологу?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Можно начать с индивидуальной терапии. Часто изменения в одном партнере приводят к изменениям в другом.
              Если партнер категорически не хочет работать над отношениями, возможно, стоит пересмотреть
              целесообразность этих отношений.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Можно ли работать онлайн?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Да, онлайн-терапия эффективна для работы с отношениями. Она особенно удобна для пар, живущих в разных
              городах или имеющих плотный график. При необходимости можем чередовать онлайн и очные встречи.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- CTA секция -->
  <section class="relationships-cta">
    <div class="wrapper">
      <div class="relationships-cta__content">
        <div class="relationships-cta__icon">
          <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
            <circle cx="32" cy="32" r="32" fill="#d2afa0" opacity="0.1" />
            <path
              d="M32 8C18.745 8 8 18.745 8 32s10.745 24 24 24 24-10.745 24-24S45.255 8 32 8zm0 44c-11.046 0-20-8.954-20-20S20.954 12 32 12s20 8.954 20 20-8.954 20-20 20z"
              fill="#d2afa0" />
            <path
              d="M32 20c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm0 20c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z"
              fill="#d2afa0" />
          </svg>
        </div>
        <h2 class="relationships-cta__title">Готовы улучшить ваши отношения?</h2>
        <p class="relationships-cta__text">
          Запишитесь на бесплатную консультацию и получите персональный план работы.
          Первый шаг к гармонии - это обращение за помощью.
        </p>
        <button class="relationships-cta__btn md-main-color-btn" popupOpen="call-back-popup"
          data-form-source="Отношения: Готовы улучшить ваши отношения?">
          <span>ЗАПИСАТЬСЯ НА КОНСУЛЬТАЦИЮ</span>
          <img src="image/phone.svg" alt="" />
        </button>
      </div>
    </div>
  </section>

  <?php include 'includes/new-footer.php'; ?>

  <script src="js/main.js"></script>
  <script src="js/new-components.js"></script>
  <script src="js/fancybox.umd.js"></script>
  <script src="js/script.js"></script>
  <script src="js/jquery.maskedinput.min.js"></script>
  <script src="js/form-handler.js?v=1.5"></script>
  <script src="js/new-homepage.js?v=3.1"></script>

  <script>
    // Инициализация Fancybox
    if (typeof Fancybox !== 'undefined') {
      Fancybox.bind('[data-fancybox]');
    }

    // FAQ аккордеон
    function toggleFaq(header) {
      const item = header.parentElement;
      const content = header.nextElementSibling;
      const icon = header.querySelector('.faq-item__icon');

      if (item.classList.contains('active')) {
        item.classList.remove('active');
        content.style.maxHeight = '0px';
        icon.textContent = '+';
      } else {
        // Закрываем все открытые элементы
        document.querySelectorAll('.faq-item.active').forEach(activeItem => {
          activeItem.classList.remove('active');
          activeItem.querySelector('.faq-item__content').style.maxHeight = '0px';
          activeItem.querySelector('.faq-item__icon').textContent = '+';
        });

        // Открываем текущий элемент
        item.classList.add('active');
        content.style.maxHeight = content.scrollHeight + 'px';
        icon.textContent = '−';
      }
    }

    // Инициализация FAQ при загрузке страницы
    document.addEventListener('DOMContentLoaded', function () {
      // Устанавливаем начальную высоту для всех FAQ элементов
      document.querySelectorAll('.faq-item__content').forEach(content => {
        content.style.maxHeight = '0px';
      });
    });
  </script>

  <!-- Yandex.Metrika counter -->
  <script type="text/javascript">
    (function (m, e, t, r, i, k, a) {
      m[i] = m[i] || function () { (m[i].a = m[i].a || []).push(arguments) };
      m[i].l = 1 * new Date();
      for (var j = 0; j < document.scripts.length; j++) { if (document.scripts[j].src === r) { return; } }
      k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
    })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js?id=103948722', 'ym');

    ym(103948722, 'init', { ssr: true, webvisor: true, clickmap: true, ecommerce: "dataLayer", accurateTrackBounce: true, trackLinks: true });
  </script>
  <noscript>
    <div><img src="https://mc.yandex.ru/watch/103948722" style="position:absolute; left:-9999px;" alt="" /></div>
  </noscript>
</body>

</html>