<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR telefon = ?");
    $stmt->execute([$login, $login, $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        $_SESSION['username'] = $user['username']; // 🟢 Това е важно
        $_SESSION['success_message'] = 'Успешен вход. Здравей, ' . htmlspecialchars($user['username']) . '!';
        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION['success_message'] = 'Грешен потребител или парола.';
        header("Location: ../index.php");
        exit;
    }
}
?>
