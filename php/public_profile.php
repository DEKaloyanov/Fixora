
<?php
require 'db.php';

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    echo "Невалиден профил.";
    exit;
}

$stmt = $conn->prepare("SELECT username, ime, familiq, phone, email, city, profile_image, show_email, show_phone, show_age, show_city, age FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Потребителят не е намерен.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Публичен профил - <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="../css/profile.css">
</head>
<body>
    <div class="profile-container">
        <h2>Профил на <?= htmlspecialchars($user['username']) ?></h2>
        <img src="<?= !empty($user['profile_image']) ? '../uploads/' . htmlspecialchars($user['profile_image']) : '../img/default-user.png' ?>" alt="Профилна снимка" width="120">

        <p><strong>Име:</strong> <?= htmlspecialchars($user['ime'] . ' ' . $user['familiq']) ?></p>

        <?php if ($user['show_city']): ?>
            <p><strong>Град:</strong> <?= htmlspecialchars($user['city']) ?></p>
        <?php endif; ?>

        <?php if ($user['show_age']): ?>
            <p><strong>Години:</strong> <?= htmlspecialchars($user['age']) ?></p>
        <?php endif; ?>

        <?php if ($user['show_phone']): ?>
            <p><strong>Телефон:</strong> <?= htmlspecialchars($user['phone']) ?></p>
        <?php endif; ?>

        <?php if ($user['show_email']): ?>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <?php endif; ?>
    </div>
</body>
</html>
