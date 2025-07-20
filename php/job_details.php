<?php
require 'db.php';

if (!isset($_GET['id'])) {
    echo "Липсва ID на обявата.";
    exit;
}

$id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT j.*, u.username FROM jobs j JOIN users u ON j.user_id = u.id WHERE j.id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo "Обявата не е намерена.";
    exit;
}

// Подготовка на изображенията
$images = json_decode($job['images'], true);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Детайли за обявата - Fixora</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .job-details { max-width: 900px; margin: 40px auto; background: #fff; padding: 20px; border-radius: 8px; }
        .job-images { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .job-images img { max-width: 200px; height: auto; border-radius: 4px; }
        .back-btn { display: inline-block; margin-bottom: 20px; padding: 8px 16px; background: #002147; color: #fff; border-radius: 4px; text-decoration: none; }
    </style>
</head>
<body>

<div class="job-details">
    <a href="javascript:history.back()" class="back-btn">⬅ Назад</a>

    <h2><?php echo htmlspecialchars($job['profession']); ?></h2>
    <p><strong>Тип обява:</strong> <?php echo $job['job_type'] === 'offer' ? 'Предлагам работа' : 'Търся работа'; ?></p>
    <p><strong>Град:</strong> <?php echo htmlspecialchars($job['city'] ?? $job['location']); ?></p>
    <?php if ($job['price_per_day']): ?>
        <p><strong>Надник:</strong> <?php echo $job['price_per_day']; ?> лв</p>
    <?php endif; ?>
    <?php if ($job['price_per_square']): ?>
        <p><strong>Цена на кв.м:</strong> <?php echo $job['price_per_square']; ?> лв</p>
    <?php endif; ?>
    <?php if ($job['work_status'] === 'team' && $job['team_members']): ?>
        <p><strong>Екип от:</strong> <?php echo $job['team_size']; ?> човека</p>
        <p><strong>Имена:</strong> <?php echo implode(", ", json_decode($job['team_members'], true)); ?></p>
    <?php endif; ?>

    <?php if (!empty($images)): ?>
        <div class="job-images">
            <?php foreach ($images as $img): ?>
                <img src="../<?php echo htmlspecialchars($img); ?>" alt="Снимка">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p><strong>Описание:</strong> <?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
    <p><strong>Собственик:</strong> <?php echo htmlspecialchars($job['username']); ?></p>
</div>

</body>
</html>
