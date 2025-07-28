<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Не сте влезли в профила си.";
    exit;
}

$sender_id = $_SESSION['user']['id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$job_id = $_POST['job_id'] ?? null;

if (!$receiver_id || !$job_id) {
    http_response_code(400);
    echo "Липсва информация за заявката.";
    exit;
}

// Проверка дали вече има изпратена заявка
$stmt = $conn->prepare("SELECT * FROM connection_requests WHERE sender_id = ? AND receiver_id = ? AND job_id = ?");
$stmt->execute([$sender_id, $receiver_id, $job_id]);
if ($stmt->fetch()) {
    echo "Вече сте изпратили заявка.";
    exit;
}

// Записване на заявката
$insert = $conn->prepare("INSERT INTO connection_requests (sender_id, receiver_id, job_id, status) VALUES (?, ?, ?, 'pending')");
$insert->execute([$sender_id, $receiver_id, $job_id]);

header("Location: job_details.php?id=" . $job_id);
exit;
