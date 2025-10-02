</div>
</main>

<?php
// Подключаем функции для CSRF токенов
if (!function_exists('generateCSRFToken')) {
    require_once __DIR__ . '/../../includes/functions.php';
}
?>

<!-- Admin Footer -->
<footer class="admin-footer">
    <div class="admin-footer-content">
        <div class="footer-left">
            <p>&copy; <?php echo date('Y'); ?> Панель администратора Черкас Терапия</p>
        </div>
        <div class="footer-right">
            <span class="version">v1.0.0</span>
            <span class="separator">|</span>
            <a href="../" target="_blank">Перейти на сайт</a>
        </div>
    </div>
</footer>

<!-- Loading overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Загрузка...</p>
    </div>
</div>

<!-- Confirmation modal -->
<div class="modal-overlay" id="confirmModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="confirmTitle">Подтверждение действия</h3>
            <button class="modal-close" onclick="closeConfirmModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <p id="confirmMessage">Вы уверены, что хотите выполнить это действие?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeConfirmModal()">Отмена</button>
            <button class="btn btn-danger" id="confirmAction">Подтвердить</button>
        </div>
    </div>
</div>

<!-- Toast notifications -->
<div class="toast-container" id="toastContainer"></div>

<!-- Admin JavaScript -->
<script src="assets/js/admin.js"></script>

<script>
    // Initialize admin functionality
    document.addEventListener('DOMContentLoaded', function () {
        initializeAdmin();
    });

    function initializeAdmin() {
        // Mobile menu functionality
        initializeMobileMenu();

        // User dropdown functionality
        initializeUserDropdown();

        // Auto-hide alerts
        initializeAlerts();

        // CSRF token for AJAX requests
        setupCSRFToken();

        // Session timeout warning
        setupSessionTimeout();
    }

    function initializeMobileMenu() {
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const mobileSidebarOverlay = document.getElementById('mobileSidebarOverlay');
        const mobileSidebarClose = document.getElementById('mobileSidebarClose');

        if (mobileMenuToggle) {
            mobileMenuToggle.addEventListener('click', function () {
                mobileSidebar.classList.add('active');
                mobileSidebarOverlay.classList.add('active');
                document.body.classList.add('sidebar-open');
            });
        }

        function closeMobileSidebar() {
            mobileSidebar.classList.remove('active');
            mobileSidebarOverlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }

        if (mobileSidebarClose) {
            mobileSidebarClose.addEventListener('click', closeMobileSidebar);
        }

        if (mobileSidebarOverlay) {
            mobileSidebarOverlay.addEventListener('click', closeMobileSidebar);
        }
    }

    function initializeUserDropdown() {
        const userMenu = document.querySelector('.admin-user-menu');
        const userDropdown = document.querySelector('.user-dropdown');

        if (userMenu && userDropdown) {
            userMenu.addEventListener('click', function (e) {
                e.stopPropagation();
                userDropdown.classList.toggle('active');
            });

            document.addEventListener('click', function () {
                userDropdown.classList.remove('active');
            });
        }
    }

    function initializeAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    }

    function setupCSRFToken() {
        // Add CSRF token to all AJAX requests
        const csrfToken = '<?php echo generateCSRFToken(); ?>';

        // Add to all forms
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            if (!form.querySelector('input[name="csrf_token"]')) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = 'csrf_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
            }
        });

        // Set default headers for fetch requests
        window.adminCSRFToken = csrfToken;
    }

    function setupSessionTimeout() {
        // Check session every 5 minutes
        setInterval(function () {
            fetch('api/check-session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        showToast('Session expired. Please log in again.', 'warning');
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 3000);
                    }
                })
                .catch(error => {
                    console.error('Session check failed:', error);
                });
        }, 5 * 60 * 1000); // 5 minutes
    }

    // Utility functions
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    function showConfirmModal(title, message, onConfirm) {
        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmModal').style.display = 'flex';

        const confirmButton = document.getElementById('confirmAction');
        confirmButton.onclick = function () {
            closeConfirmModal();
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
        };
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function showToast(message, type = 'info', duration = 5000) {
        const toastContainer = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icon = type === 'success' ? 'check-circle' :
            type === 'error' ? 'exclamation-circle' :
                type === 'warning' ? 'exclamation-triangle' : 'info-circle';

        toast.innerHTML = `
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;

        toastContainer.appendChild(toast);

        // Auto remove after duration
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, duration);
    }

    // Fetch wrapper with error handling and CSRF
    function adminFetch(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.adminCSRFToken
            }
        };

        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            options.body = JSON.stringify(options.body);
        }

        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };

        return fetch(url, mergedOptions)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('An error occurred. Please try again.', 'error');
                throw error;
            });
    }
</script>

<?php if (isset($additionalJS)): ?>
    <?php foreach ($additionalJS as $jsFile): ?>
        <script src="<?php echo $jsFile; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($inlineJS)): ?>
    <script><?php echo $inlineJS; ?></script>
<?php endif; ?>
</body>

</html>