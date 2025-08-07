<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

$current_user_id = $_SESSION['user']['id'];

// Проверка за валидни входни данни
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id']) && isset($_POST['owner_id'])) {
    $job_id = (int) $_POST['job_id'];
    $owner_id = (int) $_POST['owner_id'];

    // Не може да се интересуваш от собствената си обява
    if ($current_user_id === $owner_id) {
        header("Location: job_details.php?job_id=$job_id&error=self");
        exit();
    }

    // Проверка дали вече има заявка
    $check = $conn->prepare("SELECT * FROM connection_requests WHERE sender_id = ? AND receiver_id = ? AND job_id = ?");
    $check->execute([$current_user_id, $owner_id, $job_id]);

    if ($check->rowCount() > 0) {
        header("Location: job_details.php?job_id=$job_id&info=exists");
        exit();
    }

    // Проверка дали вече има връзка
    $checkConnection = $conn->prepare("SELECT * FROM connections WHERE ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)) AND job_id = ?");
    $checkConnection->execute([$current_user_id, $owner_id, $owner_id, $current_user_id, $job_id]);

    if ($checkConnection->rowCount() > 0) {
        header("Location: job_details.php?job_id=$job_id&info=connected");
        exit();
    }

    // Запис на заявката
    $stmt = $conn->prepare("INSERT INTO connection_requests (sender_id, receiver_id, job_id, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->execute([$current_user_id, $owner_id, $job_id]);

    header("Location: job_details.php?job_id=$job_id&success=request_sent");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>
