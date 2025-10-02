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

<div class="content-container">
  <div class="content-header">
    <h1>Управление промокодами</h1>
    <button class="btn btn-primary" onclick="openPromoModal()">
      <i class="fas fa-plus"></i> Добавить промокод
    </button>
  </div>

  <!-- Filters -->
  <div class="filters-section">
    <div class="filter-group">
      <label for="statusFilter">Статус:</label>
      <select id="statusFilter" onchange="loadPromos()">
        <option value="">Все</option>
        <option value="active">Активные</option>
        <option value="inactive">Неактивные</option>
        <option value="expired">Истекшие</option>
        <option value="not_started">Не начавшиеся</option>
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

  <!-- Promos Table -->
  <div class="table-container">
    <table class="data-table" id="promosTable">
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
          <td colspan="9" class="loading">Загрузка промокодов...</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <div class="pagination" id="pagination"></div>
</div>

<!-- Promo Modal -->
<div id="promoModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitle">Добавить промокод</h2>
      <span class="close" onclick="closePromoModal()">&times;</span>
    </div>

    <form id="promoForm" onsubmit="savePromo(event)">
      <input type="hidden" id="promoId" name="id">

      <div class="form-group">
        <label for="promoCode">Код промокода *</label>
        <input type="text" id="promoCode" name="code" required maxlength="50" placeholder="Например: WELCOME10"
          onkeyup="this.value = this.value.toUpperCase()">
      </div>

      <div class="form-group">
        <label for="promoName">Название *</label>
        <input type="text" id="promoName" name="name" required maxlength="255" placeholder="Краткое название промокода">
      </div>

      <div class="form-group">
        <label for="promoDescription">Описание</label>
        <textarea id="promoDescription" name="description" rows="3"
          placeholder="Описание промокода для клиентов"></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="promoType">Тип скидки *</label>
          <select id="promoType" name="type" required onchange="updateValueLabel()">
            <option value="percentage">Процентная</option>
            <option value="fixed">Фиксированная</option>
          </select>
        </div>

        <div class="form-group">
          <label for="promoValue" id="valueLabel">Значение скидки (%) *</label>
          <input type="number" id="promoValue" name="value" required min="0" step="0.01" placeholder="10">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="promoMinAmount">Минимальная сумма (₽)</label>
          <input type="number" id="promoMinAmount" name="min_amount" min="0" step="0.01" placeholder="0">
        </div>

        <div class="form-group">
          <label for="promoMaxUses">Максимум использований</label>
          <input type="number" id="promoMaxUses" name="max_uses" min="1" placeholder="Без ограничений">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="promoValidFrom">Действует с</label>
          <input type="datetime-local" id="promoValidFrom" name="valid_from">
        </div>

        <div class="form-group">
          <label for="promoValidUntil">Действует до</label>
          <input type="datetime-local" id="promoValidUntil" name="valid_until">
        </div>
      </div>

      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" id="promoIsActive" name="is_active" checked>
          <span class="checkmark"></span>
          Активный
        </label>
      </div>

      <div class="form-actions">
        <button type="button" class="btn btn-secondary" onclick="closePromoModal()">Отмена</button>
        <button type="submit" class="btn btn-primary">Сохранить</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Подтверждение удаления</h2>
      <span class="close" onclick="closeDeleteModal()">&times;</span>
    </div>

    <div class="modal-body">
      <p>Вы уверены, что хотите удалить промокод "<span id="deletePromoName"></span>"?</p>
      <p class="warning">Это действие нельзя отменить!</p>
    </div>

    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeDeleteModal()">Отмена</button>
      <button class="btn btn-danger" onclick="confirmDelete()">Удалить</button>
    </div>
  </div>
</div>

