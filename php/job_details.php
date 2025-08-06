<?php
require 'db.php';
require_once 'rating_utils.php';
require_once 'favorites_utils.php';
session_start();


$job_id = $_GET['id'] ?? $_GET['job_id'] ?? null;

if (!$job_id) {
    echo "Липсва ID на обявата.";
    exit;
}
$id = (int) $job_id;


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
        .job-details {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }

        .job-images {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .job-images img {
            max-width: 200px;
            height: auto;
            border-radius: 4px;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: #002147;
            color: #fff;
            border-radius: 4px;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="job-details">
        <a href="javascript:history.back()" class="back-btn">⬅ Назад</a>

        <h2 style="display: flex; align-items: center; justify-content: space-between;">
            <span><?php echo htmlspecialchars($job['profession']); ?></span>
            <?php
            if (isset($_SESSION['user'])) {
                $isFavorite = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
                $icon = $isFavorite ? '../img/heart-filled.png' : '../img/heart-outline.png';
                $alt = $isFavorite ? 'Премахни от любими' : 'Добави в любими';
                echo '<img src="' . $icon . '" alt="' . $alt . '" title="' . $alt . '" class="favorite-heart" data-job-id="' . $job['id'] . '" style="width: 28px; height: 28px; cursor: pointer;">';

            }
            ?>
        </h2>
        <p><strong>Тип обява:</strong> <?php echo $job['job_type'] === 'offer' ? 'Предлагам работа' : 'Търся работа'; ?>
        </p>
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
        <p><strong>Собственик:</strong>
            <a href="public_profile.php?id=<?= $job['user_id'] ?>">
                <?= htmlspecialchars($job['username']) ?>
            </a>
        </p>

        <?php

        if (isset($_SESSION['user']) && $_SESSION['user']['id'] !== $job['user_id']) {
            // Проверка дали вече има изпратена заявка
            $check = $conn->prepare("SELECT * FROM connection_requests WHERE sender_id = ? AND receiver_id = ? AND id = ?");
            $check->execute([$_SESSION['user']['id'], $job['user_id'], $job['id']]);
            $existing = $check->fetch();

            if (!$existing) {
                echo '<form action="send_request.php" method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="job_id" value="' . $job['id'] . '">
                    <input type="hidden" name="owner_id" value="' . $job['user_id'] . '">
                    <button type="submit" style="padding: 10px 20px; background: green; color: white; border: none; border-radius: 5px;">
                        📩 Интересувам се
                    </button>
                </form>';
            } else {
                echo '<p style="margin-top: 20px; color: gray;">Вече сте изпратили заявка за тази обява.</p>';
            }
        }
        ?>

        <hr>
        <h3>⭐ Оценки и коментари:</h3>
        <?php
        $ratings = getRatingsForJob($job['id']);
        if (count($ratings) === 0) {
            echo "<p>Все още няма оценки за тази обява.</p>";
        } else {
            foreach ($ratings as $r) {
                echo '<div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">';
                echo '<strong>' . htmlspecialchars($r['ime'] . ' ' . $r['familiq']) . '</strong><br>';
                echo number_format($r['rating'], 2) . ' / 5<br>';
                if (!empty($r['comment'])) {
                    echo '<em>' . nl2br(htmlspecialchars($r['comment'])) . '</em>';
                }
                echo '</div>';
            }
        }
        ?>


    </div>

    <script src="../js/favorites.js" defer></script>



</body>

</html>