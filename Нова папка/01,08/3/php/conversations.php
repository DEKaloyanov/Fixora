<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$current_user_id = $_SESSION['user']['id'];

// Взимаме всички връзки
$stmt = $conn->prepare("
    SELECT u.id, u.ime, u.familiq, u.profile_image
    FROM connections c
    JOIN users u ON (u.id = c.user1_id OR u.id = c.user2_id)
    WHERE (c.user1_id = :id OR c.user2_id = :id) AND u.id != :id
");
$stmt->execute(['id' => $current_user_id]);
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Разговори - Fixora</title>
    <link rel="stylesheet" href="../css/chat.css?v=<?= time() ?>">
    <style>
        .conversation-list {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
        }
        .user-card {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            background: #f9f9f9;
            border-radius: 6px;
            cursor: pointer;
        }
        .user-card:hover {
            background: #eaeaea;
        }
        .user-card img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="conversation-list">
        <h2>Вашите разговори</h2>
        <?php if (empty($contacts)): ?>
            <p>Все още нямате свързани потребители.</p>
        <?php else: ?>
            <?php foreach ($contacts as $user): ?>
                <div class="user-card" onclick="location.href='chat.php?with=<?= $user['id'] ?>'">
                    <img src="<?= !empty($user['profile_image']) ? '../uploads/' . htmlspecialchars($user['profile_image']) : '../img/default-user.png' ?>">
                    <div><?= htmlspecialchars($user['ime'] . ' ' . $user['familiq']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
