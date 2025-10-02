console.log('=== FORM HANDLER LOADED ===')

// Агрессивный обработчик отправки форм
function initFormHandler() {
  console.log('Инициализация обработчика форм...')

  // Обработка всех форм с классом md-standart-form
  const forms = document.querySelectorAll('.md-standart-form')
  console.log('Найдено форм md-standart-form:', forms.length)

  // Обработка формы контактов
  const contactForm = document.querySelector('#contactForm')
  if (contactForm) {
    console.log('Найдена форма контактов:', contactForm)

    // Проверяем, не обработана ли уже форма
    if (contactForm.dataset.formHandlerInitialized === 'true') {
      console.log('Форма контактов уже обработана, пропускаем')
    } else {
      // Добавляем обработчик submit
      contactForm.addEventListener(
        'submit',
        function (e) {
          console.log('Перехвачен submit формы контактов:', contactForm)
          e.preventDefault()
          e.stopPropagation()
          e.stopImmediatePropagation()
          handleContactFormSubmit(contactForm)
          return false
        },
        true
      )

      // Отмечаем форму как обработанную
      contactForm.dataset.formHandlerInitialized = 'true'
    }
  }

  forms.forEach(function (form, index) {
    console.log(`Обработка формы ${index + 1}:`, form)

    // Проверяем, не обработана ли уже форма
    if (form.dataset.formHandlerInitialized === 'true') {
      console.log(`Форма ${index + 1} уже обработана, пропускаем`)
      return
    }

    // Добавляем обработчик submit
    form.addEventListener(
      'submit',
      function (e) {
        console.log('Перехвачен submit формы:', form)
        e.preventDefault()
        e.stopPropagation()
        e.stopImmediatePropagation()
        handleFormSubmit(form)
        return false
      },
      true
    )

    // Обработка клика по кнопке отправки
    const submitBtn = form.querySelector('[agreementcontrolbtn_js]')
    if (submitBtn) {
      console.log('Найдена кнопка отправки:', submitBtn)

      // Удаляем старые обработчики
      const newSubmitBtn = submitBtn.cloneNode(true)
      submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn)

      // Добавляем новый обработчик клика
      newSubmitBtn.addEventListener(
        'click',
        function (e) {
          console.log('Перехвачен клик по кнопке:', newSubmitBtn)
          e.preventDefault()
          e.stopPropagation()
          e.stopImmediatePropagation()

          // Проверяем согласие с условиями
          const agreementCheckbox = form.querySelector(
            '[agreementcontrolcheckbox_js]'
          )
          if (agreementCheckbox && !agreementCheckbox.checked) {
            showMessage('Необходимо согласие с условиями', 'error')
            return false
          }

          handleFormSubmit(form)
          return false
        },
        true
      )
    }

    // Отмечаем форму как обработанную
    form.dataset.formHandlerInitialized = 'true'
  })

  // Повторно применяем маски к полям телефонов
  applyPhoneMasks()
}

// Функция для применения масок к полям телефонов
function applyPhoneMasks() {
  console.log('🔧 applyPhoneMasks вызвана')

  // Проверяем доступность jQuery и плагина
  if (typeof $ === 'undefined') {
    console.log('❌ jQuery недоступен в applyPhoneMasks')
    return
  }

  if (!$.fn.mask) {
    console.log('❌ Плагин маски недоступен в applyPhoneMasks')
    return
  }

  const phoneInputs = document.querySelectorAll('input[phoneMask_JS]')
  const telInputs = document.querySelectorAll('input[type="tel"]')

  console.log('📱 Найдено полей phoneMask_JS:', phoneInputs.length)
  console.log('📱 Найдено полей type="tel":', telInputs.length)

  // Применяем маски к полям с phoneMask_JS
  phoneInputs.forEach(function (input) {
    // Проверяем, не применена ли уже маска
    if (input.dataset.maskApplied === 'true') {
      console.log('✅ Маска уже применена к полю:', input)
      return
    }

    $(input).mask('+7 (999) 999-99-99')
    input.dataset.maskApplied = 'true'
    console.log('✅ Маска применена к полю phoneMask_JS:', input)
  })

  // Применяем маски к полям type="tel"
  telInputs.forEach(function (input) {
    // Проверяем, не применена ли уже маска
    if (input.dataset.maskApplied === 'true') {
      console.log('✅ Маска уже применена к полю:', input)
      return
    }

    $(input).mask('+7 (999) 999-99-99')
    input.dataset.maskApplied = 'true'
    console.log('✅ Маска применена к полю type="tel":', input)
  })
}

