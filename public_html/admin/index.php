<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/config.php';

// Check if user is logged in, if not redirect to login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$pageTitle = 'Панель управления';
require_once __DIR__ . '/includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Добро пожаловать в панель управления</h1>
        <p>Управление контентом сайта психотерапии</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <h3 id="reviews-count">-</h3>
                <p>Всего отзывов</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <h3 id="articles-count">-</h3>
                <p>Опубликованные статьи</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🛍️</div>
            <div class="stat-content">
                <h3 id="products-count">-</h3>
                <p>Товары</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3 id="orders-count">-</h3>
                <p>Заказы</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2>Быстрые действия</h2>
        <div class="actions-grid">
            <a href="reviews.php" class="action-card">
                <div class="action-icon">💬</div>
                <h3>Управление отзывами</h3>
                <p>Просмотр, одобрение или отклонение отзывов клиентов</p>
            </a>

            <a href="articles.php" class="action-card">
                <div class="action-icon">✍️</div>
                <h3>Управление статьями</h3>
                <p>Создание и редактирование статей по психологии</p>
            </a>

            <a href="products.php" class="action-card">
                <div class="action-icon">🎯</div>
                <h3>Управление товарами</h3>
                <p>Добавление или изменение медитативных продуктов</p>
            </a>

            <a href="orders.php" class="action-card">
                <div class="action-icon">📋</div>
                <h3>Просмотр заказов</h3>
                <p>Отслеживание заказов клиентов и платежей</p>
            </a>

            <a href="promos.php" class="action-card">
                <div class="action-icon">🏷️</div>
                <h3>Управление промокодами</h3>
                <p>Создание и управление промокодами для скидок</p>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="recent-activity">
        <h2>Последняя активность</h2>
        <div class="activity-list" id="recent-activity">
            <div class="activity-item">
                <div class="activity-icon">⏳</div>
                <div class="activity-content">
                    <p>Загрузка последней активности...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Load dashboard statistics
    document.addEventListener('DOMContentLoaded', function () {
        loadDashboardStats();
        loadRecentActivity();
    });

    async function loadDashboardStats() {
        try {
            const response = await fetch('api/dashboard-stats.php');
            const data = await response.json();

            if (data.success) {
                document.getElementById('reviews-count').textContent = data.stats.reviews || 0;
                document.getElementById('articles-count').textContent = data.stats.articles || 0;
                document.getElementById('products-count').textContent = data.stats.products || 0;
                document.getElementById('orders-count').textContent = data.stats.orders || 0;
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
        }
    }

    async function loadRecentActivity() {
        try {
            const response = await fetch('api/recent-activity.php');
            const data = await response.json();

            if (data.success && data.activities.length > 0) {
                const activityList = document.getElementById('recent-activity');
                activityList.innerHTML = '';

                data.activities.forEach(activity => {
                    const activityItem = document.createElement('div');
                    activityItem.className = 'activity-item';
                    activityItem.innerHTML = `
                    <div class="activity-icon">${activity.icon}</div>
                    <div class="activity-content">
                        <p>${activity.description}</p>
                        <span class="activity-time">${activity.time}</span>
                    </div>
                `;
                    activityList.appendChild(activityItem);
                });
            }
        } catch (error) {
            console.error('Error loading recent activity:', error);
            document.getElementById('recent-activity').innerHTML = `
            <div class="activity-item">
                <div class="activity-icon">ℹ️</div>
                <div class="activity-content">
                    <p>No recent activity to display</p>
                </div>
            </div>
        `;
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>