/**
 * Улучшения для страницы статьи
 */

document.addEventListener('DOMContentLoaded', function () {
  // Инициализация всех улучшений
  initArticleEnhancements()

  function initArticleEnhancements() {
    console.log('Инициализация улучшений страницы...')

    // Инициализация темы
    initTheme()

    // Улучшенная навигация по заголовкам
    initTableOfContents()

    // Улучшенные изображения
    initImageEnhancements()

    // Улучшенные кнопки действий
    initActionButtons()

    // Улучшенная читаемость
    initReadabilityEnhancements()

    // Улучшенная производительность
    initPerformanceOptimizations()

    // Улучшенная доступность
    initAccessibilityEnhancements()

    console.log('Все улучшения инициализированы')
  }

  /**
   * Инициализация темы
   */
  function initTheme() {
    console.log('Инициализация темы...')

    // Проверяем сохраненную тему при загрузке
    const savedTheme = localStorage.getItem('article-theme')
    if (savedTheme === 'dark') {
      document.body.classList.add('dark-mode')
      console.log('Темная тема активирована')
    }

    // Создаем плавающую кнопку переключения темы
    createFloatingThemeToggle()
    console.log('Плавающая кнопка создана')
  }

  /**
   * Создание оглавления для длинных статей
   */
  function initTableOfContents() {
    const articleContent = document.querySelector('.article-content')
    if (!articleContent) return

    const headings = articleContent.querySelectorAll('h2, h3, h4')
    if (headings.length < 3) return // Создаем оглавление только для статей с 3+ заголовками

    const toc = document.createElement('div')
    toc.className = 'article-toc'
    toc.innerHTML = `
            <div class="toc-header">
                <h3>Содержание статьи</h3>
                <button class="toc-toggle" aria-label="Скрыть/показать оглавление">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </button>
            </div>
            <nav class="toc-nav">
                <ul class="toc-list"></ul>
            </nav>
        `

    const tocList = toc.querySelector('.toc-list')

    headings.forEach((heading, index) => {
      // Добавляем ID к заголовкам если их нет
      if (!heading.id) {
        heading.id = `heading-${index}`
      }

      const listItem = document.createElement('li')
      listItem.className = `toc-item toc-${heading.tagName.toLowerCase()}`

      const link = document.createElement('a')
      link.href = `#${heading.id}`
      link.textContent = heading.textContent
      link.className = 'toc-link'

      listItem.appendChild(link)
      tocList.appendChild(listItem)
    })

    // Вставляем оглавление после заголовка статьи
    const articleTitle = document.querySelector('.article-title')
    if (articleTitle) {
      articleTitle.parentNode.insertBefore(toc, articleTitle.nextSibling)
    }

    // Обработчик переключения оглавления
    const tocToggle = toc.querySelector('.toc-toggle')
    const tocNav = toc.querySelector('.toc-nav')

    tocToggle.addEventListener('click', function () {
      tocNav.classList.toggle('toc-hidden')
      tocToggle.classList.toggle('toc-rotated')
    })

    // Плавная прокрутка к заголовкам
    tocList.addEventListener('click', function (e) {
      if (e.target.classList.contains('toc-link')) {
        e.preventDefault()
        const targetId = e.target.getAttribute('href').substring(1)
        const targetElement = document.getElementById(targetId)

        if (targetElement) {
          const headerHeight =
            document.querySelector('header')?.offsetHeight || 0
          const targetPosition = targetElement.offsetTop - headerHeight - 20

          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth',
          })
        }
      }
    })

    // Подсветка текущего раздела при скролле
    let currentSection = ''
    window.addEventListener('scroll', function () {
      const scrollPosition = window.scrollY + 100

      headings.forEach((heading) => {
        const sectionTop = heading.offsetTop
        const sectionHeight = heading.offsetHeight

        if (
          scrollPosition >= sectionTop &&
          scrollPosition < sectionTop + sectionHeight
        ) {
          if (currentSection !== heading.id) {
            currentSection = heading.id

            // Убираем активный класс со всех ссылок
            tocList.querySelectorAll('.toc-link').forEach((link) => {
              link.classList.remove('toc-active')
            })

            // Добавляем активный класс к текущей ссылке
            const activeLink = tocList.querySelector(`[href="#${heading.id}"]`)
            if (activeLink) {
              activeLink.classList.add('toc-active')
            }
          }
        }
      })
    })
  }

  /**
   * Улучшения для изображений
   */
  function initImageEnhancements() {
    const images = document.querySelectorAll('.article-content img')

    images.forEach((img) => {
      // Добавляем атрибуты для ленивой загрузки
      if (!img.loading) {
        img.loading = 'lazy'
      }

      // Добавляем возможность увеличения изображений
      img.style.cursor = 'pointer'
      img.addEventListener('click', function () {
        openImageModal(this.src, this.alt)
      })

      // Добавляем подписи к изображениям если их нет
      if (!img.nextElementSibling?.classList.contains('image-caption')) {
        const caption = document.createElement('p')
        caption.className = 'image-caption'
        caption.textContent = img.alt || 'Изображение'
        caption.style.textAlign = 'center'
        caption.style.fontSize = '0.9rem'
        caption.style.color = '#666'
        caption.style.marginTop = '10px'
        caption.style.fontStyle = 'italic'

        img.parentNode.insertBefore(caption, img.nextSibling)
      }
    })
  }

  /**
   * Модальное окно для изображений
   */
  function openImageModal(src, alt) {
    const modal = document.createElement('div')
    modal.className = 'image-modal'
    modal.innerHTML = `
            <div class="image-modal-overlay"></div>
            <div class="image-modal-content">
                <button class="image-modal-close" aria-label="Закрыть">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
                <img src="${src}" alt="${alt}" class="image-modal-img">
                <p class="image-modal-caption">${alt}</p>
            </div>
        `

    document.body.appendChild(modal)

    // Анимация появления
    setTimeout(() => modal.classList.add('image-modal-visible'), 10)

    // Закрытие по клику на оверлей или кнопку
    modal.addEventListener('click', function (e) {
      if (
        e.target.classList.contains('image-modal-overlay') ||
        e.target.classList.contains('image-modal-close') ||
        e.target.closest('.image-modal-close')
      ) {
        closeImageModal(modal)
      }
    })

    // Закрытие по Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeImageModal(modal)
      }
    })
  }

  function closeImageModal(modal) {
    modal.classList.remove('image-modal-visible')
    setTimeout(() => modal.remove(), 300)
  }

  /**
   * Улучшения для кнопок действий
   */
  function initActionButtons() {
    const likeBtn = document.querySelector('.article-like-btn')
    const shareBtn = document.querySelector('.article-share-btn')

    if (likeBtn) {
      // Анимация лайка
      likeBtn.addEventListener('click', function () {
        this.classList.toggle('liked')

        if (this.classList.contains('liked')) {
          const counter = this.querySelector('.like-counter')
          if (counter) {
            const currentCount = parseInt(counter.textContent) || 0
            counter.textContent = currentCount + 1
          }

          // Анимация сердца
          const heart = this.querySelector('svg')
          heart.style.transform = 'scale(1.3)'
          setTimeout(() => {
            heart.style.transform = 'scale(1)'
          }, 200)
        } else {
          const counter = this.querySelector('.like-counter')
          if (counter) {
            const currentCount = parseInt(counter.textContent) || 0
            if (currentCount > 0) {
              counter.textContent = currentCount - 1
            }
          }
        }
      })
    }

    if (shareBtn) {
      // Улучшенное разделение
      shareBtn.addEventListener('click', function () {
        if (navigator.share) {
          // Нативное API разделения
          navigator.share({
            title: document.title,
            text: document.querySelector('.article-excerpt')?.textContent || '',
            url: window.location.href,
          })
        } else {
          // Fallback - копирование ссылки
          copyToClipboard(window.location.href)
          showNotification('Ссылка скопирована в буфер обмена')
        }
      })
    }
  }

  /**
   * Улучшения читаемости
   */
  function initReadabilityEnhancements() {
    // Добавляем кнопку изменения размера шрифта
    const articleContent = document.querySelector('.article-content')
    if (!articleContent) return

    const fontSizeControls = document.createElement('div')
    fontSizeControls.className = 'font-size-controls'
    fontSizeControls.innerHTML = `
            <span class="font-size-label">Размер текста:</span>
            <button class="font-size-btn" data-size="small">A-</button>
            <button class="font-size-btn" data-size="normal">A</button>
            <button class="font-size-btn" data-size="large">A+</button>
        `

    articleContent.parentNode.insertBefore(fontSizeControls, articleContent)

    // Обработчики изменения размера шрифта
    const fontSizeBtns = fontSizeControls.querySelectorAll('.font-size-btn')
    fontSizeBtns.forEach((btn) => {
      btn.addEventListener('click', function () {
        const size = this.dataset.size
        setFontSize(size)

        // Обновляем активную кнопку
        fontSizeBtns.forEach((b) => b.classList.remove('active'))
        this.classList.add('active')
      })
    })

    // Устанавливаем размер по умолчанию
    setFontSize('normal')
    fontSizeBtns[1].classList.add('active')
  }

  /**
   * Создание плавающей кнопки переключения темы
   */
  function createFloatingThemeToggle() {
    console.log('Создание плавающей кнопки...')

    const floatingToggle = document.createElement('button')
    floatingToggle.className = 'floating-theme-toggle'
    floatingToggle.innerHTML = `
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
      </svg>
    `
    floatingToggle.setAttribute('aria-label', 'Переключить темную тему')
    floatingToggle.title = 'Переключить темную тему'

    document.body.appendChild(floatingToggle)
    console.log('Плавающая кнопка добавлена в DOM')

    // Проверяем сохраненную тему для плавающей кнопки
    const savedTheme = localStorage.getItem('article-theme')
    if (savedTheme === 'dark') {
      floatingToggle.classList.add('dark-mode-active')
    }

    floatingToggle.addEventListener('click', function () {
      const isDark = document.body.classList.toggle('dark-mode')
      this.classList.toggle('dark-mode-active')

      // Сохраняем выбор пользователя
      localStorage.setItem('article-theme', isDark ? 'dark' : 'light')
    })
  }

  /**
   * Установка размера шрифта
   */
  function setFontSize(size) {
    const articleContent = document.querySelector('.article-content')
    if (!articleContent) return

    const sizes = {
      small: '0.95rem',
      normal: '1.1rem',
      large: '1.25rem',
    }

    // Принудительно переопределяем любые внешние стили
    articleContent.style.setProperty('font-size', sizes[size], 'important')
    localStorage.setItem('article-font-size', size)
  }

  /**
   * Оптимизации производительности
   */
  function initPerformanceOptimizations() {
    // Ленивая загрузка изображений
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const img = entry.target
            img.src = img.dataset.src || img.src
            img.classList.remove('lazy')
            observer.unobserve(img)
          }
        })
      })

      document.querySelectorAll('img[data-src]').forEach((img) => {
        imageObserver.observe(img)
      })
    }

    // Оптимизация скролла
    let ticking = false
    window.addEventListener('scroll', function () {
      if (!ticking) {
        requestAnimationFrame(function () {
          updateScrollEffects()
          ticking = false
        })
        ticking = true
      }
    })
  }

  /**
   * Эффекты при скролле
   */
  function updateScrollEffects() {
    const scrollY = window.scrollY
    const articleMain = document.querySelector('.article-main')

    if (articleMain) {
      // Параллакс эффект для заголовка
      const articleTitle = articleMain.querySelector('.article-title')
      if (articleTitle) {
        const translateY = scrollY * 0.1
        articleTitle.style.transform = `translateY(${translateY}px)`
      }
    }
  }

  /**
   * Улучшения доступности
   */
  function initAccessibilityEnhancements() {
    // Добавляем ARIA-атрибуты
    const articleContent = document.querySelector('.article-content')
    if (articleContent) {
      articleContent.setAttribute('role', 'main')
      articleContent.setAttribute('aria-label', 'Содержание статьи')
    }

    // Улучшенная навигация по клавиатуре
    document.addEventListener('keydown', function (e) {
      // Ctrl/Cmd + Enter для лайка
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const likeBtn = document.querySelector('.article-like-btn')
        if (likeBtn) {
          likeBtn.click()
          e.preventDefault()
        }
      }

      // Ctrl/Cmd + Shift + S для поделиться
      if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'S') {
        const shareBtn = document.querySelector('.article-share-btn')
        if (shareBtn) {
          shareBtn.click()
          e.preventDefault()
        }
      }
    })

    // Добавляем подсказки по клавиатуре
    const likeBtn = document.querySelector('.article-like-btn')
    const shareBtn = document.querySelector('.article-share-btn')

    if (likeBtn) {
      likeBtn.setAttribute('title', 'Лайкнуть статью (Ctrl+Enter)')
    }

    if (shareBtn) {
      shareBtn.setAttribute('title', 'Поделиться статьей (Ctrl+Shift+S)')
    }
  }

  /**
   * Копирование в буфер обмена
   */
  function copyToClipboard(text) {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(text)
    } else {
      // Fallback для старых браузеров
      const textArea = document.createElement('textarea')
      textArea.value = text
      document.body.appendChild(textArea)
      textArea.select()
      document.execCommand('copy')
      document.body.removeChild(textArea)
    }
  }

  /**
   * Показ уведомлений
   */
  function showNotification(message) {
    const notification = document.createElement('div')
    notification.className = 'notification'
    notification.textContent = message

    document.body.appendChild(notification)

    // Анимация появления
    setTimeout(() => notification.classList.add('notification-visible'), 10)

    // Автоматическое скрытие
    setTimeout(() => {
      notification.classList.remove('notification-visible')
      setTimeout(() => notification.remove(), 300)
    }, 3000)
  }

  // Восстанавливаем сохраненные настройки
  const savedFontSize = localStorage.getItem('article-font-size')
  if (savedFontSize) {
    setTimeout(() => setFontSize(savedFontSize), 100)
  }
})

