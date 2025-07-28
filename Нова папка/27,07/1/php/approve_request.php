<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$request_id || !in_array($action, ['accept', 'decline'])) {
    http_response_code(400);
    echo "Невалидна заявка.";
    exit;
}

// Вземаме данните за заявката
$stmt = $conn->prepare("SELECT * FROM connection_requests WHERE id = ? AND receiver_id = ?");
$stmt->execute([$request_id, $user_id]);
$request = $stmt->fetch();

if (!$request) {
    http_response_code(404);
    echo "Заявката не е намерена.";
    exit;
}

if ($action === 'accept') {
    // Добавяме нова връзка
    $insert = $conn->prepare("INSERT INTO connections (user1_id, user2_id) VALUES (?, ?)");
    $insert->execute([$user_id, $request['sender_id']]);
    
    // Обновяваме статуса
    $update = $conn->prepare("UPDATE connection_requests SET status = 'accepted' WHERE id = ?");
    $update->execute([$request_id]);

    // Пренасочване към чат страницата (по избор)
    header("Location: chat.php?with=" . $request['sender_id']);
    exit;

} else {
    // Ако е отказана – просто обновяваме статуса
    $update = $conn->prepare("UPDATE connection_requests SET status = 'declined' WHERE id = ?");
    $update->execute([$request_id]);

    header("Location: profil.php");
    exit;
}
