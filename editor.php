<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY position ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$links = [];
while ($row = $result->fetch_assoc()) {
    $links[] = $row;
}
$stmt->close();
$conn->close();

if (!$profile) {
    $profile = [
        'display_name' => $username,
        'bio' => '',
        'bg_color1' => '#667eea',
        'bg_color2' => '#764ba2',
        'button_color' => '#ffffff',
        'button_text_color' => '#1a1a2e'
    ];
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактор профілю - LinkHub</title>
    <link rel="stylesheet" href="styles/editor.css">
</head>
<body>
    <header class="header">
        <a href="index.html" class="logo">
            🔗 LinkHub
        </a>
        <div class="header-actions">
            <span style="color: #6b7280; font-weight: 600;">@<?php echo htmlspecialchars($username); ?></span>
            <a href="profile.php?user=<?php echo urlencode($username); ?>" class="btn-preview" target="_blank">Переглянути профіль</a>
            <a href="logout.php" class="btn-logout">Вийти</a>
        </div>
    </header>

    <div class="container">
        <div class="editor-panel">
            <h1 style="font-size: 28px; margin-bottom: 30px; color: #1a1a2e;">Редагування профілю</h1>

            <form id="profileForm" method="POST" action="save_profile.php">
                <div class="section">
                    <h2 class="section-title">👤 Профіль</h2>
                    
                    <div class="form-group">
                        <label for="display_name">Відображуване ім'я</label>
                        <input type="text" id="display_name" name="display_name" placeholder="Твоє ім'я" value="<?php echo htmlspecialchars($profile['display_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="bio">Біографія</label>
                        <textarea id="bio" name="bio" placeholder="Розкажи про себе..."><?php echo htmlspecialchars($profile['bio']); ?></textarea>
                        <div class="help-text">Використовуй емодзі щоб зробити біо цікавішою 🎉</div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">🎨 Оформлення</h2>
                    
                    <div class="color-picker-group">
                        <div class="form-group">
                            <label>Колір фону</label>
                            <div class="color-input-wrapper">
                                <input type="color" id="bg_color1" name="bg_color1" value="<?php echo htmlspecialchars($profile['bg_color1']); ?>">
                                <span class="color-value"><?php echo htmlspecialchars($profile['bg_color1']); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Другий колір фону</label>
                            <div class="color-input-wrapper">
                                <input type="color" id="bg_color2" name="bg_color2" value="<?php echo htmlspecialchars($profile['bg_color2']); ?>">
                                <span class="color-value"><?php echo htmlspecialchars($profile['bg_color2']); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Колір кнопок</label>
                            <div class="color-input-wrapper">
                                <input type="color" id="button_color" name="button_color" value="<?php echo htmlspecialchars($profile['button_color']); ?>">
                                <span class="color-value"><?php echo htmlspecialchars($profile['button_color']); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Колір тексту кнопок</label>
                            <div class="color-input-wrapper">
                                <input type="color" id="button_text_color" name="button_text_color" value="<?php echo htmlspecialchars($profile['button_text_color']); ?>">
                                <span class="color-value"><?php echo htmlspecialchars($profile['button_text_color']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">🔗 Посилання</h2>
                    
                    <div class="links-list" id="linksList">
                        <?php foreach ($links as $index => $link): ?>
                        <div class="link-item" data-link-id="<?php echo $link['id']; ?>">
                            <span class="drag-handle">☰</span>
                            <input type="text" name="link_title[]" placeholder="Назва посилання" value="<?php echo htmlspecialchars($link['title']); ?>">
                            <input type="text" name="link_url[]" placeholder="https://..." value="<?php echo htmlspecialchars($link['url']); ?>">
                            <input type="hidden" name="link_id[]" value="<?php echo $link['id']; ?>">
                            <button type="button" class="btn-delete" onclick="removeLink(this)">🗑</button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" class="btn-add" onclick="addLink()">+ Додати посилання</button>
                </div>
                <button type="submit" class="btn-save">💾 Зберегти зміни</button>
            </form>
        </div>

        <div class="preview-panel">
            <div class="preview-header">
                <span class="preview-title">📱 Попередній перегляд</span>
                <span class="preview-url">linkhub.com/<?php echo htmlspecialchars($username); ?></span>
            </div>
            <div class="preview-container">
                <div class="preview-phone" id="previewPhone">
                    <div class="preview-avatar" id="previewAvatar"><?php echo strtoupper(substr($profile['display_name'], 0, 1)); ?></div>
                    <div class="preview-username" id="previewUsername"><?php echo htmlspecialchars($profile['display_name']); ?></div>
                    <div class="preview-bio" id="previewBio"><?php echo $profile['bio'] ? htmlspecialchars($profile['bio']) : 'Твоя біографія з\'явиться тут'; ?></div>
                    <div class="preview-links" id="previewLinks">
                        <?php foreach ($links as $link): ?>
                        <a href="#" class="preview-link"><?php echo htmlspecialchars($link['title']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="save-indicator" id="saveIndicator">
        ✓ Зміни збережено
    </div>

    <script src="scripts/editor.js"></script>
</body>
</html>
