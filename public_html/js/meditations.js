// JavaScript для страницы медитаций

document.addEventListener('DOMContentLoaded', function () {
  // Инициализация фильтрации
  initMeditationFilter()

  // Инициализация категорий
  initCategoryCards()

  // Инициализация карусели
  initCarousel()

  // Инициализация аудио плееров
  initAudioPlayers()

  // Плавная прокрутка для якорных ссылок
  initSmoothScroll()

  // Инициализация кнопок лайков и избранного
  initLikeAndFavoriteButtons()
})

// Обработчик изменения размера окна для карусели
window.addEventListener('resize', function () {
  updateSlidesPerView()
  updateCarouselPosition()
  updateCarouselDots()
  updateCarouselButtons()
})

// Обработчик клавиатуры для карусели
document.addEventListener('keydown', function (e) {
  const carousel = document.querySelector('.categories-carousel')
  if (!carousel) return

  // Проверяем, находится ли фокус в области карусели
  const isCarouselFocused =
    carousel.contains(document.activeElement) ||
    carousel === document.activeElement

  if (isCarouselFocused) {
    switch (e.key) {
      case 'ArrowLeft':
        e.preventDefault()
        moveCarousel(-1)
        break
      case 'ArrowRight':
        e.preventDefault()
        moveCarousel(1)
        break
      case 'Home':
        e.preventDefault()
        goToSlide(0)
        break
      case 'End':
        e.preventDefault()
        goToSlide(totalSlides - 1)
        break
    }
  }
})

// Функция инициализации фильтрации медитаций
function initMeditationFilter() {
  const filterButtons = document.querySelectorAll('.filter-btn')
  const meditationCards = document.querySelectorAll('.meditation-card')

  filterButtons.forEach((button) => {
    button.addEventListener('click', function () {
      const category = this.getAttribute('data-category')

      // Обновляем активную кнопку
      filterButtons.forEach((btn) => btn.classList.remove('active'))
      this.classList.add('active')

      // Фильтруем карточки
      filterMeditations(category, meditationCards)
    })
  })
}

// Функция фильтрации медитаций
function filterMeditations(category, cards) {
  // Получаем избранные медитации
  const favorites = JSON.parse(
    localStorage.getItem('meditationFavorites') || '[]'
  )

  cards.forEach((card) => {
    const cardCategory = card.getAttribute('data-category')
    const meditationId = card
      .querySelector('.meditation-card__like-btn')
      ?.getAttribute('data-meditation-id')

    if (category === 'favorites') {
      // Показываем только избранные медитации
      if (favorites.includes(meditationId)) {
        card.classList.remove('hidden')
        card.classList.add('visible')
      } else {
        card.classList.add('hidden')
        card.classList.remove('visible')
      }
    } else if (category === 'all' || cardCategory === category) {
      card.classList.remove('hidden')
      card.classList.add('visible')
    } else {
      card.classList.add('hidden')
      card.classList.remove('visible')
    }
  })

  // Анимация появления отфильтрованных карточек
  setTimeout(() => {
    const visibleCards = document.querySelectorAll('.meditation-card.visible')
    visibleCards.forEach((card, index) => {
      card.style.animationDelay = `${index * 0.1}s`
    })
  }, 100)
}

// Переменные для карусели
let currentSlide = 0
let totalSlides = 0
let slidesPerView = 3

// Переменные для свайпов
let startX = 0
let currentX = 0
let isDragging = false
let startTime = 0

// Функция инициализации карточек категорий
function initCategoryCards() {
  const categoryCards = document.querySelectorAll('.category-card')

  categoryCards.forEach((card) => {
    card.addEventListener('click', function () {
      const category = this.getAttribute('data-category')

      // Находим соответствующую кнопку фильтра и кликаем по ней
      const filterButton = document.querySelector(
        `.filter-btn[data-category="${category}"]`
      )
      if (filterButton) {
        filterButton.click()

        // Плавная прокрутка к списку медитаций
        const meditationsSection = document.querySelector('.meditations-list')
        if (meditationsSection) {
          meditationsSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
          })
        }
      }
    })
  })
}

