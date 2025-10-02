<?php
session_start();
require_once 'includes/functions.php';

$meta = [
  'title' => 'Работа с зависимостями - Психолог Денис Черкас | Москва',
  'description' => 'Профессиональная помощь в преодолении различных видов зависимостей: алкогольной, наркотической, игровой, пищевой. Индивидуальный подход и эффективные методики.',
  'keywords' => 'зависимость, лечение зависимостей, алкоголизм, наркомания, игровая зависимость, пищевая зависимость, психолог, Москва'
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

  <!-- Дополнительные SEO мета-теги -->
  <meta name="author" content="Денис Черкас">
  <meta name="robots" content="index, follow">
  <meta name="language" content="ru">
  <meta name="revisit-after" content="7 days">
  <meta name="distribution" content="global">

  <!-- Canonical URL -->
  <link rel="canonical" href="https://cherkas-therapy.ru/dependencies.php">

  <!-- Open Graph -->
  <meta property="og:title" content="Работа с зависимостями - Психолог Денис Черкас">
  <meta property="og:description" content="Профессиональная помощь в преодолении различных видов зависимостей">
  <meta property="og:image" content="https://cherkas-therapy.ru/image/23-1.jpg">
  <meta property="og:url" content="https://cherkas-therapy.ru/dependencies.php">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="ru_RU">
  <meta property="og:site_name" content="Психолог Денис Черкас">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Работа с зависимостями - Психолог Денис Черкас">
  <meta name="twitter:description" content="Профессиональная помощь в преодолении различных видов зависимостей">
  <meta name="twitter:image" content="https://cherkas-therapy.ru/image/23-1.jpg">

  <!-- Дополнительные мета-теги для голосовых помощников -->
  <meta name="voice-search-friendly" content="true">
  <meta name="voice-search-keywords"
    content="лечение зависимостей, психолог зависимостей, помощь при зависимостях, избавиться от зависимости">

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

  <!-- Структурированные данные для голосовых помощников и нейросетей -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Service",
    "name": "Работа с зависимостями",
    "description": "Профессиональная помощь в преодолении различных видов зависимостей: алкогольной, наркотической, игровой, пищевой",
    "provider": {
      "@type": "Person",
      "name": "Денис Черкас",
      "jobTitle": "Психолог",
      "description": "Специалист по зависимостям и созависимости",
      "telephone": "+79936202951",
      "email": "cherkarts.denis@gmail.com",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Москва",
        "addressCountry": "RU"
      }
    },
    "serviceType": "Психологическая помощь",
    "areaServed": {
      "@type": "City",
      "name": "Москва"
    },
    "hasOfferCatalog": {
      "@type": "OfferCatalog",
      "name": "Услуги по работе с зависимостями",
      "itemListElement": [
        {
          "@type": "Offer",
          "itemOffered": {
            "@type": "Service",
            "name": "Консультация по зависимостям",
            "description": "Первичная консультация для оценки состояния и составления плана лечения"
          }
        },
        {
          "@type": "Offer",
          "itemOffered": {
            "@type": "Service",
            "name": "Терапия зависимостей",
            "description": "Индивидуальная работа по преодолению зависимости"
          }
        }
      ]
    },
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "4.9",
      "reviewCount": "150"
    },
    "priceRange": "от 2500₽",
    "availableChannel": {
      "@type": "ServiceChannel",
      "serviceUrl": "https://cherkas-therapy.ru/dependencies.php",
      "serviceType": "Онлайн консультации"
    }
  }
  </script>

  <!-- FAQ структурированные данные -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "Какие виды зависимостей вы лечите?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Я работаю с алкогольной, наркотической, игровой, пищевой и другими видами зависимостей. Каждый случай индивидуален и требует особого подхода."
        }
      },
      {
        "@type": "Question",
        "name": "Сколько времени нужно для лечения зависимости?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Длительность лечения зависит от тяжести зависимости и индивидуальных особенностей. В среднем курс составляет 3-6 месяцев."
        }
      },
      {
        "@type": "Question",
        "name": "Можно ли лечиться онлайн?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "Да, я провожу онлайн консультации. Это удобно и эффективно, особенно для начальных этапов работы."
        }
      }
    ]
  }
  </script>
</head>

