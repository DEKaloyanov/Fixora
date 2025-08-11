<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    exit("Невалиден достъп.");
}

$current_id = $_SESSION['user']['id'];
$with_id = $_POST['with_id'] ?? null;
$job_id = $_POST['job_id'] ?? null;

if (!$with_id || !$job_id) {
    http_response_code(400);
    exit("Липсва параметър.");
}

// Отбелязваме съобщенията като прочетени
$stmt = $conn->prepare("
    UPDATE messages
    SET is_read = 1
    WHERE sender_id = ? AND receiver_id = ? AND job_id = ? AND is_read = 0
");
$stmt->execute([$with_id, $current_id, $job_id]);

echo "OK";
?>