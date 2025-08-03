<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// маркираме всички като прочетени
$conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Известия</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notif-item {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            background: #f9f9f9;
        }
        .notif-item a { text-decoration: none; color: #0077cc; }
        .notif-time { font-size: 12px; color: gray; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Моите известия</h2>
        <?php if (empty($notifications)): ?>
            <p>Нямате известия.</p>
        <?php else: ?>
            <?php foreach ($notifications as $n): ?>
                <div class="notif-item">
                    <div><?= htmlspecialchars($n['message']) ?></div>
                    <?php if ($n['link']): ?>
                        <a href="<?= htmlspecialchars($n['link']) ?>">Прегледай</a>
                    <?php endif; ?>
                    <div class="notif-time"><?= $n['created_at'] ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
