<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Нямате достъп.";
    exit;
}

$user_id = $_SESSION['user']['id'];
$filter = $_GET['type'] ?? null;

$query = "SELECT * FROM jobs WHERE user_id = :user_id";
$params = ['user_id' => $user_id];

if ($filter && in_array($filter, ['offer', 'seek'])) {
    $query .= " AND job_type = :job_type";
    $params['job_type'] = $filter;
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Зареждане на профилните снимки за seek обяви
$userImages = [];
foreach ($jobs as $j) {
    if ($j['job_type'] === 'seek' && !isset($userImages[$j['user_id']])) {
        $uStmt = $conn->prepare("SELECT profile_image FROM users WHERE id = :id LIMIT 1");
        $uStmt->execute(['id' => $j['user_id']]);
        $user = $uStmt->fetch(PDO::FETCH_ASSOC);
        $userImages[$j['user_id']] = !empty($user['profile_image']) ? '../uploads/' . $user['profile_image'] : '../img/default-user.png';
    }
}

// Генериране на HTML за всяка обява
foreach ($jobs as $job) {
    if ($job['job_type'] === 'seek') {
        $image = $userImages[$job['user_id']] ?? '../img/default-user.png';
    } else {
        $images = json_decode($job['images'], true);
        if (is_array($images) && !empty($images[0])) {
            $image = '../' . htmlspecialchars($images[0]);
        } else {
            $image = '../img/default-jobs.png';
        }


    }

    echo '<div class="job-card" onclick="location.href=\'job_details.php?id=' . $job['id'] . '\'">';
    echo '  <div class="job-image">';
    echo '    <img src="' . $image . '" alt="Обява">';
    echo '  </div>';

    echo '  <div class="job-details">';

    // Мапване на професиите към кирилица
    $professionMap = [
        'boqjdiq' => 'Бояджия',
        'zidar' => 'Зидар',
        'kofraj' => 'Кофражист',
        'elektrikar' => 'Електротехник',
        'mazach' => 'Мазач',
        'armat' => 'Арматурист',
        'dvijenie' => 'Работник по пътна поддръжка',
        'tehnik' => 'Техник',
        'dograma' => 'Монтажник на дограма',
        'vhodove' => 'Овластител входове'
    ];

    $professionName = $professionMap[$job['profession']] ?? ucfirst($job['profession']);
    echo '    <h3>' . htmlspecialchars($professionName) . '</h3>';

    if ($job['city']) {
        echo '<p><strong>Град:</strong> ' . htmlspecialchars($job['city']) . '</p>';
    } elseif ($job['location']) {
        echo '<p><strong>Локация:</strong> ' . htmlspecialchars($job['location']) . '</p>';
    }

    if ($job['price_per_square']) {
        echo '<p><strong>Цена/кв.м:</strong> ' . htmlspecialchars($job['price_per_square']) . ' лв</p>';
    }

    if ($job['price_per_day']) {
        echo '<p><strong>Надник:</strong> ' . htmlspecialchars($job['price_per_day']) . ' лв</p>';
    }

    // Добавяне на показване на екипа (ако има)
    if (!empty($job['team_members'])) {
        $teamMembers = json_decode($job['team_members'], true);
        if (is_array($teamMembers) && !empty($teamMembers)) {
            echo '<p><strong>Екип:</strong> ' . htmlspecialchars(implode(', ', $teamMembers)) . '</p>';
        }
    }

    // Добавяне на показване на всички снимки (ако има)


    if (!empty($job['description'])) {
        echo '<p><strong>Описание:</strong> ' . nl2br(htmlspecialchars($job['description'])) . '</p>';
    }

    echo '    <a href="edit_job.php?id=' . $job['id'] . '" class="button edit-btn">Редактирай</a>';
    echo '    <div class="job-rating">';
    echo getJobAverageRating($job['id'], true);
    echo '    </div>';

    echo '  </div>';
    echo '</div>';
}
?>