// Функция инициализации карусели
function initCarousel() {
  const track = document.querySelector('.categories-carousel__track')
  const cards = document.querySelectorAll(
    '.categories-carousel__track .category-card'
  )

  if (!track || cards.length === 0) return

  totalSlides = Math.ceil(cards.length / slidesPerView)

  // Обновляем количество слайдов в зависимости от экрана
  updateSlidesPerView()

  // Обновляем точки навигации
  updateCarouselDots()

  // Обновляем состояние кнопок
  updateCarouselButtons()

  // Инициализируем свайпы
  initSwipeGestures()
}

// Функция инициализации свайпов
function initSwipeGestures() {
  const container = document.querySelector('.categories-carousel__container')
  if (!container) return

  // Touch события
  container.addEventListener('touchstart', handleTouchStart, { passive: false })
  container.addEventListener('touchmove', handleTouchMove, { passive: false })
  container.addEventListener('touchend', handleTouchEnd, { passive: false })

  // Mouse события для десктопа
  container.addEventListener('mousedown', handleMouseDown)
  container.addEventListener('mousemove', handleMouseMove)
  container.addEventListener('mouseup', handleMouseUp)
  container.addEventListener('mouseleave', handleMouseUp)

  // Предотвращаем выделение текста при свайпе
  container.addEventListener('selectstart', (e) => e.preventDefault())
}

// Обработчики touch событий
function handleTouchStart(e) {
  e.preventDefault()
  startX = e.touches[0].clientX
  currentX = startX
  isDragging = true
  startTime = Date.now()

  const track = document.querySelector('.categories-carousel__track')
  if (track) {
    track.style.transition = 'none'
  }
}

function handleTouchMove(e) {
  if (!isDragging) return
  e.preventDefault()

  currentX = e.touches[0].clientX
  const diffX = currentX - startX

  const track = document.querySelector('.categories-carousel__track')
  if (track) {
    const cardWidth = track.querySelector('.category-card')?.offsetWidth || 280
    const gap = 30
    const currentTranslate = -(currentSlide * (cardWidth + gap) * slidesPerView)
    const newTranslate = currentTranslate + diffX

    track.style.transform = `translateX(${newTranslate}px)`
  }
}

function handleTouchEnd(e) {
  if (!isDragging) return

  const endTime = Date.now()
  const duration = endTime - startTime
  const diffX = currentX - startX
  const minSwipeDistance = 50
  const maxSwipeTime = 300

  const track = document.querySelector('.categories-carousel__track')
  if (track) {
    track.style.transition = 'transform 0.3s ease'
  }

  // Определяем направление свайпа
  if (Math.abs(diffX) > minSwipeDistance && duration < maxSwipeTime) {
    if (diffX > 0) {
      // Свайп вправо - предыдущий слайд
      moveCarousel(-1)
    } else {
      // Свайп влево - следующий слайд
      moveCarousel(1)
    }
  } else {
    // Возвращаемся к текущему слайду
    updateCarouselPosition()
  }

  isDragging = false
}

// Обработчики mouse событий
function handleMouseDown(e) {
  e.preventDefault()
  startX = e.clientX
  currentX = startX
  isDragging = true
  startTime = Date.now()

  const track = document.querySelector('.categories-carousel__track')
  if (track) {
    track.style.transition = 'none'
  }
}

function handleMouseMove(e) {
  if (!isDragging) return
  e.preventDefault()

  currentX = e.clientX
  const diffX = currentX - startX

  const track = document.querySelector('.categories-carousel__track')
  if (track) {
    const cardWidth = track.querySelector('.category-card')?.offsetWidth || 280
    const gap = 30
    const currentTranslate = -(currentSlide * (cardWidth + gap) * slidesPerView)
    const newTranslate = currentTranslate + diffX

    track.style.transform = `translateX(${newTranslate}px)`
  }
}

