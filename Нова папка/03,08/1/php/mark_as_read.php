<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$contactId = $data['contact_id'] ?? null;
$currentUserId = $_SESSION['user']['id'];

if ($contactId) {
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
    $stmt->execute([$contactId, $currentUserId]);
    
    header("HTTP/1.1 200 OK");
    echo json_encode(['success' => true]);
} else {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'Invalid request']);
}
?>