<body class="page dependencies-page">
  <?php include 'includes/new-header.php'; ?>

  <!-- Hero секция -->
  <section class="dependencies-hero">
    <div class="wrapper">
      <div class="dependencies-hero__content">
        <div class="dependencies-hero__badge">
          <span>Специализация</span>
        </div>
        <h1 class="dependencies-hero__title md-main-title">
          РАБОТА С <span class="dependencies-hero__title-accent">ЗАВИСИМОСТЯМИ</span>
        </h1>
        <p class="dependencies-hero__subtitle">
          Профессиональная помощь в преодолении различных видов зависимостей.
          Использую проверенные методики и индивидуальный подход для достижения устойчивых результатов.
        </p>
        <div class="dependencies-hero__stats">
          <div class="hero-stat">
            <span class="hero-stat__number">85%</span>
            <span class="hero-stat__label">Успешных случаев</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat__number">6+</span>
            <span class="hero-stat__label">Лет опыта</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat__number">200+</span>
            <span class="hero-stat__label">Клиентов</span>
          </div>
        </div>
      </div>
    </div>
    <div class="dependencies-hero__decoration">
      <div class="hero-shape hero-shape--1"></div>
      <div class="hero-shape hero-shape--2"></div>
      <div class="hero-shape hero-shape--3"></div>
    </div>
  </section>

  <!-- Виды зависимостей -->
  <section class="dependencies-types">
    <div class="wrapper">
      <div class="dependencies-types__header">
        <h2 class="dependencies-types__title">Виды зависимостей, с которыми я работаю</h2>
        <p class="dependencies-types__subtitle">
          Каждый вид зависимости требует особого подхода и специализированных методик
        </p>
      </div>
      <div class="dependencies-types__grid">

        <div class="dependency-card">
          <div class="dependency-card__icon">
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
          <h3 class="dependency-card__title">Алкогольная зависимость</h3>
          <p class="dependency-card__description">
            Комплексная работа с алкогольной зависимостью, включая мотивацию к лечению,
            работу с семьей и профилактику рецидивов.
          </p>
          <ul class="dependency-card__features">
            <li>Мотивационное интервью</li>
            <li>Когнитивно-поведенческая терапия</li>
            <li>Работа с семьей</li>
            <li>Профилактика рецидивов</li>
          </ul>
        </div>

        <div class="dependency-card">
          <div class="dependency-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#d2afa0" opacity="0.1" />
              <path
                d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4zm0 36c-8.837 0-16-7.163-16-16S15.163 8 24 8s16 7.163 16 16-7.163 16-16 16z"
                fill="#d2afa0" />
              <path d="M20 16h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="#d2afa0" />
            </svg>
          </div>
          <h3 class="dependency-card__title">Наркотическая зависимость</h3>
          <p class="dependency-card__description">
            Специализированная помощь при наркотической зависимости с учетом
            индивидуальных особенностей и стадии заболевания.
          </p>
          <ul class="dependency-card__features">
            <li>Индивидуальная программа лечения</li>
            <li>Работа с сопутствующими расстройствами</li>
            <li>Поддержка в период реабилитации</li>
            <li>Интеграция в общество</li>
          </ul>
        </div>

        <div class="dependency-card">
          <div class="dependency-card__icon">
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
          <h3 class="dependency-card__title">Игровая зависимость</h3>
          <p class="dependency-card__description">
            Современный подход к лечению игровой зависимости, включая
            компьютерные игры, азартные игры и социальные сети.
          </p>
          <ul class="dependency-card__features">
            <li>Диагностика игрового поведения</li>
            <li>Техники контроля времени</li>
            <li>Развитие альтернативных интересов</li>
            <li>Работа с семьей</li>
          </ul>
        </div>

        <div class="dependency-card">
          <div class="dependency-card__icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
              <circle cx="24" cy="24" r="24" fill="#d2afa0" opacity="0.1" />
              <path
                d="M24 4C12.954 4 4 12.954 4 24s8.954 20 20 20 20-8.954 20-20S35.046 4 24 4zm0 36c-8.837 0-16-7.163-16-16S15.163 8 24 8s16 7.163 16 16-7.163 16-16 16z"
                fill="#d2afa0" />
              <path d="M20 16h8v2h-8v-2zm0 4h8v2h-8v-2zm0 4h8v2h-8v-2z" fill="#d2afa0" />
            </svg>
          </div>
          <h3 class="dependency-card__title">Пищевая зависимость</h3>
          <p class="dependency-card__description">
            Работа с пищевым поведением, эмоциональным перееданием и
            расстройствами пищевого поведения.
          </p>
          <ul class="dependency-card__features">
            <li>Анализ пищевого поведения</li>
            <li>Работа с эмоциональными триггерами</li>
            <li>Формирование здоровых привычек</li>
            <li>Нормализация отношений с едой</li>
          </ul>
        </div>

      </div>
    </div>
  </section>

  <!-- Методы работы -->
  <section class="dependencies-methods">
    <div class="wrapper">
      <div class="dependencies-methods__header">
        <h2 class="dependencies-methods__title">Методы и подходы в работе</h2>
        <p class="dependencies-methods__subtitle">
          Использую научно обоснованные методики, доказавшие свою эффективность
        </p>
      </div>
      <div class="dependencies-methods__grid">

        <div class="method-item">
          <div class="method-item__number">01</div>
          <h3 class="method-item__title">Мотивационное интервью</h3>
          <p class="method-item__description">
            Помогаю клиенту осознать проблему и сформировать внутреннюю мотивацию
            к изменениям. Это ключевой этап в работе с зависимостями.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">02</div>
          <h3 class="method-item__title">Когнитивно-поведенческая терапия</h3>
          <p class="method-item__description">
            Работаю с искаженными убеждениями и автоматическими мыслями,
            которые поддерживают зависимое поведение.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">03</div>
          <h3 class="method-item__title">Диалектическая поведенческая терапия</h3>
          <p class="method-item__description">
            Помогаю развить навыки эмоциональной регуляции, осознанности
            и эффективного взаимодействия с окружающими.
          </p>
        </div>

        <div class="method-item">
          <div class="method-item__number">04</div>
          <h3 class="method-item__title">Семейная терапия</h3>
          <p class="method-item__description">
            Работаю с семьей зависимого человека, помогая восстановить
            здоровые отношения и создать поддерживающую среду.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- FAQ секция -->
  <section class="dependencies-faq">
    <div class="wrapper">
      <div class="dependencies-faq__header">
        <h2 class="dependencies-faq__title">Часто задаваемые вопросы</h2>
        <p class="dependencies-faq__subtitle">
          Ответы на самые популярные вопросы о работе с зависимостями
        </p>
      </div>
      <div class="faq-accordion">

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Сколько времени нужно для лечения зависимости?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Время лечения зависит от вида зависимости, стадии заболевания и индивидуальных особенностей. В среднем
              курс составляет 3-6 месяцев с еженедельными встречами. Важно понимать, что зависимость - это хроническое
              заболевание, требующее длительной работы.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Можно ли лечить зависимость без ведома зависимого?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Лечение зависимого человека без его согласия невозможно и неэффективно. Однако я могу работать с семьей
              зависимого, помогая им понять проблему, научиться правильно взаимодействовать с зависимым и мотивировать
              его к лечению.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Какая вероятность рецидива после лечения?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Рецидивы - это нормальная часть процесса выздоровления. При правильном подходе и соблюдении рекомендаций
              вероятность рецидива снижается до 20-30%. Важно продолжать поддерживающую терапию и работу над собой.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Нужно ли принимать лекарства при лечении зависимости?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Медикаментозное лечение может быть рекомендовано в некоторых случаях, особенно при тяжелых формах
              зависимости или наличии сопутствующих психических расстройств. Решение принимается индивидуально после
              консультации с психиатром.</p>
          </div>
        </div>

        <div class="faq-item">
          <div class="faq-item__header" onclick="toggleFaq(this)">
            <h3 class="faq-item__question">Можно ли лечить зависимость онлайн?</h3>
            <span class="faq-item__icon">+</span>
          </div>
          <div class="faq-item__content">
            <p>Да, онлайн-консультации эффективны для лечения многих видов зависимостей. Они особенно подходят для
              поддерживающей терапии и работы с легкими формами зависимости. При тяжелых случаях может потребоваться
              очная встреча.</p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- CTA секция -->
  <section class="dependencies-cta">
    <div class="wrapper">
      <div class="dependencies-cta__content">
        <div class="dependencies-cta__icon">
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
        <h2 class="dependencies-cta__title">Готовы избавиться от зависимости?</h2>
        <p class="dependencies-cta__text">
          Запишитесь на бесплатную консультацию и получите персональный план лечения.
          Первый шаг к свободе - это обращение за помощью.
        </p>
        <button class="dependencies-cta__btn md-main-color-btn" popupOpen="call-back-popup"
          data-form-source="Зависимости: Готовы избавиться от зависимости?">
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