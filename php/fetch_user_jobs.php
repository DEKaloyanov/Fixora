<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
require_once __DIR__ . '/professions.php';
require_once 'favorites_utils.php';

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($user_id <= 0) {
    http_response_code(400);
    echo "Невалиден потребител.";
    exit;
}

$filter  = $_GET['type'] ?? null;

// взимаме username на собственика
$unStmt = $conn->prepare("SELECT username FROM users WHERE id = :id LIMIT 1");
$unStmt->execute([':id' => $user_id]);
$ownerRow = $unStmt->fetch(PDO::FETCH_ASSOC);
$ownerUsername = htmlspecialchars($ownerRow['username'] ?? '');

// обяви на този потребител
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

// кеш на профилни снимки за 'seek'
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

// helper: рендер на методи на плащане
function renderPayments(array $job): string {
    $pm = [];
    if (!empty($job['payment_methods'])) {
        $decoded = json_decode($job['payment_methods'], true);
        if (is_array($decoded)) $pm = $decoded;
    }
    $types = $pm['types'] ?? [];
    if (!$types) {
        if (!empty($job['price_per_day']))     $types['day']    = (float)$job['price_per_day'];
        if (!empty($job['price_per_square']))  $types['square'] = (float)$job['price_per_square'];
    }
    if (!$types) return '';

    $labels = [
        'day'     => ['Надник',         'fa-coins',           ' лв/ден'],
        'square'  => ['Цена/кв.м',      'fa-ruler-combined',  ' лв/кв.м'],
        'hour'    => ['Цена на час',    'fa-clock',           ' лв/час'],
        'project' => ['Цена за проект', 'fa-briefcase',       ' лв/проект'],
        'other'   => ['Друго',          'fa-tag',             ''],
    ];

    $out = [];
    foreach ($types as $k => $v) {
        $def = $labels[$k] ?? [$k, 'fa-tag', ''];
        $val = is_numeric($v) ? number_format((float)$v, 2, '.', '') : (string)$v;
        $out[] = '<span class="payment-pill"><i class="fas ' . $def[1] . '"></i>' .
                 htmlspecialchars($def[0]) . ': <strong>' . htmlspecialchars($val . $def[2]) . '</strong></span>';
    }
    return '<div class="payment-list">' . implode('', $out) . '</div>';
}

// изход
foreach ($jobs as $job) {
    // Корица
    if ($job['job_type'] === 'seek') {
        $image = $userImages[$job['user_id']] ?? '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
    } else {
        $images = json_decode($job['images'] ?? '[]', true);
        $image  = (is_array($images) && !empty($images[0]))
                  ? '../' . $images[0]
                  : '../img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png';
    }

    $singleKey   = (string)($job['profession'] ?? '');
    $singleLabel = $professions[$singleKey] ?? ucfirst($singleKey);
    $place       = $job['city'] ?: $job['location'];

    echo '<div class="job-card" data-job-id="'.(int)$job['id'].'">';

      // Снимка
      echo '<img class="job-card-img" src="'.htmlspecialchars($image).'" alt="Снимка">';

      // Ляв блок: заглавие/потребител/плащания/локация/фирма
      echo '<div class="job-card-info">';
        echo '<h3>'. htmlspecialchars($singleLabel) .'</h3>';
        echo '<p class="job-username"><i class="fas fa-user"></i> ' . $ownerUsername . '</p>';
        echo renderPayments($job);

        if (!empty($place)) {
            echo '<p class="job-meta-item"><i class="fas fa-map-marker-alt"></i><strong>Град:</strong> '. htmlspecialchars($place) .'</p>';
        }
        if ((int)$job['is_company'] === 1) {
            echo '<span class="badge-company" title="Фирмена обява">Фирма</span>';
        }
        // Описанието не се показва
      echo '</div>';

      // Дясна „страничка“: Любими + Рейтинг (без Редакция/Изтриване)
      echo '<aside class="job-side">';

        if (isset($_SESSION['user'])) {
            $isFavorite = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
            $heartIcon  = $isFavorite ? '../img/heart-filled.png' : '../img/heart-outline.png';
            $heartAlt   = $isFavorite ? 'Премахни от любими' : 'Добави в любими';
            echo '<div class="favorite-icon">';
            echo '  <img src="'.$heartIcon.'" alt="'.$heartAlt.'" title="'.$heartAlt.'" data-job-id="'.(int)$job['id'].'" class="favorite-heart'.($isFavorite?' favorited':'').'">';
            echo '</div>';
        }

        echo '<div class="job-rating">'. getJobAverageRating($job['id'], true) .'</div>';

      echo '</aside>';

    echo '</div>';
}
