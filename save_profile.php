<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $conn = getDBConnection();

    $displayName = trim($_POST['display_name']);
    $bio = trim($_POST['bio']);
    $bgColor1 = $_POST['bg_color1'];
    $bgColor2 = $_POST['bg_color2'];
    $buttonColor = $_POST['button_color'];
    $buttonTextColor = $_POST['button_text_color'];

    $stmt = $conn->prepare("SELECT id FROM profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE profiles SET display_name = ?, bio = ?, bg_color1 = ?, bg_color2 = ?, button_color = ?, button_text_color = ? WHERE user_id = ?");
        $stmt->bind_param("ssssssi", $displayName, $bio, $bgColor1, $bgColor2, $buttonColor, $buttonTextColor, $userId);
    } else {
        $stmt = $conn->prepare("INSERT INTO profiles (user_id, display_name, bio, bg_color1, bg_color2, button_color, button_text_color) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $userId, $displayName, $bio, $bgColor1, $bgColor2, $buttonColor, $buttonTextColor);
    }
    
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM links WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    if (isset($_POST['link_title']) && is_array($_POST['link_title'])) {
        $stmt = $conn->prepare("INSERT INTO links (user_id, title, url, position) VALUES (?, ?, ?, ?)");
        
        foreach ($_POST['link_title'] as $index => $title) {
            $title = trim($title);
            $url = trim($_POST['link_url'][$index]);
            
            if (!empty($title) && !empty($url)) {
                $stmt->bind_param("issi", $userId, $title, $url, $index);
                $stmt->execute();
            }
        }
        
        $stmt->close();
    }
    
    $conn->close();

    $_SESSION['save_success'] = true;
    header('Location: editor.php');
    exit();
}
?>
