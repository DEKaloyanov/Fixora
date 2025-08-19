<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
require_once __DIR__ . '/professions.php';       // $professions (лист от ключ => етикет)
require_once __DIR__ . '/professions_tree.php';  // $PROFESSIONS_TREE + get_profession_children()

/**
 * Открий таблицата за рейтинги, ако съществува.
 * Пробваме "job_ratings" и "ratings"; ако няма — връщаме null и просто ще игнорираме филтъра/сортировката по рейтинг.
 */
function detectRatingsTable(PDO $conn): ?string {
    foreach (['job_ratings', 'ratings'] as $t) {
        try {
            $conn->query("SELECT 1 FROM `$t` LIMIT 1");
            return $t;
        } catch (Throwable $e) { /* няма такава таблица */ }
    }
    return null;
}

$typeFilter       = $_GET['type']          ?? '';
$companyOnly      = $_GET['company_only']  ?? '';
$placeFilter      = trim($_GET['place']    ?? '');

$mainKey          = $_GET['main']          ?? ''; // основна категория (ключ от $PROFESSIONS_TREE)
$subKey           = $_GET['sub']           ?? ''; // подпрофесия (ключ от $professions)

$minDay           = $_GET['minDay']        ?? '';
$maxDay           = $_GET['maxDay']        ?? '';
$minSq            = $_GET['minSq']         ?? '';
$maxSq            = $_GET['maxSq']         ?? '';

$minRating        = $_GET['minRating']     ?? '';
$dateFrom         = $_GET['dateFrom']      ?? '';
$dateTo           = $_GET['dateTo']        ?? '';

$sortBy           = $_GET['sort']          ?? 'newest';

// ще добавим JOIN по рейтинг само ако е нужно и има таблица
$needsRatingJoin  = ($minRating !== '' || in_array($sortBy, ['rating_desc','rating_asc'], true));
$ratingsTable     = $needsRatingJoin ? detectRatingsTable($conn) : null;
$needsRatingJoin  = $needsRatingJoin && $ratingsTable !== null;

// базов SELECT
$sql  = "SELECT j.*";
if ($needsRatingJoin) {
    $sql .= ", COALESCE(r.avg_rating, 0) AS avg_rating";
}
$sql .= " FROM jobs j";

// JOIN за рейтинг (ако е наличен)
if ($needsRatingJoin) {
    $sql .= " LEFT JOIN (
                SELECT job_id, AVG(rating) AS avg_rating
                FROM `$ratingsTable`
                GROUP BY job_id
              ) r ON r.job_id = j.id";
}

$where  = [];
$params = [];

/* Тип */
if ($typeFilter !== '' && in_array($typeFilter, ['offer','seek'], true)) {
    $where[] = "j.job_type = :job_type";
    $params[':job_type'] = $typeFilter;
}

/* Само фирми */
if ($companyOnly === '1') {
    $where[] = "j.is_company = 1";
}

/* Град/място: търсим и в location, и в city (LIKE за удобство) */
if ($placeFilter !== '') {
    $where[] = "(j.location LIKE :place_like OR j.city LIKE :place_like)";
    $params[':place_like'] = '%' . $placeFilter . '%';
}

/* Професии: ако има sub (подпрофесия) — тя има предимство; иначе main -> всички нейни деца */
if ($subKey !== '') {
    // match в единичната колона
    $w = "(j.profession = :subKey";
    $params[':subKey'] = $subKey;

    // и в JSON-а за фирми
    $w .= " OR (j.is_company = 1 AND j.professions IS NOT NULL AND j.professions <> '' AND j.professions LIKE :subKey_like))";
    $params[':subKey_like'] = '%"'.$subKey.'"%';
    $where[] = $w;

} elseif ($mainKey !== '' && isset($PROFESSIONS_TREE[$mainKey])) {
    // взимаме децата за тази основна категория
    $children = get_profession_children($mainKey);

    if ($children) {
        // в единичната колона:
        $inPlaceholders = [];
        foreach ($children as $i => $ck) {
            $ph = ':prof_' . $i;
            $inPlaceholders[] = $ph;
            $params[$ph] = $ck;
        }
        $w = "(j.profession IN (" . implode(',', $inPlaceholders) . ")";

        // и в JSON-а за фирми:
        // ще направим OR група от LIKE условия за всяко дете
        $jsonLikes = [];
        foreach ($children as $i => $ck) {
            $ph = ':json_' . $i;
            $jsonLikes[] = "j.professions LIKE $ph";
            $params[$ph] = '%"'.$ck.'"%';
        }
        $w .= " OR (j.is_company = 1 AND j.professions IS NOT NULL AND j.professions <> '' AND (" . implode(' OR ', $jsonLikes) . ")))";
        $where[] = $w;
    }
}

