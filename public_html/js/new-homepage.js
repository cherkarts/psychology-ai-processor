// Новый JavaScript для главной страницы

document.addEventListener('DOMContentLoaded', function () {
  console.log('[MenuDebug] DOMContentLoaded: start init sequence')
  let mobileMenuInitialized = false
  // Инициализация всех компонентов
  initPopups()
  initFAQ()
  initAdvantagesCarousel()
  initCertificatesCarousel()
  initFancybox()
  initReviewsImagesSlider()
  initResultsCarousel()
  initHeaderScroll()
  initSmartHeader()
  initFormHandlers()
  initPhoneMask()
  initDynamicFormFields()
  initSmoothScroll()
  initScrollAnimations()
  initCart()
  initMobileMenu()
  initLazyLoading()
  animateNumbers()
  initScrollToTop()
  initShowMoreRequests()
  initShowMoreFaq()

  // Cookie consent (совместимо со старой и новой системой попапов)
  function initCookieConsent() {
    function getCookie(name) {
      const match = document.cookie.match(
        new RegExp('(?:^|; )' + name + '=([^;]*)')
      )
      return match ? decodeURIComponent(match[1]) : null
    }

    function openCookiePopup() {
      // Старая система попапов (popupID) — включаем обе совместимые метки
      const legacyPopup = document.querySelector(
        '[popupID="cookie-consent-popup"]'
      )
      if (legacyPopup) {
        legacyPopup.classList.add('open')
        legacyPopup.classList.add('active') // чтобы сработали стили главной
      }
      // Новая система попапов (id)
      const newPopup = document.getElementById('cookie-consent-popup')
      if (newPopup) {
        newPopup.classList.add('active')
        newPopup.classList.add('open') // на случай, если подключены старые стили
      }
    }

    function closeCookiePopup() {
      const legacyPopup = document.querySelector(
        '[popupID="cookie-consent-popup"]'
      )
      if (legacyPopup) {
        legacyPopup.classList.remove('open')
        legacyPopup.classList.remove('active')
      }
      const newPopup = document.getElementById('cookie-consent-popup')
      if (newPopup) {
        newPopup.classList.remove('active')
        newPopup.classList.remove('open')
      }
      // Страховка: вернуть прокрутку
      document.body.style.overflow = ''
      try {
        document.documentElement.style.overflow = ''
      } catch (e) {}
    }

    // Показываем, если согласие ещё не дано
    const cookieAccept = getCookie('cookiteAccept')
    console.log('Cookie consent check:', {
      cookieAccept: cookieAccept,
      shouldShow: cookieAccept !== '1',
      allCookies: document.cookie,
    })
    if (cookieAccept !== '1') {
      setTimeout(openCookiePopup, 800)
    }

    // Обработка клика по кнопке согласия
    document.addEventListener('click', function (e) {
      if (e.target && e.target.id === 'cookie-accept-btn') {
        console.log('Cookie accept button clicked')
        const date = new Date()
        date.setDate(date.getDate() + 180)
        let cookie =
          'cookiteAccept=1; expires=' +
          date.toUTCString() +
          '; path=/; SameSite=Lax'
        if (location.protocol === 'https:') cookie += '; Secure'
        document.cookie = cookie
        console.log('Cookie set:', cookie)
        console.log('All cookies after setting:', document.cookie)
        closeCookiePopup()
      }
    })
  }

  initCookieConsent()

  // Попапы
  function initPopups() {
    const popupTriggers = document.querySelectorAll('[data-popup]')
    const popups = document.querySelectorAll('.popup')
    const popupCloses = document.querySelectorAll(
      '.popup__close, .popup__overlay'
    )

    // Гарантируем использование нативных select внутри попапов
    function ensureNativeSelects(scope) {
      try {
        if (!scope) return
        const selects = scope.querySelectorAll('select.js-native-select')
        selects.forEach((selectEl) => {
          // Удаляем клоны nice-select, которые могли быть добавлены плагином
          const siblings = [
            selectEl.previousElementSibling,
            selectEl.nextElementSibling,
          ]
          siblings.forEach((sib) => {
            if (sib && sib.classList && sib.classList.contains('nice-select')) {
              if (sib.parentNode) sib.parentNode.removeChild(sib)
            }
          })
          // Делаем нативный селект видимым
          selectEl.style.display = ''
          selectEl.classList.add('ready')
        })
        // Страховка: скрываем любые оставшиеся .nice-select внутри попапа
        scope.querySelectorAll('.nice-select').forEach((clone) => {
          clone.style.display = 'none'
        })
      } catch (e) {}
    }

    // Вспомогательная: получить ближайший заголовок к элементу
    function getNearestTitle(element) {
      if (!element) return ''
      const container = element.closest(
        '.request-card, .pricing-card, .advantage-card, section, .header, .hero, .cta'
      )
      const candidates = []
      if (container) {
        candidates.push(
          ...container.querySelectorAll(
            'h1, h2, h3, .section-title, .pricing-card h3, .request-card h3'
          )
        )
      }
      // Если внутри контейнера не нашли, попробуем взять ближайших родителей
      let parent = element.parentElement
      let hops = 0
      while (
        (!candidates.length || !candidates[0]?.textContent?.trim()) &&
        parent &&
        hops < 5
      ) {
        const found = parent.querySelectorAll('h1, h2, h3, .section-title')
        if (found && found.length) {
          candidates.push(...found)
        }
        parent = parent.parentElement
        hops++
      }
      const titleEl = candidates.find(
        (n) => (n.textContent || '').trim().length > 0
      )
      return titleEl ? titleEl.textContent.trim() : ''
    }

    function setPopupFormSource(popup, sourceLabel) {
      if (!popup) return
      const form = popup.querySelector('.popup__form')
      if (!form) return
      let input = form.querySelector('input[name="form_source"]')
      if (!input) {
        input = document.createElement('input')
        input.type = 'hidden'
        input.name = 'form_source'
        form.prepend(input)
      }
      if (sourceLabel && sourceLabel.length) {
        input.value = sourceLabel
      }
    }

    // Обработчик для ссылок на политику конфиденциальности
    const privacyPolicyLinks = document.querySelectorAll('.privacy-policy-link')
    privacyPolicyLinks.forEach((link) => {
      link.addEventListener('click', function (e) {
        // Проверяем, не нажат ли Ctrl или Cmd (для открытия в новой вкладке)
        if (e.ctrlKey || e.metaKey) {
          return // Позволяем браузеру открыть ссылку в новой вкладке
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

    // Открытие попапов - отключаем на странице корзины
    if (!window.location.pathname.includes('cart.php')) {
      popupTriggers.forEach((trigger) => {
        trigger.addEventListener('click', function (e) {
          // Проверяем, что клик не в корзине или на элементах корзины
          const cartContainer = e.target.closest(
            '.cart-content, .cart-items, .cart-item, .cart-sidebar'
          )
          if (cartContainer) {
            console.log(
              'Popup (new-homepage): ignoring click in cart container',
              e.target
            )
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
            console.log(
              'Popup (new-homepage): ignoring click on cart element',
              e.target
            )
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

            // Если ранее заменяли содержимое на сообщение об успехе — восстановим исходное
            try {
              const content = popup.querySelector('.popup__content')
              if (
                content &&
                content.dataset &&
                content.dataset.originalContent
              ) {
                content.innerHTML = content.dataset.originalContent
                // Инициализируем обработчики только для новых форм в этом попапе
                const popupForms = popup.querySelectorAll('form')
                popupForms.forEach((f) => {
                  if (!f.dataset.nhInit) {
                    f.dataset.nhInit = '1'
                    f.addEventListener('submit', function (ev) {
                      ev.preventDefault()
                      handleFormSubmit(this)
                    })
                  }
                })
                // Применяем маску к новым телефонам
                try {
                  initPhoneMask()
                } catch (e) {}
                // После восстановления содержимого — гарантируем нативные select
                ensureNativeSelects(popup)
              }
            } catch (e) {}

            // Присваиваем источнику формы ближайший заголовок
            const label = getNearestTitle(this)
            setPopupFormSource(popup, label)
            // На всякий случай — чистим клоны nice-select при каждом открытии
            ensureNativeSelects(popup)

            // Применяем маски к полям телефонов в попапе
            setTimeout(() => {
              const phoneInputs = popup.querySelectorAll(
                'input[type="tel"], input[phoneMask_JS]'
              )
              phoneInputs.forEach((input) => {
                const jq = window.jQuery || window.$
                if (jq && jq.fn && typeof jq.fn.mask === 'function') {
                  jq(input).mask('+7 (999) 999-99-99')
                }
              })
            }, 100)
          }
        })
      })
    } else {
      console.log('Popup (new-homepage): disabled on cart page')
    }

    // Закрытие попапов
    popupCloses.forEach((close) => {
      close.addEventListener('click', function () {
        popups.forEach((popup) => {
          popup.classList.remove('active')
        })
        document.body.style.overflow = ''
        try {
          document.documentElement.style.overflow = ''
        } catch (e) {}
      })
    })

    // Закрытие по Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        popups.forEach((popup) => {
          popup.classList.remove('active')
        })
        document.body.style.overflow = ''
        try {
          document.documentElement.style.overflow = ''
        } catch (e) {}
      }
    })
  }

  // FAQ аккордеон
  function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item')

    faqItems.forEach((item) => {
      const question =
        item.querySelector('.faq-item__question') ||
        item.querySelector('.faq__question')

      if (question) {
        question.addEventListener('click', function () {
          const isActive = item.classList.contains('active')

          // Закрываем все остальные
          faqItems.forEach((otherItem) => {
            otherItem.classList.remove('active')
          })

          // Открываем текущий, если он был закрыт
          if (!isActive) {
            item.classList.add('active')
          }
        })
      }
    })
  }

  // Карусель преимуществ
  function initAdvantagesCarousel() {
    const advantagesCarousel = document.querySelector('.advantages__carousel')

    if (advantagesCarousel) {
      new Swiper(advantagesCarousel, {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: {
          delay: 4000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.advantages__carousel .swiper-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.advantages__carousel .slider-next-btn',
          prevEl: '.advantages__carousel .slider-prev-btn',
        },
        breakpoints: {
          540: {
            slidesPerView: 1,
          },
          768: {
            slidesPerView: 2,
          },
          1024: {
            slidesPerView: 3,
          },
          1200: {
            slidesPerView: 3,
          },
        },
      })
    }
  }

  // Карусель дипломов
  function initCertificatesCarousel() {
    const certificatesCarousel = document.querySelector('.certificates')

    if (certificatesCarousel) {
      new Swiper(certificatesCarousel, {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: true,
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        pagination: {
          el: '.certificates .swiper-pagination',
          clickable: true,
        },
        navigation: {
          nextEl: '.certificates .slider-next-btn',
          prevEl: '.certificates .slider-prev-btn',
        },
      })
    }
  }

  // Инициализация Fancybox для дипломов
  function initFancybox() {
    // Проверяем, что Fancybox загружен
    if (typeof Fancybox !== 'undefined') {
      Fancybox.bind('[data-fancybox="certificates"]', {
        loop: true,
        buttons: ['close', 'slideShow', 'fullScreen', 'thumbs'],
        animationEffect: 'fade',
        transitionEffect: 'slide',
        thumbs: {
          autoStart: false,
        },
      })
    } else {
      console.warn('Fancybox не загружен')
    }
  }

  // Слайдер изображений отзывов
  function initReviewsImagesSlider() {
    const reviewsImagesSlider = document.querySelector(
      '.reviews__img-slider .swiper-container'
    )

    if (reviewsImagesSlider) {
      new Swiper(reviewsImagesSlider, {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: true,
        autoplay: {
          delay: 4000,
          disableOnInteraction: false,
        },
        navigation: {
          nextEl: '.reviews__img-slider .slider-next-btn',
          prevEl: '.reviews__img-slider .slider-prev-btn',
        },
        pagination: {
          el: '.reviews__img-slider .slider-pagination',
          clickable: true,
          bulletActiveColor: '#6a7e9f',
        },
        breakpoints: {
          768: {
            slidesPerView: 2,
            spaceBetween: 15,
          },
          1024: {
            slidesPerView: 3,
            spaceBetween: 20,
          },
        },
      })
    }
  }

  // Карусель результатов для мобильных устройств
  function initResultsCarousel() {
    const resultsCarousel = document.querySelector('.results__benefits-mobile')

    if (resultsCarousel) {
      new Swiper(resultsCarousel, {
        slidesPerView: 1,
        spaceBetween: 20,
        loop: true,
        autoplay: {
          delay: 3000,
          disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
          crossFade: true,
        },
      })
    }
  }

  // Анимация хедера при скролле
  function initHeaderScroll() {
    const header = document.querySelector('.header')
    let lastScrollTop = 0

    window.addEventListener('scroll', function () {
      if (!header) return // Проверяем существование header

      const scrollTop = window.pageYOffset || document.documentElement.scrollTop

      if (scrollTop > 100) {
        header.style.background = 'rgba(255, 255, 255, 0.98)'
        header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)'
      } else {
        header.style.background = 'rgba(255, 255, 255, 0.95)'
        header.style.boxShadow = 'none'
      }

      lastScrollTop = scrollTop
    })
  }

  // Умная шапка - скрытие/показ на десктопе
  function initSmartHeader() {
    // Отключено: поведение на hover и автоскрытие не используется
    return

    // Функция скрытия шапки
    function hideHeader() {
      if (!header) return // Проверяем существование header
      if (!isMouseOverHeader && isHeaderVisible) {
        header.classList.add('header--collapsed')
        isHeaderVisible = false
      }
    }

    // Функция показа шапки
    function showHeader() {
      if (!header) return // Проверяем существование header
      if (!isHeaderVisible) {
        header.classList.remove('header--collapsed')
        isHeaderVisible = true
      }
    }
  }

  // Обработчики форм
  function initFormHandlers() {
    const forms = document.querySelectorAll('form')

    forms.forEach((form) => {
      // Не перехватываем поисковые и любые GET-формы
      const method = (form.getAttribute('method') || 'GET').toUpperCase()
      const isSearchForm = form.classList.contains('search-form')
      const isCheckoutForm = form.classList.contains('checkout-form')
      if (method === 'GET' || isSearchForm || isCheckoutForm) {
        return
      }

      form.addEventListener('submit', function (e) {
        e.preventDefault()
        // Если это форма из попапа и form_source пуст — попробуем вычислить по заголовку рядом с кнопкой
        const submitBtn = this.querySelector('button[type="submit"]')
        const hasSource = this.querySelector('input[name="form_source"][value]')
        if (!hasSource) {
          try {
            const label = (function getNearestTitleFromForm(f) {
              const titleEl = f.querySelector('h1, h2, h3, .section-title')
              if (titleEl && titleEl.textContent)
                return titleEl.textContent.trim()
              const parent = f.closest(
                '.request-card, .pricing-card, .advantage-card, section, .popup__content, .cta'
              )
              if (parent) {
                const found = parent.querySelector(
                  'h1, h2, h3, .section-title, .pricing-card h3, .request-card h3'
                )
                if (found && found.textContent) return found.textContent.trim()
              }
              if (submitBtn) {
                const near = submitBtn.closest('*')
                const h =
                  near && near.querySelector('h1, h2, h3, .section-title')
                if (h && h.textContent) return h.textContent.trim()
              }
              return ''
            })(this)
            if (label) {
              const hidden = document.createElement('input')
              hidden.type = 'hidden'
              hidden.name = 'form_source'
              hidden.value = label
              this.prepend(hidden)
            }
          } catch (e) {}
        }
        handleFormSubmit(this)
      })
    })
  }

  // Обработка отправки формы
  function handleFormSubmit(form) {
    const formData = new FormData(form)
    const submitBtn = form.querySelector('button[type="submit"]')
    const originalText = submitBtn.textContent

    // Обработка специальных полей для формы "ДЛЯ ВАС"
    if (form.classList.contains('download__form')) {
      const deliveryMethod = formData.get('delivery_method')
      let contactInfo = ''

      switch (deliveryMethod) {
        case 'whatsapp':
          contactInfo = formData.get('whatsapp_phone')
          formData.set('contact_info', `WhatsApp: ${contactInfo}`)
          break
        case 'telegram':
          contactInfo = formData.get('telegram_username') || ''
          // Валидация: запрещаем кириллицу и любые недопустимые символы
          const hasCyrillic = /[А-Яа-яЁё]/.test(contactInfo)
          const cleanedForCheck = contactInfo.trim().replace(/^@+/, '')
          const allowedPattern = /^[a-zA-Z0-9_]+$/
          if (
            hasCyrillic ||
            (cleanedForCheck && !allowedPattern.test(cleanedForCheck))
          ) {
            showNotification(
              'Введите корректный Telegram username: только латиница, цифры и _',
              'error'
            )
            if (submitBtn) {
              submitBtn.textContent = originalText
              submitBtn.disabled = false
            }
            return
          }
          // Нормализуем telegram username: только латиница, цифры и _; добавляем @ если не указан
          contactInfo = contactInfo.trim().replace(/^@+/, '')
          contactInfo = contactInfo.replace(/[^a-zA-Z0-9_]/g, '')
          if (contactInfo) {
            contactInfo = '@' + contactInfo
          }
          formData.set('telegram_username', contactInfo)
          formData.set('contact_info', `Telegram: ${contactInfo}`)
          break
        case 'email':
          contactInfo = formData.get('email')
          formData.set('contact_info', `Email: ${contactInfo}`)
          break
      }
    }

    // Для читабельного времени звонка передаем подпись опции
    try {
      const timeSelect = form.querySelector('select[name="time"]')
      if (timeSelect) {
        const selectedOption = timeSelect.options[timeSelect.selectedIndex]
        if (selectedOption && selectedOption.textContent) {
          formData.set('time_label', selectedOption.textContent.trim())
        }
      }
    } catch (e) {}

    // Показываем загрузку
    submitBtn.textContent = 'Отправляем...'
    submitBtn.disabled = true

    // Получаем CSRF токен
    // CSRF токен: безопасно получаем, с фолбэком
    let csrfToken = ''
    try {
      const meta = document.querySelector('meta[name="csrf-token"]')
      if (meta) csrfToken = meta.getAttribute('content') || ''
    } catch (e) {
      csrfToken = ''
    }

    // Отправляем данные
    fetch('/api/contact.php', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': csrfToken,
      },
      body: formData,
    })
      .then(async (response) => {
        // Всегда читаем как текст и безопасно парсим как JSON
        let text = ''
        try {
          text = await response.text()
        } catch (e) {
          return { success: false, message: 'Empty server response' }
        }

        if (!text) {
          return { success: false, message: 'Empty server response' }
        }

        try {
          return JSON.parse(text)
        } catch (e) {
          return { success: false, message: text }
        }
      })
      .then((data) => {
        if (data.success) {
          try {
            // Если форма внутри попапа — показываем красивое сообщение как на услугах
            const popup = form.closest('.popup')
            const content =
              form.closest('.popup__content') || form.parentElement
            if (popup && content) {
              if (!content.dataset.originalContent) {
                content.dataset.originalContent = content.innerHTML
              }
              content.innerHTML = `
                <div style="text-align: center; padding: 40px 20px;">
                  <svg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" fill=\"none\" style=\"color: #28a745; margin-bottom: 20px;\">
                    <circle cx=\"30\" cy=\"30\" r=\"30\" fill=\"currentColor\" opacity=\"0.1\"/>
                    <path d=\"M20 30L27 37L40 24\" stroke=\"currentColor\" stroke-width=\"3\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>
                  </svg>
                  <h3 style=\"color: #28a745; margin-bottom: 10px; font-size: 28px;\">Спасибо!</h3>
                  <p style=\"color: #666; margin-bottom: 20px;\">Ваша заявка отправлена. Мы свяжемся с вами в ближайшее время.</p>
                  <button type=\"button\" onclick=\"window.closeParentPopup(this)\" style=\"background: #007bff; color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer;\">Закрыть</button>
                </div>`
            } else {
              // Вне попапа — просто показываем уведомление
              showNotification(
                'Спасибо! Мы свяжемся с вами в ближайшее время.',
                'success'
              )
            }
          } catch (e) {
            // Фолбэк при ошибке
            showNotification(
              'Спасибо! Мы свяжемся с вами в ближайшее время.',
              'success'
            )
          }

          form.reset()
        } else {
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
        submitBtn.textContent = originalText
        submitBtn.disabled = false
      })
  }

  // Маска для телефона
  function initPhoneMask() {
    const phoneInputs = document.querySelectorAll(
      'input[type="tel"], input[phoneMask_JS]'
    )

    phoneInputs.forEach((input) => {
      const jq = window.jQuery || window.$
      if (jq && jq.fn && typeof jq.fn.mask === 'function') {
        jq(input).mask('+7 (999) 999-99-99')
      }
    })

    // Обновляем маску при появлении новых полей
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeType === 1) {
            // Element node
            const newPhoneInputs = node.querySelectorAll
              ? node.querySelectorAll('input[type="tel"], input[phoneMask_JS]')
              : []
            newPhoneInputs.forEach((input) => {
              const jq = window.jQuery || window.$
              if (jq && jq.fn && typeof jq.fn.mask === 'function') {
                jq(input).mask('+7 (999) 999-99-99')
              }
            })
          }
        })
      })
    })

    observer.observe(document.body, {
      childList: true,
      subtree: true,
    })
  }

  // Плавная прокрутка
  function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]')

    links.forEach((link) => {
      link.addEventListener('click', function (e) {
        e.preventDefault()
        const targetId = this.getAttribute('href')

        // Проверяем, что targetId не пустой и не равен просто '#'
        if (!targetId || targetId === '#') {
          return
        }

        const targetElement = document.querySelector(targetId)

        if (targetElement) {
          const headerHeight = document.querySelector('.header').offsetHeight
          const targetPosition = targetElement.offsetTop - headerHeight

          window.scrollTo({
            top: targetPosition,
            behavior: 'smooth',
          })
        }
      })
    })
  }

  // Уведомления
  function showNotification(message, type = 'info') {
    const notification = document.createElement('div')
    notification.className = `notification notification--${type}`
    notification.innerHTML = `
            <div class="notification__content">
                <span class="notification__message">${message}</span>
                <button class="notification__close">&times;</button>
            </div>
        `

    // Добавляем стили для уведомления
    notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${
              type === 'success'
                ? '#28a745'
                : type === 'error'
                ? '#dc3545'
                : '#17a2b8'
            };
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            z-index: 3000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 400px;
        `

    document.body.appendChild(notification)

    // Анимация появления
    setTimeout(() => {
      notification.style.transform = 'translateX(0)'
    }, 100)

    // Обработчик закрытия
    const closeBtn = notification.querySelector('.notification__close')
    closeBtn.addEventListener('click', () => {
      notification.style.transform = 'translateX(100%)'
      setTimeout(() => {
        document.body.removeChild(notification)
      }, 300)
    })

    // Автоматическое закрытие
    setTimeout(() => {
      if (document.body.contains(notification)) {
        notification.style.transform = 'translateX(100%)'
        setTimeout(() => {
          if (document.body.contains(notification)) {
            document.body.removeChild(notification)
          }
        }, 300)
      }
    }, 5000)
  }

  // Анимации при скролле
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

    // Наблюдаем за элементами для анимации
    const animatedElements = document.querySelectorAll(
      '.stats__item, .advantage-card, .request-card, .pricing-card'
    )
    animatedElements.forEach((el) => {
      observer.observe(el)
    })
  }

  // Инициализация анимаций
  initScrollAnimations()

  // Инициализация нативных select внутри попапов шапки (страница услуг и др.)
  ;(function initPopupNativeSelects() {
    try {
      // Берем исключительно теги select с классом js-native-select внутри попапов
      const selects = document.querySelectorAll(
        '.popup select.js-native-select'
      )
      selects.forEach((selectEl) => {
        // Удаляем возможные обертки «nice-select» рядом (и до, и после — на всякий случай)
        const siblings = [
          selectEl.previousElementSibling,
          selectEl.nextElementSibling,
        ]
        siblings.forEach((sib) => {
          if (sib && sib.classList && sib.classList.contains('nice-select')) {
            if (sib.parentNode) sib.parentNode.removeChild(sib)
          }
        })
        // Показываем нативный select
        selectEl.style.display = ''
        selectEl.classList.add('ready')
      })

      // Фолбэк: скрываем любые оставшиеся клоны nice-select в попапах
      document.querySelectorAll('.popup .nice-select').forEach((clone) => {
        clone.style.display = 'none'
      })
    } catch (e) {
      console.warn('initPopupNativeSelects error', e)
    }
  })()

  // Обработка корзины
  function initCart() {
    const cartCounter = document.querySelector('.cart-counter')

    // Если счетчик корзины отсутствует (на всех страницах, кроме магазина/товара) — не инициализируем
    if (!cartCounter) {
      return
    }

    // Обновление счетчика корзины
    function updateCartCount() {
      fetch('/api/cart.php?action=count')
        .then((response) => response.json())
        .then((data) => {
          if (!cartCounter) return
          const count =
            data && typeof data === 'object'
              ? // API возвращает { success: true, data: { count } }
                data.data && typeof data.data.count === 'number'
                ? data.data.count
                : typeof data.count === 'number'
                ? data.count
                : 0
              : 0
          if (count > 0) {
            cartCounter.textContent = count
            cartCounter.style.display = 'block'
          } else {
            cartCounter.style.display = 'none'
          }
        })
        .catch((error) => {
          console.error('Error updating cart count:', error)
        })
    }

    // Обновляем счетчик при загрузке
    updateCartCount()

    // Обновляем каждые 30 секунд
    setInterval(updateCartCount, 30000)
  }

  // Инициализация корзины
  initCart()

  // Мобильное меню
  function initMobileMenu() {
    if (mobileMenuInitialized) {
      console.warn('[MenuDebug] initMobileMenu: already initialized, skip')
      return
    }
    console.log('[MenuDebug] initMobileMenu: start')
    const menuBtn = document.querySelector('.header__menu-btn')
    const nav = document.querySelector('.header__nav')
    const header = document.querySelector('.header')
    const closeBtn = document.querySelector('.header__nav-close')

    console.log('[MenuDebug] elements', {
      hasMenuBtn: !!menuBtn,
      hasNav: !!nav,
      hasHeader: !!header,
      hasCloseBtn: !!closeBtn,
      viewport: window.innerWidth,
    })

    if (menuBtn && nav && header) {
      mobileMenuInitialized = true
      console.log('[MenuDebug] binding listeners')
      // Функции показа/скрытия верхней части хедера
      function hideHeader() {
        if (!header) return // Проверяем существование header
        header.classList.add('header--collapsed')
        console.log('[MenuDebug] hideHeader -> header--collapsed added')
      }
      function showHeader() {
        if (!header) return // Проверяем существование header
        header.classList.remove('header--collapsed')
        console.log('[MenuDebug] showHeader -> header--collapsed removed')
      }

      // Функция для закрытия мобильного меню
      function closeMenu() {
        nav.classList.remove('active')
        nav.classList.remove('open')
        if (header) header.classList.remove('open')
        menuBtn.classList.remove('active')
        document.body.classList.remove('menu-open')

        const spans = menuBtn.querySelectorAll('span')
        spans[0].style.transform = 'none'
        spans[1].style.opacity = '1'
        spans[2].style.transform = 'none'
        console.log('[MenuDebug] closeMenu: classes removed', {
          navClasses: nav.className,
          headerClasses: header ? header.className : 'header not found',
          btnClasses: menuBtn.className,
          bodyMenuOpen: document.body.classList.contains('menu-open'),
        })
      }

      // Функция для открытия мобильного меню
      function openMenu() {
        nav.classList.add('active')
        nav.classList.add('open')
        if (header) header.classList.add('open')
        menuBtn.classList.add('active')
        document.body.classList.add('menu-open')

        const spans = menuBtn.querySelectorAll('span')
        spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)'
        spans[1].style.opacity = '0'
        spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)'
        // Страховка против CSS конфликтов — делаем nav видимым явным стилем
        nav.style.display = 'flex'
        console.log('[MenuDebug] openMenu: classes added', {
          navClasses: nav.className,
          headerClasses: header ? header.className : 'header not found',
          btnClasses: menuBtn.className,
          bodyMenuOpen: document.body.classList.contains('menu-open'),
        })
      }

      // Обработчик клика по кнопке меню
      menuBtn.addEventListener('click', function () {
        console.log(
          '[MenuDebug] menuBtn click, isActive=',
          this.classList.contains('active')
        )
        if (this.classList.contains('active')) {
          closeMenu()
        } else {
          openMenu()
        }
      })

      // Обработчик клика по кнопке закрытия
      if (closeBtn) {
        closeBtn.addEventListener('click', () => {
          console.log('[MenuDebug] closeBtn click')
          closeMenu()
        })
      }

      // Закрытие меню при клике на ссылку
      const navLinks = nav.querySelectorAll('.nav-link')
      navLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
          console.log('[MenuDebug] nav link click -> close', {
            href: link.getAttribute('href'),
          })
          closeMenu()
        })
      })

      // Закрытие по клику вне меню
      document.addEventListener('click', function (e) {
        if (
          nav.classList.contains('active') &&
          !nav.contains(e.target) &&
          !menuBtn.contains(e.target)
        ) {
          console.log('[MenuDebug] click outside -> close')
          closeMenu()
        }
      })

      // Закрытие по Escape
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && nav.classList.contains('active')) {
          console.log('[MenuDebug] Escape pressed -> close')
          closeMenu()
        }
      })

      // Скрытие/показ по направлению скролла (везде, кроме когда меню открыто)
      let lastScrollTop = 0
      const headerHeight = header ? header.offsetHeight : 0
      window.addEventListener('scroll', function () {
        const scrollTop = window.scrollY || document.documentElement.scrollTop

        if (!nav.classList.contains('open')) {
          if (scrollTop > lastScrollTop && scrollTop > headerHeight) {
            hideHeader() // вниз
          } else if (scrollTop < lastScrollTop) {
            showHeader() // вверх
          }
        }

        if (scrollTop === 0) {
          showHeader()
        }

        lastScrollTop = scrollTop
      })
    } else {
      console.warn('[MenuDebug] initMobileMenu: missing elements', {
        hasMenuBtn: !!menuBtn,
        hasNav: !!nav,
        hasHeader: !!header,
      })
    }
  }

  // Инициализация мобильного меню
  initMobileMenu()

  // Ленивая загрузка изображений
  function initLazyLoading() {
    const images = document.querySelectorAll('img[data-src]')

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

  // Инициализация ленивой загрузки
  initLazyLoading()
  console.log('[MenuDebug] init sequence complete')

  // Обработка ошибок загрузки изображений
  document.querySelectorAll('img').forEach((img) => {
    img.addEventListener('error', function () {
      this.style.display = 'none'
    })
  })

  // Анимация чисел в статистике
  function animateNumbers() {
    const numbers = document.querySelectorAll('.stats__number, .stat-number')

    const numberObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          const number = entry.target
          const finalNumber = parseInt(number.textContent.replace(/\D/g, ''))
          const suffix = number.textContent.replace(/\d/g, '')

          animateNumber(number, 0, finalNumber, suffix)
          numberObserver.unobserve(number)
        }
      })
    })

    numbers.forEach((number) => numberObserver.observe(number))
  }

  function animateNumber(element, start, end, suffix) {
    const duration = 2000
    const startTime = performance.now()

    function updateNumber(currentTime) {
      const elapsed = currentTime - startTime
      const progress = Math.min(elapsed / duration, 1)

      const current = Math.floor(start + (end - start) * progress)
      element.textContent = current + suffix

      if (progress < 1) {
        requestAnimationFrame(updateNumber)
      }
    }

    requestAnimationFrame(updateNumber)
  }

  // Инициализация анимации чисел
  animateNumbers()

  // Обработка загрузки страницы
  window.addEventListener('load', function () {
    document.body.classList.add('loaded')
  })

  // Обработка ошибок
  window.addEventListener('error', function (e) {
    console.error('Page error:', e.error)
  })

  // Обработка необработанных промисов
  window.addEventListener('unhandledrejection', function (e) {
    console.error('Unhandled promise rejection:', e.reason)
  })

  // Динамические поля формы в разделе "ДЛЯ ВАС"
  function initDynamicFormFields() {
    const deliveryMethod = document.getElementById('delivery-method')
    const whatsappField = document.getElementById('whatsapp-field')
    const telegramField = document.getElementById('telegram-field')
    const emailField = document.getElementById('email-field')

    if (!deliveryMethod) return

    function showField(fieldToShow) {
      // Скрываем все поля
      whatsappField.style.display = 'none'
      telegramField.style.display = 'none'
      emailField.style.display = 'none'

      // Убираем required со всех полей
      const allInputs = [whatsappField, telegramField, emailField].map(
        (field) => field.querySelector('input')
      )
      allInputs.forEach((input) => {
        if (input) input.removeAttribute('required')
      })

      // Показываем нужное поле и добавляем required
      if (fieldToShow) {
        fieldToShow.style.display = 'block'
        const input = fieldToShow.querySelector('input')
        if (input) input.setAttribute('required', 'required')
      }
    }

    deliveryMethod.addEventListener('change', function () {
      const selectedValue = this.value

      switch (selectedValue) {
        case 'whatsapp':
          showField(whatsappField)
          break
        case 'telegram':
          showField(telegramField)
          break
        case 'email':
          showField(emailField)
          break
        default:
          showField(null)
      }
    })
  }

  // Кнопка "Наверх"
  function initScrollToTop() {
    const scrollToTopBtn = document.getElementById('scrollToTop')

    if (scrollToTopBtn) {
      // Показываем кнопку при скролле вниз
      window.addEventListener('scroll', function () {
        if (window.pageYOffset > 300) {
          scrollToTopBtn.classList.add('visible')
        } else {
          scrollToTopBtn.classList.remove('visible')
        }
      })

      // Прокрутка наверх при клике
      scrollToTopBtn.addEventListener('click', function () {
        window.scrollTo({
          top: 0,
          behavior: 'smooth',
        })
      })
    }
  }

  // Функция для кнопки "Показать еще" в разделе запросов
  function initShowMoreRequests() {
    const showMoreBtn = document.getElementById('showMoreRequests')
    const requestCards = document.querySelectorAll(
      '.requests__grid .request-card'
    )

    if (showMoreBtn) {
      showMoreBtn.addEventListener('click', function () {
        // Показываем все скрытые карточки
        requestCards.forEach((card) => {
          card.style.display = 'block'
        })

        // Скрываем кнопку
        showMoreBtn.style.display = 'none'
      })
    }
  }

  // Функция для кнопки "Показать еще вопросы" в FAQ
  function initShowMoreFaq() {
    const showMoreBtn = document.getElementById('showMoreFaq')
    const faqItems = document.querySelectorAll('.faq__grid .faq-item')

    if (showMoreBtn) {
      showMoreBtn.addEventListener('click', function () {
        // Показываем все скрытые вопросы
        faqItems.forEach((item) => {
          item.style.display = 'block'
        })

        // Скрываем кнопку
        showMoreBtn.style.display = 'none'
      })
    }
  }
})

// Глобальные функции для использования в HTML
window.showPopup = function (popupId) {
  const popup = document.getElementById(popupId)
  if (popup) {
    popup.classList.add('active')
    document.body.style.overflow = 'hidden'
    try {
      document.documentElement.style.overflow = 'hidden'
    } catch (e) {}
  }
}

window.closePopup = function (popupId) {
  const popup = document.getElementById(popupId)
  if (popup) {
    popup.classList.remove('active')
    document.body.style.overflow = ''
    try {
      document.documentElement.style.overflow = ''
    } catch (e) {}
  }
}

// Закрытие попапа по кнопке внутри его содержимого (для окна "Спасибо!")
window.closeParentPopup = function (buttonEl) {
  try {
    const popup = buttonEl.closest('.popup')
    if (popup) {
      popup.classList.remove('active')
      document.body.style.overflow = ''
      try {
        document.documentElement.style.overflow = ''
      } catch (e) {}
    }
  } catch (e) {}
}

// Утилиты
window.utils = {
  debounce: function (func, wait) {
    let timeout
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout)
        func(...args)
      }
      clearTimeout(timeout)
      timeout = setTimeout(later, wait)
    }
  },

  throttle: function (func, limit) {
    let inThrottle
    return function () {
      const args = arguments
      const context = this
      if (!inThrottle) {
        func.apply(context, args)
        inThrottle = true
        setTimeout(() => (inThrottle = false), limit)
      }
    }
  },
}
