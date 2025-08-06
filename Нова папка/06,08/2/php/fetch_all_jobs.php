<?php
require 'db.php';
require_once 'rating_utils.php';
require_once 'favorites_utils.php';
session_start();

$typeFilter = $_GET['type'] ?? '';
$professionFilter = $_GET['profession'] ?? '';

$query = "SELECT * FROM jobs WHERE 1";
$params = [];

if (!empty($typeFilter)) {
    $query .= " AND job_type = :job_type";
    $params[':job_type'] = $typeFilter;
}

if (!empty($professionFilter)) {
    $query .= " AND profession = :profession";
    $params[':profession'] = $professionFilter;
}

$query .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($jobs as $job) {
    $images = json_decode($job['images'], true);

    // Определяме изображение
    if ($job['job_type'] === 'seek') {
        $stmtUser = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmtUser->execute([$job['user_id']]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        $cover = (!empty($user['profile_image']) && file_exists(__DIR__ . '/../uploads/' . $user['profile_image']))
            ? 'uploads/' . $user['profile_image']
            : 'img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
    } else {
        $cover = (!empty($images) && !empty($images[0]))
            ? $images[0]
            : 'img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png';
    }

    // Започваме картата
    echo '<div class="job-card" onclick="handleCardClick(event, ' . $job['id'] . ')">';

    // Любими – само ако е логнат
    if (isset($_SESSION['user'])) {
        $isFavorite = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
        $heartIcon = $isFavorite ? '../img/heart-filled.png' : '../img/heart-outline.png';
        $heartAlt = $isFavorite ? 'Премахни от любими' : 'Добави в любими';

        echo '<div class="favorite-icon">';
echo '<img src="' . $heartIcon . '" alt="' . $heartAlt . '" title="' . $heartAlt . '" data-job-id="' . $job['id'] . '" class="favorite-heart' . ($isFavorite ? ' favorited' : '') . '">';
echo '</div>';

    }


    echo '<img class="job-card-img" src="../' . htmlspecialchars($cover) . '" alt="Снимка">';

    echo '<div class="job-card-info">';
    echo '<div class="job-rating">' . getJobAverageRating($job['id'], true) . '</div>';
    echo '<h3>' . htmlspecialchars($job['profession']) . '</h3>';
    echo '<p><strong>Град:</strong> ' . htmlspecialchars($job['location'] ?? $job['city']) . '</p>';
    echo '<p><strong>Цена на ден:</strong> ' . ($job['price_per_day'] ? $job['price_per_day'] . ' лв' : '-') . '</p>';
    echo '<p><strong>Цена/кв.м:</strong> ' . ($job['price_per_square'] ? $job['price_per_square'] . ' лв' : '-') . '</p>';
    echo '</div>'; // .job-card-info
    echo '</div>'; // .job-card
}
