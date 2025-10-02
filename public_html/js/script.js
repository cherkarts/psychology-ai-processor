// Проверка jQuery после загрузки
if (typeof $ !== 'undefined' && $.fn) {
  // Убеждаемся, что jQuery работает корректно
  console.log('jQuery загружен успешно')
}

// Проверка исправления o.call
if (typeof window.o === 'function') {
  console.log('✅ Исправление o.call применено в script.js')
} else {
  console.warn('⚠️ Исправление o.call не применено в script.js')
}

$(document).ready(function () {
  // Инициализация Fancybox
  if (typeof Fancybox !== 'undefined') {
    Fancybox.bind('[data-fancybox]')
    console.log('Fancybox initialized')
  } else {
    console.log('Fancybox not found')
  }

  // Обработка ошибок для предотвращения TypeError
  window.addEventListener('error', function (e) {
    console.warn('JavaScript error caught:', e.error)
    return false
  })

  $('.process-item').click(function () {
    $('.process-item').removeClass('active')
    $(this).addClass('active')
    $('.process__content').removeClass('active')
    let id = $(this).attr('data-id') - 1
    console.log(id)
    $('.process__content').eq(id).addClass('active')
  })

  $('.products-slider__item').click(function () {
    $('.products-slider__item').removeClass('active')
    $(this).addClass('active')
    $('.products__item').removeClass('active')
    let id = $(this).attr('data-id') - 1
    console.log(id)
    $('.products__item').eq(id).addClass('active')
  })

  let d = new Date()
  let month = d.getMonth() + 1
  let day = d.getDate()
  let output = day + '.' + month + '.' + d.getFullYear()

  $('.download-price__circle p span').text(output)

  $('.nav-item.home').prependTo('.nav')

  let calcBannerTime = $('[calcBannerFixed_JS]').data('time') * 1000
  setTimeout(function () {
    $('[calcBannerFixed_JS]').addClass('visible')
  }, calcBannerTime)

  // Маска для телефона
  if ($.fn.mask) {
    $('[phoneMask_JS]').mask('+7 (999) 999-99-99')
  } else {
    console.warn('jQuery Masked Input не найден!')
  }
})
