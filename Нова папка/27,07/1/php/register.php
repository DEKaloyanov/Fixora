<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $ime = trim($_POST['ime'] ?? '');
    $familiq = trim($_POST['familiq'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $_SESSION['error'] = 'Моля, попълнете всички задължителни полета.';
        header('Location: ../index.php');
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['error'] = 'Паролите не съвпадат.';
        header('Location: ../index.php');
        exit;
    }

    // Проверка дали съществува потребител
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Потребителското име вече съществува.';
        header('Location: ../index.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, ime, familiq, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$username, $ime, $familiq, $email, $hashedPassword]);

    $_SESSION['success'] = 'Регистрацията е успешна. Моля, влезте.';
    header('Location: ../index.php');
    exit;
} else {
    header('Location: ../index.php');
    exit;
}
