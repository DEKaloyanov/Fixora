<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Админ панел - Fixora</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-nav { margin: 40px auto; max-width: 600px; display: flex; flex-direction: column; gap: 15px; }
        .admin-nav a { padding: 15px; background: #002147; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .admin-nav a:hover { background: #004080; }
    </style>
</head>
<body>
    <div class="admin-nav">
        <a href="admin_users.php">👥 Управление на потребители</a>
        <a href="admin_jobs.php">📄 Управление на обяви</a>
        <a href="admin_reports.php">🚨 Докладвани случаи</a>
    </div>
</body>
</html>
