<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
require_once __DIR__ . '/professions.php'; // $professions
require_once __DIR__ . '/label_utils.php'; // job_label()
require_once 'favorites_utils.php';

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

/* helper: рендер на методи на плащане (по РЕДОВЕ) */
function renderPayments(array $job): string {
    $pm = [];
    if (!empty($job['payment_methods'])) {
        $decoded = json_decode($job['payment_methods'], true);
        if (is_array($decoded)) {
            $pm = $decoded;
        }
    }
    $types = $pm['types'] ?? [];

    // fallback към legacy колони
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

        // Ред по ред, с иконка
        $out[] =
          '<div class="job-pay-item">'
          . '<i class="fas ' . $def[1] . '"></i>'
          . htmlspecialchars($def[0]) . ': '
          . '<strong>' . htmlspecialchars($val . $def[2]) . '</strong>'
          . '</div>';
    }
    return '<div class="payment-list">' . implode('', $out) . '</div>';
}

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

// потребителско име (в профила е винаги собственикът)
$ownerUsername = htmlspecialchars($_SESSION['user']['username'] ?? '');

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
    $singleLabel = job_label($singleKey); // винаги на БГ
    $place       = $job['city'] ?: $job['location'];

    echo '<div class="job-card" data-job-id="'.(int)$job['id'].'">';

    // СНИМКА
    echo '<img class="job-card-img" src="'.htmlspecialchars($image).'" alt="Снимка">';

    // ЛЯВО: професия, username, плащания
    echo '<div class="job-card-info">';
      echo '<h3>'. htmlspecialchars($singleLabel) .'</h3>';
      echo '<p class="job-username"><i class="fas fa-user"></i> ' . $ownerUsername . '</p>';
      echo renderPayments($job);

      // Локация и „Фирма“
      if (!empty($place)) {
          echo '<p class="job-meta-item"><i class="fas fa-map-marker-alt"></i><strong>Град:</strong> '. htmlspecialchars($place) .'</p>';
      }
      if ((int)$job['is_company'] === 1) {
          echo '<span class="badge-company" title="Фирмена обява">Фирма</span>';
      }

      // Описание не се показва
    echo '</div>'; // .job-card-info

    // ДЯСНО: любими → рейтинг → Редактирай → Изтрий
    echo '<aside class="job-side" onClick="event.stopPropagation();">';

      // Любими
      if (isset($_SESSION['user'])) {
          $isFavorite = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
          $heartIcon  = $isFavorite ? '../img/heart-filled.png' : '../img/heart-outline.png';
          $heartAlt   = $isFavorite ? 'Премахни от любими' : 'Добави в любими';
          echo '<div class="favorite-icon">';
          echo '  <img src="'.$heartIcon.'" alt="'.$heartAlt.'" title="'.$heartAlt.'" data-job-id="'.(int)$job['id'].'" class="favorite-heart'.($isFavorite?' favorited':'').'">';
          echo '</div>';
      }

      // Рейтинг
      echo '<div class="job-rating">'. getJobAverageRating($job['id'], true) .'</div>';

      // Редакция
      echo '<a href="edit_job.php?id='.(int)$job['id'].'" class="edit-btn">Редактирай</a>';

      // Изтриване с иконка кошче
      echo '<form method="POST" action="delete_job.php" class="delete-form" onsubmit="return confirm(\'Сигурни ли сте, че искате да изтриете обявата? Тази операция е необратима.\');">';
      echo '  <input type="hidden" name="id" value="'.(int)$job['id'].'">';
      echo '  <button type="submit" class="delete-btn" title="Изтрий">';
      echo '    <img src="../img/trash-icon.png" alt="Изтрий" class="trash-icon">';
      echo '  </button>';
      echo '</form>';

    echo '</aside>';

    echo '</div>'; // .job-card
}
