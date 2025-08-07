<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: ../index.php');
    exit;
}

$from_user_id = $_SESSION['user']['id'];
$to_user_id = $_POST['to_user_id'] ?? null;
$job_id = $_POST['job_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = $_POST['comment'] ?? '';

if (!$to_user_id || !$job_id || !$rating || $rating < 1 || $rating > 5) {
    die('Невалидни данни.');
}

// Проверка за вече подадена оценка
$stmt = $conn->prepare("
    SELECT * FROM ratings 
    WHERE from_user_id = ? AND to_user_id = ? AND job_id = ?
");
$stmt->execute([$from_user_id, $to_user_id, $job_id]);
if ($stmt->rowCount() > 0) {
    die('Вече сте оценили този потребител.');
}

// Запис на оценката
$stmt = $conn->prepare("
    INSERT INTO ratings (from_user_id, to_user_id, job_id, rating, comment)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->execute([$from_user_id, $to_user_id, $job_id, $rating, $comment]);

// Обновяване на project_status
$stmt = $conn->prepare("
    UPDATE project_status 
    SET " . ($from_user_id < $to_user_id ? "user1_rated = 1" : "user2_rated = 1") . "
    WHERE job_id = ? AND ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
");
$stmt->execute([$job_id, $from_user_id, $to_user_id, $to_user_id, $from_user_id]);

header('Location: profil.php');
exit;
