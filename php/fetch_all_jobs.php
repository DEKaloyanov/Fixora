<?php
require 'db.php';
require_once 'rating_utils.php';


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
    $cover = $images[0] ?? 'img/default-job.jpg';
    echo '<a class="job-card" href="job_details.php?id=' . $job['id'] . '">';
    echo '<img class="job-card-img" src="../' . htmlspecialchars($cover) . '" alt="Снимка">';
    echo '<div class="job-card-info">';
    echo '<div class="job-rating">';
    echo getJobAverageRating($job['id'], true);
    echo '</div>';

    echo '<h3>' . htmlspecialchars($job['profession']) . '</h3>';
    echo '<p><strong>Град:</strong> ' . htmlspecialchars($job['location'] ?? $job['city']) . '</p>';
    echo '<p><strong>Цена на ден:</strong> ' . ($job['price_per_day'] ? $job['price_per_day'] . ' лв' : '-') . '</p>';
    echo '<p><strong>Цена/кв.м:</strong> ' . ($job['price_per_square'] ? $job['price_per_square'] . ' лв' : '-') . '</p>';
    echo '</div></a>';

}
