<?php
session_start();
require_once 'db.php';

// Проверка дали потребителят е логнат
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Пренасочва ако не е логнат
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Връзки за чат (списък с контакти)
$contacts = [];
$stmt = $conn->prepare("
    SELECT u.id, u.ime, u.familiq, u.profile_image, j.profession, j.id as job_id
    FROM connections c
    JOIN users u ON (c.user1_id = u.id OR c.user2_id = u.id) AND u.id != ?
    JOIN jobs j ON c.job_id = j.id
    WHERE (c.user1_id = ? OR c.user2_id = ?)
");
$stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $contacts[] = $row;
}

// Проверка за активен чат
$active_chat = null;
$active_job = null;
$other_user = null;

if (isset($_GET['with']) && isset($_GET['job'])) {
    $other_user_id = intval($_GET['with']);
    $job_id = intval($_GET['job']);

    // Проверка дали чатът е разрешен (има връзка в connections)
    $stmt = $conn->prepare("
        SELECT * FROM connections 
        WHERE job_id = ? 
        AND ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
    ");
    $stmt->bind_param("iiiii", $job_id, $current_user_id, $other_user_id, $other_user_id, $current_user_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Валиден чат - взимаме информация за обявата и събеседника
        $stmt = $conn->prepare("
            SELECT j.*, u.ime, u.familiq, u.profile_image 
            FROM jobs j
            JOIN users u ON j.user_id = u.id
            WHERE j.id = ?
        ");
        $stmt->bind_param("i", $job_id);
        $stmt->execute();
        $active_job = $stmt->get_result()->fetch_assoc();

        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $other_user_id);
        $stmt->execute();
        $other_user = $stmt->get_result()->fetch_assoc();

        $active_chat = true;
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fixora - Чат</title>
    <link rel="stylesheet" href="css/chat.css">
</head>
<body>
    <div class="chat-container">
        <!-- Списък с контакти -->
        <div class="contacts-list">
            <h2>Съобщения</h2>
            <?php if (empty($contacts)): ?>
                <p>Нямате активни чатове</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($contacts as $contact): ?>
                        <li class="<?= (isset($_GET['with']) && $_GET['with'] == $contact['id'] && isset($_GET['job']) && $_GET['job'] == $contact['job_id']) ? 'active' : '' ?>">
                            <a href="chat.php?with=<?= $contact['id'] ?>&job=<?= $contact['job_id'] ?>">
                                <img src="<?= !empty($contact['profile_image']) ? 'uploads/profiles/'.$contact['profile_image'] : 'images/default-profile.png' ?>" alt="Профилна снимка">
                                <div>
                                    <h4><?= htmlspecialchars($contact['ime'] . ' ' . htmlspecialchars($contact['familiq'])) ?></h4>
                                    <p><?= htmlspecialchars($contact['profession']) ?></p>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Чат област -->
        <div class="chat-area">
            <?php if ($active_chat): ?>
                <div class="chat-header">
                    <img src="<?= !empty($other_user['profile_image']) ? 'uploads/profiles/'.$other_user['profile_image'] : 'images/default-profile.png' ?>" alt="Профилна снимка">
                    <h3><?= htmlspecialchars($other_user['ime'] . ' ' . htmlspecialchars($other_user['familiq'])) ?></h3>
                    <p>Обява: <?= htmlspecialchars($active_job['profession']) ?> в <?= htmlspecialchars($active_job['city']) ?></p>
                </div>

                <div class="messages-container" id="messages-container">
                    <!-- Съобщенията ще се зареждат тук чрез AJAX -->
                </div>

                <form id="message-form" class="message-form">
                    <input type="hidden" name="receiver_id" value="<?= $other_user['id'] ?>">
                    <input type="hidden" name="job_id" value="<?= $active_job['id'] ?>">
                    <textarea name="message" placeholder="Напишете съобщение..." required></textarea>
                    <button type="submit">Изпрати</button>
                </form>
            <?php else: ?>
                <div class="no-chat-selected">
                    <p>Изберете чат от списъка или нямате разрешение за този чат</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/chat.js"></script>
</body>
</html>