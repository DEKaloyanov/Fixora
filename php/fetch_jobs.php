<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
require_once __DIR__ . '/professions.php';
require_once __DIR__ . '/label_utils.php';
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

/**
 * Заплащане – вертикален списък с икони
 */
function renderPayments(array $job): string {
    $allowed = [
        'day'         => ['Надник',          'fa-coins',            ' лв/ден'],
        'hour'        => ['Цена на час',     'fa-clock',            ' лв/час'],
        'square'      => ['Цена/кв.м',       'fa-ruler-combined',   ' лв/кв.м'],
        'linear'      => ['Цена/л.м',        'fa-ruler-horizontal', ' лв/л.м'],
        'piece'       => ['Цена/бр.',        'fa-hashtag',          ' лв/бр.'],
        'project'     => ['Цена за проект',  'fa-briefcase',        ' лв/проект'],
        'per_m3'      => ['Обем',            'fa-cube',             ' лв/м³'],
        'per_ton'     => ['Тонаж',           'fa-weight-hanging',   ' лв/тон'],
        'callout_fee' => ['Такса посещение', 'fa-taxi',             ' лв'],
    ];

    $types = [];
    if (!empty($job['payment_methods'])) {
        $pm = json_decode($job['payment_methods'], true);
        if (is_array($pm) && !empty($pm['types']) && is_array($pm['types'])) {
            foreach ($pm['types'] as $k => $v) {
                if (isset($allowed[$k])) $types[$k] = $v;
            }
        }
    }
    // Фолбек към старите две колони
    if (!$types) {
        if (!empty($job['price_per_day']))    $types['day']    = (float)$job['price_per_day'];
        if (!empty($job['price_per_square'])) $types['square'] = (float)$job['price_per_square'];
    }
    if (!$types) return '';

    $rows = [];
    foreach ($types as $k => $v) {
        $def = $allowed[$k] ?? [$k, 'fa-tag', ''];
        $val = is_numeric($v) ? number_format((float)$v, 2, '.', '') : (string)$v;
        $rows[] = '<div class="job-pay-item" data-pay="'.htmlspecialchars($k).'">'
                . '<i class="fas '.htmlspecialchars($def[1]).'"></i>'
                . '<strong>'.htmlspecialchars($def[0]).':</strong> '
                . htmlspecialchars($val.$def[2])
                . '</div>';
    }
    return '<div class="payment-list">'.implode('', $rows).'</div>';
}

$ownerUsername = htmlspecialchars($_SESSION['user']['username'] ?? '');

/* Кеш на профилни снимки за 'seek' */
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
    // Корица
    if ($job['job_type'] === 'seek') {
        $image = $userImages[$job['user_id']] ?? '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
    } else {
        $images = json_decode($job['images'] ?? '[]', true);
        $image  = (is_array($images) && !empty($images[0]))
                  ? '../' . $images[0]
                  : '../img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png';
    }

    // Етикет на професията за заглавие
    $profKey    = (string)($job['profession'] ?? '');
    $profession = job_label($profKey) ?: 'Обява';

    // Кратко описание – показваме само ако е реално въведено, а не автоматичен ключ/етикет
    $rawTitle = trim((string)($job['title'] ?? ''));
    $subtitle = '';
    if ($rawTitle !== '') {
        $rawLower   = mb_strtolower($rawTitle, 'UTF-8');
        $keyLower   = mb_strtolower($profKey, 'UTF-8');
        $labelLower = mb_strtolower($profession, 'UTF-8');

        $looksLikeKey = (bool)preg_match('/^[a-z0-9_-]+$/', $rawTitle); // няма интервали → вероятно системен ключ

        if ($rawLower !== $keyLower && $rawLower !== $labelLower && !$looksLikeKey) {
            $subtitle = $rawTitle;
        }
    }

    $place = $job['city'] ?: $job['location'];

    echo '<div class="job-card" data-job-id="'.(int)$job['id'].'">';

      echo '<img class="job-card-img" src="'.htmlspecialchars($image).'" alt="Снимка">';

      echo '<div class="job-card-info">';
        // Заглавие = Професия (винаги)
        echo '<h3>'. htmlspecialchars($profession) .'</h3>';
        // Подзаглавие = Кратко описание (само ако е валидно и не прилича на ключ)
        if ($subtitle !== '') {
            echo '<p class="job-subtitle">'. htmlspecialchars($subtitle) .'</p>';
        }

        echo '<p class="job-username"><i class="fas fa-user"></i> ' . $ownerUsername . '</p>';

        echo renderPayments($job);

        if (!empty($place)) {
            echo '<p class="job-meta-item"><i class="fas fa-map-marker-alt"></i><strong>Град:</strong> '. htmlspecialchars($place) .'</p>';
        }
        if ((int)$job['is_company'] === 1) {
            echo '<span class="badge-company" title="Фирмена обява">Фирма</span>';
        }
      echo '</div>';

      echo '<aside class="job-side" onClick="event.stopPropagation();">';
        if (isset($_SESSION['user'])) {
            $isFavorite = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
            $heartIcon  = $isFavorite ? '../img/heart-filled.png' : '../img/heart-outline.png';
            $heartAlt   = $isFavorite ? 'Премахни от любими' : 'Добави в любими';
            echo '<div class="favorite-icon">';
            echo '  <img src="'.$heartIcon.'" alt="'.$heartAlt.'" title="'.$heartAlt.'" data-job-id="'.(int)$job['id'].'" class="favorite-heart'.($isFavorite?' favorited':'').'">';
            echo '</div>';
        }

        echo '<div class="job-rating">'. getJobAverageRating($job['id'], true) .'</div>';

        echo '<a href="edit_job.php?id='.(int)$job['id'].'" class="edit-btn">Редактирай</a>';

        echo '<form method="POST" action="delete_job.php" class="delete-form" onsubmit="return confirm(\'Сигурни ли сте, че искате да изтриете обявата? Тази операция е необратима.\');">';
        echo '  <input type="hidden" name="id" value="'.(int)$job['id'].'">';
        echo '  <button type="submit" class="delete-btn" title="Изтрий">';
        echo '    <img src="../img/trash-icon.png" alt="Изтрий" class="trash-icon">';
        echo '  </button>';
        echo '</form>';

      echo '</aside>';

    echo '</div>';
}
