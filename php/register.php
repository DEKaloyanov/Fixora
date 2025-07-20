<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $ime = $_POST['ime'];
    $familiq = $_POST['familiq'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirmPassword'];

    if ($password !== $confirm) {
        echo "Паролите не съвпадат.";
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, ime, familiq, telefon, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$username, $ime, $familiq, $telefon, $email, $hash]);
        header("Location: ../index.php?register=success");
    } catch (PDOException $e) {
        echo "Грешка при регистрация: " . $e->getMessage();
    }
}
?>
