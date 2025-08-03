<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Проверка дали потребителят е логнат
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Не сте влезли в системата']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Проверка за задължителни параметри
if (!isset($_GET['receiver_id']) || !isset($_GET['job_id'])) {
    echo json_encode(['error' => 'Липсват задължителни параметри']);
    exit;
}

$receiver_id = intval($_GET['receiver_id']);
$job_id = intval($_GET['job_id']);

// Проверка дали потребителят има право да вижда този чат
$stmt = $conn->prepare("
    SELECT id FROM connections 
    WHERE job_id = ? 
    AND ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
");
$stmt->bind_param("iiiii", $job_id, $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['error' => 'Нямате достъп до този чат']);
    exit;
}

// Взимане на съобщенията
$stmt = $conn->prepare("
    SELECT m.*, u.ime, u.familiq, u.profile_image 
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
    AND (m.job_id = ? OR m.job_id IS NULL)
    ORDER BY m.created_at ASC
");
$stmt->bind_param("iiiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id, $job_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'id' => $row['id'],
        'sender_id' => $row['sender_id'],
        'message' => htmlspecialchars($row['message']),
        'created_at' => date("H:i, d.m.Y", strtotime($row['created_at'])),
        'is_read' => $row['is_read'],
        'is_current_user' => ($row['sender_id'] == $current_user_id),
        'sender_name' => htmlspecialchars($row['ime'] . ' ' . $row['familiq']),
        'profile_image' => !empty($row['profile_image']) ? 'uploads/profiles/' . $row['profile_image'] : 'images/default-profile.png'
    ];
}

// Маркиране на съобщенията като прочетени
$stmt = $conn->prepare("
    UPDATE messages 
    SET is_read = 1 
    WHERE receiver_id = ? AND sender_id = ? AND job_id = ? AND is_read = 0
");
$stmt->bind_param("iii", $current_user_id, $receiver_id, $job_id);
$stmt->execute();

echo json_encode(['messages' => $messages]);