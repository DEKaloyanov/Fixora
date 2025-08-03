<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$current_user_id = $_SESSION['user']['id'];
$with_id = $_GET['with'] ?? null;
$job_id = $_GET['job'] ?? null;
$valid_chat = false;

if ($with_id && !$job_id && $with_id != $current_user_id) {
    $fallback = $conn->prepare("
        SELECT job_id 
        FROM connection_requests
        WHERE 
            ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
            AND job_id IS NOT NULL
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $fallback->execute([$current_user_id, $with_id, $with_id, $current_user_id]);
    $job_row = $fallback->fetch();

    if ($job_row && $job_row['job_id']) {
        // Пренасочи към същата страница с добавен job_id
        header("Location: chat.php?with=$with_id&job=" . $job_row['job_id']);
        exit;
    }
}


// Извличане на контакти
$contactsStmt = $conn->prepare("
    SELECT 
        u.id, u.ime, u.familiq, u.profile_image,
        c.job_id,
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
        ) AS last_time,
        (
            SELECT COUNT(*) FROM messages 
            WHERE sender_id = u.id AND receiver_id = :me AND is_read = 0
        ) AS unread_count
    FROM users u
    JOIN connections c ON (u.id = c.user1_id OR u.id = c.user2_id)
    WHERE (c.user1_id = :me OR c.user2_id = :me) AND u.id != :me
    ORDER BY last_time IS NULL, last_time DESC
");
$contactsStmt->execute(['me' => $current_user_id]);
$contacts = $contactsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат | <?= htmlspecialchars($_SESSION['user']['ime'] ?? 'Потребител') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/chat.css?v=<?= time() ?>">
</head>
<body>
<div class="chat-app">
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="user-profile">
                <img src="<?= !empty($_SESSION['user']['profile_image']) ? '../uploads/' . htmlspecialchars($_SESSION['user']['profile_image']) : '../img/default-user.png' ?>" alt="Профил">
                <span><?= htmlspecialchars($_SESSION['user']['ime'] . ' ' . $_SESSION['user']['familiq']) ?></span>
            </div>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Търсене на контакти...">
            </div>
        </div>
        
        <div class="contacts">
            <h3>Съобщения</h3>
            <ul>
                <?php foreach ($contacts as $contact): ?>
                    <?php
                        $contactId = $contact['id'] ?? 0;
                        $isUnread = $contact['unread_count'] ?? 0;
                    ?>
                    <li class="<?= $contactId == $with_id ? 'active' : '' ?> <?= $isUnread > 0 ? 'unread' : '' ?>">

                        <a href="chat.php?with=<?= $contact['id'] ?>&job=<?= $contact['job_id'] ?>">
                            <div class="contact-avatar">
                                <img src="<?= !empty($contact['profile_image']) ? '../uploads/' . htmlspecialchars($contact['profile_image']) : '../img/default-user.png' ?>" alt="avatar">
                                <?php if ($contact['unread_count'] > 0): ?>
                                    <span class="badge"><?= $contact['unread_count'] ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="contact-info">
                                <div class="contact-name"><?= htmlspecialchars($contact['ime'] . ' ' . $contact['familiq']) ?></div>
                                <div class="last-message"><?= htmlspecialchars(mb_strimwidth($contact['last_message'] ?? 'Няма съобщения', 0, 25, '...')) ?></div>
                            </div>
                            <div class="message-time">
                                <?php if (!empty($contact['last_time'])): ?>
                                    <?= date("H:i", strtotime($contact['last_time'])) ?>
                                <?php endif; ?>
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="chat-area">
        <?php if ($valid_chat): ?>
            <div class="chat-header">
                <div class="chat-partner">
                    <img src="<?= !empty($receiver['profile_image']) ? '../uploads/' . htmlspecialchars($receiver['profile_image']) : '../img/default-user.png' ?>" class="avatar">
                    <div>
                        <h4><?= htmlspecialchars($receiver['ime'] . ' ' . $receiver['familiq']) ?></h4>
                        <small style="font-size: 14px; color: #888;">
                            Обява: <?= htmlspecialchars($job['profession']) ?> — <?= htmlspecialchars($job['city']) ?>
                        </small>

                        <span class="status">online</span>
                    </div>
                </div>
                <div class="chat-actions">
                    <button><i class="fas fa-phone"></i></button>
                    <button><i class="fas fa-video"></i></button>
                    <button><i class="fas fa-ellipsis-v"></i></button>
                </div>
            </div>

            <div class="messages" id="chat-messages">
                <!-- Messages will be loaded here -->
            </div>

            <div class="message-input">
                <form id="chat-form">
                    <input type="hidden" name="receiver_id" value="<?= $with_id ?>">
                    <input type="hidden" name="job_id" value="<?= $job_id ?>">
                    <div class="input-group">
                        <button type="button" class="emoji-btn"><i class="far fa-smile"></i></button>
                        <input type="text" name="message" placeholder="Напиши съобщение..." autocomplete="off" required>
                        <button type="button" class="attach-btn"><i class="fas fa-paperclip"></i></button>
                        <button type="submit" class="send-btn"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="no-chat-selected">
                <div class="empty-state">
                    <i class="far fa-comment-dots"></i>
                    <h3>Изберете чат</h3>
                    <p>Изберете контакт от списъка, за да започнете разговор</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    let currentUserId = <?= json_encode($_SESSION['user']['id']) ?>;
    let currentChatId = <?= json_encode($with_id) ?>;
    let currentJobId = <?= json_encode($_GET['job'] ?? null) ?>;
</script>
<script src="../js/chat.js?v=<?= time() ?>"></script>
</body>
</html>