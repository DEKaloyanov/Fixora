<?php
session_start();
require 'db.php'; // връзка с базата данни

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = 'Моля, въведете потребителско име и парола.';
        header('Location: ../index.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header('Location: profil.php');
        exit;
    } else {
        $_SESSION['error'] = 'Грешно потребителско име или парола.';
        header('Location: ../index.php');
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
