<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: editor.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($usernameOrEmail) || empty($password)) {
        $error = 'Заповніть всі поля';
    } else {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                header('Location: editor.php');
                exit();
            } else {
                $error = 'Невірний пароль';
            }
        } else {
            $error = 'Користувача не знайдено';
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
    <title>Вхід - LinkHub</title>
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

            <h2>Вітаємо назад!</h2>
            <p class="subtitle">Увійдіть, щоб продовжити</p>

            <?php if ($error): ?>
                <div class="message error" style="display: block;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Ім'я користувача або Email</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        placeholder="твоє_імя або email@example.com" 
                        required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Ваш пароль" 
                        required
                    >
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Запам'ятати мене</label>
                </div>

                <button type="submit" class="btn-submit">Увійти</button>
            </form>

            <div class="form-footer">
                Немає акаунту? <a href="register.php">Зареєструватися</a>
            </div>
        </div>
    </div>
</body>
</html>
