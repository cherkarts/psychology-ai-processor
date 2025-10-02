/**
 * Улучшения для слайдов отзывов
 */

document.addEventListener('DOMContentLoaded', function () {
  // Функция для инициализации улучшенных слайдов отзывов
  function initEnhancedReviewsSlider() {
    // Находим все слайдеры отзывов
    const reviewSliders = document.querySelectorAll('.other-reviews__slider')

    reviewSliders.forEach(function (slider) {
      // Инициализация Swiper с улучшенными настройками
      const swiperContainer = slider.querySelector('.swiper-container')
      if (!swiperContainer) return

      // Создаем новый экземпляр Swiper с улучшенными настройками
      const swiper = new Swiper(swiperContainer, {
        direction: 'horizontal',
        slidesPerView: 1,
        speed: 500,
        spaceBetween: 20,
        autoHeight: true, // Автоматическая высота слайдов
        watchOverflow: true,
        watchSlidesProgress: true,
        watchSlidesVisibility: true,

        // Пагинация
        pagination: {
          el: slider.querySelector('.slider-pagination'),
          type: 'bullets',
          clickable: true,
          dynamicBullets: true,
          dynamicMainBullets: 3,
        },

        // Навигация
        navigation: {
          nextEl: slider.querySelector('.slider-next-btn'),
          prevEl: slider.querySelector('.slider-prev-btn'),
        },

        // Автоматическое переключение
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
          pauseOnMouseEnter: true,
        },

        // Эффекты
        effect: 'slide',
        fadeEffect: {
          crossFade: true,
        },

        // События
        on: {
          init: function () {
            updateSlideHeights(this)
          },
          slideChange: function () {
            updateSlideHeights(this)
          },
          resize: function () {
            updateSlideHeights(this)
          },
        },
      })

      // Функция для обновления высоты слайдов
      function updateSlideHeights(swiperInstance) {
        const slides = swiperInstance.slides
        let maxHeight = 0

        // Находим максимальную высоту среди всех слайдов
        slides.forEach(function (slide) {
          const slideContent = slide.querySelector('.other-reviews__item')
          if (slideContent) {
            const slideHeight = slideContent.offsetHeight
            maxHeight = Math.max(maxHeight, slideHeight)
          }
        })

        // Устанавливаем одинаковую высоту для всех слайдов
        slides.forEach(function (slide) {
          const slideContent = slide.querySelector('.other-reviews__item')
          if (slideContent) {
            slideContent.style.minHeight = maxHeight + 'px'
          }
        })

        // Обновляем Swiper
        swiperInstance.update()
      }

      // Обработчик для кнопки "Читать далее"
      const readMoreButtons = slider.querySelectorAll(
        '.review-content__read-more .read-more-btn'
      )
      readMoreButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
          e.preventDefault()

          const reviewItem = this.closest('.other-reviews__item')
          const reviewContent = reviewItem.querySelector('.review-content')
          const textContainer = reviewContent.querySelector(
            '.review-content__text-container'
          )
          const firstText = textContainer.querySelector(
            '.review-content__text:first-child'
          )
          const hiddenTexts = textContainer.querySelectorAll(
            '.review-content__text:nth-child(n+2)'
          )
          const readMoreBtn = reviewContent.querySelector(
            '.review-content__read-more'
          )

          if (reviewContent.classList.contains('open')) {
            // Закрываем текст
            reviewContent.classList.remove('open')
            firstText.style.maxHeight = '14.0625vw'
            firstText.style.webkitLineClamp = '3'
            hiddenTexts.forEach(function (text) {
              text.style.display = 'none'
            })
            readMoreBtn.style.display = 'flex'
            this.querySelector('span').textContent = 'Читать далее'
          } else {
            // Открываем текст
            reviewContent.classList.add('open')
            firstText.style.maxHeight = 'none'
            firstText.style.webkitLineClamp = 'unset'
            hiddenTexts.forEach(function (text) {
              text.style.display = 'block'
            })
            readMoreBtn.style.display = 'none'
            this.querySelector('span').textContent = 'Скрыть'
          }

          // Обновляем высоту слайдов после изменения контента
          setTimeout(function () {
            updateSlideHeights(swiper)
          }, 300)
        })
      })

      // Обработчик для изображений в отзывах
      const reviewImages = slider.querySelectorAll(
        '.review-content__img-container a'
      )
      reviewImages.forEach(function (imageLink) {
        imageLink.addEventListener('click', function (e) {
          e.preventDefault()

          const imageSrc = this.querySelector('img').src
          const imageAlt =
            this.querySelector('img').alt || 'Изображение из отзыва'

          // Открываем изображение в Fancybox
          if (typeof Fancybox !== 'undefined') {
            Fancybox.show([
              {
                src: imageSrc,
                type: 'image',
                caption: imageAlt,
              },
            ])
          }
        })
      })

      // Адаптивность для мобильных устройств
      function handleResize() {
        const isMobile = window.innerWidth <= 768

        if (isMobile) {
          // Настройки для мобильных устройств
          swiper.params.slidesPerView = 1
          swiper.params.spaceBetween = 10
          swiper.params.autoplay.delay = 4000
        } else {
          // Настройки для десктопа
          swiper.params.slidesPerView = 1
          swiper.params.spaceBetween = 20
          swiper.params.autoplay.delay = 5000
        }

        swiper.update()
        updateSlideHeights(swiper)
      }

      // Обработчик изменения размера окна
      window.addEventListener('resize', handleResize)
      handleResize() // Инициализация при загрузке

      // Добавляем индикатор загрузки
      const loadingIndicator = document.createElement('div')
      loadingIndicator.className = 'reviews-loading'
      loadingIndicator.innerHTML = '<div class="loading-spinner"></div>'
      loadingIndicator.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: 10;
                display: none;
            `

      slider.appendChild(loadingIndicator)

      // Показываем индикатор загрузки при переключении слайдов
      swiper.on('slideChangeTransitionStart', function () {
        loadingIndicator.style.display = 'block'
      })

      swiper.on('slideChangeTransitionEnd', function () {
        loadingIndicator.style.display = 'none'
      })

      // Добавляем анимации для слайдов
      const slides = slider.querySelectorAll('.swiper-slide')
      slides.forEach(function (slide, index) {
        slide.style.transition = 'all 0.5s ease'
        slide.style.opacity = '0'
        slide.style.transform = 'translateY(20px)'

        setTimeout(function () {
          slide.style.opacity = '1'
          slide.style.transform = 'translateY(0)'
        }, index * 100)
      })
    })
  }

  // Инициализация при загрузке страницы
  initEnhancedReviewsSlider()

  // Инициализация при динамической загрузке контента
  if (typeof MutationObserver !== 'undefined') {
    const observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
          mutation.addedNodes.forEach(function (node) {
            if (
              node.nodeType === 1 &&
              node.querySelector &&
              node.querySelector('.other-reviews__slider')
            ) {
              initEnhancedReviewsSlider()
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

  // Добавляем стили для индикатора загрузки
  const loadingStyles = document.createElement('style')
  loadingStyles.textContent = `
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--main-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .other-reviews__item {
            transition: all 0.3s ease;
        }
        
        .other-reviews__item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .review-content__read-more .read-more-btn {
            transition: all 0.3s ease;
        }
        
        .review-content__read-more .read-more-btn:hover {
            transform: translateY(-2px);
            color: var(--main-color-darken);
        }
    `
  document.head.appendChild(loadingStyles)
})

// Экспорт функции для использования в других модулях
if (typeof module !== 'undefined' && module.exports) {
  module.exports = {
    initEnhancedReviewsSlider: function () {
      // Код инициализации
    },
  }
}
