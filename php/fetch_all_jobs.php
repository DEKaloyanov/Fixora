<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
require_once 'favorites_utils.php';
require_once __DIR__ . '/professions.php'; // $professions

$typeFilter       = $_GET['type']        ?? '';
$companyOnly      = $_GET['companyOnly'] ?? '';
$placeFilter      = trim($_GET['place']  ?? '');

$professionStr    = trim($_GET['profession'] ?? '');
$professionList   = $professionStr !== '' ? array_filter(array_map('trim', explode(',', $professionStr))) : [];

$minDay           = $_GET['minDay']   ?? '';
$maxDay           = $_GET['maxDay']   ?? '';
$minSq            = $_GET['minSq']    ?? '';
$maxSq            = $_GET['maxSq']    ?? '';

$minRating        = $_GET['minRating'] ?? '';            // 1..5
$dateFrom         = $_GET['dateFrom']  ?? '';            // YYYY-MM-DD
$dateTo           = $_GET['dateTo']    ?? '';            // YYYY-MM-DD

$sort             = $_GET['sort']      ?? 'newest';      // newest|oldest|price_day_asc|price_day_desc|price_sq_asc|price_sq_desc|rating_desc|rating_asc

$ratingsTable     = 'ratings'; // смени ако таблицата ти за оценки е с друго име

// Нуждаем ли се от JOIN към рейтингите?
$wantRatingJoin   = ($minRating !== '' && is_numeric($minRating)) || str_starts_with($sort, 'rating_');

function buildSql($withRatingJoin, $ratingsTable, $sort, &$params) {
    $select = "SELECT jobs.*";
    $from   = " FROM jobs ";
    $join   = "";
    $where  = ["1"]; // винаги истина
    $order  = "";

    // --- type ---
    global $typeFilter;
    if ($typeFilter !== '') {
        $where[] = "jobs.job_type = :job_type";
        $params[':job_type'] = $typeFilter;
    }

    // --- companyOnly ---
    global $companyOnly;
    if ($companyOnly === '1') {
        $where[] = "jobs.is_company = 1";
    }

    // --- place ---
    global $placeFilter;
    if ($placeFilter !== '') {
        $where[] = "(jobs.location LIKE :place OR jobs.city LIKE :place)";
        $params[':place'] = '%'.$placeFilter.'%';
    }

    // --- profession (множество) ---
    global $professionList;
    if (!empty($professionList)) {
        $inPh   = [];
        foreach ($professionList as $i => $k) {
            $ph = ":p$i";
            $inPh[] = $ph;
            $params[$ph] = $k;
        }
        $likeParts = [];
        foreach ($professionList as $i => $k) {
            $ph = ":plike$i";
            $likeParts[] = "jobs.professions LIKE $ph";
            $params[$ph] = '%"'.$k.'"%';
        }
        $where[] = "("
            . "jobs.profession IN (".implode(',', $inPh).")"
            . " OR (jobs.is_company = 1 AND jobs.professions IS NOT NULL AND jobs.professions <> '' AND ("
            . implode(' OR ', $likeParts)
            . "))"
            . ")";
    }

    // --- prices ---
    global $minDay, $maxDay, $minSq, $maxSq;
    if ($minDay !== '' && is_numeric($minDay)) {
        $where[] = "jobs.price_per_day >= :min_day";
        $params[':min_day'] = $minDay;
    }
    if ($maxDay !== '' && is_numeric($maxDay)) {
        $where[] = "jobs.price_per_day <= :max_day";
        $params[':max_day'] = $maxDay;
    }
    if ($minSq !== '' && is_numeric($minSq)) {
        $where[] = "jobs.price_per_square >= :min_sq";
        $params[':min_sq'] = $minSq;
    }
    if ($maxSq !== '' && is_numeric($maxSq)) {
        $where[] = "jobs.price_per_square <= :max_sq";
        $params[':max_sq'] = $maxSq;
    }

    // --- dates ---
    global $dateFrom, $dateTo;
    if ($dateFrom !== '') {
        $where[] = "DATE(jobs.created_at) >= :date_from";
        $params[':date_from'] = $dateFrom;
    }
    if ($dateTo !== '') {
        $where[] = "DATE(jobs.created_at) <= :date_to";
        $params[':date_to'] = $dateTo;
    }

    // --- rating join + filter ---
    global $minRating;
    if ($withRatingJoin) {
        $select .= ", r.avg_rating";
        $join   .= " LEFT JOIN (SELECT job_id, AVG(rating) AS avg_rating FROM {$ratingsTable} GROUP BY job_id) r ON r.job_id = jobs.id ";
        if ($minRating !== '' && is_numeric($minRating)) {
            $where[] = "r.avg_rating >= :min_rating";
            $params[':min_rating'] = (float)$minRating;
        }
    }

    // --- order by ---
    switch ($sort) {
        case 'oldest':
            $order = " ORDER BY jobs.created_at ASC";
            break;
        case 'price_day_asc':
            $order = " ORDER BY jobs.price_per_day ASC, jobs.created_at DESC";
            break;
        case 'price_day_desc':
            $order = " ORDER BY jobs.price_per_day DESC, jobs.created_at DESC";
            break;
        case 'price_sq_asc':
            $order = " ORDER BY jobs.price_per_square ASC, jobs.created_at DESC";
            break;
        case 'price_sq_desc':
            $order = " ORDER BY jobs.price_per_square DESC, jobs.created_at DESC";
            break;
        case 'rating_asc':
            // NULL последни
            if ($withRatingJoin) {
                $order = " ORDER BY (r.avg_rating IS NULL), r.avg_rating ASC, jobs.created_at DESC";
            } else {
                $order = " ORDER BY jobs.created_at DESC";
            }
            break;
        case 'rating_desc':
            // NULL последни
            if ($withRatingJoin) {
                $order = " ORDER BY (r.avg_rating IS NULL), r.avg_rating DESC, jobs.created_at DESC";
            } else {
                $order = " ORDER BY jobs.created_at DESC";
            }
            break;
        case 'newest':
        default:
            $order = " ORDER BY jobs.created_at DESC";
            break;
    }

    $sql = $select . $from . $join . " WHERE " . implode(' AND ', $where) . $order;
    return $sql;
}

