// Объединенный JavaScript для сайта

document.addEventListener('DOMContentLoaded', function () {
  console.log('[Core] DOMContentLoaded: start init sequence')

  // ===== ИНИЦИАЛИЗАЦИЯ ВСЕХ КОМПОНЕНТОВ =====
  initHeader()
  initPopups()
  initFAQ()
  initCarousels()
  initFancybox()
  initFormHandlers()
  initPhoneMask()
  initDynamicFormFields()
  initSmoothScroll()
  initScrollAnimations()
  initCart()
  initLazyLoading()
  animateNumbers()
  initScrollToTop()
  initShowMoreButtons()
  initCookieConsent()

  // ===== HEADER FUNCTIONALITY =====
  function initHeader() {
    // Проверяем, есть ли уже инициализированная универсальная шапка
    if (window.universalHeader) {
      console.log('[Core] Universal header already initialized')
      return
    }

    // Fallback для старых страниц без универсальной шапки
    const header = document.querySelector('.header')
    const menuBtn = document.querySelector('.header__menu-btn')
    const nav = document.querySelector('.header__nav')
    const navClose = document.querySelector('.header__nav-close')
    const navLinks = document.querySelectorAll('.nav-link')

    if (!header) {
      console.warn('[Core] Header element not found')
      return
    }

    // Инициализация шапки - всегда показываем при загрузке
    header.style.transform = 'translateY(0)'

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

    // Умная шапка - скрытие при прокрутке вниз (fallback)
    let lastScrollTop = 0
    let ticking = false

    function updateHeader() {
      const scrollTop = window.pageYOffset || document.documentElement.scrollTop

      if (scrollTop > 100) {
        if (scrollTop > lastScrollTop && scrollTop > 200) {
          // Прокрутка вниз - скрываем шапку
          header.style.transform = 'translateY(-100%)'
        } else {
          // Прокрутка вверх - показываем шапку
          header.style.transform = 'translateY(0)'
        }
      } else {
        // Вверху страницы - всегда показываем
        header.style.transform = 'translateY(0)'
      }

      lastScrollTop = scrollTop
      ticking = false
    }

    function requestTick() {
      if (!ticking) {
        requestAnimationFrame(updateHeader)
        ticking = true
      }
    }

    window.addEventListener('scroll', requestTick, { passive: true })
  }

  // ===== ПОПАПЫ =====
  function initPopups() {
    const popupTriggers = document.querySelectorAll('[data-popup]')
    const popups = document.querySelectorAll('.popup')
    const popupCloses = document.querySelectorAll('.popup__close')

    // Открытие попапов
    popupTriggers.forEach((trigger) => {
      trigger.addEventListener('click', function (e) {
        e.preventDefault()
        const popupId = this.getAttribute('data-popup')
        const popup = document.getElementById(popupId)

        if (popup) {
          popup.classList.add('active')
          document.body.style.overflow = 'hidden'
        }
      })
    })

    // Закрытие попапов
    popupCloses.forEach((close) => {
      close.addEventListener('click', function () {
        const popup = this.closest('.popup')
        if (popup) {
          popup.classList.remove('active')
          document.body.style.overflow = ''
        }
      })
    })

    // Закрытие по клику на overlay
    popups.forEach((popup) => {
      popup.addEventListener('click', function (e) {
        if (e.target === this) {
          this.classList.remove('active')
          document.body.style.overflow = ''
        }
      })
    })

    // Закрытие по Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        popups.forEach((popup) => {
          if (popup.classList.contains('active')) {
            popup.classList.remove('active')
            document.body.style.overflow = ''
          }
        })
      }
    })
  }

  // ===== FAQ =====
  function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item')

    faqItems.forEach((item) => {
      const header = item.querySelector('.faq__question, .faq-item__header')
      const content = item.querySelector('.faq__answer, .faq-item__content')
      const icon = item.querySelector('.faq__icon, .faq-item__icon')

      if (header && content) {
        header.addEventListener('click', function () {
          const isActive = item.classList.contains('active')

          // Закрываем все активные элементы
          faqItems.forEach((otherItem) => {
            if (otherItem !== item) {
              otherItem.classList.remove('active')
              const otherContent = otherItem.querySelector(
                '.faq__answer, .faq-item__content'
              )
              const otherIcon = otherItem.querySelector(
                '.faq__icon, .faq-item__icon'
              )
              if (otherContent) otherContent.style.maxHeight = '0px'
              if (otherIcon) otherIcon.textContent = '+'
            }
          })

          // Переключаем текущий элемент
          if (isActive) {
            item.classList.remove('active')
            content.style.maxHeight = '0px'
            if (icon) icon.textContent = '+'
          } else {
            item.classList.add('active')
            content.style.maxHeight = content.scrollHeight + 'px'
            if (icon) icon.textContent = '−'
          }
        })

        // Устанавливаем начальную высоту
        content.style.maxHeight = '0px'
      }
    })
  }

  // ===== КАРУСЕЛИ =====
  function initCarousels() {
    // Инициализация Swiper для всех каруселей
    if (typeof Swiper !== 'undefined') {
      // Карусель преимуществ
      const advantagesCarousel = document.querySelector('.advantages__carousel')
      if (advantagesCarousel) {
        new Swiper(advantagesCarousel, {
          slidesPerView: 1,
          spaceBetween: 20,
          loop: true,
          autoplay: {
            delay: 5000,
            disableOnInteraction: false,
          },
          navigation: {
            nextEl: '.slider-next-btn',
            prevEl: '.slider-prev-btn',
          },
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
          breakpoints: {
            768: {
              slidesPerView: 2,
            },
            1024: {
              slidesPerView: 3,
            },
          },
        })
      }

      // Карусель сертификатов
      const certificatesCarousel = document.querySelector('.certificates')
      if (certificatesCarousel) {
        new Swiper(certificatesCarousel, {
          slidesPerView: 1,
          spaceBetween: 20,
          loop: true,
          navigation: {
            nextEl: '.slider-next-btn',
            prevEl: '.slider-prev-btn',
          },
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
          breakpoints: {
            768: {
              slidesPerView: 2,
            },
            1024: {
              slidesPerView: 3,
            },
          },
        })
      }

      // Карусель отзывов
      const reviewsCarousel = document.querySelector(
        '.reviews__img-slider .swiper-container'
      )
      if (reviewsCarousel) {
        new Swiper(reviewsCarousel, {
          slidesPerView: 1,
          spaceBetween: 20,
          loop: true,
          navigation: {
            nextEl: '.slider-next-btn',
            prevEl: '.slider-prev-btn',
          },
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
          breakpoints: {
            768: {
              slidesPerView: 2,
            },
            1024: {
              slidesPerView: 3,
            },
          },
        })
      }

      // Мобильная карусель результатов
      const resultsCarousel = document.querySelector(
        '.results__benefits-mobile'
      )
      if (resultsCarousel) {
        new Swiper(resultsCarousel, {
          slidesPerView: 1,
          spaceBetween: 20,
          loop: true,
          pagination: {
            el: '.swiper-pagination',
            clickable: true,
          },
        })
      }
    }
  }

  // ===== FANCYBOX =====
  function initFancybox() {
    if (typeof Fancybox !== 'undefined') {
      Fancybox.bind('[data-fancybox]', {
        // Настройки Fancybox
        Toolbar: {
          display: {
            left: ['infobar'],
            middle: [],
            right: ['slideshow', 'thumbs', 'close'],
          },
        },
        Thumbs: {
          autoStart: false,
        },
      })
    }
  }

  // ===== ОБРАБОТЧИКИ ФОРМ =====
  function initFormHandlers() {
    const forms = document.querySelectorAll('form')

    forms.forEach((form) => {
      form.addEventListener('submit', function (e) {
        e.preventDefault()

        const formData = new FormData(this)
        const submitBtn = this.querySelector('button[type="submit"]')
        const originalText = submitBtn ? submitBtn.textContent : ''

        // Показываем загрузку
        if (submitBtn) {
          submitBtn.textContent = 'Отправка...'
          submitBtn.disabled = true
        }

        // Отправляем данные
        fetch('/api/contact.php', {
          method: 'POST',
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              // Показываем успех
              showNotification('Спасибо! Ваше сообщение отправлено.', 'success')
              this.reset()
            } else {
              // Показываем ошибку
              showNotification(
                data.message || 'Произошла ошибка. Попробуйте еще раз.',
                'error'
              )
            }
          })
          .catch((error) => {
            console.error('Error:', error)
            showNotification('Произошла ошибка. Попробуйте еще раз.', 'error')
          })
          .finally(() => {
            // Восстанавливаем кнопку
            if (submitBtn) {
              submitBtn.textContent = originalText
              submitBtn.disabled = false
            }
          })
      })
    })
  }

  // ===== МАСКА ТЕЛЕФОНА =====
  function initPhoneMask() {
    if (typeof $.fn.mask !== 'undefined') {
      $('input[type="tel"]').mask('+7 (999) 999-99-99')
    }
  }

  // ===== ДИНАМИЧЕСКИЕ ПОЛЯ ФОРМ =====
  function initDynamicFormFields() {
    const deliveryMethod = document.getElementById('delivery-method')
    if (deliveryMethod) {
      deliveryMethod.addEventListener('change', function () {
        const value = this.value
        const fields = {
          whatsapp: document.getElementById('whatsapp-field'),
          telegram: document.getElementById('telegram-field'),
          email: document.getElementById('email-field'),
        }

        // Скрываем все поля
        Object.values(fields).forEach((field) => {
          if (field) field.style.display = 'none'
        })

        // Показываем нужное поле
        if (fields[value]) {
          fields[value].style.display = 'block'
        }
      })
    }
  }

  // ===== ПЛАВНАЯ ПРОКРУТКА =====
  function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]')

    links.forEach((link) => {
      link.addEventListener('click', function (e) {
        e.preventDefault()

        const targetId = this.getAttribute('href').substring(1)
        const targetElement = document.getElementById(targetId)

        if (targetElement) {
          const headerHeight =
            document.querySelector('.header')?.offsetHeight || 0
          const targetPosition = targetElement.offsetTop - headerHeight - 20

          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth',
          })
        }
      })
    })
  }

  // ===== АНИМАЦИИ ПРИ ПРОКРУТКЕ =====
  function initScrollAnimations() {
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px',
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in')
        }
      })
    }, observerOptions)

    // Наблюдаем за элементами с классом animate-on-scroll
    document.querySelectorAll('.animate-on-scroll').forEach((el) => {
      observer.observe(el)
    })
  }

  // ===== КОРЗИНА =====
  function initCart() {
    // Функциональность корзины
    const addToCartBtns = document.querySelectorAll('[data-add-to-cart]')

    addToCartBtns.forEach((btn) => {
      btn.addEventListener('click', function () {
        const productId = this.getAttribute('data-add-to-cart')
        const productName = this.getAttribute('data-product-name') || 'Товар'

        // Добавляем в корзину
        fetch('/api/add-to-cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ product_id: productId }),
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              showNotification(`${productName} добавлен в корзину`, 'success')
              updateCartCounter()
            } else {
              showNotification(
                data.message || 'Ошибка добавления в корзину',
                'error'
              )
            }
          })
          .catch((error) => {
            console.error('Error:', error)
            showNotification('Ошибка добавления в корзину', 'error')
          })
      })
    })
  }

  // ===== ЛЕНИВАЯ ЗАГРУЗКА =====
  function initLazyLoading() {
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

      document.querySelectorAll('img[data-src]').forEach((img) => {
        imageObserver.observe(img)
      })
    }
  }

  // ===== АНИМАЦИЯ ЧИСЕЛ =====
  function animateNumbers() {
    const numbers = document.querySelectorAll(
      '.stat-number, .hero-stat__number'
    )

    const animateNumber = (element) => {
      const target = parseInt(element.textContent.replace(/\D/g, ''))
      const duration = 2000
      const start = performance.now()

      const updateNumber = (currentTime) => {
        const elapsed = currentTime - start
        const progress = Math.min(elapsed / duration, 1)
        const current = Math.floor(progress * target)

        element.textContent = element.textContent.replace(/\d+/, current)

        if (progress < 1) {
          requestAnimationFrame(updateNumber)
        }
      }

      requestAnimationFrame(updateNumber)
    }

    const numberObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateNumber(entry.target)
          numberObserver.unobserve(entry.target)
        }
      })
    })

    numbers.forEach((number) => {
      numberObserver.observe(number)
    })
  }

  // ===== КНОПКА "НАВЕРХ" =====
  function initScrollToTop() {
    const scrollToTopBtn = document.createElement('button')
    scrollToTopBtn.innerHTML = '↑'
    scrollToTopBtn.className = 'scroll-to-top'
    scrollToTopBtn.style.cssText = `
      position: fixed;
      bottom: 20px;
      right: 20px;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: var(--brand-primary);
      color: white;
      border: none;
      cursor: pointer;
      font-size: 20px;
      z-index: 1000;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    `

    document.body.appendChild(scrollToTopBtn)

    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        scrollToTopBtn.style.opacity = '1'
        scrollToTopBtn.style.visibility = 'visible'
      } else {
        scrollToTopBtn.style.opacity = '0'
        scrollToTopBtn.style.visibility = 'hidden'
      }
    })

    scrollToTopBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth',
      })
    })
  }

  // ===== КНОПКИ "ПОКАЗАТЬ ЕЩЕ" =====
  function initShowMoreButtons() {
    const showMoreRequests = document.getElementById('showMoreRequests')
    if (showMoreRequests) {
      showMoreRequests.addEventListener('click', function () {
        const hiddenRequests = document.querySelectorAll(
          '.request-card:nth-child(n+5)'
        )
        hiddenRequests.forEach((card) => {
          card.style.display = 'block'
        })
        this.style.display = 'none'
      })
    }

    const showMoreFaq = document.getElementById('showMoreFaq')
    if (showMoreFaq) {
      showMoreFaq.addEventListener('click', function () {
        const hiddenFaq = document.querySelectorAll('.faq-item:nth-child(n+5)')
        hiddenFaq.forEach((item) => {
          item.style.display = 'block'
        })
        this.style.display = 'none'
      })
    }
  }

  // ===== COOKIE CONSENT =====
  function initCookieConsent() {
    function getCookie(name) {
      const match = document.cookie.match(
        new RegExp('(?:^|; )' + name + '=([^;]*)')
      )
      return match ? decodeURIComponent(match[1]) : null
    }

    function openCookiePopup() {
      const popup = document.getElementById('cookie-consent-popup')
      if (popup) {
        popup.classList.add('active')
      }
    }

    function closeCookiePopup() {
      const popup = document.getElementById('cookie-consent-popup')
      if (popup) {
        popup.classList.remove('active')
      }
    }

    // Показываем, если согласие ещё не дано
    const cookieAccept = getCookie('cookiteAccept')
    if (cookieAccept !== '1') {
      setTimeout(openCookiePopup, 800)
    }

    // Обработка клика по кнопке согласия
    document.addEventListener('click', function (e) {
      if (e.target && e.target.id === 'cookie-accept-btn') {
        const date = new Date()
        date.setDate(date.getDate() + 180)
        let cookie =
          'cookiteAccept=1; expires=' +
          date.toUTCString() +
          '; path=/; SameSite=Lax'
        if (location.protocol === 'https:') cookie += '; Secure'
        document.cookie = cookie
        closeCookiePopup()
      }
    })
  }

  // ===== УТИЛИТЫ =====
  function showNotification(message, type = 'info') {
    const notification = document.createElement('div')
    notification.className = `notification notification--${type}`
    notification.textContent = message
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 20px;
      border-radius: 5px;
      color: white;
      z-index: 10000;
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.3s ease;
    `

    // Цвета для разных типов уведомлений
    const colors = {
      success: '#28a745',
      error: '#dc3545',
      warning: '#ffc107',
      info: '#17a2b8',
    }

    notification.style.backgroundColor = colors[type] || colors.info

    document.body.appendChild(notification)

    // Анимация появления
    setTimeout(() => {
      notification.style.opacity = '1'
      notification.style.transform = 'translateX(0)'
    }, 100)

    // Автоматическое скрытие
    setTimeout(() => {
      notification.style.opacity = '0'
      notification.style.transform = 'translateX(100%)'
      setTimeout(() => {
        document.body.removeChild(notification)
      }, 300)
    }, 5000)
  }

  function updateCartCounter() {
    // Обновление счетчика корзины
    fetch('/api/cart.php')
      .then((response) => response.json())
      .then((data) => {
        const counter = document.querySelector('.cart-counter')
        if (counter && data.count !== undefined) {
          counter.textContent = data.count
        }
      })
      .catch((error) => console.error('Error updating cart counter:', error))
  }
})

// ===== ГЛОБАЛЬНЫЕ ФУНКЦИИ =====
window.toggleFaq = function (header) {
  const item = header.parentElement
  const content = header.nextElementSibling
  const icon = header.querySelector('.faq-item__icon')

  if (item.classList.contains('active')) {
    item.classList.remove('active')
    content.style.maxHeight = '0px'
    icon.textContent = '+'
  } else {
    // Закрываем все открытые элементы
    document.querySelectorAll('.faq-item.active').forEach((activeItem) => {
      activeItem.classList.remove('active')
      activeItem.querySelector('.faq-item__content').style.maxHeight = '0px'
      activeItem.querySelector('.faq-item__icon').textContent = '+'
    })

    // Открываем текущий элемент
    item.classList.add('active')
    content.style.maxHeight = content.scrollHeight + 'px'
    icon.textContent = '−'
  }
}

// ===== ОБРАБОТКА ОШИБОК =====
window.addEventListener('error', function (e) {
  console.warn('JavaScript error caught:', e.error)
  return false
})

// ===== ПРОВЕРКА JQUERY =====
if (typeof $ !== 'undefined' && $.fn) {
  console.log('jQuery загружен успешно')

  // Дополнительная функциональность с jQuery
  $(document).ready(function () {
    // Обработка кликов по элементам с data-id
    $('.process-item').click(function () {
      $('.process-item').removeClass('active')
      $(this).addClass('active')
      $('.process__content').removeClass('active')
      let id = $(this).attr('data-id') - 1
      $('.process__content').eq(id).addClass('active')
    })

    $('.products-slider__item').click(function () {
      $('.products-slider__item').removeClass('active')
      $(this).addClass('active')
      $('.products__item').removeClass('active')
      let id = $(this).attr('data-id') - 1
      $('.products__item').eq(id).addClass('active')
    })
  })
}
