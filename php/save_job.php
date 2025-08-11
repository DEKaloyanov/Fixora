<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

$job_type = $_POST['job_type'];
$profession = $_POST['profession'];
$price_per_day = !empty($_POST['price_per_day']) ? $_POST['price_per_day'] : null;
$price_per_square = !empty($_POST['price_per_square']) ? $_POST['price_per_square'] : null;
$description = $_POST['description'] ?? null;
$location = $_POST['location'] ?? null;
$city = $_POST['city'] ?? null;

$work_status = null;
$team_size = null;
$team_members = null;

if ($job_type === 'seek') {
    $work_status = $_POST['team_size'] > 1 ? 'team' : 'solo';
    $team_size = $_POST['team_size'];

    $team = [];
    for ($i = 1; $i <= $team_size; $i++) {
        $member = $_POST["team_member_$i"] ?? null;
        if ($member) $team[] = $member;
    }
    $team_members = json_encode($team);
}

// 📸 Обработка на снимките ПРЕДИ INSERT
$uploadedImages = [];

if (!empty($_FILES['images']['name'][0])) {
    $uploadDir = '../uploads/jobs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($_FILES['images']['name'] as $index => $name) {
        $tmp = $_FILES['images']['tmp_name'][$index];
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $newName = uniqid('job_') . "." . $extension;
        $targetFile = $uploadDir . $newName;

        if (move_uploaded_file($tmp, $targetFile)) {
            $uploadedImages[] = 'uploads/jobs/' . $newName; // относителен път
        }
    }
}

$imageJSON = json_encode($uploadedImages); // тук се генерира за базата

// ✅ Една, завършена INSERT заявка с всичко
$stmt = $conn->prepare("INSERT INTO jobs 
(user_id, job_type, profession, location, city, price_per_day, price_per_square, work_status, team_size, team_members, description, images)
VALUES
(:user_id, :job_type, :profession, :location, :city, :price_per_day, :price_per_square, :work_status, :team_size, :team_members, :description, :images)");

$stmt->execute([
    'user_id' => $user_id,
    'job_type' => $job_type,
    'profession' => $profession,
    'location' => $location,
    'city' => $city,
    'price_per_day' => $price_per_day,
    'price_per_square' => $price_per_square,
    'work_status' => $work_status,
    'team_size' => $team_size,
    'team_members' => $team_members,
    'description' => $description,
    'images' => $imageJSON
]);

$cover_index = isset($_POST['cover_index']) ? (int)$_POST['cover_index'] : 0;
// Файловете вече идват в желан ред (чрез DataTransfer).
// При запис в БД/файлова система маркирай като "корица" този с индекс $cover_index.
// Пример: ако имаш jobs_images таблица — за снимката с този индекс сложи is_cover = 1.


header("Location: profil.php");
exit;
