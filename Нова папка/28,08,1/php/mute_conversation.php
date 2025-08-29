<?php
session_start();
require_once __DIR__.'db.php';
if (!isset($_SESSION['user']['id'])) { exit('Грешка: не сте влезли.'); }
$me   = (int)$_SESSION['user']['id'];
$with = (int)($_POST['with'] ?? 0);
$job  = (int)($_POST['job']  ?? 0);
$mute = (int)($_POST['mute'] ?? 0);

if ($with<=0 || $job<=0) exit('Невалидни данни.');

try{
  if ($mute) {
    $st = $conn->prepare("REPLACE INTO muted_conversations (user_id,other_user_id,job_id,muted_until) VALUES (:me,:with,:job,NULL)");
    $st->execute([':me'=>$me, ':with'=>$with, ':job'=>$job]);
  } else {
    $st = $conn->prepare("DELETE FROM muted_conversations WHERE user_id=:me AND other_user_id=:with AND job_id=:job");
    $st->execute([':me'=>$me, ':with'=>$with, ':job'=>$job]);
  }
  echo 'ok';
} catch (Throwable $e){
  http_response_code(500);
  echo 'Грешка: '.$e->getMessage();
}