function handleMouseUp(e) {
  if (!isDragging) return

  const endTime = Date.now()
  const duration = endTime - startTime
  const diffX = currentX - startX
  const minSwipeDistance = 50
  const maxSwipeTime = 300

  const track = document.querySelector('.categories-carousel__track')
  if (track) {
    track.style.transition = 'transform 0.3s ease'
  }

  // Определяем направление свайпа
  if (Math.abs(diffX) > minSwipeDistance && duration < maxSwipeTime) {
    if (diffX > 0) {
      // Свайп вправо - предыдущий слайд
      moveCarousel(-1)
    } else {
      // Свайп влево - следующий слайд
      moveCarousel(1)
    }
  } else {
    // Возвращаемся к текущему слайду
    updateCarouselPosition()
  }

  isDragging = false
}

// Функция обновления количества слайдов на экране
function updateSlidesPerView() {
  const width = window.innerWidth

  if (width <= 480) {
    slidesPerView = 1
  } else if (width <= 1200) {
    slidesPerView = 2
  } else {
    slidesPerView = 3
  }

  totalSlides = Math.ceil(
    document.querySelectorAll('.categories-carousel__track .category-card')
      .length / slidesPerView
  )
}

// Функция перемещения карусели
function moveCarousel(direction) {
  const newSlide = currentSlide + direction

  if (newSlide >= 0 && newSlide < totalSlides) {
    currentSlide = newSlide
    updateCarouselPosition()
    updateCarouselDots()
    updateCarouselButtons()
  }
}

// Функция перехода к конкретному слайду
function goToSlide(slideIndex) {
  if (slideIndex >= 0 && slideIndex < totalSlides) {
    currentSlide = slideIndex
    updateCarouselPosition()
    updateCarouselDots()
    updateCarouselButtons()
  }
}

// Функция обновления позиции карусели
function updateCarouselPosition() {
  const track = document.querySelector('.categories-carousel__track')
  if (!track) return

  const cardWidth = track.querySelector('.category-card')?.offsetWidth || 280
  const gap = 30
  const translateX = -(currentSlide * (cardWidth + gap) * slidesPerView)

  track.style.transform = `translateX(${translateX}px)`
}

// Функция обновления точек навигации
function updateCarouselDots() {
  const dotsContainer = document.querySelector('.carousel-dots')
  if (!dotsContainer) return

  // Очищаем существующие точки
  dotsContainer.innerHTML = ''

  // Создаем новые точки
  for (let i = 0; i < totalSlides; i++) {
    const dot = document.createElement('button')
    dot.className = `carousel-dot ${i === currentSlide ? 'active' : ''}`
    dot.onclick = () => goToSlide(i)
    dot.setAttribute('role', 'tab')
    dot.setAttribute('aria-selected', i === currentSlide ? 'true' : 'false')
    dot.setAttribute('aria-label', `Слайд ${i + 1}`)
    dotsContainer.appendChild(dot)
  }
}

// Функция обновления состояния кнопок карусели
function updateCarouselButtons() {
  const prevBtn = document.querySelector('.carousel-btn--prev')
  const nextBtn = document.querySelector('.carousel-btn--next')

  if (prevBtn) {
    prevBtn.disabled = currentSlide === 0
  }

  if (nextBtn) {
    nextBtn.disabled = currentSlide >= totalSlides - 1
  }
}

// Функция инициализации аудио плееров
function initAudioPlayers() {
  const audioPlayers = document.querySelectorAll('.audio-player')

  audioPlayers.forEach((player) => {
    // Добавляем обработчики событий для аудио плееров
    player.addEventListener('play', function () {
      // Останавливаем все остальные плееры
      audioPlayers.forEach((otherPlayer) => {
        if (otherPlayer !== this) {
          otherPlayer.pause()
        }
      })

      // Добавляем визуальную индикацию воспроизведения
      const card = this.closest('.meditation-card')
      if (card) {
        card.style.borderColor = 'var(--brand-primary)'
        card.style.boxShadow = '0 8px 30px rgba(106, 126, 159, 0.2)'
      }
    })

    player.addEventListener('pause', function () {
      // Убираем визуальную индикацию
      const card = this.closest('.meditation-card')
      if (card) {
        card.style.borderColor = ''
        card.style.boxShadow = ''
      }
    })

    player.addEventListener('ended', function () {
      // Убираем визуальную индикацию при окончании
      const card = this.closest('.meditation-card')
      if (card) {
        card.style.borderColor = ''
        card.style.boxShadow = ''
      }
    })
  })
}

