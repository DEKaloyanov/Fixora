<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$current_id = $_SESSION['user']['id'];
$with_id = $_GET['with'] ?? null;

if (!$with_id || $with_id == $current_id) {
    echo json_encode([]);
    exit;
}

// Проверка дали има връзка между двамата
$stmt = $conn->prepare("
    SELECT * FROM connections
    WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
");
$stmt->execute([$current_id, $with_id, $with_id, $current_id]);
if (!$stmt->fetch()) {
    echo json_encode([]);
    exit;
}

// Вземане на съобщения
$msgStmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = :me AND receiver_id = :them) 
       OR (sender_id = :them AND receiver_id = :me)
    ORDER BY created_at ASC
");
$msgStmt->execute([
    'me' => $current_id,
    'them' => $with_id
]);
$messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