<script>
  let currentPage = 1;
  let currentPromoId = null;

  // Загрузка промокодов
  function loadPromos(page = 1) {
    currentPage = page;

    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const searchFilter = document.getElementById('searchFilter').value;

    const params = new URLSearchParams({
      page: page,
      limit: 20
    });

    if (statusFilter) params.append('status', statusFilter);
    if (typeFilter) params.append('type', typeFilter);
    if (searchFilter) params.append('search', searchFilter);

    fetch(`api/get-promos.php?${params}`)
      .then(response => response.json())
      .then(data => {
        if (data && data.success) {
          renderPromosTable(data.promos || []);
          renderPagination(data.pagination || {});
        } else {
          showError('Ошибка загрузки промокодов');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showError('Ошибка загрузки промокодов');
      });
  }

  // Отрисовка таблицы промокодов
  function renderPromosTable(promos) {
    const tbody = document.getElementById('promosTableBody');

    if (promos.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" class="no-data">Промокоды не найдены</td></tr>';
      return;
    }

    tbody.innerHTML = promos.map(promo => `
        <tr data-id="${promo.id}">
            <td><strong>${promo.code}</strong></td>
            <td>${promo.name}</td>
            <td>
                <span class="badge badge-${promo.type === 'percentage' ? 'primary' : 'secondary'}">
                    ${promo.type === 'percentage' ? 'Процент' : 'Фиксированная'}
                </span>
            </td>
            <td>
                ${promo.type === 'percentage' ? promo.value + '%' : promo.value + ' ₽'}
            </td>
            <td>${promo.min_amount > 0 ? promo.min_amount + ' ₽' : 'Любая'}</td>
            <td>
                ${promo.used_count}${promo.max_uses ? '/' + promo.max_uses : ''}
                ${promo.usage_stats ? `<br><small>Скидка: ${promo.usage_stats.total_discount} ₽</small>` : ''}
            </td>
            <td>
                ${getStatusBadge(promo)}
            </td>
            <td>
                ${formatDateRange(promo)}
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="editPromo(${promo.id})" title="Редактировать">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deletePromo(${promo.id}, '${promo.name}')" title="Удалить">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
  }

  // Получение статуса промокода
  function getStatusBadge(promo) {
    if (!promo.is_active) {
      return '<span class="promo-status inactive">Неактивен</span>';
    }

    if (promo.is_expired) {
      return '<span class="promo-status expired">Истек</span>';
    }

    if (promo.is_not_started) {
      return '<span class="promo-status not-started">Не начался</span>';
    }

    if (promo.is_limit_reached) {
      return '<span class="promo-status limit-reached">Лимит исчерпан</span>';
    }

    return '<span class="promo-status active">Активен</span>';
  }

  // Форматирование диапазона дат
  function formatDateRange(promo) {
    let result = '';

    if (promo.valid_from) {
      result += `С: ${promo.valid_from_formatted}<br>`;
    }

    if (promo.valid_until) {
      result += `До: ${promo.valid_until_formatted}`;
    }

    if (!result) {
      result = 'Без ограничений';
    }

    return result;
  }

  // Отрисовка пагинации
  function renderPagination(pagination) {
    const paginationEl = document.getElementById('pagination');

    if (pagination.pages <= 1) {
      paginationEl.innerHTML = '';
      return;
    }

    let html = '<div class="pagination-controls">';

    // Кнопка "Предыдущая"
    if (pagination.page > 1) {
      html += `<button class="btn btn-sm" onclick="loadPromos(${pagination.page - 1})">← Предыдущая</button>`;
    }

    // Номера страниц
    for (let i = 1; i <= pagination.pages; i++) {
      if (i === pagination.page) {
        html += `<span class="current-page">${i}</span>`;
      } else if (i === 1 || i === pagination.pages || (i >= pagination.page - 2 && i <= pagination.page + 2)) {
        html += `<button class="btn btn-sm" onclick="loadPromos(${i})">${i}</button>`;
      } else if (i === pagination.page - 3 || i === pagination.page + 3) {
        html += '<span>...</span>';
      }
    }

    // Кнопка "Следующая"
    if (pagination.page < pagination.pages) {
      html += `<button class="btn btn-sm" onclick="loadPromos(${pagination.page + 1})">Следующая →</button>`;
    }

    html += '</div>';
    paginationEl.innerHTML = html;
  }

  // Открытие модального окна для добавления/редактирования
  function openPromoModal(promoId = null) {
    currentPromoId = promoId;
    const modal = document.getElementById('promoModal');
    const modalTitle = document.getElementById('modalTitle');
    const form = document.getElementById('promoForm');

    if (promoId) {
      modalTitle.textContent = 'Редактировать промокод';
      // Загружаем данные промокода для редактирования
      loadPromoData(promoId);
    } else {
      modalTitle.textContent = 'Добавить промокод';
      form.reset();
      document.getElementById('promoId').value = '';
      updateValueLabel();
    }

    modal.style.display = 'block';
  }

  // Закрытие модального окна
  function closePromoModal() {
    document.getElementById('promoModal').style.display = 'none';
    currentPromoId = null;
  }

  // Загрузка данных промокода для редактирования
  function loadPromoData(promoId) {
    fetch(`api/get-promo.php?id=${promoId}`)
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const promo = data.promo;

          // Заполняем форму данными
          document.getElementById('promoId').value = promo.id;
          document.getElementById('promoCode').value = promo.code;
          document.getElementById('promoName').value = promo.name;
          document.getElementById('promoDescription').value = promo.description || '';
          document.getElementById('promoType').value = promo.type;
          document.getElementById('promoValue').value = promo.value;
          document.getElementById('promoMinAmount').value = promo.min_amount || '';
          document.getElementById('promoMaxUses').value = promo.max_uses || '';
          document.getElementById('promoValidFrom').value = promo.valid_from_formatted || '';
          document.getElementById('promoValidUntil').value = promo.valid_until_formatted || '';
          document.getElementById('promoIsActive').checked = promo.is_active == 1;

          // Обновляем лейбл для поля значения
          updateValueLabel();
        } else {
          showError('Ошибка загрузки данных промокода: ' + data.error);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showError('Ошибка загрузки данных промокода');
      });
  }

  // Обновление лейбла для поля значения
  function updateValueLabel() {
    const type = document.getElementById('promoType').value;
    const label = document.getElementById('valueLabel');
    const input = document.getElementById('promoValue');

    if (type === 'percentage') {
      label.textContent = 'Значение скидки (%) *';
      input.max = '100';
      input.placeholder = '10';
    } else {
      label.textContent = 'Значение скидки (₽) *';
      input.removeAttribute('max');
      input.placeholder = '500';
    }
  }

  // Сохранение промокода
  function savePromo(event) {
    event.preventDefault();

    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    // Преобразуем чекбокс в булево значение
    data.is_active = formData.has('is_active');

    // Удаляем пустые значения
    Object.keys(data).forEach(key => {
      if (data[key] === '') {
        delete data[key];
      }
    });

    fetch('api/save-promo.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data)
    })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          showSuccess(result.message);
          closePromoModal();
          loadPromos(currentPage);
        } else {
          if (result.errors) {
            showError('Ошибки валидации: ' + result.errors.join(', '));
          } else {
            showError(result.error);
          }
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showError('Ошибка сохранения промокода');
      });
  }

  // Редактирование промокода
  function editPromo(promoId) {
    openPromoModal(promoId);
  }

  // Удаление промокода
  function deletePromo(promoId, promoName) {
    document.getElementById('deletePromoName').textContent = promoName;
    document.getElementById('deleteModal').style.display = 'block';
    currentPromoId = promoId;
  }

  // Закрытие модального окна удаления
  function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    currentPromoId = null;
  }

  // Подтверждение удаления
  function confirmDelete() {
    if (!currentPromoId) return;

    fetch('api/delete-promo.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ id: currentPromoId })
    })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          showSuccess(result.message);
          closeDeleteModal();
          loadPromos(currentPage);
        } else {
          showError(result.error);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showError('Ошибка удаления промокода');
      });
  }

  // Утилиты
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

  function showSuccess(message) {
    showNotification(message, 'success');
  }

  function showError(message) {
    showNotification(message, 'error');
  }

  function showNotification(message, type = 'info') {
    const container = document.getElementById('notificationContainer');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;

    const icons = {
      success: '✅',
      error: '❌',
      warning: '⚠️',
      info: 'ℹ️'
    };

    notification.innerHTML = `
        <div class="notification-content">
            <div class="notification-icon">${icons[type]}</div>
            <div class="notification-text">${message}</div>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;

    container.appendChild(notification);

    // Показываем уведомление с анимацией
    setTimeout(() => {
      notification.classList.add('show');
    }, 100);

    // Автоматически скрываем через 5 секунд
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => {
        if (notification.parentElement) {
          notification.remove();
        }
      }, 300);
    }, 5000);
  }

  // Закрытие модальных окон при клике вне их
  window.onclick = function (event) {
    const promoModal = document.getElementById('promoModal');
    const deleteModal = document.getElementById('deleteModal');

    if (event.target === promoModal) {
      closePromoModal();
    }

    if (event.target === deleteModal) {
      closeDeleteModal();
    }
  }

  // Загрузка промокодов при загрузке страницы
  document.addEventListener('DOMContentLoaded', function () {
    loadPromos();
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>