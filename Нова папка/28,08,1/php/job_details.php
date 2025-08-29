<?php
require 'db.php';
require_once 'rating_utils.php';
require_once 'favorites_utils.php';
require_once __DIR__ . '/label_utils.php';
session_start();

$job_id = $_GET['id'] ?? $_GET['job_id'] ?? null;
if (!$job_id) { echo "Липсва ID на обявата."; exit; }
$id = (int)$job_id;

$stmt = $conn->prepare("
  SELECT j.*,
         u.username,
         u.profile_image,
         u.ime, u.familiq
  FROM jobs j
  JOIN users u ON j.user_id = u.id
  WHERE j.id = ?
  LIMIT 1
");
$stmt->execute([$id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$job) { echo "Обявата не е намерена."; exit; }

$images = json_decode($job['images'] ?? '[]', true) ?: [];

/* етикети */
$profKey = (string)($job['profession'] ?? '');
$profLbl = job_label($profKey) ?: 'Обява';
$title   = isset($job['title']) && $job['title'] !== '' ? $job['title'] : $profLbl;

/* главно изображение / аватар */
$defaultOffer  = '../img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png';
$defaultAvatar = '../img/ChatGPT Image Aug 6, 2025, 03_15_39 PM.png';
$ownerAvatar = (!empty($job['profile_image']) && file_exists(__DIR__ . '/../uploads/' . $job['profile_image']))
  ? '../uploads/' . $job['profile_image']
  : $defaultAvatar;

/* галерия */
$galleryImages = [];
if ($job['job_type'] === 'seek') {
  $galleryImages = [$ownerAvatar];
} else {
  if (!empty($images)) {
    foreach ($images as $p) $galleryImages[] = '../' . ltrim($p, '/');
  } else {
    $galleryImages = [$defaultOffer];
  }
}
$mainImage = $galleryImages[0] ?? $defaultOffer;

/* плащания – чипове */
function render_payment_chips(array $job): string {
  $pm = [];
  if (!empty($job['payment_methods'])) {
    $decoded = json_decode($job['payment_methods'], true);
    if (is_array($decoded)) $pm = $decoded;
  }
  $types = $pm['types'] ?? [];

  if (!$types) {
    if (!empty($job['price_per_day']))    $types['day']    = (float)$job['price_per_day'];
    if (!empty($job['price_per_square'])) $types['square'] = (float)$job['price_per_square'];
  }
  if (!$types) return '';

  $labels = [
    'day'            => ['Надник',            'fa-coins',          ' лв/ден'],
    'square'         => ['Цена/кв.м',         'fa-ruler-combined', ' лв/кв.м'],
    'hour'           => ['Цена на час',       'fa-clock',          ' лв/час'],
    'project'        => ['Цена за проект',    'fa-briefcase',      ' лв/проект'],
    'linear'         => ['Цена/л.м',          'fa-ruler-horizontal',' лв/л.м'],
    'piece'          => ['Цена/бр.',          'fa-hashtag',        ' лв/бр.'],
    'per_point'      => ['Ел. точка',         'fa-plug',           ' лв/бр.'],
    'per_fixture'    => ['ВиК арматура',      'fa-faucet',         ' лв/бр.'],
    'per_window'     => ['Прозорец',          'fa-border-none',    ' лв/бр.'],
    'per_door'       => ['Врата',             'fa-door-closed',    ' лв/бр.'],
    'per_m3'         => ['Обем',              'fa-cube',           ' лв/м³'],
    'per_ton'        => ['Тонаж',             'fa-weight-hanging', ' лв/тон'],
    'tile_m2'        => ['Плочки',            'fa-th',             ' лв/м²'],
    'plaster_m2'     => ['Шпакловка/мазилка', 'fa-align-left',     ' лв/м²'],
    'paint_m2'       => ['Боядисване',        'fa-paint-roller',   ' лв/м²'],
    'insulation_m2'  => ['Изолация',          'fa-layer-group',    ' лв/м²'],
    'callout_fee'    => ['Такса посещение',   'fa-taxi',           ' лв'],
    'min_charge'     => ['Мин. такса',        'fa-euro-sign',      ' лв'],
  ];

  $out = [];
  foreach ($types as $k => $v) {
    $def = $labels[$k] ?? [$k, 'fa-tag', ''];
    $val = is_numeric($v) ? number_format((float)$v, 2, '.', '') : (string)$v;
    $out[] = '<span class="jd-chip"><i class="fas ' . $def[1] . '"></i>'
           . htmlspecialchars($def[0]) . ': <strong>' . htmlspecialchars($val . $def[2]) . '</strong></span>';
  }

  return '<div class="jd-chips">' . implode('', $out) . '</div>';
}

/* безопасен текст */
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

/* град/локация */
$place = $job['city'] ?: $job['location'];
$region= $job['region'] ?? '';

/* фирма (ако има JSON) */
$company = null;
if (!empty($job['company_json'])) {
  $tmp = json_decode($job['company_json'], true);
  if (is_array($tmp)) $company = $tmp;
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
  <meta charset="UTF-8">
  <title><?= esc($title) ?> — Детайли за обявата | Fixora-Build</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/job_details.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<?php if (file_exists(__DIR__.'/navbar.php')) include 'navbar.php'; ?>

<main class="jd-wrap">
  <div class="jd-topbar">
    <a href="javascript:history.back()" class="jd-back"><i class="fas fa-arrow-left"></i> Назад</a>
    <button id="jdCopyLink" class="jd-copy" data-url="<?= esc((isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>">
      <i class="fas fa-link"></i> Копирай линк
    </button>
  </div>

  <div class="jd-title-row">
    <h1 class="jd-title"><?= esc($title) ?></h1>
    <div class="jd-title-rt">
      <span class="jd-type <?= $job['job_type']==='offer' ? 'offer' : 'seek' ?>">
        <?= $job['job_type']==='offer' ? 'Предлагам работа' : 'Търся работа' ?>
      </span>
      <?php if (isset($_SESSION['user'])):
        $isFavorite = isJobFavorite($conn, $_SESSION['user']['id'], $job['id']);
        $icon = $isFavorite ? '../img/heart-filled.png' : '../img/heart-outline.png';
        $alt  = $isFavorite ? 'Премахни от любими' : 'Добави в любими';
      ?>
        <img class="favorite-heart jd-heart-inline"
             data-job-id="<?= (int)$job['id'] ?>"
             src="<?= esc($icon) ?>"
             alt="<?= esc($alt) ?>"
             title="<?= esc($alt) ?>">
      <?php endif; ?>
    </div>
  </div>

  <div class="jd-meta">
    <?php if (!empty($region)): ?>
      <span class="jd-meta-item"><i class="fas fa-map"></i> <?= esc($region) ?></span>
    <?php endif; ?>
    <?php if (!empty($place)): ?>
      <span class="jd-meta-item"><i class="fas fa-map-marker-alt"></i> <?= esc($place) ?></span>
    <?php endif; ?>
    <span class="jd-meta-item"><i class="far fa-calendar-alt"></i> Публикувана: <?= esc(date('d.m.Y', strtotime($job['created_at'] ?? 'now'))) ?></span>
    <span class="jd-meta-item">
      <i class="fas fa-user"></i>
      Собственик:
      <a href="public_profile.php?id=<?= (int)$job['user_id'] ?>"><?= esc($job['username']) ?></a>
    </span>
  </div>

  <section class="jd-grid">
    <!-- Галерия -->
    <div class="jd-gallery" id="jdGallery" data-images='<?= esc(json_encode($galleryImages, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)) ?>'>
      <div class="jd-main-img">
        <?php if (count($galleryImages) > 1): ?>
          <button class="jd-g-nav jd-g-prev" type="button" aria-label="Предишно"><span></span></button>
        <?php endif; ?>

        <img id="jdMainImage" src="<?= esc($mainImage) ?>" alt="Обява" data-index="0">

        <?php if (count($galleryImages) > 1): ?>
          <button class="jd-g-nav jd-g-next" type="button" aria-label="Следващо"><span></span></button>
        <?php endif; ?>
      </div>

      <?php if (!empty($galleryImages)): ?>
        <div class="jd-thumbs">
          <?php foreach ($galleryImages as $i => $src): ?>
            <img class="jd-thumb <?= $i===0 ? 'active' : '' ?>"
                 src="<?= esc($src) ?>"
                 alt="thumb"
                 data-index="<?= (int)$i ?>"
                 data-src="<?= esc($src) ?>">
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Детайли/описание/цени -->
    <aside class="jd-side">
      <?php
        $chips = render_payment_chips($job);
        if ($chips) {
          echo '<h3 class="jd-side-h">Заплащане</h3>';
          echo $chips;
        }
      ?>

      <?php if (!empty($job['description'])): ?>
        <h3 class="jd-side-h">Описание</h3>
        <div class="jd-desc"><?= nl2br(esc($job['description'])) ?></div>
      <?php endif; ?>

      <?php if (!empty($job['work_status']) && $job['work_status']==='team' && !empty($job['team_size'])): ?>
        <h3 class="jd-side-h">Екип</h3>
        <p><strong>Брой:</strong> <?= (int)$job['team_size'] ?></p>
        <?php
          $members = [];
          if (!empty($job['team_members'])) {
            $arr = json_decode($job['team_members'], true);
            if (is_array($arr)) $members = $arr;
          }
          if ($members) echo '<p><strong>Имена:</strong> ' . esc(implode(', ', $members)) . '</p>';
        ?>
      <?php endif; ?>

      <?php if (is_array($company) && (!empty($company['name']) || !empty($company['logo']))): ?>
        <h3 class="jd-side-h">Фирма</h3>
        <div class="jd-owner-card">
          <?php if (!empty($company['logo'])): ?>
            <img src="<?= esc('../'.ltrim($company['logo'],'/')) ?>" alt="Лого" class="jd-owner-avatar">
          <?php else: ?>
            <img src="<?= esc($ownerAvatar) ?>" alt="Потребител" class="jd-owner-avatar">
          <?php endif; ?>
          <div>
            <div class="jd-owner-name"><?= esc($company['name'] ?? '') ?></div>
            <?php if (!empty($company['contacts']['website'])): ?>
              <a class="jd-owner-link" href="<?= esc($company['contacts']['website']) ?>" target="_blank" rel="noopener">Уебсайт</a>
            <?php endif; ?>
            <?php if (!empty($company['contacts']['phone'])): ?>
              <div class="jd-muted"><i class="fas fa-phone"></i> <?= esc($company['contacts']['phone']) ?></div>
            <?php endif; ?>
            <?php if (!empty($company['contacts']['email'])): ?>
              <div class="jd-muted"><i class="fas fa-envelope"></i> <?= esc($company['contacts']['email']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      <?php else: ?>
        <div class="jd-owner-card">
          <img src="<?= esc($ownerAvatar) ?>" alt="Потребител" class="jd-owner-avatar">
          <div>
            <div class="jd-owner-name"><?= esc(($job['ime'] ?: '') . ' ' . ($job['familiq'] ?: '')) ?></div>
            <a class="jd-owner-link" href="public_profile.php?id=<?= (int)$job['user_id'] ?>">Виж профила</a>
          </div>
        </div>
      <?php endif; ?>

      <div class="jd-rating-summary">
        <?= getJobAverageRating($job['id'], true) ?>
      </div>
    </aside>
  </section>

  <section class="jd-reviews">
    <h3>⭐ Оценки и коментари</h3>
    <?php
      $ratings = getRatingsForJob($job['id']);
      if (!$ratings || count($ratings) === 0) {
        echo '<p class="jd-muted">Все още няма оценки за тази обява.</p>';
      } else {
        foreach ($ratings as $r) {
          echo '<article class="jd-review">';
            echo '<header class="jd-review-h">';
              echo '<strong>'.esc($r['ime'].' '.$r['familiq']).'</strong>';
              echo '<span class="jd-review-score">'.number_format((float)$r['rating'], 2).'/5</span>';
            echo '</header>';
            if (!empty($r['comment'])) {
              echo '<div class="jd-review-txt"><em>'.nl2br(esc($r['comment'])).'</em></div>';
            }
          echo '</article>';
        }
      }
    ?>
  </section>
</main>

<!-- Лайтбокс -->
<div id="jdLightbox" class="jd-lb hidden" aria-hidden="true">
  <div class="jd-lb-backdrop"></div>
  <div class="jd-lb-shell" role="dialog" aria-modal="true" aria-label="Преглед на изображение">
    <div class="jd-lb-toolbar">
      <button class="jd-lb-btn jd-lb-prev" type="button" title="Предишно (←)">‹</button>
      <button class="jd-lb-btn jd-lb-next" type="button" title="Следващо (→)">›</button>
      <span class="jd-lb-flex"></span>
      <button class="jd-lb-btn jd-lb-zoom-out" type="button" title="Намали (−)">−</button>
      <button class="jd-lb-btn jd-lb-zoom-in"  type="button" title="Увеличи (+)">+</button>
      <button class="jd-lb-btn jd-lb-zoom-reset" type="button" title="100%">100%</button>
      <a class="jd-lb-btn jd-lb-download" id="jdLbDownload" title="Свали" download>⭳</a>
      <button class="jd-lb-btn jd-lb-close" type="button" title="Затвори (Esc)">✕</button>
    </div>
    <div class="jd-lb-stage" id="jdLbStage">
      <img id="jdLbImage" src="" alt="Голямо изображение">
    </div>
  </div>
</div>

<script src="../js/job_details.js?v=<?= time() ?>" defer></script>
<script src="../js/favorites.js" defer></script>
</body>
</html>
