<?php
session_start();
require 'db.php';
require_once 'rating_utils.php';
require_once __DIR__ . '/favorites_utils.php';
require_once __DIR__ . '/professions.php';
require_once __DIR__ . '/label_utils.php';      // job_label()
require_once __DIR__ . '/professions_tree.php';

function detectRatingsTable(PDO $conn): ?string {
  foreach (['job_ratings','ratings'] as $t) {
    try { $conn->query("SELECT 1 FROM `$t` LIMIT 1"); return $t; }
    catch (Throwable $e) {}
  }
  return null;
}

/* Плащания – вертикален списък + ИКОНКИ (същите като в профила) */
function render_payments_row(array $job): string {
  $json = $job['payment_methods'] ?? null;
  $pm   = $json ? json_decode($json, true) : null;

  $icons = [
    'day'     => 'fa-coins',
    'square'  => 'fa-ruler-combined',
    'hour'    => 'fa-clock',
    'project' => 'fa-briefcase',
    'other'   => 'fa-tag',
    'custom'  => 'fa-tag',
  ];

  $out = '';

  $fmt = function($v){ return is_numeric($v) ? number_format((float)$v, 2, '.', '') : htmlspecialchars((string)$v); };

  if (is_array($pm) && (!empty($pm['types']) || !empty($pm['custom']))) {
    $types = $pm['types'] ?? [];

    if (isset($types['day'])) {
      $out .= '<p class="job-pay-item" data-pay="day">'
            . '<i class="fas '.$icons['day'].'"></i>'
            . '<strong>Надник:</strong> ' . $fmt($types['day']) . ' лв/ден</p>';
    }
    if (isset($types['square'])) {
      $out .= '<p class="job-pay-item" data-pay="square">'
            . '<i class="fas '.$icons['square'].'"></i>'
            . '<strong>Цена/кв.м:</strong> ' . $fmt($types['square']) . ' лв/кв.м</p>';
    }
    if (isset($types['hour'])) {
      $out .= '<p class="job-pay-item" data-pay="hour">'
            . '<i class="fas '.$icons['hour'].'"></i>'
            . '<strong>Цена на час:</strong> ' . $fmt($types['hour']) . ' лв/час</p>';
    }
    if (isset($types['project'])) {
      $out .= '<p class="job-pay-item" data-pay="project">'
            . '<i class="fas '.$icons['project'].'"></i>'
            . '<strong>Цена за проект:</strong> ' . $fmt($types['project']) . ' лв/проект</p>';
    }

    if (!empty($pm['custom']) && is_array($pm['custom'])) {
      foreach ($pm['custom'] as $c) {
        $lbl = htmlspecialchars((string)($c['label'] ?? 'Друг'));
        $pr  = (string)($c['price'] ?? '');
        if ($pr !== '') {
          $out .= '<p class="job-pay-item" data-pay="custom">'
                . '<i class="fas '.$icons['custom'].'"></i>'
                . '<strong>' . $lbl . ':</strong> ' . htmlspecialchars($pr) . ' лв</p>';
        }
      }
    }

    return $out ? '<div class="payment-list">'.$out.'</div>' : '';
  }

  // Fallback към старите колони (с иконки)
  if (!empty($job['price_per_day'])) {
    $out .= '<p class="job-pay-item" data-pay="day">'
          . '<i class="fas '.$icons['day'].'"></i>'
          . '<strong>Надник:</strong> ' . $fmt($job['price_per_day']) . ' лв/ден</p>';
  }
  if (!empty($job['price_per_square'])) {
    $out .= '<p class="job-pay-item" data-pay="square">'
          . '<i class="fas '.$icons['square'].'"></i>'
          . '<strong>Цена/кв.м:</strong> ' . $fmt($job['price_per_square']) . ' лв/кв.м</p>';
  }

  return $out ? '<div class="payment-list">'.$out.'</div>' : '';
}

/* ================== ПАРАМЕТРИ ================== */
$typeFilter  = $_GET['type']         ?? '';
$companyOnly = $_GET['company_only'] ?? '';
$placeFilter = trim($_GET['place']   ?? '');

$mainKey     = $_GET['main']         ?? '';
$subKey      = $_GET['sub']          ?? '';

$minDay      = $_GET['minDay']       ?? '';
$maxDay      = $_GET['maxDay']       ?? '';
$minSq       = $_GET['minSq']        ?? '';
$maxSq       = $_GET['maxSq']        ?? '';

