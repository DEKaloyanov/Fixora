<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$jobs = $conn->query("SELECT j.id, j.profession, j.job_type, u.username 
                      FROM jobs j 
                      JOIN users u ON j.user_id = u.id 
                      ORDER BY j.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Обяви - Админ</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #002147; color: white; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Всички обяви</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Професия</th>
            <th>Тип</th>
            <th>Собственик</th>
        </tr>
        <?php foreach ($jobs as $job): ?>
            <tr>
                <td><?= $job['id'] ?></td>
                <td><?= htmlspecialchars($job['profession']) ?></td>
                <td><?= $job['job_type'] === 'offer' ? 'Предлага' : 'Търси' ?></td>
                <td><?= htmlspecialchars($job['username']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
