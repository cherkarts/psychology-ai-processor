/**
 * Улучшения для страницы магазина v2.1 - Fixed Filters
 */

document.addEventListener('DOMContentLoaded', function () {
  // Инициализация слайдера категорий
  initCategorySlider()

  // Инициализация улучшенных фильтров
  initEnhancedFilters()

  // Инициализация карточек товаров
  initProductCards()

  // Инициализация пагинации
  initPagination()

  // Инициализация дополнительных функций
  initEnhancedSearch()
  initMobileEnhancements()

  // Делегированный обработчик для кнопок добавления в корзину вне карточек (герой продукта)
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.add-to-cart-btn')
    if (!btn) return
    // Карточки обрабатываются отдельно в initProductCards
    if (btn.closest('.product-card')) return
    e.preventDefault()
    addToCartFromButton(btn)
  })

  // Дополнительная проверка и исправление фильтров
  setTimeout(() => {
    const selects = document.querySelectorAll('.filters-form select')
    selects.forEach((select) => {
      select.style.display = 'block'
      select.style.opacity = '1'
      select.style.visibility = 'visible'
      select.style.height = 'auto'
    })
  }, 100)
})

/**
 * Инициализация слайдера категорий
 */
function initCategorySlider() {
  const sliderContainer = document.querySelector(
    '.filters__slider .swiper-container'
  )
  if (!sliderContainer) return

  // Проверяем, что Swiper доступен
  if (typeof Swiper === 'undefined') {
    console.warn('Swiper library not loaded')
    return
  }

  // Создаем новый экземпляр Swiper
  const swiper = new Swiper(sliderContainer, {
    direction: 'horizontal',
    slidesPerView: 'auto',
    spaceBetween: 20,
    navigation: {
      nextEl: '.slider-next-btn',
      prevEl: '.slider-prev-btn',
    },
    breakpoints: {
      320: {
        slidesPerView: 2,
        spaceBetween: 10,
      },
      768: {
        slidesPerView: 3,
        spaceBetween: 15,
      },
      1024: {
        slidesPerView: 'auto',
        spaceBetween: 20,
      },
    },
  })

  // Обновляем состояние кнопок навигации
  swiper.on('slideChange', function () {
    updateSliderButtons(swiper)
  })

  // Инициализация состояния кнопок
  updateSliderButtons(swiper)
}

/**
 * Обновление состояния кнопок слайдера
 */
function updateSliderButtons(swiper) {
  const prevBtn = document.querySelector('.slider-prev-btn')
  const nextBtn = document.querySelector('.slider-next-btn')

  if (prevBtn) {
    if (swiper.isBeginning) {
      prevBtn.classList.add('swiper-button-disabled')
    } else {
      prevBtn.classList.remove('swiper-button-disabled')
    }
  }

  if (nextBtn) {
    if (swiper.isEnd) {
      nextBtn.classList.add('swiper-button-disabled')
    } else {
      nextBtn.classList.remove('swiper-button-disabled')
    }
  }
}

/**
 * Инициализация улучшенных фильтров - ИСПРАВЛЕНО
 */
function initEnhancedFilters() {
  const filterForm = document.querySelector('.filters-form')
  if (!filterForm) return

  // Принудительно показываем все элементы select
  const selects = filterForm.querySelectorAll('select')
  selects.forEach((select) => {
    // Убираем любые inline стили, которые могут скрывать элементы
    select.style.display = 'block'
    select.style.opacity = '1'
    select.style.visibility = 'visible'
    select.style.height = 'auto'

    // Добавляем обработчик для автоматической отправки формы при изменении селектов
    select.addEventListener('change', function () {
      // Добавляем небольшую задержку для лучшего UX
      setTimeout(() => {
        filterForm.submit()
      }, 300)
    })
  })

  // Улучшенная валидация формы
  filterForm.addEventListener('submit', function (e) {
    // Можно добавить дополнительную валидацию здесь
    console.log('Фильтры применены')
  })

  // Обработчик для кнопки "Применить"
  const applyBtn = filterForm.querySelector('.filter-btn')
  if (applyBtn) {
    applyBtn.addEventListener('click', function (e) {
      // Добавляем анимацию кнопки
      this.style.transform = 'scale(0.95)'
      setTimeout(() => {
        this.style.transform = ''
      }, 150)
    })
  }
}

