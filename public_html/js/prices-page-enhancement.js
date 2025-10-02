/**
 * Улучшения для страницы цен
 * Специальная настройка попапов и отправки заявок
 */

console.log('=== PRICES PAGE ENHANCEMENT LOADED ===')

class PricesPageEnhancement {
  constructor() {
    this.init()
  }

  init() {
    console.log('Инициализация улучшений страницы цен...')

    // Ждем загрузки DOM
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () =>
        this.setupEnhancements()
      )
    } else {
      this.setupEnhancements()
    }
  }

  setupEnhancements() {
    console.log('Настройка улучшений...')

    // Улучшаем работу попапов
    this.enhancePopups()

    // Улучшаем работу форм
    this.enhanceForms()

    // Добавляем дополнительные обработчики
    this.addEventListeners()
  }

  enhancePopups() {
    console.log('Улучшение работы попапов...')

    // Переопределяем функцию открытия попапов
    if (typeof popupOpen === 'function') {
      const originalPopupOpen = popupOpen
      window.popupOpen = function (target) {
        console.log('Открытие попапа:', target)
        originalPopupOpen(target)

        // Дополнительная инициализация для попапа
        setTimeout(() => {
          const popup = document.querySelector(`[popupID="${target}"]`)
          if (popup) {
            console.log('Попап найден, инициализация форм...')

            // Применяем маски к полям телефонов в попапе
            const phoneInputs = popup.querySelectorAll('input[phoneMask_JS]')
            phoneInputs.forEach((input) => {
              if (
                typeof $ !== 'undefined' &&
                $.fn.mask &&
                !input.dataset.maskApplied
              ) {
                $(input).mask('+7 (999) 999-99-99')
                input.dataset.maskApplied = 'true'
                console.log('Маска применена к полю в попапе:', input)
              }
            })

            // Инициализируем nice-select в попапе
            const niceSelects = popup.querySelectorAll('.nice-select')
            niceSelects.forEach((select) => {
              if (typeof $.fn.niceSelect !== 'undefined') {
                $(select).niceSelect()
                console.log('Nice select инициализирован в попапе')
              }
            })

            // Инициализируем формы в попапе
            const forms = popup.querySelectorAll('.md-standart-form')
            forms.forEach((form) => {
              if (!form.dataset.formHandlerInitialized) {
                console.log('Инициализация формы в попапе:', form)
                this.initFormInPopup(form)
              }
            })
          }
        }, 100)
      }
    }
  }

  enhanceForms() {
    console.log('Улучшение работы форм...')

    // Находим все формы на странице
    const forms = document.querySelectorAll('.md-standart-form')
    forms.forEach((form) => {
      this.initFormInPopup(form)
    })
  }

  initFormInPopup(form) {
    console.log('Инициализация формы:', form)

    // Проверяем, не инициализирована ли уже форма
    if (form.dataset.pricesEnhancementInitialized === 'true') {
      return
    }

    // Удаляем старые обработчики submit
    const newForm = form.cloneNode(true)
    form.parentNode.replaceChild(newForm, form)

    // Добавляем обработчик submit
    newForm.addEventListener(
      'submit',
      (e) => {
        console.log('Submit формы перехвачен:', newForm)
        e.preventDefault()
        e.stopPropagation()
        e.stopImmediatePropagation()
        this.handleFormSubmit(newForm)
        return false
      },
      true
    )

    // Находим кнопку отправки
    const submitBtn = newForm.querySelector('[agreementcontrolbtn_js]')
    if (submitBtn) {
      console.log('Найдена кнопка отправки:', submitBtn)

      // Удаляем старые обработчики клика
      const newSubmitBtn = submitBtn.cloneNode(true)
      submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn)

      // Добавляем новый обработчик клика
      newSubmitBtn.addEventListener(
        'click',
        (e) => {
          console.log('Клик по кнопке перехвачен:', newSubmitBtn)
          e.preventDefault()
          e.stopPropagation()
          e.stopImmediatePropagation()

          // Проверяем согласие с условиями
          const agreementCheckbox = newForm.querySelector(
            '[agreementcontrolcheckbox_js]'
          )
          if (agreementCheckbox && !agreementCheckbox.checked) {
            this.showMessage('Необходимо согласие с условиями', 'error')
            return false
          }

          this.handleFormSubmit(newForm)
          return false
        },
        true
      )
    }

    // Отмечаем форму как инициализированную
    newForm.dataset.pricesEnhancementInitialized = 'true'
  }

  handleFormSubmit(form) {
    console.log('Обработка отправки формы:', form)

    const formData = new FormData(form)
    const submitBtn = form.querySelector('[agreementcontrolbtn_js]')
    const originalText = submitBtn ? submitBtn.innerHTML : ''

    // Добавляем CSRF токен
    formData.append('csrf_token', this.getCSRFToken())

    // Добавляем honeypot поле
    formData.append('website', '')

    // Добавляем URL страницы
    formData.append('page_url', window.location.href)

    // Показываем загрузку
    if (submitBtn) {
      submitBtn.innerHTML = '<span>Отправка...</span>'
      submitBtn.disabled = true
    }

    // Отправляем данные
    fetch('/telegram-sender.php', {
      method: 'POST',
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        console.log('Ответ сервера:', data)

        if (data.status === 'success') {
          this.showMessage(data.message, 'success')

          // Очищаем форму
          form.reset()

          // Сбрасываем nice-select
          const niceSelects = form.querySelectorAll('.nice-select')
          niceSelects.forEach((select) => {
            const current = select.querySelector('.current')
            if (current) {
              current.textContent = 'Сейчас'
            }
          })

          // Сбрасываем чекбокс согласия
          const agreementContainer = form.querySelector('[agreementcontrol_js]')
          if (agreementContainer) {
            agreementContainer.classList.add('checked')
          }

          // Закрываем попап через 2 секунды
          const popup = form.closest('.popup')
          if (popup) {
            setTimeout(() => {
              const closeBtn = popup.querySelector('[popupClose_JS]')
              if (closeBtn) {
                closeBtn.click()
              }
            }, 2000)
          }
        } else {
          this.showMessage(data.message || 'Ошибка при отправке', 'error')
        }
      })
      .catch((error) => {
        console.error('Ошибка:', error)
        this.showMessage('Ошибка при отправке заявки', 'error')
      })
      .finally(() => {
        // Восстанавливаем кнопку
        if (submitBtn) {
          submitBtn.innerHTML = originalText
          submitBtn.disabled = false
        }
      })
  }

  getCSRFToken() {
    // Ищем токен в мета-тегах
    const metaToken = document.querySelector('meta[name="csrf-token"]')
    if (metaToken) {
      return metaToken.getAttribute('content')
    }

    // Ищем токен в скрытом поле
    const hiddenToken = document.querySelector('input[name="csrf_token"]')
    if (hiddenToken) {
      return hiddenToken.value
    }

    // Генерируем временный токен
    return 'temp_token_' + Date.now()
  }

  showMessage(message, type = 'info') {
    // Удаляем существующие сообщения
    const existingMessages = document.querySelectorAll('.prices-message')
    existingMessages.forEach((msg) => msg.remove())

    // Создаем новое сообщение
    const messageDiv = document.createElement('div')
    messageDiv.className = `prices-message prices-message--${type}`
    messageDiv.innerHTML = `
      <div class="prices-message__content">
        <span class="prices-message__text">${message}</span>
        <button class="prices-message__close" onclick="this.parentElement.parentElement.remove()">×</button>
      </div>
    `

    // Добавляем стили
    messageDiv.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 10000;
      padding: 15px 20px;
      border-radius: 8px;
      color: white;
      font-weight: 500;
      max-width: 400px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      animation: pricesSlideIn 0.3s ease-out;
    `

    // Цвета в зависимости от типа
    if (type === 'success') {
      messageDiv.style.backgroundColor = '#31B939'
    } else if (type === 'error') {
      messageDiv.style.backgroundColor = '#e74c3c'
    } else {
      messageDiv.style.backgroundColor = '#3498db'
    }

    // Добавляем в DOM
    document.body.appendChild(messageDiv)

    // Автоматически удаляем через 5 секунд
    setTimeout(() => {
      if (messageDiv.parentElement) {
        messageDiv.remove()
      }
    }, 5000)
  }

  addEventListeners() {
    console.log('Добавление обработчиков событий...')

    // Глобальный перехват submit форм
    document.addEventListener(
      'submit',
      (e) => {
        const form = e.target
        if (form.classList.contains('md-standart-form')) {
          console.log('Глобальный перехват submit формы:', form)
          e.preventDefault()
          e.stopPropagation()
          e.stopImmediatePropagation()
          this.handleFormSubmit(form)
          return false
        }
      },
      true
    )

    // Глобальный перехват кликов по кнопкам
    document.addEventListener(
      'click',
      (e) => {
        const btn = e.target.closest('[agreementcontrolbtn_js]')
        if (btn) {
          const form = btn.closest('.md-standart-form')
          if (form) {
            console.log('Глобальный перехват клика по кнопке:', btn)
            e.preventDefault()
            e.stopPropagation()
            e.stopImmediatePropagation()

            // Проверяем согласие с условиями
            const agreementCheckbox = form.querySelector(
              '[agreementcontrolcheckbox_js]'
            )
            if (agreementCheckbox && !agreementCheckbox.checked) {
              this.showMessage('Необходимо согласие с условиями', 'error')
              return false
            }

            this.handleFormSubmit(form)
            return false
          }
        }
      },
      true
    )
  }
}

// Инициализация при загрузке страницы
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    new PricesPageEnhancement()
  })
} else {
  new PricesPageEnhancement()
}

// Добавляем CSS анимации
if (!document.querySelector('#prices-enhancement-styles')) {
  const style = document.createElement('style')
  style.id = 'prices-enhancement-styles'
  style.textContent = `
    @keyframes pricesSlideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    .prices-message__content {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .prices-message__close {
      background: none;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      margin-left: 10px;
      padding: 0;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .prices-message__close:hover {
      opacity: 0.8;
    }
  `
  document.head.appendChild(style)
}
