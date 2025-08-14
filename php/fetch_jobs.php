<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
require_once __DIR__ . '/professions.php'; // $professions

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Нямате достъп.";
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$filter  = $_GET['type'] ?? null;

$sql    = "SELECT * FROM jobs WHERE user_id = :uid";
$params = [':uid' => $user_id];

if ($filter && in_array($filter, ['offer','seek'], true)) {
    $sql .= " AND job_type = :jt";
    $params[':jt'] = $filter;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// кеш за профилни снимки (seek)
$userImages = [];
foreach ($jobs as $j) {
    if ($j['job_type'] === 'seek' && !isset($userImages[$j['user_id']])) {
        $uStmt = $conn->prepare("SELECT profile_image FROM users WHERE id = :id LIMIT 1");
        $uStmt->execute([':id' => (int)$j['user_id']]);
        $u = $uStmt->fetch(PDO::FETCH_ASSOC);
        $userImages[$j['user_id']] =
            (!empty($u['profile_image']) && file_exists(__DIR__ . '/../uploads/' . $u['profile_image']))
            ? '../uploads/' . $u['profile_image']
            : '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
    }
}

foreach ($jobs as $job) {
    // корица
    if ($job['job_type'] === 'seek') {
        $image = $userImages[$job['user_id']] ?? '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
    } else {
        $images = json_decode($job['images'] ?? '[]', true);
        $image  = (is_array($images) && !empty($images[0]))
                  ? '../' . htmlspecialchars($images[0])
                  : '../img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png';
    }

    echo '<div class="job-card" data-job-id="' . (int)$job['id'] . '">';
    echo '  <div class="job-image"><img src="' . $image . '" alt="Обява"></div>';
    echo '  <div class="job-details" style="position:relative;">';

    // заглавие + бейдж
    $singleKey   = (string)($job['profession'] ?? '');
    $singleLabel = $professions[$singleKey] ?? ucfirst($singleKey);

    if ((int)$job['is_company'] === 1) {
        echo '<span class="badge-company" title="Фирмена обява">Фирма</span>';
    }
    echo '    <h3>' . htmlspecialchars($singleLabel) . '</h3>';

    // чипове с множествени професии
    if ((int)$job['is_company'] === 1 && !empty($job['professions'])) {
        $list = json_decode($job['professions'], true);
        if (is_array($list) && $list) {
            echo '<div class="profession-chips">';
            foreach ($list as $k) {
                $lbl = $professions[$k] ?? ucfirst((string)$k);
                echo '<span class="chip">' . htmlspecialchars($lbl) . '</span>';
            }
            echo '</div>';
        }
    }

    // локация / град
    if (!empty($job['city'])) {
        echo '<p><strong>Град:</strong> ' . htmlspecialchars($job['city']) . '</p>';
    } elseif (!empty($job['location'])) {
        echo '<p><strong>Локация:</strong> ' . htmlspecialchars($job['location']) . '</p>';
    }

    if (!empty($job['price_per_square'])) {
        echo '<p><strong>Цена/кв.м:</strong> ' . htmlspecialchars($job['price_per_square']) . ' лв</p>';
    }
    if (!empty($job['price_per_day'])) {
        echo '<p><strong>Надник:</strong> ' . htmlspecialchars($job['price_per_day']) . ' лв</p>';
    }

    // екип (ако има)
    if (!empty($job['team_members'])) {
        $tm = json_decode($job['team_members'], true);
        if (is_array($tm) && $tm) {
            echo '<p><strong>Екип:</strong> ' . htmlspecialchars(implode(', ', $tm)) . '</p>';
        }
    }

    if (!empty($job['description'])) {
        echo '<p><strong>Описание:</strong> ' . nl2br(htmlspecialchars($job['description'])) . '</p>';
    }

    echo '    <a href="edit_job.php?id=' . (int)$job['id'] . '" class="button edit-btn">Редактирай</a>';

    echo '    <div class="job-rating">' . getJobAverageRating($job['id'], true) . '</div>';

    // любими
    require_once 'favorites_utils.php';
    if (isset($_SESSION['user'])) {
        $isFavorite = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
        $heartIcon  = $isFavorite ? '../img/heart-filled.png' : '../img/heart-outline.png';
        $heartAlt   = $isFavorite ? 'Премахни от любими' : 'Добави в любими';
        echo '<div class="favorite-icon">';
        echo '<img src="' . $heartIcon . '" alt="' . $heartAlt . '" title="' . $heartAlt . '" data-job-id="' . (int)$job['id'] . '" class="favorite-heart">';
        echo '</div>';
    }

    echo '  </div>';
    echo '</div>';
}
