document.addEventListener('DOMContentLoaded', function () {
  // Загружаем количество лайков при загрузке страницы
  loadArticleLikes()

  // Обработка кнопки лайк
  const likeBtn = document.querySelector('.article-like-btn')
  if (likeBtn) {
    likeBtn.addEventListener('click', function () {
      const articleSlug = this.dataset.article
      const isLiked = this.classList.contains('liked')

      // Отключаем кнопку во время запроса
      this.disabled = true
      this.classList.add('loading')

      const action = isLiked ? 'unlike' : 'like'

      // Получаем CSRF токен
      const csrfToken =
        document
          .querySelector('meta[name="csrf-token"]')
          ?.getAttribute('content') || ''

      // Отправляем AJAX запрос
      fetch('/api/article-likes.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: new URLSearchParams({
          article_slug: articleSlug,
          action: action,
          csrf_token: csrfToken,
        }),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`)
          }
          return response.json()
        })
        .then((data) => {
          if (data.success) {
            // Обновляем состояние кнопки
            if (action === 'like') {
              this.classList.add('liked')
              this.querySelector('span').textContent = 'Нравится'
              this.querySelector('svg').style.fill = 'currentColor'
            } else {
              this.classList.remove('liked')
              this.querySelector('span').textContent = 'Нравится'
              this.querySelector('svg').style.fill = 'none'
            }

            // Обновляем счетчик лайков если есть
            const likeCounter = document.querySelector('.like-counter')
            if (likeCounter) {
              likeCounter.textContent = data.likes
            }

            // Показываем сообщение
            const message =
              action === 'like' ? 'Лайк поставлен! 👍' : 'Лайк убран 👎'
            showNotification(message, 'success')

            // Сохраняем состояние в localStorage для быстрого отображения
            const userLikes = JSON.parse(
              localStorage.getItem('articleLikes') || '[]'
            )
            if (action === 'like' && !userLikes.includes(articleSlug)) {
              userLikes.push(articleSlug)
            } else if (action === 'unlike') {
              const index = userLikes.indexOf(articleSlug)
              if (index > -1) userLikes.splice(index, 1)
            }
            localStorage.setItem('articleLikes', JSON.stringify(userLikes))
          } else {
            // Handle CSRF errors specifically
            if (data.code === 'CSRF_ERROR') {
              showNotification(
                'Сессия истекла. Обновите страницу и попробуйте снова.',
                'error'
              )
            } else {
              showNotification(data.error || 'Произошла ошибка', 'error')
            }
          }
        })
        .catch((error) => {
          console.error('Ошибка при обработке лайка:', error)
          showNotification('Произошла ошибка. Попробуйте позже.', 'error')
        })
        .finally(() => {
          // Включаем кнопку обратно
          this.disabled = false
          this.classList.remove('loading')
        })
    })
  }

  // Обработка кнопки поделиться
  const shareBtn = document.querySelector('.article-share-btn')
  if (shareBtn) {
    shareBtn.addEventListener('click', function () {
      const url = this.dataset.url
      const title = document.querySelector('.article-title').textContent

      if (navigator.share) {
        // Используем нативное API для мобильных устройств
        navigator.share({
          title: title,
          url: url,
        })
      } else {
        // Fallback для десктопа - копируем ссылку в буфер обмена
        navigator.clipboard
          .writeText(url)
          .then(function () {
            // Показываем уведомление
            showNotification('Ссылка скопирована в буфер обмена! 📋', 'success')
          })
          .catch(function () {
            // Fallback если clipboard API не поддерживается
            const textArea = document.createElement('textarea')
            textArea.value = url
            document.body.appendChild(textArea)
            textArea.select()
            document.execCommand('copy')
            document.body.removeChild(textArea)
            showNotification('Ссылка скопирована в буфер обмена! 📋', 'success')
          })

        // Гарантируем, что блок с кнопками соцсетей видим
        const socials = document.querySelector('.article-share-social')
        if (socials) {
          socials.style.display = 'flex'
        }
      }
    })
  }
})

/**
 * Загружает количество лайков и состояние кнопки при загрузке страницы
 */
function loadArticleLikes() {
  const likeBtn = document.querySelector('.article-like-btn')
  if (!likeBtn) return

  const articleSlug = likeBtn.dataset.article
  if (!articleSlug) return

  // Проверяем localStorage для быстрого отображения состояния
  const userLikes = JSON.parse(localStorage.getItem('articleLikes') || '[]')
  const hasLiked = userLikes.includes(articleSlug)

  if (hasLiked) {
    likeBtn.classList.add('liked')
    likeBtn.querySelector('svg').style.fill = 'currentColor'
  }

  // Загружаем актуальное количество лайков с сервера
  fetch(
    `/api/article-likes.php?article_slug=${encodeURIComponent(articleSlug)}`
  )
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }
      return response.json()
    })
    .then((data) => {
      if (data.success) {
        const likeCounter = document.querySelector('.like-counter')
        if (likeCounter) {
          likeCounter.textContent = data.likes
        }
      } else {
        console.warn('API вернул ошибку:', data.error || 'Неизвестная ошибка')
      }
    })
    .catch((error) => {
      console.error('Ошибка при загрузке лайков:', error)
      // Не показываем уведомление пользователю для ошибок загрузки лайков
    })
}

/**
 * Показывает уведомление пользователю
 */
function showNotification(message, type = 'info') {
  // Создаем элемент уведомления
  const notification = document.createElement('div')
  notification.className = `notification notification--${type}`

  // Добавляем иконку в зависимости от типа
  let icon = ''
  if (type === 'success') {
    icon = '✅'
  } else if (type === 'error') {
    icon = '❌'
  } else {
    icon = 'ℹ️'
  }

  notification.innerHTML = `
        <div class="notification__content">
            <div class="notification__icon">${icon}</div>
            <div class="notification__text">
                <div class="notification__title">${
                  type === 'success'
                    ? 'Успешно!'
                    : type === 'error'
                    ? 'Ошибка!'
                    : 'Информация'
                }</div>
                <div class="notification__message">${message}</div>
            </div>
            <button class="notification__close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `

  // Добавляем стили для уведомления
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        min-width: 350px;
        max-width: 450px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        border-left: 4px solid ${
          type === 'success'
            ? '#28a745'
            : type === 'error'
            ? '#dc3545'
            : '#007bff'
        };
        animation: slideInRight 0.3s ease-out;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    `

  const content = notification.querySelector('.notification__content')
  content.style.cssText = `
        display: flex;
        align-items: flex-start;
        padding: 20px;
        color: #333;
        gap: 12px;
    `

  const iconEl = notification.querySelector('.notification__icon')
  iconEl.style.cssText = `
        font-size: 24px;
        flex-shrink: 0;
        margin-top: 2px;
    `

  const textEl = notification.querySelector('.notification__text')
  textEl.style.cssText = `
        flex: 1;
        min-width: 0;
    `

  const titleEl = notification.querySelector('.notification__title')
  titleEl.style.cssText = `
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
        color: ${
          type === 'success'
            ? '#28a745'
            : type === 'error'
            ? '#dc3545'
            : '#007bff'
        };
    `

  const messageEl = notification.querySelector('.notification__message')
  messageEl.style.cssText = `
        font-size: 14px;
        line-height: 1.4;
        color: #666;
    `

  const closeBtn = notification.querySelector('.notification__close')
  closeBtn.style.cssText = `
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #999;
        margin-left: 8px;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background-color 0.2s;
    `

  closeBtn.addEventListener('mouseenter', () => {
    closeBtn.style.backgroundColor = '#f0f0f0'
  })

  closeBtn.addEventListener('mouseleave', () => {
    closeBtn.style.backgroundColor = 'transparent'
  })

  // Добавляем анимацию
  if (!document.getElementById('notification-styles')) {
    const styles = document.createElement('style')
    styles.id = 'notification-styles'
    styles.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `
    document.head.appendChild(styles)
  }

  // Добавляем на страницу
  document.body.appendChild(notification)

  // Автоматически удаляем через 4 секунды
  setTimeout(() => {
    if (notification.parentElement) {
      notification.style.animation = 'slideInRight 0.3s ease-out reverse'
      setTimeout(() => notification.remove(), 300)
    }
  }, 4000)
}
