// Глобальные переменные для карусели отзывов
let allReviews = []
let currentFilter = 'all'
let reviewsSwiper = null

// Загрузка отзывов
async function loadReviews(type = 'all') {
  // Проверяем, есть ли контейнер для отзывов
  const container = document.getElementById('reviewsContainer')
  if (!container) {
    console.log('ℹ️ Контейнер отзывов не найден, пропускаем загрузку')
    return
  }

  try {
    console.log('🔍 Загружаем отзывы типа:', type)
    const response = await fetch(`/api/reviews.php?type=${type}`)

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    console.log('📦 Получены данные:', data)

    if (data.success) {
      if (type === 'all') {
        allReviews = data.data
      }
      console.log('✅ Рендерим отзывы:', data.data.length)
      renderReviews(data.data)
    } else {
      console.error('❌ Ошибка загрузки отзывов:', data.error)
    }
  } catch (error) {
    console.error('❌ Ошибка загрузки отзывов:', error)
  }
}

// Рендеринг отзывов
function renderReviews(reviews) {
  console.log('🎨 Начинаем рендеринг отзывов')
  const container = document.getElementById('reviewsContainer')
  if (!container) {
    console.error('❌ Контейнер reviewsContainer не найден!')
    return
  }

  console.log('🧹 Очищаем контейнер')
  container.innerHTML = ''

  console.log('📝 Создаем слайды для', reviews.length, 'отзывов')
  reviews.forEach((review, index) => {
    console.log('📄 Создаем слайд', index + 1, 'для отзыва:', review.name)
    const reviewSlide = createReviewSlide(review)
    container.appendChild(reviewSlide)
  })

  console.log('🔄 Инициализируем Swiper')
  // Инициализируем Swiper после рендеринга отзывов
  initReviewsSwiper()
}

// Создание слайда отзыва
function createReviewSlide(review) {
  const slide = document.createElement('div')
  slide.className = `swiper-slide review-slide review-slide--${review.type}`

  let mediaContent = ''

  // Создание медиа контента в зависимости от типа
  if (review.type === 'photo' && review.image) {
    const safeImageSrc = review.image
    mediaContent = `
            <div class="review-slide__media">
                <img src="${safeImageSrc}" alt="Отзыв ${review.author}" onclick="openPhotoModal('${safeImageSrc}', '${review.author}')" style="cursor: pointer;" />
                <div class="review-slide__zoom-icon" onclick="openPhotoModal('${safeImageSrc}', '${review.author}')">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        <path d="M10 7H9v2H7v1h2v2h1v-2h2V9h-2V7z"/>
                    </svg>
                </div>
            </div>
        `
  } else if (review.type === 'video' && (review.thumbnail || review.video)) {
    const safeThumb = review.thumbnail || 'image/video-thumbnail-default.jpg'
    const safeVideo = review.video || ''
    mediaContent = `
            <div class="review-slide__media">
                <div class="review-slide__video">
                    <img src="${safeThumb}" alt="Превью видео ${
      review.author
    }" />
                    <div class="review-slide__video-overlay" ${
                      safeVideo ? `onclick="playVideo('${safeVideo}')"` : ''
                    }>
                        <div class="review-slide__play-button">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        `
  }

  // Создание звездочек рейтинга
  const rating = parseInt(review.rating) || 0
  const stars = Array(5)
    .fill()
    .map((_, index) => {
      const isFilled = index < rating
      return `
        <svg viewBox="0 0 24 24" fill="${
          isFilled ? '#ffc107' : '#ddd'
        }" class="star ${isFilled ? 'filled' : ''}">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
      `
    })
    .join('')

  // Форматирование даты
  const formattedDate = new Date(review.created_at).toLocaleDateString(
    'ru-RU',
    {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }
  )

  // Аватар: используем аватар Telegram, если есть; иначе — инициалы
  const initials = (review.name || 'А').charAt(0).toUpperCase()
  const avatarUrl =
    review.telegram_avatar || review.avatar_url || review.photo_url || ''

  slide.innerHTML = `
        ${mediaContent}
        <div class="review-slide__header">
            <div class="review-slide__avatar">
                ${
                  avatarUrl
                    ? `<img src="${avatarUrl}"
                           alt="Avatar"
                           class="review-slide__avatar-img"
                           width="48" height="48"
                           onerror="this.style.display='none'; this.parentNode.querySelector('.review-slide__avatar-placeholder').style.display='flex';" />`
                    : ''
                }
                <div class="review-slide__avatar-placeholder" style="${
                  avatarUrl ? 'display:none' : 'display:flex'
                }">${initials}</div>
            </div>
            <div class="review-slide__info">
                <h4>${review.name || 'Аноним'}</h4>
                <div class="review-slide__rating">
                    ${stars}
                </div>
                <span class="review-slide__date">${formattedDate}</span>
            </div>
        </div>
        <div class="review-slide__text">
            ${review.text || ''}
        </div>
        ${
          review.verification_status
            ? '<div class="review-slide__verified">✓ Проверенный отзыв</div>'
            : ''
        }
    `

  return slide
}

// Воспроизведение видео
function playVideo(videoUrl) {
  if (typeof Fancybox !== 'undefined') {
    Fancybox.show([
      {
        src: videoUrl,
        type: 'video',
      },
    ])
  } else {
    // Fallback - открытие в новом окне
    window.open(videoUrl, '_blank')
  }
}