// Запускаем инициализацию при загрузке DOM
document.addEventListener('DOMContentLoaded', initFormHandler)

// Также запускаем после полной загрузки страницы
window.addEventListener('load', function () {
  setTimeout(initFormHandler, 100)
})

// И еще раз через секунду для надежности
setTimeout(initFormHandler, 1000)

// Применяем маски после полной загрузки страницы
setTimeout(applyPhoneMasks, 2000)

// Функция для обработки клика по кнопке (вынесена отдельно для возможности удаления)
function handleButtonClick(e) {
  e.preventDefault()
  e.stopPropagation()

  const form = this.closest('form')
  const agreementCheckbox = form.querySelector('[agreementcontrolcheckbox_js]')

  if (agreementCheckbox && !agreementCheckbox.checked) {
    showMessage('Необходимо согласие с условиями', 'error')
    return false
  }

  handleFormSubmit(form)
  return false
}

// Функция обработки отправки формы
function handleFormSubmit(form) {
  console.log('Отправка формы:', form)

  const formData = new FormData(form)
  const submitBtn = form.querySelector('[agreementcontrolbtn_js]')
  const originalText = submitBtn ? submitBtn.innerHTML : ''

  // Добавляем CSRF токен
  formData.append('csrf_token', getCSRFToken())

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
        showMessage(data.message, 'success')

        // Очищаем форму
        form.reset()

        // Сбрасываем nice-select
        const niceSelects = form.querySelectorAll('.nice-select')
        niceSelects.forEach(function (select) {
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

        // Закрываем попап, если форма в попапе (поддержка старых и новых попапов)
        const popup = form.closest('.popup')
        if (popup) {
          setTimeout(() => {
            const legacyClose = popup.querySelector('[popupClose_JS]')
            const newClose = popup.querySelector('.popup__close')
            const overlay = popup.querySelector('.popup__overlay')

            if (legacyClose) {
              legacyClose.click()
            } else if (newClose) {
              newClose.click()
            } else if (overlay) {
              overlay.click()
            } else {
              // Фолбэк: снимаем классы и возвращаем прокрутку вручную
              popup.classList.remove('active')
              popup.classList.remove('open')
              document.body.style.overflow = ''
              try {
                document.documentElement.style.overflow = ''
              } catch (e) {}
            }
          }, 2000)
        }
      } else {
        showMessage(data.message || 'Ошибка при отправке', 'error')
      }
    })
    .catch((error) => {
      console.error('Ошибка:', error)
      showMessage('Ошибка при отправке заявки', 'error')
    })
    .finally(() => {
      // Восстанавливаем кнопку
      if (submitBtn) {
        submitBtn.innerHTML = originalText
        submitBtn.disabled = false
      }
    })
}

// Функция получения CSRF токена
function getCSRFToken() {
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

  // Генерируем временный токен (в продакшене должен быть получен с сервера)
  return 'temp_token_' + Date.now()
}

