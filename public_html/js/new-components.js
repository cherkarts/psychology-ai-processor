// Новые компоненты - функциональность хедера и футера

document.addEventListener('DOMContentLoaded', function () {
  // ===== HEADER FUNCTIONALITY =====

  const header = document.querySelector('.header')
  const menuBtn = document.querySelector('.header__menu-btn')
  const nav = document.querySelector('.header__nav')
  const navClose = document.querySelector('.header__nav-close')
  const navLinks = document.querySelectorAll('.nav-link')

  // Инициализация шапки - всегда показываем при загрузке
  if (header) {
    header.style.transform = 'translateY(0)'
  }

  // Открытие/закрытие мобильного меню
  if (menuBtn && nav) {
    menuBtn.addEventListener('click', function () {
      nav.classList.add('active')
      document.body.style.overflow = 'hidden'
      try {
        document.documentElement.style.overflow = 'hidden'
      } catch (e) {}
    })
  }

  if (navClose && nav) {
    navClose.addEventListener('click', function () {
      nav.classList.remove('active')
      document.body.style.overflow = ''
      try {
        document.documentElement.style.overflow = ''
      } catch (e) {}
    })
  }

  // Закрытие меню при клике на ссылку
  navLinks.forEach((link) => {
    link.addEventListener('click', function () {
      nav.classList.remove('active')
      document.body.style.overflow = ''
      try {
        document.documentElement.style.overflow = ''
      } catch (e) {}
    })
  })

  // Закрытие меню при клике вне его
  if (nav) {
    nav.addEventListener('click', function (e) {
      if (e.target === nav) {
        nav.classList.remove('active')
        document.body.style.overflow = ''
        try {
          document.documentElement.style.overflow = ''
        } catch (e) {}
      }
    })
  }

  // Изменение хедера при скролле
  let lastScrollTop = 0
  let scrollTimeout = null

  // Функция для определения мобильного устройства
  function isMobile() {
    return window.innerWidth <= 768
  }

  window.addEventListener('scroll', function () {
    if (!header) return // Проверяем существование header

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop

    if (scrollTop > 100) {
      header.classList.add('scrolled')
    } else {
      header.classList.remove('scrolled')
    }

    // Скрытие/показ хедера только на десктопе
    if (isMobile()) {
      // На мобильных устройствах шапка всегда видна
      header.style.transform = 'translateY(0)'
      return
    }

    // Скрытие/показ хедера при скролле только на десктопе
    clearTimeout(scrollTimeout)

    if (scrollTop > lastScrollTop && scrollTop > 200) {
      // Скрываем шапку при прокрутке вниз
      scrollTimeout = setTimeout(() => {
        header.style.transform = 'translateY(-100%)'
      }, 150)
    } else {
      // Показываем шапку при прокрутке вверх
      header.style.transform = 'translateY(0)'
    }

    lastScrollTop = scrollTop
  })

  // Обработчик изменения размера окна
  window.addEventListener('resize', function () {
    if (isMobile()) {
      // При переходе на мобильное устройство показываем шапку
      if (header) {
        header.style.transform = 'translateY(0)'
      }
    }
  })

  // ===== FOOTER FUNCTIONALITY =====

  // Кнопка "Наверх"
  const scrollToTopBtn = document.getElementById('scrollToTop')

  if (scrollToTopBtn) {
    window.addEventListener('scroll', function () {
      if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.add('visible')
      } else {
        scrollToTopBtn.classList.remove('visible')
      }
    })

    scrollToTopBtn.addEventListener('click', function () {
      window.scrollTo({
        top: 0,
        behavior: 'smooth',
      })
    })
  }

  // ===== POPUP FUNCTIONALITY =====

  const popups = document.querySelectorAll('.popup')
  const popupTriggers = document.querySelectorAll('[data-popup]')
  const popupCloses = document.querySelectorAll('.popup__close')

  // Открытие попапов - отключаем на странице корзины
  if (!window.location.pathname.includes('cart.php')) {
    popupTriggers.forEach((trigger) => {
      trigger.addEventListener('click', function (e) {
        // Проверяем, что клик не в корзине или на элементах корзины
        const cartContainer = e.target.closest(
          '.cart-content, .cart-items, .cart-item, .cart-sidebar'
        )
        if (cartContainer) {
          console.log('Popup: ignoring click in cart container', e.target)
          return
        }

        // Дополнительная проверка на элементы корзины
        if (
          e.target.closest('.quantity-btn') ||
          e.target.closest('.quantity-input') ||
          e.target.closest('.quantity-confirm-btn') ||
          e.target.closest('.remove-item-btn') ||
          e.target.closest('.cart-summary') ||
          e.target.closest('.promo-code-section')
        ) {
          console.log('Popup: ignoring click on cart element', e.target)
          return
        }

        e.preventDefault()
        const popupId = this.getAttribute('data-popup')
        const popup = document.getElementById(popupId)

        if (popup) {
          popup.classList.add('active')
          document.body.style.overflow = 'hidden'
          try {
            document.documentElement.style.overflow = 'hidden'
          } catch (e) {}
        }
      })
    })
  } else {
    console.log('Popup: disabled on cart page')
  }

  // Закрытие попапов
  popupCloses.forEach((close) => {
    close.addEventListener('click', function () {
      const popup = this.closest('.popup')
      if (popup) {
        popup.classList.remove('active')
        document.body.style.overflow = ''
        try {
          document.documentElement.style.overflow = ''
        } catch (e) {}
      }
    })
  })

  // Закрытие попапов при клике на оверлей
  popups.forEach((popup) => {
    popup.addEventListener('click', function (e) {
      if (e.target === this || e.target.classList.contains('popup__overlay')) {
        this.classList.remove('active')
        document.body.style.overflow = ''
        try {
          document.documentElement.style.overflow = ''
        } catch (e) {}
      }
    })
  })

  // Закрытие попапов по Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      const activePopup = document.querySelector('.popup.active')
      if (activePopup) {
        activePopup.classList.remove('active')
        document.body.style.overflow = ''
        try {
          document.documentElement.style.overflow = ''
        } catch (e) {}
      }
    }
  })

  // ===== FORM FUNCTIONALITY =====

  const forms = document.querySelectorAll('.popup__form')

  forms.forEach((form) => {
    form.addEventListener('submit', function (e) {
      e.preventDefault()

      // Простая валидация
      const inputs = this.querySelectorAll('input[required], select[required]')
      let isValid = true

      inputs.forEach((input) => {
        if (!input.value.trim()) {
          isValid = false
          input.style.borderColor = '#e74c3c'
        } else {
          input.style.borderColor = '#ddd'
        }
      })

      if (isValid) {
        // Здесь можно добавить отправку формы
        console.log('Форма отправлена:', new FormData(this))

        // Показываем сообщение об успехе
        showSuccessMessage(this)
      }
    })
  })

  // Функция показа сообщения об успехе
  function showSuccessMessage(form) {
    const formContainer = form.parentElement
    const originalContent = formContainer.innerHTML

    formContainer.innerHTML = `
            <div style="text-align: center; padding: 40px 20px;">
                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" style="color: #28a745; margin-bottom: 20px;">
                    <circle cx="30" cy="30" r="30" fill="currentColor" opacity="0.1"/>
                    <path d="M20 30L27 37L40 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h3 style="color: #28a745; margin-bottom: 10px;">Спасибо!</h3>
                <p style="color: #666; margin-bottom: 20px;">Ваша заявка отправлена. Мы свяжемся с вами в ближайшее время.</p>
                <button onclick="closePopup(this)" style="background: #007bff; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer;">Закрыть</button>
            </div>
        `
  }

  // Функция закрытия попапа
  window.closePopup = function (button) {
    const popup = button.closest('.popup')
    if (popup) {
      popup.classList.remove('active')
      document.body.style.overflow = ''
      try {
        document.documentElement.style.overflow = ''
      } catch (e) {}
    }
  }

  // ===== CART FUNCTIONALITY =====

  const cartCounter = document.querySelector('.cart-counter')

  // Обновление счетчика корзины
  function updateCartCounter(count) {
    if (cartCounter) {
      cartCounter.textContent = count
      cartCounter.style.display = count > 0 ? 'block' : 'none'
    }
  }

  // Инициализация счетчика корзины (можно загружать из localStorage или сервера)
  const cartCount = localStorage.getItem('cartCount') || 0
  updateCartCounter(cartCount)

  // ===== ADDITIONAL STYLES FOR SCROLLED HEADER =====

  // Добавляем стили для скроллированного хедера
  const style = document.createElement('style')
  style.textContent = `
        .header.scrolled {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        
        .header.scrolled .header__logo img {
            height: 35px;
        }
        
        .header.scrolled .header__top {
            padding: 10px 0;
        }
    `
  document.head.appendChild(style)

  // ===== SMOOTH SCROLLING FOR ANCHOR LINKS =====

  const anchorLinks = document.querySelectorAll('a[href^="#"]')

  anchorLinks.forEach((link) => {
    link.addEventListener('click', function (e) {
      const href = this.getAttribute('href')

      // Проверяем, что href не пустой и не равен просто '#'
      if (!href || href === '#') {
        return
      }

      const target = document.querySelector(href)

      if (target) {
        e.preventDefault()

        const headerHeight = header ? header.offsetHeight : 0
        const targetPosition = target.offsetTop - headerHeight - 20

        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth',
        })
      }
    })
  })

  // ===== LAZY LOADING FOR IMAGES =====

  const images = document.querySelectorAll('img[data-src]')

  if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const img = entry.target
          img.src = img.dataset.src
          img.classList.remove('lazy')
          imageObserver.unobserve(img)
        }
      })
    })

    images.forEach((img) => imageObserver.observe(img))
  }

  // ===== PERFORMANCE OPTIMIZATION =====

  // Дебаунс для обработчиков скролла
  function debounce(func, wait) {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  }

  // Применяем дебаунс к обработчику скролла
  const debouncedScrollHandler = debounce(function () {
    if (!header) return // Проверяем существование header

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop

    if (scrollTop > 100) {
      header.classList.add('scrolled')
    } else {
      header.classList.remove('scrolled')
    }

    if (scrollTop > lastScrollTop && scrollTop > 200) {
      header.style.transform = 'translateY(-100%)'
    } else {
      header.style.transform = 'translateY(0)'
    }

    lastScrollTop = scrollTop
  }, 10)

  // Заменяем обработчик скролла на дебаунсированный
  window.removeEventListener('scroll', arguments.callee)
  window.addEventListener('scroll', debouncedScrollHandler)
})