/**
 * Инициализация карточек товаров
 */
function initProductCards() {
  const productCards = document.querySelectorAll('.product-card')

  productCards.forEach((card) => {
    // Проверяем, не инициализирована ли уже карточка
    if (card.dataset.initialized === 'true') {
      return
    }

    // Добавляем анимацию при наведении
    card.addEventListener('mouseenter', function () {
      this.style.transform = 'translateY(-8px)'
    })

    card.addEventListener('mouseleave', function () {
      this.style.transform = 'translateY(0)'
    })

    // Обработка кнопки "В корзину"
    const addToCartBtn = card.querySelector('.add-to-cart-btn')
    if (addToCartBtn) {
      addToCartBtn.addEventListener('click', function (e) {
        e.preventDefault()
        const productId = this.getAttribute('data-product-id')
        addToCart(productId, card)
      })
    }

    // Обработка кнопки "Подробнее"
    const detailsBtn = card.querySelector('.product-card__btn--secondary')
    if (detailsBtn) {
      detailsBtn.addEventListener('click', function (e) {
        // Анимация клика
        this.style.transform = 'scale(0.95)'
        setTimeout(() => {
          this.style.transform = ''
        }, 150)
      })
    }

    // Отмечаем карточку как инициализированную
    card.dataset.initialized = 'true'
  })
}

/**
 * Добавление товара в корзину
 */
function addToCart(productId, card) {
  // Анимация кнопки
  const btn = card.querySelector('.add-to-cart-btn')
  const originalText = btn.innerHTML

  btn.innerHTML = '<span>Добавлено ✓</span>'
  btn.style.background = 'linear-gradient(135deg, #3c475a 0%, #6a7e9f 100%)'
  btn.disabled = true

  // Отправляем запрос на добавление в корзину
  fetch('/api/cart.php?action=add', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      product_id: productId,
      quantity: 1,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      console.log('Shop Enhancement API Response:', data)
      if (data.success) {
        // Обновляем счетчик корзины
        updateCartCounter(data.data.count)

        // Показываем уведомление
        showNotification('Товар добавлен в корзину!', 'success')
      } else {
        showNotification(data.error || 'Ошибка при добавлении товара', 'error')
        // Возвращаем кнопку в исходное состояние
        btn.innerHTML = originalText
        btn.style.background = ''
        btn.disabled = false
      }
    })
    .catch((error) => {
      console.error('Ошибка:', error)
      showNotification('Ошибка при добавлении товара', 'error')
      // Возвращаем кнопку в исходное состояние
      btn.innerHTML = originalText
      btn.style.background = ''
      btn.disabled = false
    })

  // Возвращаем кнопку в исходное состояние через 2 секунды
  setTimeout(() => {
    btn.innerHTML = originalText
    btn.style.background = ''
    btn.disabled = false
  }, 2000)
}

/**
 * Добавление товара в корзину по кнопке вне карточки
 */
function addToCartFromButton(btn) {
  const productId = btn.getAttribute('data-product-id')
  if (!productId) return

  const originalHTML = btn.innerHTML
  btn.innerHTML = '<span>Добавлено ✓</span>'
  btn.style.background = 'linear-gradient(135deg, #3c475a 0%, #6a7e9f 100%)'
  btn.disabled = true

  fetch('/api/cart.php?action=add', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: String(productId), quantity: 1 }),
  })
    .then((r) => r.json())
    .then((data) => {
      if (data && data.success) {
        const count =
          data.data && typeof data.data.count === 'number' ? data.data.count : 0
        updateCartCounter(count)
        showNotification('Товар добавлен в корзину!', 'success')
      } else {
        showNotification(
          (data && data.error) || 'Ошибка при добавлении товара',
          'error'
        )
        btn.innerHTML = originalHTML
        btn.style.background = ''
        btn.disabled = false
      }
    })
    .catch(() => {
      showNotification('Ошибка при добавлении товара', 'error')
      btn.innerHTML = originalHTML
      btn.style.background = ''
      btn.disabled = false
    })
}

