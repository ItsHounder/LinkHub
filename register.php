<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) {
    header('Location: editor.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if (strlen($username) < 3) {
        $error = 'Ім\'я користувача має містити мінімум 3 символи';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Ім\'я користувача може містити тільки літери, цифри та підкреслення';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Невірний формат email';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль має містити мінімум 6 символів';
    } elseif ($password !== $confirmPassword) {
        $error = 'Паролі не співпадають';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Це ім\'я користувача або email вже зайняті';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashedPassword);
            
            if ($stmt->execute()) {
                $userId = $conn->insert_id;
                
                $stmt = $conn->prepare("INSERT INTO profiles (user_id, display_name) VALUES (?, ?)");
                $stmt->bind_param("is", $userId, $username);
                $stmt->execute();
                
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                
                $success = 'Реєстрація успішна! Перенаправлення...';
                header('refresh:2;url=editor.php');
            } else {
                $error = 'Помилка реєстрації. Спробуйте пізніше';
            }
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реєстрація - LinkHub</title>
    <link rel="stylesheet" href="styles/auth.css">
</head>
<body>
    <div class="auth-container">
        <a href="index.html" class="back-link">
            ← Повернутися на головну
        </a>

        <div class="auth-card">
            <div class="logo">
                <div class="logo-icon">🔗</div>
                <div class="logo-text">LinkHub</div>
            </div>

            <h2>Створи свій профіль</h2>
            <p class="subtitle">Приєднуйся до тисяч користувачів</p>

            <?php if ($error): ?>
                <div class="message error" style="display: block;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success" style="display: block;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="username">Ім'я користувача</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="твоє_імя" 
                        required
                        pattern="[a-zA-Z0-9_]+"
                        minlength="3"
                        maxlength="20"
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    >
                    <div class="username-preview">
                        Твоє посилання: <span id="urlPreview">linkhub.com/твоє_імя</span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="example@email.com" 
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Мінімум 6 символів" 
                        required
                        minlength="6"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Підтвердження паролю</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Повтори пароль" 
                        required
                    >
                </div>

                <button type="submit" class="btn-submit">Створити профіль</button>
            </form>

            <div class="form-footer">
                Вже є акаунт? <a href="login.php">Увійти</a>
            </div>
        </div>
    </div>

    <script>
        const usernameInput = document.getElementById('username');
        const urlPreview = document.getElementById('urlPreview');

        if (usernameInput && urlPreview) {
            usernameInput.addEventListener('input', function() {
                const username = this.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
                urlPreview.textContent = username ? `linkhub.com/${username}` : 'linkhub.com/твоє_імя';
            });
        }
    </script>
</body>
</html>
