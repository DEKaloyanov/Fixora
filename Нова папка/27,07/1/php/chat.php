<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$current_user_id = $_SESSION['user']['id'];
$with_id = $_GET['with'] ?? null;

if (!$with_id || $with_id == $current_user_id) {
    echo "Невалиден чат.";
    exit;
}

// Проверка дали потребителите са свързани
$stmt = $conn->prepare("
    SELECT * FROM connections 
    WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
");
$stmt->execute([$current_user_id, $with_id, $with_id, $current_user_id]);
$connection = $stmt->fetch();

if (!$connection) {
    echo "Нямате връзка с този потребител.";
    exit;
}

// Вземаме името на събеседника
$getUser = $conn->prepare("SELECT first_name, last_name, profile_image FROM users WHERE id = ?");
$getUser->execute([$with_id]);
$receiver = $getUser->fetch();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Чат с <?= htmlspecialchars($receiver['first_name']) ?></title>
    <link rel="stylesheet" href="../css/chat.css?v=<?= time() ?>">
</head>
<body>
<div class="chat-container">
    <div class="chat-header">
        <img src="<?= !empty($receiver['profile_image']) ? '../uploads/' . htmlspecialchars($receiver['profile_image']) : '../img/default-user.png' ?>" class="avatar">
        <span><?= htmlspecialchars($receiver['first_name'] . ' ' . $receiver['last_name']) ?></span>
    </div>

    <div class="chat-messages" id="chat-messages"></div>

    <form id="chat-form">
        <input type="hidden" name="receiver_id" value="<?= $with_id ?>">
        <input type="text" name="message" placeholder="Напиши съобщение..." autocomplete="off" required>
        <button type="submit">📨</button>
    </form>
</div>
<script>
    let currentUserId = <?= json_encode($_SESSION['user']['id']) ?>;
</script>
<script src="../js/chat.js?v=<?= time() ?>"></script>
</body>
</html>
