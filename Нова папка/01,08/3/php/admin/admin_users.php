<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$users = $conn->query("SELECT id, username, email, role FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Потребители - Админ</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #002147; color: white; }
        .actions button { margin-right: 5px; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Всички потребители</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Потребителско име</th>
            <th>Имейл</th>
            <th>Роля</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
