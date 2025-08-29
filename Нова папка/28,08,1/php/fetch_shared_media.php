<?php
session_start();
require_once __DIR__.'/db.php';

if (!isset($_SESSION['user']['id'])) { http_response_code(401); exit; }
$me   = (int)$_SESSION['user']['id'];
$with = isset($_GET['with']) ? (int)$_GET['with'] : 0;
$job  = isset($_GET['job'])  ? (int)$_GET['job']  : 0;
$offset = max(0, (int)($_GET['offset'] ?? 0));
$limit  = min(60, max(1, (int)($_GET['limit'] ?? 24)));

if ($with<=0 || $job<=0){ exit; }

/* Разрешение */
$ck = $conn->prepare("
  SELECT 1 FROM connections
  WHERE job_id=:job AND (
    (user1_id=:me AND user2_id=:with) OR
    (user1_id=:with AND user2_id=:me)
  ) LIMIT 1
");
$ck->execute([':job'=>$job, ':me'=>$me, ':with'=>$with]);
if (!$ck->fetchColumn()) { exit; }

$q = $conn->prepare("
 SELECT id, image_path, thumb_path, created_at
 FROM messages
 WHERE job_id=:job AND message_type=1
   AND (
      (sender_id=:me AND receiver_id=:with) OR
      (sender_id=:with AND receiver_id=:me)
   )
 ORDER BY created_at DESC, id DESC
 LIMIT :offset, :limit
");
$q->bindValue(':job', $job, PDO::PARAM_INT);
$q->bindValue(':me',  $me,  PDO::PARAM_INT);
$q->bindValue(':with', $with, PDO::PARAM_INT);
$q->bindValue(':offset', $offset, PDO::PARAM_INT);
$q->bindValue(':limit',  $limit, PDO::PARAM_INT);
$q->execute();
$rows = $q->fetchAll(PDO::FETCH_ASSOC);

function esc($s){ return htmlspecialchars($s??'', ENT_QUOTES,'UTF-8'); }

foreach ($rows as $r){
  $img  = '../' . ltrim($r['image_path'],'/');
  $thumb= '../' . ltrim(($r['thumb_path'] ?: $r['image_path']),'/');
  echo '<a class="cp-card" href="'.esc($img).'" target="_blank" rel="noopener">';
  echo   '<img src="'.esc($thumb).'" alt="image">';
  echo   '<div class="cp-meta">'.esc(date('d.m.Y H:i', strtotime($r['created_at']))).'</div>';
  echo '</a>';
}
