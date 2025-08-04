<?php
require 'db.php';

$profession = $_GET['profession'] ?? '';
$city = $_GET['city'] ?? '';
$job_type = $_GET['job_type'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$query = "SELECT * FROM jobs WHERE 1=1";
$params = [];

if (!empty($profession)) {
    $query .= " AND profession LIKE ?";
    $params[] = "%$profession%";
}
if (!empty($city)) {
    $query .= " AND city = ?";
    $params[] = $city;
}
if (!empty($job_type)) {
    $query .= " AND job_type = ?";
    $params[] = $job_type;
}
if (is_numeric($min_price)) {
    $query .= " AND (price_per_day >= ? OR price_per_square >= ?)";
    $params[] = $min_price;
    $params[] = $min_price;
}
if (is_numeric($max_price)) {
    $query .= " AND (price_per_day <= ? OR price_per_square <= ?)";
    $params[] = $max_price;
    $params[] = $max_price;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Търсене на обяви</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= time() ?>">
</head>
<body>
    <h2 style="text-align:center;">Резултати от търсенето</h2>
    <div style="max-width: 900px; margin: auto;">
        <?php if (count($jobs) === 0): ?>
            <p>Няма намерени обяви.</p>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div style="border:1px solid #ccc; padding:15px; margin-bottom:15px;">
                    <h3><?= htmlspecialchars($job['profession']) ?></h3>
                    <p><strong>Град:</strong> <?= htmlspecialchars($job['city']) ?></p>
                    <p><strong>Тип:</strong> <?= $job['job_type'] === 'offer' ? 'Предлагам работа' : 'Търся работа' ?></p>
                    <?php if ($job['price_per_day']): ?>
                        <p><strong>Надник:</strong> <?= $job['price_per_day'] ?> лв</p>
                    <?php endif; ?>
                    <?php if ($job['price_per_square']): ?>
                        <p><strong>На кв.м:</strong> <?= $job['price_per_square'] ?> лв</p>
                    <?php endif; ?>
                    <a href="job_details.php?id=<?= $job['id'] ?>" style="color:blue;">Виж обявата</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>