$minRating   = $_GET['minRating']    ?? '';
$dateFrom    = $_GET['dateFrom']     ?? '';
$dateTo      = $_GET['dateTo']       ?? '';

$sortBy      = $_GET['sort']         ?? 'newest';

/* Нужен ли е join по рейтинг */
$needsRatingJoin = ($minRating !== '' || in_array($sortBy, ['rating_desc','rating_asc'], true));
$ratingsTable    = $needsRatingJoin ? detectRatingsTable($conn) : null;
$needsRatingJoin = $needsRatingJoin && $ratingsTable !== null;

/* ================== ЗАЯВКА ================== */
$sql  = "SELECT j.*, u.username";
if ($needsRatingJoin) $sql .= ", COALESCE(r.avg_rating, 0) AS avg_rating";
$sql .= " FROM jobs j
          JOIN users u ON u.id = j.user_id";

if ($needsRatingJoin) {
  $sql .= " LEFT JOIN (
              SELECT job_id, AVG(rating) AS avg_rating
              FROM `$ratingsTable`
              GROUP BY job_id
            ) r ON r.job_id = j.id";
}

$where  = [];
$params = [];

/* Филтри */
if ($typeFilter !== '' && in_array($typeFilter, ['offer','seek'], true)) {
  $where[] = "j.job_type = :job_type"; $params[':job_type'] = $typeFilter;
}
if ($companyOnly === '1') {
  $where[] = "j.is_company = 1";
}
if ($placeFilter !== '') {
  $where[] = "(j.location LIKE :place_like OR j.city LIKE :place_like)";
  $params[':place_like'] = '%'.$placeFilter.'%';
}
if ($subKey !== '') {
  $w = "(j.profession = :subKey";
  $params[':subKey'] = $subKey;
  $w .= " OR (j.is_company = 1 AND j.professions IS NOT NULL AND j.professions <> '' AND j.professions LIKE :subKey_like))";
  $params[':subKey_like'] = '%"'.$subKey.'"%';
  $where[] = $w;
} elseif ($mainKey !== '' && isset($PROFESSIONS_TREE[$mainKey])) {
  $children = get_profession_children($mainKey);
  if ($children) {
    $in = [];
    foreach ($children as $i => $ck) {
      $ph = ':prof_'.$i; $in[] = $ph; $params[$ph] = $ck;
    }
    $w = "(j.profession IN (".implode(',', $in).")";
    $jsonLikes = [];
    foreach ($children as $i => $ck) {
      $ph = ':json_'.$i; $jsonLikes[] = "j.professions LIKE $ph"; $params[$ph] = '%"'.$ck.'"%';
    }
    $w .= " OR (j.is_company = 1 AND j.professions IS NOT NULL AND j.professions <> '' AND (".implode(' OR ', $jsonLikes).")))";
    $where[] = $w;
  }
}

/* Диапазони по старите колони */
if ($minDay !== '') { $where[] = "j.price_per_day IS NOT NULL AND j.price_per_day >= :minDay"; $params[':minDay'] = (float)$minDay; }
if ($maxDay !== '') { $where[] = "j.price_per_day IS NOT NULL AND j.price_per_day <= :maxDay"; $params[':maxDay'] = (float)$maxDay; }
if ($minSq  !== '') { $where[] = "j.price_per_square IS NOT NULL AND j.price_per_square >= :minSq"; $params[':minSq']  = (float)$minSq; }
if ($maxSq  !== '') { $where[] = "j.price_per_square IS NOT NULL AND j.price_per_square <= :maxSq"; $params[':maxSq']  = (float)$maxSq; }

/* Дати */
if ($dateFrom !== '') { $where[] = "j.created_at >= :dateFrom"; $params[':dateFrom'] = $dateFrom.' 00:00:00'; }
if ($dateTo   !== '') { $where[] = "j.created_at <= :dateTo";   $params[':dateTo']   = $dateTo.' 23:59:59'; }

/* Рейтинг */
if ($minRating !== '' && $needsRatingJoin) {
  $where[] = "COALESCE(r.avg_rating, 0) >= :minRating";
  $params[':minRating'] = (float)$minRating;
}

if ($where) $sql .= " WHERE ".implode(' AND ', $where);

