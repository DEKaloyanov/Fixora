<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$tab = $_GET['tab'] ?? 'users';
$search = trim($_GET['search'] ?? '');
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Админ Панел - Fixora</title>
    <link rel="stylesheet" href="../css/admin.css?v=<?= time() ?>">
</head>
<body>
    <div class="admin-container">
        <h1>Админ Панел</h1>

        <div class="tabs">
            <a href="?tab=users" class="<?= $tab === 'users' ? 'active' : '' ?>">Потребители</a>
            <a href="?tab=jobs" class="<?= $tab === 'jobs' ? 'active' : '' ?>">Обяви</a>
            <a href="?tab=reports" class="<?= $tab === 'reports' ? 'active' : '' ?>">Доклади</a>
        </div>

        <form method="GET" class="search-bar">
            <input type="hidden" name="tab" value="<?= $tab ?>">
            <input type="text" name="search" placeholder="Търсене..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">🔍</button>
        </form>

        <div class="content-box">
            <?php if ($tab === 'users'): ?>
                <h2>Списък с потребители</h2>
                <table>
                    <tr><th>ID</th><th>Име</th><th>Email</th><th>Роля</th></tr>
                    <?php
                        $stmt = $conn->prepare("SELECT id, ime, familiq, email, role FROM users WHERE CONCAT(ime, ' ', familiq, email) LIKE ?");
                        $stmt->execute(["%$search%"]);
                        while ($user = $stmt->fetch()):
                    ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['ime'] . ' ' . $user['familiq']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>

            <?php elseif ($tab === 'jobs'): ?>
                <h2>Списък с обяви</h2>
                <table>
                    <tr><th>ID</th><th>Професия</th><th>Град</th><th>Собственик</th></tr>
                    <?php
                        $stmt = $conn->prepare("
                            SELECT j.id, j.profession, j.city, u.ime, u.familiq 
                            FROM jobs j 
                            JOIN users u ON j.user_id = u.id
                            WHERE j.profession LIKE ? OR j.city LIKE ?
                        ");
                        $stmt->execute(["%$search%", "%$search%"]);
                        while ($job = $stmt->fetch()):
                    ?>
                        <tr>
                            <td><?= $job['id'] ?></td>
                            <td><?= htmlspecialchars($job['profession']) ?></td>
                            <td><?= htmlspecialchars($job['city']) ?></td>
                            <td><?= htmlspecialchars($job['ime'] . ' ' . $job['familiq']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>

            <?php elseif ($tab === 'reports'): ?>
                <h2>Докладвани случаи</h2>
                <table>
                    <tr><th>ID</th><th>Тип</th><th>Подател</th><th>Дата</th><th>Причина</th></tr>
                    <?php
                        $stmt = $conn->prepare("
                            SELECT r.id, r.type, r.reason, r.created_at, u.ime, u.familiq
                            FROM reports r
                            JOIN users u ON r.sender_id = u.id
                            WHERE r.reason LIKE ?
                            ORDER BY r.created_at DESC
                        ");
                        $stmt->execute(["%$search%"]);
                        while ($report = $stmt->fetch()):
                    ?>
                        <tr>
                            <td><?= $report['id'] ?></td>
                            <td><?= htmlspecialchars($report['type']) ?></td>
                            <td><?= htmlspecialchars($report['ime'] . ' ' . $report['familiq']) ?></td>
                            <td><?= $report['created_at'] ?></td>
                            <td><?= htmlspecialchars($report['reason']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
