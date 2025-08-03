<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Проверка дали потребителят е логнат
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не сте влезли в системата']);
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Проверка за POST заявка и задължителни полета
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['receiver_id']) || !isset($_POST['job_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'error' => 'Невалидна заявка']);
    exit;
}

$receiver_id = intval($_POST['receiver_id']);
$job_id = intval($_POST['job_id']);
$message = trim($_POST['message']);

// Валидация на съобщението
if (empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Съобщението не може да бъде празно']);
    exit;
}

// Проверка дали потребителят има право да изпраща съобщения в този чат
$stmt = $conn->prepare("
    SELECT id FROM connections 
    WHERE job_id = ? 
    AND ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?))
");
$stmt->bind_param("iiiii", $job_id, $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Нямате достъп до този чат']);
    exit;
}

// Записване на съобщението в базата данни
$stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, job_id, message, created_at, is_read)
    VALUES (?, ?, ?, ?, NOW(), 0)
");
$stmt->bind_param("iiis", $current_user_id, $receiver_id, $job_id, $message);

if ($stmt->execute()) {
    // Връщаме информация за новото съобщение
    $new_message_id = $conn->insert_id;
    
    $stmt = $conn->prepare("
        SELECT m.*, u.ime, u.familiq, u.profile_image 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.id = ?
    ");
    $stmt->bind_param("i", $new_message_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $message_data = $result->fetch_assoc();
    
    $response = [
        'success' => true,
        'message' => [
            'id' => $message_data['id'],
            'sender_id' => $message_data['sender_id'],
            'message' => htmlspecialchars($message_data['message']),
            'created_at' => date("H:i, d.m.Y", strtotime($message_data['created_at'])),
            'is_read' => $message_data['is_read'],
            'is_current_user' => true,
            'sender_name' => htmlspecialchars($message_data['ime'] . ' ' . $message_data['familiq']),
            'profile_image' => !empty($message_data['profile_image']) ? 'uploads/profiles/' . $message_data['profile_image'] : 'images/default-profile.png'
        ]
    ];
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'error' => 'Грешка при изпращане на съобщението']);
}