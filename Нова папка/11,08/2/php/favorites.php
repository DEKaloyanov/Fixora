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
            overflow: hidden;
            background: #f9f9f9;
            position: relative;
            cursor: pointer;
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

        .remove-favorite {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 24px;
            height: 24px;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s ease;
            z-index: 10;
        }

        .remove-favorite:hover {
            opacity: 1;
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
                <div class="job-card" data-job-id="<?= $job['id'] ?>">
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
                    <img src="../img/trash-icon.png" class="remove-favorite" title="Премахни от любими">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            document.querySelectorAll('.job-card').forEach(card => {
                const trash = card.querySelector('.remove-favorite');

                // Спри зареждане на детайли при натискане на кошче
                trash.addEventListener('click', function (e) {
                    e.stopPropagation();

                    const jobId = card.dataset.jobId;

                    fetch('toggle_favorite.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'job_id=' + encodeURIComponent(jobId)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'removed') {
                            card.remove(); // премахни от DOM
                        }
                    });
                });

                // Отваряне на детайли при клик извън кошчето
                card.addEventListener('click', function (e) {
                    if (!e.target.classList.contains('remove-favorite')) {
                        window.location.href = 'job_details.php?id=' + card.dataset.jobId;
                    }
                });
            });
        });
    </script>
</body>
</html>
