<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    exit('Не сте влезли.');
}

$user_id = (int)$_SESSION['user']['id'];

$title       = trim($_POST['title'] ?? '');
$profession  = trim($_POST['profession'] ?? '');
$city        = trim($_POST['city'] ?? '');
$location    = trim($_POST['location'] ?? '');
$start_date  = $_POST['start_date'] ?? null;
$end_date    = $_POST['end_date'] ?? null;
$description = trim($_POST['description'] ?? '');
$cover_index = isset($_POST['cover_index']) ? (int)$_POST['cover_index'] : 0;

if ($title==='' || $profession==='' || $city==='') {
    exit('Моля, попълнете задължителните полета.');
}

/* подсигуряване на папка */
$baseDir = realpath(__DIR__ . '/../uploads');
$histDir = $baseDir . '/history';
if (!is_dir($histDir)) @mkdir($histDir, 0775, true);

$allowed = ['jpg','jpeg','png','webp','gif'];
$images = [];

if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $n = count($_FILES['images']['name']);
    for ($i=0; $i<$n; $i++) {
        if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
        $tmp  = $_FILES['images']['tmp_name'][$i];
        $name = $_FILES['images']['name'][$i];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) continue;

        // прост MIME check
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        if (strpos($mime,'image/') !== 0) continue;

        $newName = 'history_' . $user_id . '_' . time() . '_' . mt_rand(1000,999999) . '.' . $ext;
        $dest    = $histDir . '/' . $newName;
        if (move_uploaded_file($tmp, $dest)) {
            // относителен път спрямо корена на сайта
            $images[] = 'uploads/history/' . $newName;
        }
    }
}

$images_json = json_encode($images, JSON_UNESCAPED_SLASHES);

$stmt = $conn->prepare("
  INSERT INTO project_history
    (user_id, title, profession, city, location, start_date, end_date, description, images_json, cover_index, created_at)
  VALUES
    (:uid, :title, :profession, :city, :location, :start_date, :end_date, :description, :images_json, :cover_index, NOW())
");
$stmt->execute([
  ':uid' => $user_id,
  ':title' => $title,
  ':profession' => $profession,
  ':city' => $city,
  ':location' => $location,
  ':start_date' => $start_date ?: null,
  ':end_date' => $end_date ?: null,
  ':description' => $description,
  ':images_json' => $images_json,
  ':cover_index' => max(0, $cover_index),
]);

echo 'ok';