// Функция показа сообщений
function showMessage(message, type = 'info') {
  // Удаляем существующие сообщения
  const existingMessages = document.querySelectorAll('.form-message')
  existingMessages.forEach((msg) => msg.remove())

  // Создаем новое сообщение
  const messageDiv = document.createElement('div')
  messageDiv.className = `form-message form-message--${type}`
  messageDiv.innerHTML = `
        <div class="form-message__content">
            <span class="form-message__text">${message}</span>
            <button class="form-message__close" onclick="this.parentElement.parentElement.remove()">×</button>
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
        animation: slideIn 0.3s ease-out;
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

// Агрессивная защита от перезагрузки страницы
document.addEventListener(
  'submit',
  function (e) {
    const form = e.target
    if (form.classList.contains('md-standart-form')) {
      console.log('Глобальный перехват submit формы:', form)
      e.preventDefault()
      e.stopPropagation()
      e.stopImmediatePropagation()
      handleFormSubmit(form)
      return false
    }
  },
  true
)

// Дополнительная защита от кликов по кнопкам
document.addEventListener(
  'click',
  function (e) {
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
          showMessage('Необходимо согласие с условиями', 'error')
          return false
        }

        handleFormSubmit(form)
        return false
      }
    }
  },
  true
)

// CSS анимация
if (!document.querySelector('#form-handler-styles')) {
  const style = document.createElement('style')
  style.id = 'form-handler-styles'
  style.textContent = `
      @keyframes slideIn {
          from {
              transform: translateX(100%);
              opacity: 0;
          }
          to {
              transform: translateX(0);
              opacity: 1;
          }
      }
      
      .form-message__content {
          display: flex;
          align-items: center;
          justify-content: space-between;
      }
      
      .form-message__close {
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
      
      .form-message__close:hover {
          opacity: 0.8;
      }
  `
  document.head.appendChild(style)
}

// Дополнительная инициализация для попапов
function initPopupForms() {
  console.log('Инициализация форм в попапах...')

  // Наблюдаем за изменениями в DOM для динамически добавляемых попапов
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.type === 'childList') {
        mutation.addedNodes.forEach(function (node) {
          if (
            node.nodeType === 1 &&
            node.classList &&
            node.classList.contains('popup')
          ) {
            console.log('Обнаружен новый попап:', node)
            setTimeout(initFormHandler, 100)
          }
        })
      }
    })
  })

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  })
}

// Запускаем инициализацию попапов
document.addEventListener('DOMContentLoaded', initPopupForms)

// Функция обработки формы контактов
function handleContactFormSubmit(form) {
  console.log('Отправка формы контактов:', form)

  const formData = new FormData(form)
  const submitBtn = form.querySelector('button[type="submit"]')
  const originalText = submitBtn ? submitBtn.innerHTML : ''

  // CSRF токен уже есть в форме, добавляем только honeypot поле
  formData.append('website', '')

  // URL страницы уже есть в форме, но добавим текущий для надежности
  if (!formData.has('page_url')) {
    formData.append('page_url', window.location.href)
  }

  // Отладочная информация
  console.log('Отправляемые данные формы:')
  for (let [key, value] of formData.entries()) {
    console.log(`${key}: ${value}`)
  }

  // Показываем загрузку
  if (submitBtn) {
    submitBtn.innerHTML = '<span>Отправка...</span>'
    submitBtn.disabled = true
  }

  // Отправляем данные
  fetch('/api/contact.php', {
    method: 'POST',
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      console.log('Ответ сервера для формы контактов:', data)

      if (data.status === 'success') {
        showContactMessage(data.message, 'success')

        // Очищаем форму
        form.reset()

        // Сбрасываем селект времени
        const timeSelect = form.querySelector('.time-select')
        if (timeSelect) {
          timeSelect.classList.remove('has-value')
        }
      } else {
        showContactMessage(data.message || 'Ошибка при отправке', 'error')
      }
    })
    .catch((error) => {
      console.error('Ошибка формы контактов:', error)
      showContactMessage('Ошибка при отправке заявки', 'error')
    })
    .finally(() => {
      // Восстанавливаем кнопку
      if (submitBtn) {
        submitBtn.innerHTML = originalText
        submitBtn.disabled = false
      }
    })
}

// Функция показа сообщений для формы контактов
function showContactMessage(message, type) {
  // Удаляем существующие сообщения
  const existingMessages = document.querySelectorAll('.contact-message')
  existingMessages.forEach((msg) => msg.remove())

  // Создаем новое сообщение
  const messageDiv = document.createElement('div')
  messageDiv.className = `contact-message contact-message--${type}`
  messageDiv.textContent = message

  // Добавляем стили
  messageDiv.style.cssText = `
    margin-top: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    font-weight: 500;
    text-align: center;
    animation: slideInUp 0.3s ease-out;
  `

  // Цвета в зависимости от типа
  if (type === 'success') {
    messageDiv.style.backgroundColor = '#d4edda'
    messageDiv.style.color = '#155724'
    messageDiv.style.border = '1px solid #c3e6cb'
  } else if (type === 'error') {
    messageDiv.style.backgroundColor = '#f8d7da'
    messageDiv.style.color = '#721c24'
    messageDiv.style.border = '1px solid #f5c6cb'
  }

  // Добавляем в DOM после формы
  const contactForm = document.querySelector('#contactForm')
  if (contactForm) {
    contactForm.parentNode.appendChild(messageDiv)
  }

  // Автоматически удаляем через 5 секунд
  setTimeout(() => {
    if (messageDiv.parentElement) {
      messageDiv.style.transition = 'opacity 0.3s ease-out'
      messageDiv.style.opacity = '0'
      setTimeout(() => {
        if (messageDiv.parentElement) {
          messageDiv.remove()
        }
      }, 300)
    }
  }, 5000)
}
