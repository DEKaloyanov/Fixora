<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$current_user_id = $_SESSION['user']['id'];
$with_id = $_GET['with'] ?? null;
$valid_chat = false;
$receiver = null;

// Извличане на списък с контакти
$contactsStmt = $conn->prepare("
    SELECT 
        u.id, u.ime, u.familiq, u.profile_image,
        (
            SELECT message FROM messages 
            WHERE 
                (sender_id = u.id AND receiver_id = :me) OR 
                (sender_id = :me AND receiver_id = u.id)
            ORDER BY created_at DESC 
            LIMIT 1
        ) AS last_message,
        (
            SELECT created_at FROM messages 
            WHERE 
                (sender_id = u.id AND receiver_id = :me) OR 
                (sender_id = :me AND receiver_id = u.id)
            ORDER BY created_at DESC 
            LIMIT 1
        ) AS last_time
    FROM users u
    JOIN connections c ON (u.id = c.user1_id OR u.id = c.user2_id)
    WHERE (c.user1_id = :me OR c.user2_id = :me) AND u.id != :me
    GROUP BY u.id
    ORDER BY last_time IS NULL, last_time DESC
");
$contactsStmt->execute(['me' => $current_user_id]);
$contacts = $contactsStmt->fetchAll(PDO::FETCH_ASSOC);

// Проверка дали има отворен чат и връзка
if ($with_id && $with_id != $current_user_id) {
    $stmt = $conn->prepare("
        SELECT * FROM connections 
        WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
    ");
    $stmt->execute([$current_user_id, $with_id, $with_id, $current_user_id]);
    $connection = $stmt->fetch();

    if ($connection) {
        $valid_chat = true;

        $getUser = $conn->prepare("SELECT ime, familiq, profile_image FROM users WHERE id = ?");
        $getUser->execute([$with_id]);
        $receiver = $getUser->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title><?= $valid_chat ? 'Чат с ' . htmlspecialchars($receiver['ime']) : 'Чатове' ?></title>
    <link rel="stylesheet" href="../css/chat.css?v=<?= time() ?>">
</head>
<body>
<div class="chat-container">
    <div class="chat-main">
        <div class="chat-wrapper">
            <div class="contact-list">
                <h3>Контакти</h3>
                <ul>
                    <?php foreach ($contacts as $contact): ?>
                        <li class="<?= $contact['id'] == $with_id ? 'active' : '' ?>">
                            <a href="chat.php?with=<?= $contact['id'] ?>">
                                <img src="<?= !empty($contact['profile_image']) ? '../uploads/' . htmlspecialchars($contact['profile_image']) : '../img/default-user.png' ?>" alt="avatar">
                                <div>
                                    <div style="font-weight: bold;"><?= htmlspecialchars($contact['ime'] . ' ' . $contact['familiq']) ?></div>
                                    <div style="font-size: 12px; color: #555;">
                                        <?= htmlspecialchars(mb_strimwidth($contact['last_message'] ?? 'Няма съобщения', 0, 35, '...')) ?>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="chat-container">
                <?php if ($valid_chat): ?>
                    <div class="chat-header">
                        <img src="<?= !empty($receiver['profile_image']) ? '../uploads/' . htmlspecialchars($receiver['profile_image']) : '../img/default-user.png' ?>" class="avatar">
                        <span><?= htmlspecialchars($receiver['ime'] . ' ' . $receiver['familiq']) ?></span>
                    </div>

                    <div class="chat-messages" id="chat-messages"></div>

                    <form id="chat-form">
                        <input type="hidden" name="receiver_id" value="<?= $with_id ?>">
                        <input type="text" name="message" placeholder="Напиши съобщение..." autocomplete="off" required>
                        <button type="submit">📨</button>
                    </form>
                <?php else: ?>
                    <div style="padding: 30px;">Изберете контакт отляво, за да започнете чат.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    let currentUserId = <?= json_encode($_SESSION['user']['id']) ?>;
</script>
<script src="../js/chat.js?v=<?= time() ?>"></script>
</body>
</html>
