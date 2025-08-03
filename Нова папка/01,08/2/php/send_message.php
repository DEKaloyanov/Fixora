<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit;
}

$sender_id = $_SESSION['user']['id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message'] ?? '');
$job_id = $_POST['job_id'] ?? null;

if (!$receiver_id || $receiver_id == $sender_id || $message === '' || !$job_id) {
    http_response_code(400);
    exit;
}

// Проверка за връзка
$stmt = $conn->prepare("
    SELECT * FROM connections
    WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
");
$stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
if (!$stmt->fetch()) {
    http_response_code(403);
    exit;
}

// Записване на съобщението
$insert = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, job_id, message, created_at)
    VALUES (?, ?, ?, ?, NOW())
");
$insert->execute([$sender_id, $receiver_id, $job_id, $message]);


echo "OK";

$notify = $conn->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
$notify->execute([$receiver_id, "Получихте ново съобщение.", "chat.php?with=$sender_id&job=$job_id"]);

