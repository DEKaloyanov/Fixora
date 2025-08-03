<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']['id'])) {
    exit('Не сте влезли в системата.');
}

$current_user_id = $_SESSION['user']['id'];
$with_id = isset($_GET['with']) ? (int)$_GET['with'] : 0;
$job_id = isset($_GET['job']) ? (int)$_GET['job'] : 0;

if ($with_id === 0 || $job_id === 0) {
    exit('Невалидна заявка.');
}

// Проверка за връзка
$stmt = $conn->prepare("
    SELECT id FROM connections
    WHERE job_id = :job_id AND (
        (user1_id = :uid AND user2_id = :with_id) OR 
        (user1_id = :with_id AND user2_id = :uid)
    )
");
$stmt->execute([
    'job_id' => $job_id,
    'uid' => $current_user_id,
    'with_id' => $with_id
]);

if ($stmt->rowCount() === 0) {
    exit('Нямате достъп до този чат.');
}

// Зареждане на съобщенията
$stmt = $conn->prepare("
    SELECT m.*, u.ime, u.familiq, u.profile_image 
    FROM messages m
    INNER JOIN users u ON u.id = m.sender_id
    WHERE 
        ((m.sender_id = :uid AND m.receiver_id = :with_id) OR 
         (m.sender_id = :with_id AND m.receiver_id = :uid)) AND
        m.job_id = :job_id
    ORDER BY m.created_at ASC
");
$stmt->execute([
    'uid' => $current_user_id,
    'with_id' => $with_id,
    'job_id' => $job_id
]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($messages as $row):
    $is_sender = $row['sender_id'] == $current_user_id;
?>
    <div class="chat-message <?= $is_sender ? 'sent' : 'received' ?>">
        <div class="message-info">
            <img src="<?= htmlspecialchars($row['profile_image'] ?? 'assets/default.png') ?>" class="avatar">
            <strong><?= htmlspecialchars($row['ime'] . ' ' . $row['familiq']) ?></strong>
            <span class="timestamp"><?= date('H:i', strtotime($row['created_at'])) ?></span>
        </div>
        <div class="message-text"><?= nl2br(htmlspecialchars($row['message'])) ?></div>
    </div>
<?php endforeach; ?>