// Функция плавной прокрутки
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
        targetElement.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        })
      }
    })
  })
}

// Функция для добавления медитации в избранное (если нужно)
function addToFavorites(meditationId) {
  let favorites = JSON.parse(
    localStorage.getItem('meditationFavorites') || '[]'
  )

  if (!favorites.includes(meditationId)) {
    favorites.push(meditationId)
    localStorage.setItem('meditationFavorites', JSON.stringify(favorites))

    // Показываем уведомление
    showNotification('Медитация добавлена в избранное')
  } else {
    // Удаляем из избранного
    favorites = favorites.filter((id) => id !== meditationId)
    localStorage.setItem('meditationFavorites', JSON.stringify(favorites))

    showNotification('Медитация удалена из избранного')
  }
}

// Функция показа уведомлений
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

// Функция для отслеживания прогресса прослушивания
function trackListeningProgress(player, meditationId) {
  let progress = 0

  player.addEventListener('timeupdate', function () {
    progress = (this.currentTime / this.duration) * 100

    // Сохраняем прогресс в localStorage
    const progressData = JSON.parse(
      localStorage.getItem('meditationProgress') || '{}'
    )
    progressData[meditationId] = progress
    localStorage.setItem('meditationProgress', JSON.stringify(progressData))

    // Если прослушано более 80%, считаем завершенным
    if (progress >= 80) {
      markAsCompleted(meditationId)
    }
  })
}

// Функция отметки медитации как завершенной
function markAsCompleted(meditationId) {
  let completed = JSON.parse(
    localStorage.getItem('completedMeditations') || '[]'
  )

  if (!completed.includes(meditationId)) {
    completed.push(meditationId)
    localStorage.setItem('completedMeditations', JSON.stringify(completed))

    // Показываем уведомление о завершении
    showNotification('Медитация завершена! 🎉', 'success')
  }
}

// Функция для получения статистики прослушивания
function getListeningStats() {
  const progressData = JSON.parse(
    localStorage.getItem('meditationProgress') || '{}'
  )
  const completed = JSON.parse(
    localStorage.getItem('completedMeditations') || '[]'
  )
  const favorites = JSON.parse(
    localStorage.getItem('meditationFavorites') || '[]'
  )

  return {
    totalProgress: Object.keys(progressData).length,
    completed: completed.length,
    favorites: favorites.length,
    totalTime: Object.values(progressData).reduce(
      (sum, progress) => sum + progress,
      0
    ),
  }
}

// Функция для шаринга медитаций
function shareMeditation(meditationId, title) {
  const url = window.location.href + '#' + meditationId
  const text = `Послушайте медитацию "${title}" от психолога Дениса Черкаса`

  if (navigator.share) {
    // Используем нативное API шаринга если доступно
    navigator
      .share({
        title: title,
        text: text,
        url: url,
      })
      .catch(console.error)
  } else {
    // Показываем меню выбора платформы
    showShareMenu(meditationId, title, url, text)
  }
}