// 1) опит с рейтинг JOIN (ако трябва)
$params = [];
$sql    = buildSql($wantRatingJoin, $ratingsTable, $sort, $params);
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
} catch (Throwable $e) {
    // fallback без рейтинг JOIN, ако таблицата ratings липсва/гърми
    if ($wantRatingJoin) {
        $params = [];
        $sql    = buildSql(false, $ratingsTable, $sort, $params);
        $stmt   = $conn->prepare($sql);
        $stmt->execute($params);
    } else {
        throw $e;
    }
}

$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($jobs as $job) {
    // Корица
    if ($job['job_type'] === 'seek') {
        $stmtUser = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
        $stmtUser->execute([(int)$job['user_id']]);
        $u = $stmtUser->fetch(PDO::FETCH_ASSOC);

        $cover = (!empty($u['profile_image']) && file_exists(__DIR__ . '/../uploads/' . $u['profile_image']))
            ? 'uploads/' . $u['profile_image']
            : 'img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
    } else {
        $imgs  = json_decode($job['images'] ?? '[]', true);
        $cover = (!empty($imgs[0])) ? $imgs[0] : 'img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png';
    }

    // Етикет за професия
    $singleKey   = (string)($job['profession'] ?? '');
    $singleLabel = $professions[$singleKey] ?? ucfirst($singleKey);

    echo '<div class="job-card" onclick="handleCardClick(event,' . (int)$job['id'] . ')">';

    // Любими (ако е логнат)
    if (isset($_SESSION['user'])) {
        $isFav    = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
        $heartSrc = $isFav ? '../img/heart-filled.png' : '../img/heart-outline.png';
        $heartAlt = $isFav ? 'Премахни от любими' : 'Добави в любими';
        echo '<div class="favorite-icon">';
        echo '<img src="' . $heartSrc . '" alt="' . $heartAlt . '" title="' . $heartAlt . '" data-job-id="' . (int)$job['id'] . '" class="favorite-heart' . ($isFav ? ' favorited' : '') . '">';
        echo '</div>';
    }

    echo '<img class="job-card-img" src="../' . htmlspecialchars($cover) . '" alt="Снимка">';

    echo '<div class="job-card-info">';
    echo '  <div class="job-rating">' . getJobAverageRating($job['id'], true) . '</div>';

    if ((int)$job['is_company'] === 1) {
        echo '<span class="badge-company" title="Фирмена обява">Фирма</span>';
    }
    echo '<h3>' . htmlspecialchars($singleLabel) . '</h3>';

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

    $place = $job['location'] ?: $job['city'];
    if ($place) {
        echo '<p class="job-meta-item location"><strong>Град:</strong> ' . htmlspecialchars($place) . '</p>';
    }

    echo '<p class="job-meta-item price-day"><strong>Цена на ден:</strong> ' . ($job['price_per_day'] ? htmlspecialchars($job['price_per_day']) . ' лв' : '-') . '</p>';
    echo '<p class="job-meta-item price-square"><strong>Цена/кв.м:</strong> ' . ($job['price_per_square'] ? htmlspecialchars($job['price_per_square']) . ' лв' : '-') . '</p>';

    echo '</div>'; // .job-card-info

    if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$job['user_id']) {
        echo '<form method="GET" action="../php/edit_job.php" onClick="event.stopPropagation();">';
        echo '<input type="hidden" name="id" value="' . (int)$job['id'] . '">';
        echo '<button type="submit" class="edit-btn">Редактирай</button>';
        echo '</form>';
    }

    echo '</div>'; // .job-card
}
