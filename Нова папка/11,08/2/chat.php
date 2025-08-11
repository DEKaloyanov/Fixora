<?php



session_start();
require_once 'php/db.php';




if (!isset($_SESSION['user']['id'])) {
    header('Location: index.php');
    exit;
}

$current_user_id = $_SESSION['user']['id'];


// Проверка дали има избран чат
$selected_user_id = isset($_GET['with']) ? (int)$_GET['with'] : null;
$selected_job_id = isset($_GET['job']) ? (int)$_GET['job'] : null;

// Извличане на всички връзки (одобрени заявки)
$stmt = $conn->prepare("
    SELECT c.*, 
           u.id AS other_user_id, 
           u.ime, 
           u.familiq, 
           u.profile_image, 
           j.profession, 
           j.id AS job_id
    FROM connections c
    INNER JOIN users u ON (u.id = CASE WHEN c.user1_id = :uid THEN c.user2_id ELSE c.user1_id END)
    INNER JOIN jobs j ON j.id = c.job_id
    WHERE c.user1_id = :uid OR c.user2_id = :uid
    ORDER BY c.id DESC
");

$stmt->execute(['uid' => $current_user_id]);
$connections = $stmt->fetchAll(PDO::FETCH_ASSOC);




// Ако няма избран чат, автоматично избираме първия наличен
if (!$selected_user_id || !$selected_job_id) {
    if (!empty($connections)) {
        $selected_user_id = $connections[0]['other_user_id'];
        $selected_job_id = $connections[0]['job_id'];
        header("Location: chat.php?with={$selected_user_id}&job={$selected_job_id}");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Чат | Fixora</title>
    <link rel="stylesheet" href="css/chat.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</head>
<body>
    <?php include 'php/navbar.php'; ?>

    <div class="chat-container">
        <div class="chat-sidebar">
            <h3>Съобщения</h3>
            <ul class="chat-contact-list">
                <?php foreach ($connections as $conn): ?>
                    <li class="chat-contact <?= ($conn['other_user_id'] == $selected_user_id && $conn['job_id'] == $selected_job_id) ? 'active' : '' ?>">
                        <a href="chat.php?with=<?= $conn['other_user_id'] ?>&job=<?= $conn['job_id'] ?>">
                            <?php
                                $avatar_path = !empty($conn['profile_image']) ? 'uploads/' . htmlspecialchars($conn['profile_image']) : 'assets/default.png';
                            ?>
                            <img src="<?= $avatar_path ?>" alt="Профил" class="contact-avatar">
                            <div class="contact-info">
                                <strong><?= htmlspecialchars($conn['profession']) ?></strong><br>
                                <small><?= htmlspecialchars($conn['ime'] . ' ' . $conn['familiq']) ?></small>
                            </div>

                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="chat-main">
            <?php if ($selected_user_id && $selected_job_id): ?>
                <div class="chat-header">
                    <h3>Разговор</h3>
                </div>
                <div class="chat-messages" id="chat-messages"></div>
                <form id="message-form">
                    <input type="hidden" name="receiver_id" value="<?= $selected_user_id ?>">
                    <input type="hidden" name="job_id" value="<?= $selected_job_id ?>">
                    <input type="text" name="message" placeholder="Въведи съобщение..." required>
                    <button type="submit">Изпрати</button>
                </form>
            <?php else: ?>
                <p class="no-chat">Няма налични чатове.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/chat.js"></script>
</body>
</html>
