// Инициализация карусели категорий в магазине
document.addEventListener('DOMContentLoaded', function () {
  const filtersSlider = document.querySelector('[filtersslider_js]')

  if (filtersSlider) {
    const swiperContainer = filtersSlider.querySelector('.swiper-container')
    const prevBtn = filtersSlider.querySelector('.slider-prev-btn')
    const nextBtn = filtersSlider.querySelector('.slider-next-btn')

    // Инициализация Swiper
    const swiper = new Swiper(swiperContainer, {
      slidesPerView: 'auto',
      spaceBetween: 27,
      navigation: {
        nextEl: nextBtn,
        prevEl: prevBtn,
      },
      breakpoints: {
        320: {
          slidesPerView: 2,
          spaceBetween: 15,
        },
        768: {
          slidesPerView: 3,
          spaceBetween: 20,
        },
        1024: {
          slidesPerView: 4,
          spaceBetween: 27,
        },
        1200: {
          slidesPerView: 5,
          spaceBetween: 27,
        },
      },
    })

    // Обработка кликов по фильтрам
    const filterItems = filtersSlider.querySelectorAll('.filters-item')
    filterItems.forEach((item) => {
      item.addEventListener('click', function (e) {
        // Убираем активный класс у всех элементов
        filterItems.forEach((filter) => filter.classList.remove('active'))
        // Добавляем активный класс к кликнутому элементу
        this.classList.add('active')
      })
    })

    // Обновление состояния кнопок навигации
    swiper.on('slideChange', function () {
      if (swiper.isBeginning) {
        prevBtn.classList.add('swiper-button-disabled')
      } else {
        prevBtn.classList.remove('swiper-button-disabled')
      }

      if (swiper.isEnd) {
        nextBtn.classList.add('swiper-button-disabled')
      } else {
        nextBtn.classList.remove('swiper-button-disabled')
      }
    })

    // Инициализация состояния кнопок
    if (swiper.isBeginning) {
      prevBtn.classList.add('swiper-button-disabled')
    }
  }
})
