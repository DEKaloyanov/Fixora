<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = (int)$_SESSION['user']['id'];
$job_id  = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($job_id <= 0) {
    header('Location: profil.php');
    exit;
}

// вземи обявата и провери собственост
$stmt = $conn->prepare("SELECT id, user_id, images FROM jobs WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job || (int)$job['user_id'] !== $user_id) {
    // не съществува или не е твоя
    header('Location: profil.php');
    exit;
}

// изтриване на снимки (ако са качени)
$imgs = json_decode($job['images'] ?? '[]', true);
if (is_array($imgs)) {
    foreach ($imgs as $rel) {
        $path = realpath(__DIR__ . '/../' . ltrim($rel, '/'));
        if ($path && is_file($path)) {
            @unlink($path);
        }
    }
}

// TODO: ако имаш други зависими записи (напр. ratings, project_status), чистиш тук.

// изтрий самата обява
$del = $conn->prepare("DELETE FROM jobs WHERE id = :id AND user_id = :uid");
$del->execute([':id' => $job_id, ':uid' => $user_id]);

header('Location: profil.php');
exit;