/* Сортиране */
switch ($sortBy) {
  case 'oldest':        $sql .= " ORDER BY j.created_at ASC"; break;
  case 'price_day_asc': $sql .= " ORDER BY j.price_per_day IS NULL, j.price_per_day ASC, j.created_at DESC"; break;
  case 'price_day_desc':$sql .= " ORDER BY j.price_per_day IS NULL, j.price_per_day DESC, j.created_at DESC"; break;
  case 'price_sq_asc':  $sql .= " ORDER BY j.price_per_square IS NULL, j.price_per_square ASC, j.created_at DESC"; break;
  case 'price_sq_desc': $sql .= " ORDER BY j.price_per_square IS NULL, j.price_per_square DESC, j.created_at DESC"; break;
  case 'rating_desc':   if ($needsRatingJoin) { $sql .= " ORDER BY avg_rating DESC, j.created_at DESC"; break; }
  case 'rating_asc':    if ($needsRatingJoin) { $sql .= " ORDER BY avg_rating ASC,  j.created_at DESC"; break; }
  case 'newest':
  default:              $sql .= " ORDER BY j.created_at DESC"; break;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ================== РЕНДЕР ================== */
foreach ($jobs as $job) {
  // Корица
  if ($job['job_type'] === 'seek') {
    $stmtUserImg = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmtUserImg->execute([(int)$job['user_id']]);
    $uimg = $stmtUserImg->fetch(PDO::FETCH_ASSOC);
    $cover = (!empty($uimg['profile_image']) && file_exists(__DIR__.'/../uploads/'.$uimg['profile_image']))
      ? 'uploads/'.$uimg['profile_image']
      : 'img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
  } else {
    $imgs  = json_decode($job['images'] ?? '[]', true);
    $cover = (!empty($imgs[0])) ? $imgs[0] : 'img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png';
  }

  $singleKey   = (string)($job['profession'] ?? '');
  $singleLabel = job_label($singleKey); // ВИНАГИ БГ
  $place       = $job['location'] ?: $job['city'];
  $ownerUser   = htmlspecialchars($job['username'] ?? '');

  echo '<div class="job-card" onclick="handleCardClick(event,'.(int)$job['id'].')">';

    // Снимка
    echo '<img class="job-card-img" src="../'.htmlspecialchars($cover).'" alt="Снимка">';

    // Ляв блок (информация)
    echo '<div class="job-card-info">';
      echo '<h3>'. htmlspecialchars($singleLabel) .'</h3>';

      // собственик
      if ($ownerUser !== '') {
        echo '<p class="job-username"><i class="fas fa-user"></i> '.$ownerUser.'</p>';
      }

      // чипове за фирма
      if ((int)$job['is_company'] === 1 && !empty($job['professions'])) {
        $list = json_decode($job['professions'], true);
        if (is_array($list) && $list) {
          echo '<div class="profession-chips">';
          foreach ($list as $k) {
            $lbl = job_label($k);
            echo '<span class="chip">'.htmlspecialchars($lbl).'</span>';
          }
          echo '</div>';
        }
      }

      // място (Град)
      if (!empty($place)) {
        echo '<p class="job-meta-item location"><i class="fas fa-map-marker-alt"></i><strong>Град:</strong> '. htmlspecialchars($place) .'</p>';
      }

      // плащания (едно под друго + иконки)
      echo render_payments_row($job);

    echo '</div>'; // .job-card-info

    // Десен стек – любими → рейтинг → редакция
    echo '<aside class="job-side" onClick="event.stopPropagation();">';

      // Любими
      if (isset($_SESSION['user'])) {
        $isFav    = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
        $heartSrc = $isFav ? '../img/heart-filled.png' : '../img/heart-outline.png';
        $heartAlt = $isFav ? 'Премахни от любими' : 'Добави в любими';
        echo '<div class="favorite-icon">';
        echo '  <img src="'.$heartSrc.'" alt="'.$heartAlt.'" title="'.$heartAlt.'" data-job-id="'.(int)$job['id'].'" class="favorite-heart'.($isFav?' favorited':'').'">';
        echo '</div>';
      }

      // Рейтинг
      echo '<div class="job-rating">'. getJobAverageRating($job['id'], true) .'</div>';

      // Редактирай за собственика
      if (isset($_SESSION['user']) && (int)$_SESSION['user']['id'] === (int)$job['user_id']) {
        echo '<a href="edit_job.php?id='.(int)$job['id'].'" class="edit-btn">Редактирай</a>';
      }

    echo '</aside>';

  echo '</div>'; // .job-card
}
