<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

// Check if user is logged in, if not redirect to login
if (!isLoggedIn()) {
  header('Location: login.php');
  exit();
}

$pageTitle = 'Управление промокодами';
require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-container">
  <div class="dashboard-header">
    <h1>Управление промокодами (Тестовая версия)</h1>
    <p>Проверка работы API промокодов</p>
  </div>

  <div class="filters-section">
    <div class="filter-group">
      <label for="statusFilter">Статус:</label>
      <select id="statusFilter" onchange="loadPromos()">
        <option value="">Все</option>
        <option value="active">Активные</option>
        <option value="inactive">Неактивные</option>
      </select>
    </div>

    <div class="filter-group">
      <label for="typeFilter">Тип:</label>
      <select id="typeFilter" onchange="loadPromos()">
        <option value="">Все</option>
        <option value="percentage">Процентные</option>
        <option value="fixed">Фиксированные</option>
      </select>
    </div>

    <div class="filter-group">
      <label for="searchFilter">Поиск:</label>
      <input type="text" id="searchFilter" placeholder="Код или название..." onkeyup="debounce(loadPromos, 500)()">
    </div>
  </div>

  <div class="table-container">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Код</th>
          <th>Название</th>
          <th>Тип</th>
          <th>Значение</th>
          <th>Мин. сумма</th>
          <th>Использовано</th>
          <th>Статус</th>
          <th>Срок действия</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody id="promosTableBody">
        <tr>
          <td colspan="9">Загрузка промокодов...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <div id="pagination"></div>
</div>

<script>
  let currentPage = 1;

  // Простая функция загрузки промокодов
  function loadPromos(page = 1) {
    currentPage = page;

    console.log('Загружаем промокоды, страница:', page);

    fetch('api/test-promos.php')
      .then(response => {
        console.log('Response status:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('Received data:', data);

        if (data && data.success) {
          renderPromosTable(data.promos || []);
          renderPagination(data.pagination || {});
        } else {
          showError('Ошибка загрузки промокодов: ' + (data.error || 'Неизвестная ошибка'));
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
        showError('Ошибка загрузки промокодов: ' + error.message);
      });
  }

  // Отрисовка таблицы
  function renderPromosTable(promos) {
    const tbody = document.getElementById('promosTableBody');

    if (!promos || promos.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9">Промокоды не найдены</td></tr>';
      return;
    }

    tbody.innerHTML = promos.map(promo => `
        <tr>
            <td><strong>${promo.code}</strong></td>
            <td>${promo.name}</td>
            <td>${promo.type === 'percentage' ? 'Процентный' : 'Фиксированный'}</td>
            <td>${promo.type === 'percentage' ? promo.value + '%' : promo.value + ' ₽'}</td>
            <td>${promo.min_amount} ₽</td>
            <td>${promo.used_count}/${promo.max_uses || '∞'}</td>
            <td>${promo.is_active ? 'Активен' : 'Неактивен'}</td>
            <td>${promo.valid_until ? new Date(promo.valid_until).toLocaleDateString() : 'Без ограничений'}</td>
            <td>
                <button class="btn btn-sm btn-primary">Редактировать</button>
                <button class="btn btn-sm btn-danger">Удалить</button>
            </td>
        </tr>
    `).join('');
  }

  // Отрисовка пагинации
  function renderPagination(pagination) {
    const paginationDiv = document.getElementById('pagination');

    if (!pagination || pagination.pages <= 1) {
      paginationDiv.innerHTML = '';
      return;
    }

    let html = '<div class="pagination">';

    if (pagination.page > 1) {
      html += `<button class="btn btn-sm" onclick="loadPromos(${pagination.page - 1})">← Предыдущая</button>`;
    }

    html += `<span>Страница ${pagination.page} из ${pagination.pages}</span>`;

    if (pagination.page < pagination.pages) {
      html += `<button class="btn btn-sm" onclick="loadPromos(${pagination.page + 1})">Следующая →</button>`;
    }

    html += '</div>';
    paginationDiv.innerHTML = html;
  }

  // Функции уведомлений
  function showError(message) {
    const notification = document.createElement('div');
    notification.className = 'notification notification-error';
    notification.innerHTML = `
        <span class="notification-icon">❌</span>
        <span class="notification-message">${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove();
      }
    }, 5000);
  }

  function showSuccess(message) {
    const notification = document.createElement('div');
    notification.className = 'notification notification-success';
    notification.innerHTML = `
        <span class="notification-icon">✅</span>
        <span class="notification-message">${message}</span>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
      if (notification.parentElement) {
        notification.remove();
      }
    }, 3000);
  }

  // Debounce функция
  function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  // Загрузка при старте
  document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM загружен, начинаем загрузку промокодов');
    loadPromos();
  });
</script>

<style>
  .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 300px;
  }

  .notification-error {
    background-color: #dc3545;
  }

  .notification-success {
    background-color: #28a745;
  }

  .notification-close {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    margin-left: auto;
  }

  .pagination {
    margin-top: 20px;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
  }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
