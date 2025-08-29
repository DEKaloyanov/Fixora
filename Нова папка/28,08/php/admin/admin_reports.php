<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Предварително подготвен, очаква структура от таблица "reports"
$reports = $conn->query("SELECT * FROM reports ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Докладвани случаи</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table { width: 90%; margin: 30px auto; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #002147; color: white; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Докладвани случаи</h2>
    <?php if ($reports): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Докладвано от</th>
            <th>Тип</th>
            <th>Описание</th>
            <th>Дата</th>
        </tr>
        <?php foreach ($reports as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= $r['reporter_id'] ?></td>
                <td><?= htmlspecialchars($r['type']) ?></td>
                <td><?= htmlspecialchars($r['description']) ?></td>
                <td><?= $r['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
        <p style="text-align:center;">Няма постъпили сигнали.</p>
    <?php endif; ?>
</body>
</html>
