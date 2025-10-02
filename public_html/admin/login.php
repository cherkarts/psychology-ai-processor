<?php
session_start();
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Verify CSRF token
    if (!verifyCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $result = loginUser($username, $password);

        if ($result['success']) {
            // Redirect to dashboard or intended page
            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админку - Черкас Терапия</title>

    <!-- Login specific CSS -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }

        .login-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .login-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-form {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .form-input.error {
            border-color: #f44336;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .login-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .alert-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .login-footer {
            padding: 20px 30px;
            background: #f8f9fa;
            text-align: center;
            border-top: 1px solid #e1e5e9;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .security-info {
            font-size: 12px;
            color: #666;
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #ffc107;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 0 10px;
            }

            .login-header {
                padding: 30px 20px 20px;
            }

            .login-form {
                padding: 20px;
            }
        }

        .loading {
            position: relative;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: translateY(-50%) rotate(0deg);
            }

            100% {
                transform: translateY(-50%) rotate(360deg);
            }
        }
    </style>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <meta name="robots" content="noindex, nofollow">
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="fas fa-brain"></i>
            </div>
            <h1 class="login-title">Панель администратора</h1>
            <p class="login-subtitle">Управление сайтом Черкас Терапия</p>
        </div>

        <form class="login-form" method="POST" id="loginForm">
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo sanitizeOutput($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo sanitizeOutput($success); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label" for="username">
                    <i class="fas fa-user"></i> Имя пользователя
                </label>
                <input type="text" id="username" name="username" class="form-input <?php echo $error ? 'error' : ''; ?>"
                    required autocomplete="username" value="<?php echo sanitizeOutput($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">
                    <i class="fas fa-lock"></i> Пароль
                </label>
                <input type="password" id="password" name="password"
                    class="form-input <?php echo $error ? 'error' : ''; ?>" required autocomplete="current-password">
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <button type="submit" class="login-button" id="loginButton">
                Войти
            </button>

            <div class="security-info">
                <i class="fas fa-shield-alt"></i>
                Ваша сессия защищена шифрованием и будет завершена после периода неактивности.
            </div>
        </form>

        <div class="login-footer">
            <a href="../" target="_blank">
                <i class="fas fa-external-link-alt"></i>
                Перейти на сайт
            </a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');

            loginForm.addEventListener('submit', function () {
                loginButton.classList.add('loading');
                loginButton.disabled = true;
                loginButton.textContent = 'Вход в систему...';
            });

            // Focus on username field
            document.getElementById('username').focus();

            // Clear error styling on input
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('input', function () {
                    this.classList.remove('error');
                });
            });
        });
    </script>
</body>

</html>