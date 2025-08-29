<?php
session_start();
require_once __DIR__.'db.php';
if (!isset($_SESSION['user']['id'])) { exit('Грешка: не сте влезли.'); }
$me   = (int)$_SESSION['user']['id'];
$with = (int)($_POST['with'] ?? 0);
$job  = (int)($_POST['job']  ?? 0);
$reason  = trim($_POST['reason'] ?? '');
$details = trim($_POST['details'] ?? '');
if ($with<=0 || $reason==='') exit('Невалидни данни.');

$st = $conn->prepare("INSERT INTO reports (reporter_id, reported_id, job_id, reason, details) VALUES (:me,:with,:job,:r,:d)");
$st->execute([':me'=>$me, ':with'=>$with, ':job'=>$job?:null, ':r'=>$reason, ':d'=>$details]);
echo 'ok';