// Открытие фото в модальном окне
function openPhotoModal(imageSrc, userName) {
  if (typeof Fancybox !== 'undefined') {
    Fancybox.show([
      {
        src: imageSrc,
        type: 'image',
        caption: `Отзыв ${userName}`,
        options: {
          zoom: true,
          panzoom: {
            zoomFade: false,
            maxScale: function () {
              return 2
            },
          },
        },
      },
    ])
  } else {
    // Fallback - создание простого модального окна
    createSimplePhotoModal(imageSrc, userName)
  }
}

// Простое модальное окно для фото (fallback)
function createSimplePhotoModal(imageSrc, userName) {
  // Удаляем существующее модальное окно, если есть
  const existingModal = document.getElementById('photoModal')
  if (existingModal) {
    existingModal.remove()
  }

  const modal = document.createElement('div')
  modal.id = 'photoModal'
  modal.className = 'photo-modal'
  modal.innerHTML = `
        <div class="photo-modal__overlay" onclick="closePhotoModal()"></div>
        <div class="photo-modal__content">
            <button class="photo-modal__close" onclick="closePhotoModal()">&times;</button>
            <img src="${imageSrc}" alt="Отзыв ${userName}" />
            <div class="photo-modal__caption">Отзыв ${userName}</div>
        </div>
    `

  document.body.appendChild(modal)

  // Показываем модальное окно
  setTimeout(() => {
    modal.classList.add('active')
  }, 10)
}

// Закрытие модального окна
function closePhotoModal() {
  const modal = document.getElementById('photoModal')
  if (modal) {
    modal.classList.remove('active')
    setTimeout(() => {
      modal.remove()
    }, 300)
  }
}

// Фильтрация отзывов
function filterReviews(type) {
  currentFilter = type

  // Обновление активной кнопки
  document.querySelectorAll('.filter-btn').forEach((btn) => {
    btn.classList.remove('active')
  })
  document.querySelector(`[data-type="${type}"]`).classList.add('active')

  // Загрузка отзывов по фильтру
  loadReviews(type)
}

// Анимация счетчиков
function animateCounters() {
  const counters = document.querySelectorAll('.stat-item__number')

  counters.forEach((counter) => {
    const target = parseFloat(counter.getAttribute('data-count'))
    const duration = 2000 // 2 секунды
    const step = target / (duration / 16) // 60 FPS
    let current = 0

    const timer = setInterval(() => {
      current += step
      if (current >= target) {
        current = target
        clearInterval(timer)
      }

      // Форматирование числа
      if (target % 1 === 0) {
        counter.textContent = Math.floor(current) + (target === 150 ? '+' : '')
      } else {
        counter.textContent = current.toFixed(1)
      }
    }, 16)
  })
}

// Инициализация Swiper для отзывов
function initReviewsSwiper() {
  if (reviewsSwiper) {
    reviewsSwiper.destroy(true, true)
  }

  const swiperContainer = document.querySelector('.reviews-swiper')
  if (!swiperContainer) {
    console.error('Контейнер .reviews-swiper не найден!')
    return
  }

  reviewsSwiper = new Swiper('.reviews-swiper', {
    slidesPerView: 1,
    spaceBetween: 30,
    loop: true,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    pagination: {
      el: '.swiper-pagination-bullets',
      clickable: true,
    },
    navigation: {
      nextEl: '.reviews-next-btn',
      prevEl: '.reviews-prev-btn',
    },
    breakpoints: {
      768: {
        slidesPerView: 2,
        spaceBetween: 30,
      },
      1024: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
      1200: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
    },
    on: {
      init: function () {
        // Анимация счетчиков при инициализации
        animateCounters()
      },
    },
  })
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function () {
  console.log('🚀 DOM загружен, инициализируем карусель отзывов')

  // Проверяем, есть ли контейнер для отзывов на странице
  const reviewsContainer = document.getElementById('reviewsContainer')
  if (!reviewsContainer) {
    console.log(
      'ℹ️ Контейнер отзывов не найден, пропускаем инициализацию карусели'
    )
    return
  }

  // Загрузка всех отзывов только если контейнер существует
  loadReviews()

  // Обработчики фильтров
  document.querySelectorAll('.filter-btn').forEach((btn) => {
    btn.addEventListener('click', function () {
      const type = this.getAttribute('data-type')
      console.log('🔘 Клик по фильтру:', type)
      filterReviews(type)
    })
  })

  // Обработчик кнопки "Добавить отзыв"
  const addReviewBtn = document.getElementById('addReviewBtn')
  if (addReviewBtn) {
    addReviewBtn.addEventListener('click', function () {
      console.log('🔘 Клик по кнопке "Добавить отзыв"')
      if (typeof window.showReviewForm === 'function') {
        window.showReviewForm()
      } else {
        console.error('❌ Функция showReviewForm не найдена')
        // Fallback - показываем форму напрямую
        const formContainer = document.getElementById('reviewFormContainer')
        if (formContainer) {
          formContainer.style.display = 'block'
          document.body.style.overflow = 'hidden'
        }
      }
    })
  }

  // Обработчик клавиши Escape для закрытия модального окна
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closePhotoModal()
      // Также закрываем форму отзывов
      if (typeof window.hideReviewForm === 'function') {
        window.hideReviewForm()
      }
    }
  })
})
