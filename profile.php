<?php
require_once 'config.php';

$username = isset($_GET['user']) ? trim($_GET['user']) : '';

if (empty($username)) {
    header('Location: index.html');
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $userNotFound = true;
} else {
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $stmt = $conn->prepare("SELECT * FROM profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT * FROM links WHERE user_id = ? ORDER BY position ASC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $links = [];
    while ($row = $result->fetch_assoc()) {
        $links[] = $row;
    }

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
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile['display_name'] ?? 'Профіль'); ?> (@<?php echo htmlspecialchars($username); ?>) - LinkHub</title>
    <link rel="stylesheet" href="styles/profile.css">
</head>
<body style="background: linear-gradient(135deg, <?php echo htmlspecialchars($profile['bg_color1']); ?> 0%, <?php echo htmlspecialchars($profile['bg_color2']); ?> 100%);">
    <div class="particles" id="particles"></div>

    <div class="container" id="profileContainer">
        <?php if (isset($userNotFound) && $userNotFound): ?>
            <div class="error-container">
                <div class="error-icon">😔</div>
                <h2 class="error-title">Профіль не знайдено</h2>
                <p class="error-text">Користувач @<?php echo htmlspecialchars($username); ?> не існує або профіль ще не налаштовано</p>
                <a href="index.html" class="btn-home">Повернутися на головну</a>
            </div>
        <?php else: ?>
            <div class="profile-section">
                <div class="avatar" style="border-color: <?php echo htmlspecialchars($profile['bg_color1']); ?>; background: linear-gradient(135deg, <?php echo htmlspecialchars($profile['bg_color1']); ?> 0%, <?php echo htmlspecialchars($profile['bg_color2']); ?> 100%);">
                    <?php echo strtoupper(substr($profile['display_name'], 0, 1)); ?>
                </div>
                <h1 class="username"><?php echo htmlspecialchars($profile['display_name']); ?></h1>
                <?php if (!empty($profile['bio'])): ?>
                    <p class="bio"><?php echo nl2br(htmlspecialchars($profile['bio'])); ?></p>
                <?php endif; ?>
                <span class="badge" style="background: linear-gradient(135deg, <?php echo htmlspecialchars($profile['bg_color1']); ?> 0%, <?php echo htmlspecialchars($profile['bg_color2']); ?> 100%);">
                    @<?php echo htmlspecialchars($username); ?>
                </span>
            </div>

            <?php if (count($links) > 0): ?>
                <div class="links-section">
                    <?php foreach ($links as $link): ?>
                        <a href="<?php echo htmlspecialchars($link['url']); ?>" 
                           class="link-button" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           style="background: <?php echo htmlspecialchars($profile['button_color']); ?>; color: <?php echo htmlspecialchars($profile['button_text_color']); ?>; border-color: <?php echo htmlspecialchars($profile['button_text_color']); ?>33;"
                           onclick="trackClick(<?php echo $link['id']; ?>)">
                            <?php echo htmlspecialchars($link['title']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                    <p>Поки що тут немає посилань</p>
                </div>
            <?php endif; ?>

            <div class="footer">
                <p class="footer-text">
                    Створено на <a href="index.html" class="footer-link">LinkHub</a> ❤️
                </p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 30;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        createParticles();
        function trackClick(linkId) {
            fetch('track_click.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'link_id=' + linkId
            });
        }
        document.querySelectorAll('.link-button').forEach(button => {
            button.addEventListener('click', function() {
                this.classList.add('clicked');
                setTimeout(() => {
                    this.classList.remove('clicked');
                }, 200);
            });
        });
    </script>
</body>
</html>
