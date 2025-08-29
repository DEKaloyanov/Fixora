<?php
session_start();
require 'db.php';
require_once __DIR__ . '/professions.php'; // за етикет на професията

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    exit;
}

$uid = (int)$_SESSION['user']['id'];

$q = $conn->prepare("
  SELECT id, title, profession, city, location, start_date, end_date, description, images_json, cover_index, created_at
  FROM project_history
  WHERE user_id = :uid
  ORDER BY created_at DESC, id DESC
");
$q->execute([':uid'=>$uid]);
$rows = $q->fetchAll(PDO::FETCH_ASSOC);

function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

if (!$rows) {
    // празно: показваме дискретен хедър
    echo '<h2>Предишни дейности</h2><p style="color:#6b7a90">Все още нямате добавени предишни проекти.</p>';
    exit;
}

echo '<h2>Предишни дейности</h2>';
echo '<div class="history-list">';
foreach ($rows as $r){
  $imgs = json_decode($r['images_json'] ?? '[]', true) ?: [];
  $coverIdx = (int)($r['cover_index'] ?? 0);
  $cover = isset($imgs[$coverIdx]) ? $imgs[$coverIdx] : (isset($imgs[0]) ? $imgs[0] : 'img/ChatGPT Image Aug 6, 2025, 03_15_37 PM.png');

  // Пътищата са застраницата /php/ -> добавяме ../
  $coverUrl = (strpos($cover, 'uploads/')===0 || strpos($cover, 'img/')===0) ? '../'.$cover : esc($cover);

  $profKey = (string)($r['profession'] ?? '');
  $profLbl = $professions[$profKey] ?? ucfirst($profKey);

  echo '<article class="history-card">';
  echo '  <img class="history-cover" src="'.$coverUrl.'" alt="cover">';
  echo '  <div class="history-info">';
  echo '    <h3>'.esc($r['title']).'</h3>';
  echo '    <div class="history-meta">';
  echo '      <span class="chip">'.esc($profLbl).'</span>';
  if (!empty($r['city']))    echo ' <span class="chip">'.esc($r['city']).'</span>';
  if (!empty($r['location']))echo ' <span class="chip">'.esc($r['location']).'</span>';
  if (!empty($r['start_date']) || !empty($r['end_date'])) {
      $range = esc($r['start_date'] ?: '—').' → '.esc($r['end_date'] ?: '—');
      echo ' <span class="chip">'.$range.'</span>';
  }
  echo '    </div>';
  if (!empty($r['description'])) {
     $desc = nl2br(esc($r['description']));
     echo '  <p class="history-desc">'.$desc.'</p>';
  }

  // мини-галерия
  if (count($imgs) > 1) {
    echo '<div class="history-grid">';
    foreach ($imgs as $i => $p) {
      $u = (strpos($p, 'uploads/')===0 || strpos($p, 'img/')===0) ? '../'.$p : esc($p);
      echo '<a href="'.$u.'" target="_blank" rel="noopener"><img src="'.$u.'" alt="img"></a>';
    }
    echo '</div>';
  }

  echo '  </div>';
  echo '</article>';
}
echo '</div>';
