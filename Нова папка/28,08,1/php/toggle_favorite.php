<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не сте влезли в системата.']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$job_id = $_POST['job_id'] ?? null;

if (!$job_id || !is_numeric($job_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Невалиден ID на обява.']);
    exit;
}

// Проверка дали вече е в любими
$stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND job_id = ?");
$stmt->execute([$user_id, $job_id]);
$exists = $stmt->fetch();

if ($exists) {
    // Премахване
    $delete = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND job_id = ?");
    $delete->execute([$user_id, $job_id]);
    echo json_encode(['status' => 'removed']);
} else {
    // Добавяне
    $insert = $conn->prepare("INSERT INTO favorites (user_id, job_id, created_at) VALUES (?, ?, NOW())");
    $insert->execute([$user_id, $job_id]);
    echo json_encode(['status' => 'added']);
}
?>