// Функция для показа меню выбора платформы шаринга
function showShareMenu(meditationId, title, url, text) {
  // Удаляем существующее меню если есть
  const existingMenu = document.getElementById('share-menu')
  if (existingMenu) {
    existingMenu.remove()
  }

  // Создаем меню
  const menu = document.createElement('div')
  menu.id = 'share-menu'
  menu.className = 'share-menu'
  menu.innerHTML = `
    <div class="share-menu__overlay" onclick="closeShareMenu()"></div>
    <div class="share-menu__content">
      <div class="share-menu__header">
        <h4>Поделиться медитацией</h4>
        <button class="share-menu__close" onclick="closeShareMenu()">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
      <div class="share-menu__options">
        <button class="share-option" onclick="shareToWhatsApp('${text}', '${url}')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488" fill="#25D366"/>
          </svg>
          WhatsApp
        </button>
        <button class="share-option" onclick="shareToTelegram('${text}', '${url}')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z" fill="#0088cc"/>
          </svg>
          Telegram
        </button>
        <button class="share-option" onclick="shareToVK('${text}', '${url}')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M15.07 8.28S14.24 8.4 13.94 8.4c-.19 0-.5-.12-.5-.5s.31-.5.5-.5c.3 0 1.13.12 1.13.12s.5.06.5.5-.31.38-.5.38zM12.94 9.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 10.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 11.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 12.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 13.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 14.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 15.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 16.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 17.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 18.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 19.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 20.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 21.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 22.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 23.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5zM12.94 24.28c-.19 0-.5-.12-.5-.5s.31-.5.5-.5.5.12.5.5-.31.5-.5.5z" fill="#4C75A3"/>
          </svg>
          ВКонтакте
        </button>
        <button class="share-option" onclick="shareToCopy('${text}', '${url}')">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 1H4C2.9 1 2 1.9 2 3V17H4V3H16V1ZM19 5H8C6.9 5 6 5.9 6 7V21C6 22.1 6.9 23 8 23H19C20.1 23 21 22.1 21 21V7C21 5.9 20.1 5 19 5ZM19 21H8V7H19V21Z" fill="#666"/>
          </svg>
          Копировать ссылку
        </button>
      </div>
    </div>
  `

  document.body.appendChild(menu)

  // Анимация появления
  setTimeout(() => {
    menu.classList.add('active')
  }, 10)
}

