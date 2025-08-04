<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: ../index.php');
    exit;
}

$current_user_id = $_SESSION['user']['id'];
$job_id = $_POST['job_id'] ?? null;

if (!$job_id) {
    die('Невалидна обява.');
}

// Проверка за съществуващ запис
$stmt = $conn->prepare("
    SELECT * FROM project_status 
    WHERE job_id = ? AND (user1_id = ? OR user2_id = ?)
");
$stmt->execute([$job_id, $current_user_id, $current_user_id]);
$project = $stmt->fetch();

if (!$project) {
    die('Проектът не е намерен.');
}

$field_to_update = ($project['user1_id'] == $current_user_id) ? 'user1_started' : 'user2_started';

$stmt = $conn->prepare("UPDATE project_status SET $field_to_update = 1 WHERE id = ?");
$stmt->execute([$project['id']]);

header("Location: ../profil.php");
exit;
