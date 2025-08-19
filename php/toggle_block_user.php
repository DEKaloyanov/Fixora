<?php
session_start();
require_once __DIR__.'db.php';
if (!isset($_SESSION['user']['id'])) { exit('Грешка: не сте влезли.'); }
$me   = (int)$_SESSION['user']['id'];
$with = (int)($_POST['with'] ?? 0);
$block= (int)($_POST['block'] ?? 0);
if ($with<=0) exit('Невалидни данни.');

try{
  if ($block) {
    $st = $conn->prepare("REPLACE INTO blocks (blocker_id, blocked_id) VALUES (:me,:with)");
    $st->execute([':me'=>$me, ':with'=>$with]);
  } else {
    $st = $conn->prepare("DELETE FROM blocks WHERE blocker_id=:me AND blocked_id=:with");
    $st->execute([':me'=>$me, ':with'=>$with]);
  }
  echo 'ok';
} catch (Throwable $e){
  http_response_code(500);
  echo 'Грешка: '.$e->getMessage();
}
