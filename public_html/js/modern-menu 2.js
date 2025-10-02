/**
 * Современное меню для всего сайта
 * Универсальное решение для всех страниц
 * Версия: 1.2 (исправлена проблема с переходами по ссылкам)
 * Дата: 2024-01-XX
 */

class ModernMenu {
  constructor() {
    this.header = document.querySelector('.header')
    this.burgerBtn = document.querySelector('[headerBtn_JS]')
    this.nav = document.querySelector('.header__nav')
    this.navLinks = document.querySelectorAll('.nav-link')
    this.isOpen = false

    this.init()
  }

  init() {
    if (!this.burgerBtn || !this.nav) {
      console.warn('ModernMenu: Не найдены необходимые элементы')
      return
    }

    this.setupEventListeners()
    this.addCloseButton()
    this.addNavInfo()
    this.setActiveLink()
    this.preventBodyScroll()
    // this.fixNavLinks() // Убираем функцию fixNavLinks которая изменяет ссылки
  }

  setupEventListeners() {
    // Обработчик клика по бургер кнопке
    this.burgerBtn.addEventListener('click', (e) => {
      e.preventDefault()
      e.stopPropagation()
      this.toggleMenu()
    })

    // Обработчик клика по ссылкам меню - исправлен для нормальной работы
    this.navLinks.forEach((link) => {
      link.addEventListener('click', (e) => {
        // Проверяем, что ссылка ведет на существующую страницу
        const href = link.getAttribute('href')
        if (href && href !== '#' && !href.startsWith('javascript:')) {
          // Закрываем меню перед переходом
          this.closeMenu()
          // НЕ предотвращаем переход - позволяем браузеру обработать ссылку нормально
        }
      })
    })

    // Закрытие по клику вне меню
    document.addEventListener('click', (e) => {
      if (
        this.isOpen &&
        !this.nav.contains(e.target) &&
        !this.burgerBtn.contains(e.target)
      ) {
        this.closeMenu()
      }
    })

    // Закрытие по Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.isOpen) {
        this.closeMenu()
      }
    })

    // Обработка свайпов на мобильных
    this.setupSwipeHandlers()
  }

  toggleMenu() {
    if (this.isOpen) {
      this.closeMenu()
    } else {
      this.openMenu()
    }
  }

  openMenu() {
    this.isOpen = true
    this.burgerBtn.classList.add('open')
    this.nav.classList.add('open')
    this.header.classList.add('open')

    // Блокируем скролл body
    document.body.style.overflow = 'hidden'

    // Убираем задержку для display: flex
    this.nav.style.display = 'flex'

    // Логирование
    console.log('ModernMenu: Меню открыто')
  }

  closeMenu() {
    this.isOpen = false
    this.burgerBtn.classList.remove('open')
    this.nav.classList.remove('open')
    this.header.classList.remove('open')

    // Разблокируем скролл body
    document.body.style.overflow = ''

    // Убираем класс для анимации
    setTimeout(() => {
      if (!this.isOpen) {
        this.nav.style.display = 'none'
      }
    }, 400)

    // Логирование
    console.log('ModernMenu: Меню закрыто')
  }

  addCloseButton() {
    // Проверяем, есть ли уже кнопка закрытия
    if (this.nav.querySelector('.nav-close')) {
      return
    }

    const closeBtn = document.createElement('div')
    closeBtn.className = 'nav-close'
    closeBtn.setAttribute('aria-label', 'Закрыть меню')

    closeBtn.addEventListener('click', (e) => {
      e.preventDefault()
      e.stopPropagation()
      this.closeMenu()
    })

    this.nav.appendChild(closeBtn)
  }

  addNavInfo() {
    // Проверяем, есть ли уже информация
    if (this.nav.querySelector('.nav-info')) {
      return
    }

    const navInfo = document.createElement('div')
    navInfo.className = 'nav-info'
    navInfo.innerHTML = `
            <p>Психолог Денис Черкас</p>
            <p>Специалист по зависимостям и созависимости</p>
            <p><a href="tel:+79936202951">+7 (993) 620-29-51</a></p>
            <p>Приемы Online с Пн по Пт</p>
        `

    this.nav.appendChild(navInfo)
  }

  setActiveLink() {
    const currentPath = window.location.pathname

    this.navLinks.forEach((link) => {
      const href = link.getAttribute('href')

      // Убираем старые классы
      link.classList.remove('active')

      // Проверяем соответствие текущему пути
      if (
        href === currentPath ||
        (currentPath === '/' && href === '/') ||
        (currentPath.includes(href.replace('/', '')) && href !== '/')
      ) {
        link.classList.add('active')
      }
    })
  }

  // Убираем функцию fixNavLinks которая изменяет ссылки
  // fixNavLinks() {
  //   // Исправляем ссылки в меню
  //   this.navLinks.forEach((link) => {
  //     const href = link.getAttribute('href')

  //     // Убираем .php из ссылок для красоты
  //     if (href && href.endsWith('.php')) {
  //       const newHref = href.replace('.php', '')
  //       link.setAttribute('href', newHref)
  //     }

  //     // Добавляем обработчик для проверки существования страницы
  //     link.addEventListener('click', (e) => {
  //       const targetHref = link.getAttribute('href')

  //       // Если ссылка ведет на несуществующую страницу, показываем уведомление
  //       if (targetHref && !this.pageExists(targetHref)) {
  //         e.preventDefault()
  //         this.showNotification('Страница находится в разработке')
  //       }
  //     })
  //   })
  // }

  pageExists(url) {
    // Список существующих страниц
    const existingPages = [
      '/',
      '/services',
      '/about',
      '/reviews',
      '/prices',
      '/articles',
      '/shop',
      '/contact',
      '/cart',
    ]

    return existingPages.includes(url)
  }

  showNotification(message) {
    // Создаем уведомление
    const notification = document.createElement('div')
    notification.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background: #ff6b6b;
      color: white;
      padding: 15px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 500;
      z-index: 10000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transform: translateX(100%);
      transition: transform 0.3s ease;
    `
    notification.textContent = message

    document.body.appendChild(notification)

    // Анимация появления
    setTimeout(() => {
      notification.style.transform = 'translateX(0)'
    }, 100)

    // Автоматическое скрытие
    setTimeout(() => {
      notification.style.transform = 'translateX(100%)'
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification)
        }
      }, 300)
    }, 3000)
  }

  setupSwipeHandlers() {
    let startX = 0
    let startY = 0
    let isSwiping = false

    // Начало свайпа
    this.nav.addEventListener('touchstart', (e) => {
      startX = e.touches[0].clientX
      startY = e.touches[0].clientY
      isSwiping = false
    })

    // Движение свайпа
    this.nav.addEventListener('touchmove', (e) => {
      if (!startX || !startY) return

      const deltaX = e.touches[0].clientX - startX
      const deltaY = e.touches[0].clientY - startY

      // Определяем направление свайпа
      if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
        isSwiping = true
      }
    })

    // Конец свайпа
    this.nav.addEventListener('touchend', (e) => {
      if (!isSwiping) return

      const deltaX = e.changedTouches[0].clientX - startX

      // Свайп влево закрывает меню
      if (deltaX < -100) {
        this.closeMenu()
      }

      startX = 0
      startY = 0
      isSwiping = false
    })
  }

  preventBodyScroll() {
    // Дополнительная защита от скролла при открытом меню
    this.nav.addEventListener(
      'touchmove',
      (e) => {
        if (this.isOpen) {
          e.preventDefault()
        }
      },
      { passive: false }
    )
  }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
  // Проверяем, не инициализировано ли уже меню
  if (window.modernMenu) {
    return
  }

  // Создаем экземпляр меню
  window.modernMenu = new ModernMenu()

  // Логирование успешной инициализации
  console.log('ModernMenu: Инициализировано успешно')
})

// Экспорт для использования в других скриптах
if (typeof module !== 'undefined' && module.exports) {
  module.exports = ModernMenu
}