// Добавляем CSS для новых элементов
const style = document.createElement('style')
style.textContent = `
    /* Оглавление */
    .article-toc {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        margin: 30px 0;
        position: sticky;
        top: 20px;
        z-index: 10;
    }
    
    .toc-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .toc-header h3 {
        margin: 0;
        font-size: 1.2rem;
        color: #2c3e50;
    }
    
    .toc-toggle {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .toc-toggle:hover {
        background: #e9ecef;
    }
    
    .toc-toggle.toc-rotated svg {
        transform: rotate(180deg);
    }
    
    .toc-nav {
        transition: all 0.3s ease;
    }
    
    .toc-nav.toc-hidden {
        display: none;
    }
    
    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .toc-item {
        margin-bottom: 8px;
    }
    
    .toc-item.toc-h3 {
        margin-left: 20px;
    }
    
    .toc-item.toc-h4 {
        margin-left: 40px;
    }
    
    .toc-link {
        color: #666;
        text-decoration: none;
        transition: color 0.3s ease;
        display: block;
        padding: 5px 0;
        border-radius: 4px;
        padding-left: 10px;
    }
    
    .toc-link:hover {
        color: #7a91b7;
        background: #e9ecef;
    }
    
    .toc-link.toc-active {
        color: #7a91b7;
        background: #e9ecef;
        font-weight: 600;
    }
    
    /* Контролы размера шрифта */
    .font-size-controls {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    
    .font-size-label {
        font-size: 0.9rem;
        color: #666;
        font-weight: 500;
    }
    
    .font-size-btn {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 5px 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
    }
    
    .font-size-btn:hover {
        border-color: #7a91b7;
        background: #f8f9fa;
    }
    
    .font-size-btn.active {
        background: #7a91b7;
        color: white;
        border-color: #7a91b7;
    }
    
    
    
    /* Модальное окно для изображений */
    .image-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .image-modal.image-modal-visible {
        opacity: 1;
        visibility: visible;
    }
    
    .image-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
    }
    
    .image-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 12px;
        padding: 20px;
        max-width: 90vw;
        max-height: 90vh;
        overflow: auto;
    }
    
    .image-modal-close {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
        border-radius: 4px;
        transition: background 0.3s ease;
    }
    
    .image-modal-close:hover {
        background: #f8f9fa;
    }
    
    .image-modal-img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
    }
    
    .image-modal-caption {
        margin-top: 15px;
        text-align: center;
        color: #666;
        font-style: italic;
    }
    
    /* Уведомления */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #7a91b7;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: 1001;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }
    
    .notification.notification-visible {
        transform: translateX(0);
    }
    
    /* Плавающая кнопка переключения темы */
    .floating-theme-toggle {
        position: fixed;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #7a91b7 0%, #a8bad5 100%);
        border: none;
        border-radius: 50%;
        color: white;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(122, 145, 183, 0.3);
        transition: all 0.3s ease;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .floating-theme-toggle:hover {
        transform: translateY(-50%) scale(1.1);
        box-shadow: 0 6px 25px rgba(122, 145, 183, 0.4);
    }
    
    .floating-theme-toggle.dark-mode-active {
        background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        color: #1a1a1a;
    }
    
    .floating-theme-toggle.dark-mode-active:hover {
        box-shadow: 0 6px 25px rgba(255, 215, 0, 0.4);
    }
    
    /* Мобильные стили для плавающей кнопки */
    @media (max-width: 768px) {
        .floating-theme-toggle {
            width: 45px;
            height: 45px;
            right: 15px;
        }
    }
    
    @media (max-width: 480px) {
        .floating-theme-toggle {
            width: 40px;
            height: 40px;
            right: 10px;
        }
    }

    /* Полноценная темная тема для всей страницы */
    .dark-mode {
        background: #0d1117 !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .header {
        background: #161b22 !important;
        border-bottom: 1px solid #30363d !important;
    }
    
    .dark-mode .header__content {
        background: #161b22 !important;
    }
    
    .dark-mode .header__logo img {
        filter: brightness(0.9) !important;
    }
    
    .dark-mode .header__mobile-contacts {
        color: #e6edf3 !important;
    }
    
    .dark-mode .header__mobile-phone a {
        color: #58a6ff !important;
    }
    
    .dark-mode .header__menu-btn span {
        background: #e6edf3 !important;
    }
    
    .dark-mode .article-single {
        background: #0d1117 !important;
    }
    
    .dark-mode .article-main {
        background: #161b22 !important;
        color: #e6edf3 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .article-title {
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-content {
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-meta {
        border-color: #30363d !important;
        color: #8b949e !important;
    }
    
    .dark-mode .article-category {
        background: linear-gradient(135deg, #58a6ff 0%, #79c0ff 100%) !important;
        color: #0d1117 !important;
    }
    
    .dark-mode .article-read-time,
    .dark-mode .article-date,
    .dark-mode .article-author {
        color: #8b949e !important;
    }
    
    .dark-mode .article-read-time svg,
    .dark-mode .article-date svg,
    .dark-mode .article-author svg {
        color: #58a6ff !important;
    }
    
    .dark-mode .article-excerpt {
        color: #8b949e !important;
    }
    
    .dark-mode .article-tags {
        color: #8b949e !important;
    }
    
    .dark-mode .article-tag {
        background: #21262d !important;
        color: #e6edf3 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .article-actions {
        border-color: #30363d !important;
    }
    
    .dark-mode .article-like-btn,
    .dark-mode .article-share-btn {
        background: #21262d !important;
        color: #e6edf3 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .article-like-btn:hover,
    .dark-mode .article-share-btn:hover {
        background: #30363d !important;
    }
    
    .dark-mode .article-share-social {
        background: #21262d !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .article-share-btn--telegram,
    .dark-mode .article-share-btn--whatsapp,
    .dark-mode .article-share-btn--vk {
        background: #21262d !important;
        color: #e6edf3 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .article-share-btn--telegram:hover {
        background: #0088cc !important;
        color: white !important;
    }
    
    .dark-mode .article-share-btn--whatsapp:hover {
        background: #25d366 !important;
        color: white !important;
    }
    
    .dark-mode .article-share-btn--vk:hover {
        background: #0077ff !important;
        color: white !important;
    }
    
    .dark-mode .article-sidebar {
        background: #161b22 !important;
    }
    
    .dark-mode .sidebar-block {
        background: #161b22 !important;
        border: 1px solid #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .sidebar-block h3 {
        color: #e6edf3 !important;
    }
    
    .dark-mode .sidebar-block p {
        color: #8b949e !important;
    }
    
    .dark-mode .consultation-btn {
        background: linear-gradient(135deg, #58a6ff 0%, #79c0ff 100%) !important;
        color: #0d1117 !important;
    }
    
    .dark-mode .telegram-btn {
        background: linear-gradient(135deg, #0088cc 0%, #00a8ff 100%) !important;
    }
    
    .dark-mode .unit__cta {
        background: linear-gradient(135deg, #58a6ff 0%, #79c0ff 100%) !important;
        color: #0d1117 !important;
    }
    
    .dark-mode .article-toc {
        background: #161b22 !important;
        border: 1px solid #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .font-size-controls {
        background: #161b22 !important;
        border: 1px solid #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .font-size-btn {
        background: #21262d !important;
        border: 1px solid #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .font-size-btn:hover {
        background: #30363d !important;
    }
    
    .dark-mode .font-size-btn.active {
        background: #58a6ff !important;
        color: #0d1117 !important;
    }
    
    
    
    /* Темная тема для мобильного меню */
    .dark-mode .header__nav {
        background: #161b22 !important;
        border-bottom: 1px solid #30363d !important;
    }
    
    .dark-mode .header__nav-close {
        color: #e6edf3 !important;
    }
    
    .dark-mode .header__nav-close:hover {
        background: #21262d !important;
        color: #58a6ff !important;
    }
    
    .dark-mode .nav-link {
        color: #e6edf3 !important;
        border-bottom-color: #30363d !important;
    }
    
    .dark-mode .nav-link:hover,
    .dark-mode .nav-link.active {
        background: #21262d !important;
        color: #58a6ff !important;
    }
    
    .dark-mode .header__nav-contacts {
        background: #161b22 !important;
        border-color: #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .header__nav-contact-item {
        color: #8b949e !important;
    }
    
    .dark-mode .header__nav-phone a {
        color: #58a6ff !important;
    }
    
    .dark-mode .header__nav-call-btn {
        background: #58a6ff !important;
        color: #0d1117 !important;
    }
    
    .dark-mode .header__nav-call-btn:hover {
        background: #79c0ff !important;
    }
    
    /* Десктопное меню в темной теме */
    .dark-mode .header__nav {
        background: #161b22 !important;
    }
    
    .dark-mode .header__nav .nav-link {
        color: #e6edf3 !important;
        background: transparent !important;
    }
    
    .dark-mode .header__nav .nav-link:hover,
    .dark-mode .header__nav .nav-link.active {
        color: #58a6ff !important;
        background: #21262d !important;
    }
    
    /* Переопределение жестко заданных цветов */
    .dark-mode .header__nav .nav-link {
        color: #e6edf3 !important;
    }
    
    /* Исправление белой области вокруг шапки */
    .dark-mode .header {
        background: #161b22 !important;
    }
    
    .dark-mode .header__top {
        background: #161b22 !important;
    }
    
    .dark-mode .header__content {
        background: #161b22 !important;
    }
    
    .dark-mode .container {
        background: #161b22 !important;
    }
    
    /* Убираем белый фон из new-homepage.css */
    body.page .header__top > .container {
        background: transparent !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    
    /* Светлый фон для всей страницы */
    body.page {
        background: #f8f9fa !important;
        color: #2c3e50 !important;
    }
    
    .dark-mode body.page {
        background: #0d1117 !important;
        color: #e6edf3 !important;
    }
    
    /* Переопределение белого фона из new-homepage.css для темной темы */
    .dark-mode body.page .header__top > .container {
        background: #161b22 !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    
    /* Исправление элементов меню в шапке */
    .dark-mode .header__info,
    .dark-mode .header__social,
    .dark-mode .header__contacts {
        color: #e6edf3 !important;
    }
    
    .dark-mode .header__info span,
    .dark-mode .header__social p,
    .dark-mode .header__social strong {
        color: #e6edf3 !important;
    }
    
    .dark-mode .header__social a {
        color: #58a6ff !important;
    }
    
    .dark-mode .header__contacts a {
        color: #58a6ff !important;
    }
    
    .dark-mode .header__contacts a:hover {
        color: #79c0ff !important;
    }
    
    /* Исправление темных цветов в ночном режиме */
    .dark-mode .page-title,
    .dark-mode .section-title,
    .dark-mode .article-card__title,
    .dark-mode .article-card__title a,
    .dark-mode .article-card__excerpt,
    .dark-mode .article-card__meta,
    .dark-mode .breadcrumb-item,
    .dark-mode .breadcrumb-item a {
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-card {
        background: #161b22 !important;
        border: 1px solid #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-card:hover {
        background: #21262d !important;
        border-color: #58a6ff !important;
    }
    
    .dark-mode .article-card__category {
        background: linear-gradient(135deg, #58a6ff 0%, #79c0ff 100%) !important;
        color: #0d1117 !important;
    }
    
    .dark-mode .pagination-btn {
        background: #21262d !important;
        color: #e6edf3 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .pagination-btn:hover {
        background: #30363d !important;
        border-color: #58a6ff !important;
    }
    
    .dark-mode .pagination-btn.active {
        background: #58a6ff !important;
        color: #0d1117 !important;
    }
    
    /* Стили для блока "Последние статьи" в темной теме */
    .dark-mode .recent-articles-block {
        background: #161b22 !important;
        border: 1px solid #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .recent-articles-block h3 {
        color: #e6edf3 !important;
    }
    
    .dark-mode .recent-articles-block .article-item {
        background: #21262d !important;
        border: 1px solid #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .recent-articles-block .article-item:hover {
        background: #30363d !important;
        border-color: #58a6ff !important;
    }
    
    .dark-mode .recent-articles-block .article-item img {
        filter: brightness(0.9) !important;
    }
    
    .dark-mode .recent-articles-block .article-item h4,
    .dark-mode .recent-articles-block .article-item h4 a {
        color: #e6edf3 !important;
    }
    
    .dark-mode .recent-articles-block .article-item h4 a:hover {
        color: #58a6ff !important;
    }
    
    .dark-mode .recent-articles-block .article-item .article-date {
        color: #8b949e !important;
    }
    
    /* Убираем белый фон у элементов статей в ночном режиме */
    .dark-mode .recent-article-item {
        background: transparent !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .recent-article-info h4 {
        color: #e6edf3 !important;
    }
    
    .dark-mode .recent-article-info h4 a {
        color: #e6edf3 !important;
    }
    
    .dark-mode .recent-article-info h4 a:hover {
        color: #58a6ff !important;
    }
    
    .dark-mode .recent-article-date {
        color: #8b949e !important;
    }
    
    .dark-mode .recent-article-item:hover {
        border-color: #58a6ff !important;
        background: rgba(88, 166, 255, 0.05) !important;
    }
    
    /* Стили для светлой темы */
    body.page .recent-article-info h4 {
        color: #2c3e50 !important;
    }
    
    body.page .recent-article-info h4 a {
        color: #2c3e50 !important;
    }
    
    body.page .recent-article-info h4 a:hover {
        color: #7a91b7 !important;
    }
    
    body.page .recent-article-date {
        color: #666 !important;
    }
    
    body.page .unit__text {
        color: #2c3e50 !important;
    }
    
    body.page .unit__title {
        color: #2c3e50 !important;
    }
    
    /* Стили для страницы статей в темной теме */
    .dark-mode .articles-hero {
        background: #0d1117 !important;
    }
    
    .dark-mode .first__title {
        color: #e6edf3 !important;
    }
    
    .dark-mode .first__subtitle {
        color: #8b949e !important;
    }
    
    .dark-mode .form-title p {
        color: #e6edf3 !important;
    }
    
    .dark-mode .form-input {
        background: #21262d !important;
        border: 1px solid #30363d !important;
        color: #e6edf3 !important;
    }
    
    .dark-mode .form-input::placeholder {
        color: #8b949e !important;
    }
    
    .dark-mode .form-btn {
        background: linear-gradient(135deg, #58a6ff 0%, #79c0ff 100%) !important;
        color: #0d1117 !important;
    }
    
    .dark-mode .shop-categories {
        background: #161b22 !important;
    }
    
    .dark-mode .advantages__title {
        color: #e6edf3 !important;
    }
    
    .dark-mode .filters-item {
        background: #21262d !important;
        color: #e6edf3 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .filters-item:hover,
    .dark-mode .filters-item.active {
        background: #58a6ff !important;
        color: #0d1117 !important;
        border-color: #58a6ff !important;
    }
    
    .dark-mode .articles-count {
        background: #161b22 !important;
    }
    
    .dark-mode .articles-count p {
        color: #e6edf3 !important;
    }
    
    .dark-mode .articles-count strong {
        color: #58a6ff !important;
    }
    
    .dark-mode .articles-grid {
        background: #0d1117 !important;
    }
    
    .dark-mode .article-card {
        background: #161b22 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .article-card:hover {
        background: #21262d !important;
        border-color: #58a6ff !important;
    }
    
    .dark-mode .article-card__category {
        background: linear-gradient(135deg, #58a6ff 0%, #79c0ff 100%) !important;
        color: #0d1117 !important;
    }
    
    .dark-mode .article-card__title a {
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-card__title a:hover {
        color: #58a6ff !important;
    }
    
    .dark-mode .article-card__excerpt {
        color: #8b949e !important;
    }
    
    .dark-mode .article-card__meta {
        color: #8b949e !important;
    }
    
    .dark-mode .pagination {
        background: #161b22 !important;
    }
    
    .dark-mode .pagination-btn {
        background: #21262d !important;
        color: #e6edf3 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .pagination-btn:hover {
        background: #30363d !important;
        border-color: #58a6ff !important;
    }
    
    .dark-mode .pagination-btn.active {
        background: #58a6ff !important;
        color: #0d1117 !important;
    }
    
    /* Исправление всех заголовков и текста в контенте статьи */
    .dark-mode .article-content h1,
    .dark-mode .article-content h2,
    .dark-mode .article-content h3,
    .dark-mode .article-content h4,
    .dark-mode .article-content h5,
    .dark-mode .article-content h6 {
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-content p,
    .dark-mode .article-content div,
    .dark-mode .article-content span,
    .dark-mode .article-content li,
    .dark-mode .article-content ul,
    .dark-mode .article-content ol,
    .dark-mode .article-content blockquote {
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-content a {
        color: #58a6ff !important;
    }
    
    .dark-mode .article-content a:hover {
        color: #79c0ff !important;
    }
    
    .dark-mode .article-content strong,
    .dark-mode .article-content b {
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-content em,
    .dark-mode .article-content i {
        color: #e6edf3 !important;
    }
    
    .dark-mode .article-content code {
        background: #21262d !important;
        color: #e6edf3 !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .article-content pre {
        background: #21262d !important;
        border: 1px solid #30363d !important;
    }
    
    .dark-mode .article-content pre code {
        background: transparent !important;
        border: none !important;
    }
    
    /* Мобильные стили */
    @media (max-width: 768px) {
        .article-toc {
            position: static;
            margin: 20px 0;
        }
        
        .font-size-controls {
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .image-modal-content {
            padding: 15px;
            margin: 20px;
        }
        
        .notification {
            right: 10px;
            left: 10px;
            transform: translateY(-100%);
        }
        
        .notification.notification-visible {
            transform: translateY(0);
        }
    }
`

document.head.appendChild(style)