// Функции для шаринга в конкретные платформы
function shareToWhatsApp(text, url) {
  const shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`
  window.open(shareUrl, '_blank')
  closeShareMenu()
}

function shareToTelegram(text, url) {
  const shareUrl = `https://t.me/share/url?url=${encodeURIComponent(
    url
  )}&text=${encodeURIComponent(text)}`
  window.open(shareUrl, '_blank')
  closeShareMenu()
}

function shareToVK(text, url) {
  const shareUrl = `https://vk.com/share.php?url=${encodeURIComponent(
    url
  )}&title=${encodeURIComponent(text)}`
  window.open(shareUrl, '_blank')
  closeShareMenu()
}

function shareToCopy(text, url) {
  navigator.clipboard
    .writeText(text + ' ' + url)
    .then(() => {
      showNotification('Ссылка скопирована в буфер обмена!')
    })
    .catch(() => {
      // Fallback для старых браузеров
      const textArea = document.createElement('textarea')
      textArea.value = text + ' ' + url
      document.body.appendChild(textArea)
      textArea.select()
      document.execCommand('copy')
      document.body.removeChild(textArea)
      showNotification('Ссылка скопирована в буфер обмена!')
    })
  closeShareMenu()
}

function closeShareMenu() {
  const menu = document.getElementById('share-menu')
  if (menu) {
    menu.classList.remove('active')
    setTimeout(() => {
      menu.remove()
    }, 300)
  }
}

// Экспортируем функции для использования в других скриптах
window.MeditationApp = {
  addToFavorites,
  trackListeningProgress,
  getListeningStats,
  showNotification,
  shareMeditation,
}

// Функции для лайков и избранного
function toggleLike(meditationId) {
  const likeBtn = document.querySelector(
    `[data-meditation-id="${meditationId}"].meditation-card__like-btn`
  )
  const likeCount = likeBtn.querySelector('.like-count')
  const likeIcon = likeBtn.querySelector('.like-icon')

  // Получаем текущие лайки из localStorage
  let likes = JSON.parse(localStorage.getItem('meditationLikes') || '{}')
  let userLikes = JSON.parse(
    localStorage.getItem('userMeditationLikes') || '[]'
  )

  if (userLikes.includes(meditationId)) {
    // Убираем лайк
    likes[meditationId] = Math.max(0, (likes[meditationId] || 1) - 1)
    userLikes = userLikes.filter((id) => id !== meditationId)
    likeBtn.classList.remove('active')
    likeIcon.style.fill = 'none'
    showNotification('Лайк убран 👎', 'success')
  } else {
    // Добавляем лайк
    likes[meditationId] = (likes[meditationId] || 0) + 1
    userLikes.push(meditationId)
    likeBtn.classList.add('active')
    likeIcon.style.fill = 'white'
    showNotification('Лайк поставлен! 👍', 'success')
  }

  // Обновляем счетчик
  likeCount.textContent = likes[meditationId] || 0

  // Сохраняем в localStorage
  localStorage.setItem('meditationLikes', JSON.stringify(likes))
  localStorage.setItem('userMeditationLikes', JSON.stringify(userLikes))
}

function toggleFavorite(meditationId) {
  const favoriteBtn = document.querySelector(
    `[data-meditation-id="${meditationId}"].meditation-card__favorite-btn`
  )
  const favoriteIcon = favoriteBtn.querySelector('.favorite-icon')

  // Получаем избранное из localStorage
  let favorites = JSON.parse(
    localStorage.getItem('meditationFavorites') || '[]'
  )

  if (favorites.includes(meditationId)) {
    // Убираем из избранного
    favorites = favorites.filter((id) => id !== meditationId)
    favoriteBtn.classList.remove('active')
    favoriteIcon.style.fill = 'none'
    showNotification('Убрано из избранного 📁', 'success')
  } else {
    // Добавляем в избранное
    favorites.push(meditationId)
    favoriteBtn.classList.add('active')
    favoriteIcon.style.fill = 'white'
    showNotification('Добавлено в избранное! 📁', 'success')
  }

  // Сохраняем в localStorage
  localStorage.setItem('meditationFavorites', JSON.stringify(favorites))
}

// Функция для инициализации состояния кнопок при загрузке страницы
function initLikeAndFavoriteButtons() {
  // Инициализируем лайки
  const likes = JSON.parse(localStorage.getItem('meditationLikes') || '{}')
  const userLikes = JSON.parse(
    localStorage.getItem('userMeditationLikes') || '[]'
  )

  userLikes.forEach((meditationId) => {
    const likeBtn = document.querySelector(
      `[data-meditation-id="${meditationId}"].meditation-card__like-btn`
    )
    if (likeBtn) {
      likeBtn.classList.add('active')
      const likeIcon = likeBtn.querySelector('.like-icon')
      likeIcon.style.fill = 'white'
    }
  })

  // Обновляем счетчики лайков
  Object.keys(likes).forEach((meditationId) => {
    const likeBtn = document.querySelector(
      `[data-meditation-id="${meditationId}"].meditation-card__like-btn`
    )
    if (likeBtn) {
      const likeCount = likeBtn.querySelector('.like-count')
      likeCount.textContent = likes[meditationId] || 0
    }
  })

  // Инициализируем избранное
  const favorites = JSON.parse(
    localStorage.getItem('meditationFavorites') || '[]'
  )
  favorites.forEach((meditationId) => {
    const favoriteBtn = document.querySelector(
      `[data-meditation-id="${meditationId}"].meditation-card__favorite-btn`
    )
    if (favoriteBtn) {
      favoriteBtn.classList.add('active')
      const favoriteIcon = favoriteBtn.querySelector('.favorite-icon')
      favoriteIcon.style.fill = 'white'
    }
  })
}

// Делаем функции доступными глобально
window.shareMeditation = shareMeditation
window.shareToWhatsApp = shareToWhatsApp
window.shareToTelegram = shareToTelegram
window.shareToVK = shareToVK
window.shareToCopy = shareToCopy
window.closeShareMenu = closeShareMenu
window.toggleLike = toggleLike
window.toggleFavorite = toggleFavorite
window.moveCarousel = moveCarousel
window.goToSlide = goToSlide
