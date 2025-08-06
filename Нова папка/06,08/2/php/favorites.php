<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';

if (!isset($_SESSION['user'])) {
    echo "Нямате достъп до тази страница.";
    exit;
}

$user_id = $_SESSION['user']['id'];

$stmt = $conn->prepare("
    SELECT j.*, u.profile_image 
    FROM favorites f 
    JOIN jobs j ON f.job_id = j.id 
    JOIN users u ON j.user_id = u.id
    WHERE f.user_id = ?
    ORDER BY j.created_at DESC
");
$stmt->execute([$user_id]);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <title>Любими обяви</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .favorites-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
        }
        .job-card {
            display: flex;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
            text-decoration: none;
            color: #000;
            overflow: hidden;
            background: #f9f9f9;
        }
        .job-card-img {
            width: 200px;
            height: 150px;
            object-fit: cover;
        }
        .job-card-info {
            padding: 15px;
            flex-grow: 1;
        }
        .job-card-info h3 {
            margin-top: 0;
        }
        .job-rating {
            margin-bottom: 10px;
        }
        .no-favorites {
            text-align: center;
            color: #666;
            font-size: 18px;
            margin-top: 50px;
        }
    </style>
</head>
<body>
<div class="favorites-container">
    <h2>❤️ Моите любими обяви</h2>
    <hr>

    <?php if (empty($jobs)): ?>
        <p class="no-favorites">Нямате добавени любими обяви.</p>
    <?php else: ?>
        <?php foreach ($jobs as $job): ?>
            <?php
            $cover = 'img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png'; // default

            if ($job['job_type'] === 'seek') {
                $cover = !empty($job['profile_image']) && file_exists(__DIR__ . '/../uploads/' . $job['profile_image'])
                    ? 'uploads/' . $job['profile_image']
                    : 'img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
            } else {
                $images = json_decode($job['images'], true);
                if (is_array($images) && !empty($images[0])) {
                    $cover = $images[0];
                }
            }

            $profession = htmlspecialchars($job['profession']);
            $location = $job['city'] ?? $job['location'] ?? '-';
            ?>
            <div class="job-card" onclick="handleCardClick(event, <?= $job['id'] ?>)">

                <img class="job-card-img" src="../<?= htmlspecialchars($cover) ?>" alt="Снимка">
                <div class="job-card-info">
                    <div class="job-rating">
                        <?= getJobAverageRating($job['id'], true) ?>
                    </div>
                    <h3><?= $profession ?></h3>
                    <p><strong>Град:</strong> <?= htmlspecialchars($location) ?></p>
                    <p><strong>Цена на ден:</strong> <?= $job['price_per_day'] ? $job['price_per_day'] . ' лв' : '-' ?></p>
                    <p><strong>Цена/кв.м:</strong> <?= $job['price_per_square'] ? $job['price_per_square'] . ' лв' : '-' ?></p>
                </div>
    
            </div>

        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script>
function handleCardClick(event, jobId) {
  const target = event.target;

  // Ако кликваме върху сърце или бутон за редакция – не правим нищо
  if (target.closest('.favorite-icon') || target.closest('.edit-button')) {
    return;
  }

  // Иначе отваряме страницата с детайли
  window.location.href = 'job_details.php?id=' + jobId;
}
</script>

</body>
</html>