/**
 * Обновление счетчика корзины
 */
function updateCartCounter(count) {
  const cartCounter = document.querySelector('.cart-counter')
  if (cartCounter) {
    cartCounter.textContent = count
    cartCounter.style.display = count > 0 ? 'block' : 'none'
  }
}

/**
 * Показ уведомлений
 */
function showNotification(message, type = 'info') {
  // Создаем элемент уведомления
  const notification = document.createElement('div')
  notification.className = `notification notification--${type}`
  notification.innerHTML = `
        <div class="notification__content">
            <span class="notification__message">${message}</span>
            <button class="notification__close">&times;</button>
        </div>
    `

  // Добавляем стили
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${
          type === 'success'
            ? 'linear-gradient(135deg, #3c475a 0%, #6a7e9f 100%)'
            : type === 'error'
            ? '#dc3545'
            : '#17a2b8'
        };
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        max-width: 300px;
    `

  // Добавляем в DOM
  document.body.appendChild(notification)

  // Показываем анимацию
  setTimeout(() => {
    notification.style.transform = 'translateX(0)'
  }, 100)

  // Обработчик закрытия
  const closeBtn = notification.querySelector('.notification__close')
  closeBtn.addEventListener('click', () => {
    hideNotification(notification)
  })

  // Автоматическое скрытие через 5 секунд
  setTimeout(() => {
    hideNotification(notification)
  }, 5000)
}

/**
 * Скрытие уведомления
 */
function hideNotification(notification) {
  notification.style.transform = 'translateX(100%)'
  setTimeout(() => {
    if (notification.parentNode) {
      notification.parentNode.removeChild(notification)
    }
  }, 300)
}

/**
 * Инициализация пагинации
 */
function initPagination() {
  const paginationLinks = document.querySelectorAll('.pagination a')

  paginationLinks.forEach((link) => {
    link.addEventListener('click', function (e) {
      // Добавляем индикатор загрузки
      showLoadingIndicator()
    })
  })
}

/**
 * Показ индикатора загрузки
 */
function showLoadingIndicator() {
  // Создаем индикатор загрузки
  const loader = document.createElement('div')
  loader.className = 'page-loader'
  loader.innerHTML = `
        <div class="loader-spinner"></div>
        <p>Загрузка...</p>
    `

  // Добавляем стили
  loader.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.9);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `

  // Стили для спиннера
  const style = document.createElement('style')
  style.textContent = `
        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #d2afa0;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `

  document.head.appendChild(style)
  document.body.appendChild(loader)

  // Удаляем индикатор через 2 секунды (или после загрузки страницы)
  setTimeout(() => {
    if (loader.parentNode) {
      loader.parentNode.removeChild(loader)
    }
  }, 2000)
}

/**
 * Улучшенный поиск с автодополнением
 */
function initEnhancedSearch() {
  const searchInput = document.querySelector('.search-input')
  if (!searchInput) return

  let searchTimeout

  searchInput.addEventListener('input', function () {
    clearTimeout(searchTimeout)

    searchTimeout = setTimeout(() => {
      const query = this.value.trim()
      if (query.length >= 2) {
        // Здесь можно добавить логику автодополнения
        console.log('Поиск:', query)
      }
    }, 300)
  })

  // Обработка Enter
  searchInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
      const form = this.closest('form')
      if (form) {
        form.submit()
      }
    }
  })
}

/**
 * Адаптивность для мобильных устройств
 */
function initMobileEnhancements() {
  // Проверяем, является ли устройство мобильным
  const isMobile = window.innerWidth <= 768

  if (isMobile) {
    // Увеличиваем размеры элементов для мобильных устройств
    const filterGroups = document.querySelectorAll('.filter-group')
    filterGroups.forEach((group) => {
      group.style.marginBottom = '20px'
    })

    // Улучшаем отзывчивость карточек
    const productCards = document.querySelectorAll('.product-card')
    productCards.forEach((card) => {
      card.style.marginBottom = '20px'
    })
  }
}

// Обработка изменения размера окна
window.addEventListener('resize', function () {
  initMobileEnhancements()
})
