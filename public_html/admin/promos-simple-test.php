<?php
session_start();
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
  header('Location: login.php');
  exit();
}

$pageTitle = 'Промокоды - Простой тест';
require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-container">
  <h1>Промокоды - Простой тест</h1>

  <div id="status">Загрузка...</div>
  <div id="results"></div>

  <button onclick="testAPI()">Тест API</button>
</div>

<script>
  function testAPI() {
    document.getElementById('status').innerHTML = 'Загрузка...';
    document.getElementById('results').innerHTML = '';

    fetch('api/simple-promos.php')
      .then(response => {
        console.log('Response status:', response.status);
        return response.json();
      })
      .then(data => {
        console.log('Received data:', data);

        if (data.success) {
          document.getElementById('status').innerHTML = '✅ API работает!';
          document.getElementById('results').innerHTML = `
                    <h3>Найдено промокодов: ${data.promos.length}</h3>
                    <table border="1" style="border-collapse: collapse; width: 100%;">
                        <tr>
                            <th>ID</th>
                            <th>Код</th>
                            <th>Название</th>
                            <th>Тип</th>
                            <th>Значение</th>
                            <th>Активен</th>
                        </tr>
                        ${data.promos.map(promo => `
                            <tr>
                                <td>${promo.id}</td>
                                <td>${promo.code}</td>
                                <td>${promo.name}</td>
                                <td>${promo.type}</td>
                                <td>${promo.value}</td>
                                <td>${promo.is_active ? 'Да' : 'Нет'}</td>
                            </tr>
                        `).join('')}
                    </table>
                `;
        } else {
          document.getElementById('status').innerHTML = '❌ Ошибка API: ' + data.error;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        document.getElementById('status').innerHTML = '❌ Ошибка: ' + error.message;
      });
  }

  // Автоматический тест при загрузке
  document.addEventListener('DOMContentLoaded', function () {
    testAPI();
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
