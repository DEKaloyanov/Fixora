<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']['id'])) {
    exit('Грешка: не сте влезли в системата.');
}

$current_user_id = $_SESSION['user']['id'];
$receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$message = trim($_POST['message'] ?? '');

if ($receiver_id === 0 || $job_id === 0 || $message === '') {
    exit('Невалидни данни.');
}

// Проверка за валидна връзка
$stmt = $conn->prepare("
    SELECT id FROM connections 
    WHERE job_id = :job_id AND (
        (user1_id = :uid AND user2_id = :rid) OR 
        (user1_id = :rid AND user2_id = :uid)
    )
");
$stmt->execute([
    'job_id' => $job_id,
    'uid' => $current_user_id,
    'rid' => $receiver_id
]);

if ($stmt->rowCount() === 0) {
    exit('Нямате право да изпратите съобщение.');
}

// Запис на съобщение
$stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, job_id, message, created_at, is_read)
    VALUES (:sid, :rid, :job_id, :msg, NOW(), 0)
");
$stmt->execute([
    'sid' => $current_user_id,
    'rid' => $receiver_id,
    'job_id' => $job_id,
    'msg' => $message
]);

echo 'ok';