/* Диапазони: цена на ден, цена на кв.м */
if ($minDay !== '') {
    $where[] = "j.price_per_day IS NOT NULL AND j.price_per_day >= :minDay";
    $params[':minDay'] = (float)$minDay;
}
if ($maxDay !== '') {
    $where[] = "j.price_per_day IS NOT NULL AND j.price_per_day <= :maxDay";
    $params[':maxDay'] = (float)$maxDay;
}
if ($minSq !== '') {
    $where[] = "j.price_per_square IS NOT NULL AND j.price_per_square >= :minSq";
    $params[':minSq'] = (float)$minSq;
}
if ($maxSq !== '') {
    $where[] = "j.price_per_square IS NOT NULL AND j.price_per_square <= :maxSq";
    $params[':maxSq'] = (float)$maxSq;
}

/* Дата: created_at между [from 00:00:00, to 23:59:59] */
if ($dateFrom !== '') {
    $where[] = "j.created_at >= :dateFrom";
    $params[':dateFrom'] = $dateFrom . ' 00:00:00';
}
if ($dateTo !== '') {
    $where[] = "j.created_at <= :dateTo";
    $params[':dateTo'] = $dateTo . ' 23:59:59';
}

/* Рейтинг (мин): само ако имаме таблица с рейтинги */
if ($minRating !== '' && $needsRatingJoin) {
    $where[] = "COALESCE(r.avg_rating, 0) >= :minRating";
    $params[':minRating'] = (float)$minRating;
}

/* сглоби WHERE */
if ($where) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

/* Сортиране */
switch ($sortBy) {
    case 'oldest':
        $sql .= " ORDER BY j.created_at ASC";
        break;
    case 'price_day_asc':
        $sql .= " ORDER BY j.price_per_day IS NULL, j.price_per_day ASC, j.created_at DESC";
        break;
    case 'price_day_desc':
        $sql .= " ORDER BY j.price_per_day IS NULL, j.price_per_day DESC, j.created_at DESC";
        break;
    case 'price_sq_asc':
        $sql .= " ORDER BY j.price_per_square IS NULL, j.price_per_square ASC, j.created_at DESC";
        break;
    case 'price_sq_desc':
        $sql .= " ORDER BY j.price_per_square IS NULL, j.price_per_square DESC, j.created_at DESC";
        break;
    case 'rating_desc':
        if ($needsRatingJoin) {
            $sql .= " ORDER BY avg_rating DESC, j.created_at DESC";
            break;
        }
        // ако няма таблица за рейтинг → fallthrough към newest
    case 'rating_asc':
        if ($needsRatingJoin) {
            $sql .= " ORDER BY avg_rating ASC, j.created_at DESC";
            break;
        }
        // fallthrough
    case 'newest':
    default:
        $sql .= " ORDER BY j.created_at DESC";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Рендер карти */
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

    // Етикет (български)
    $singleKey   = (string)($job['profession'] ?? '');
    $singleLabel = $professions[$singleKey] ?? ucfirst($singleKey);

    echo '<div class="job-card" onclick="handleCardClick(event,' . (int)$job['id'] . ')">';

    // Любими (ако е логнат)
    if (isset($_SESSION['user'])) {
        require_once 'favorites_utils.php';
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

    // Чипове за множествени професии при фирма
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
